<?php

namespace Tickets\Imports\Steps;

use Closure;
use Illuminate\Support\Facades\DB;
use Tickets\Enums\TicketStatus;
use Tickets\Imports\ImportBatch;
use Tickets\Models\Ticket;

/**
 * Writes the rows that survived, in one insert inside one transaction: the
 * accepted part of a file lands whole or not at all.
 */
class CreateTickets
{
    public function handle(ImportBatch $batch, Closure $next): ImportBatch
    {
        if ($batch->rows === []) {
            return $next($batch);
        }

        $writtenAt = now();
        $newTickets = [];

        foreach ($batch->rows as $row) {
            $newTickets[] = [
                'requester_id' => $batch->requesterIdsByEmail[$row['requester_email']],
                'title' => $row['title'],
                'description' => $row['description'],
                'status' => TicketStatus::Open->value,
                'priority' => $row['priority'],
                'created_at' => $writtenAt,
                'updated_at' => $writtenAt,
            ];
        }

        DB::transaction(function () use ($newTickets, $batch): void {
            Ticket::insert($newTickets);

            $batch->created = count($newTickets);
        });

        return $next($batch);
    }
}
