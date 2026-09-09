<?php

namespace Tickets\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use ReflectionClass;
use Tickets\Models\Attributes\TracksChanges;
use Tickets\Models\ChangeLog;

/**
 * Journals the columns the model itself declares through TracksChanges. The
 * model opts in; nothing watches it from the outside.
 */
trait RecordsChanges
{
    /**
     * @var array<class-string, array<int, string>>
     */
    private static array $trackedColumnsPerModel = [];

    public static function bootRecordsChanges(): void
    {
        static::updated(function (self $saved): void {
            $saved->journalTrackedChanges();
        });
    }

    /**
     * @return MorphMany<ChangeLog, $this>
     */
    public function changeLogs(): MorphMany
    {
        return $this->morphMany(ChangeLog::class, 'loggable');
    }

    private function journalTrackedChanges(): void
    {
        $entries = [];
        $writtenAt = now();

        foreach ($this->getChanges() as $column => $newValue) {
            if (! in_array($column, $this->trackedColumns(), true)) {
                continue;
            }

            $entries[] = [
                'loggable_type' => $this->getMorphClass(),
                'loggable_id' => $this->getKey(),
                'attribute' => $column,
                'old_value' => $this->stringifyValue($this->getRawOriginal($column)),
                'new_value' => $this->stringifyValue($newValue),
                'author_id' => auth()->id(),
                'created_at' => $writtenAt,
            ];
        }

        if ($entries !== []) {
            ChangeLog::insert($entries);
        }
    }

    /**
     * @return array<int, string>
     */
    private function trackedColumns(): array
    {
        return self::$trackedColumnsPerModel[static::class] ??= $this->readDeclaredColumns();
    }

    /**
     * @return array<int, string>
     */
    private function readDeclaredColumns(): array
    {
        $declaration = (new ReflectionClass(static::class))->getAttributes(TracksChanges::class)[0] ?? null;

        return $declaration === null ? [] : $declaration->newInstance()->columns;
    }

    private function stringifyValue(mixed $stored): ?string
    {
        return $stored === null ? null : (string) $stored;
    }
}
