<?php

namespace Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Tickets\Exceptions\ChangeLogIsImmutable;

/**
 * One line of the journal. Written once, read afterwards, never edited.
 *
 * @property string $attribute
 */
class ChangeLog extends Model
{
    use Prunable;

    public const UPDATED_AT = null;

    public const RETENTION_DAYS = 365;

    protected $table = 'ticket_change_logs';

    protected static function booted(): void
    {
        static::updating(fn () => throw ChangeLogIsImmutable::create());
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(self::RETENTION_DAYS));
    }
}
