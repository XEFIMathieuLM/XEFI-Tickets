<?php

namespace Tickets\Actions;

use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Resolved → Closed. The last move a ticket makes.
 */
class CloseTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::Closed);
        $ticket->save();

        return $ticket;
    }
}
