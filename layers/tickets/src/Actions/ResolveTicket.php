<?php

namespace Tickets\Actions;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Jobs\EvaluateResolutionDelay;
use Tickets\Models\Ticket;

/**
 * InProgress → Resolved. Stamps the resolution date and asks for the delay
 * verdict, which the job may only compute once the row is committed.
 */
class ResolveTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::Resolved);

        DB::transaction(function () use ($ticket): void {
            $ticket->resolved_at = Carbon::now();
            $ticket->save();

            EvaluateResolutionDelay::dispatch($ticket->getKey())->afterCommit();
        });

        return $ticket;
    }
}
