<?php

namespace Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Lomkit\Access\Controls\HasControl;
use Tickets\Actions\RemoveTicketDependents;
use Tickets\Database\Factories\TicketFactory;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Attributes\TracksChanges;
use Tickets\Models\Concerns\RecordsChanges;
use Tickets\Policies\TicketPolicy;

/**
 * @property TicketStatus $status
 * @property TicketPriority $priority
 * @property Carbon|null $resolved_at
 * @property Carbon|null $escalated_at
 */
#[Fillable(['requester_id', 'assigned_technician_id', 'title', 'description', 'status', 'priority', 'resolved_at'])]
#[UseFactory(TicketFactory::class)]
#[UsePolicy(TicketPolicy::class)]
#[TracksChanges(['status', 'priority', 'assigned_technician_id'])]
class Ticket extends Model
{
    use HasControl;

    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    use Prunable;
    use RecordsChanges;
    use SoftDeletes;

    /**
     * How long a soft-deleted ticket is retained before the scheduled purge removes it.
     */
    public const RETENTION_DAYS = 90;

    /**
     * Whoever opens a ticket does not weigh it, so every ticket enters the
     * lifecycle at the same importance until the support team says otherwise.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'priority' => TicketPriority::Normal->value,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'resolved_at' => 'datetime',
            'escalated_at' => 'datetime',
            'is_resolved_on_time' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Retention policy: soft-deleted tickets are purged once they exceed RETENTION_DAYS.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(self::RETENTION_DAYS));
    }

    /**
     * Framework hook fired just before the purge destroys the row. The work
     * itself belongs to an action, not here.
     */
    protected function pruning(): void
    {
        app(RemoveTicketDependents::class)->handle($this);
    }
}
