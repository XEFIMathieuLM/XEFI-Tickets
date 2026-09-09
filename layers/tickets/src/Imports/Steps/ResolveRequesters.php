<?php

namespace Tickets\Imports\Steps;

use App\Models\User;
use Closure;
use Tickets\Imports\ImportBatch;

/**
 * Resolves every requester in one query, whatever the file holds: a thousand
 * lines ask the database once, not a thousand times.
 */
class ResolveRequesters
{
    public function handle(ImportBatch $batch, Closure $next): ImportBatch
    {
        $wanted = array_unique(array_column($batch->rows, 'requester_email'));

        if ($wanted !== []) {
            $batch->requesterIdsByEmail = User::query()
                ->whereIn('email', $wanted)
                ->pluck('id', 'email')
                ->all();
        }

        foreach ($batch->rows as $line => $row) {
            if (! array_key_exists($row['requester_email'], $batch->requesterIdsByEmail)) {
                $batch->reject($line, __('tickets::import.rejection.unknown_requester', [
                    'email' => $row['requester_email'],
                ]));
            }
        }

        return $next($batch);
    }
}
