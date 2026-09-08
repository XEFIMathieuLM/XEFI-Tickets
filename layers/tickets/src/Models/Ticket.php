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
use Tickets\Policies\TicketPolicy;

/**
 * The columns whose runtime type comes from casts() rather than from the schema.
 *
 * @property TicketStatus $status
 * @property TicketPriority $priority
 * @property Carbon|null $resolved_at
 */
#[Fillable(['requester_id', 'assigned_technician_id', 'title', 'description', 'status', 'priority', 'resolved_at'])]
#[UseFactory(TicketFactory::class)]
#[UsePolicy(TicketPolicy::class)]
class Ticket extends Model
{
    use HasControl;

    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    use Prunable;
    use SoftDeletes;

    /**
     * How long a soft-deleted ticket is retained before the scheduled purge removes it.
     */
    public const RETENTION_DAYS = 90;

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
