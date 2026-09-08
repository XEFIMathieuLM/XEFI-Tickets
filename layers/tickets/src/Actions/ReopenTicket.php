<?php

namespace Tickets\Actions;

use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Resolved → InProgress. The resolution did not hold, so its date and its
 * delay verdict are cleared with it.
 */
class ReopenTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::InProgress);

        $ticket->resolved_at = null;
        $ticket->is_resolved_on_time = null;
        $ticket->save();

        return $ticket;
    }
}
