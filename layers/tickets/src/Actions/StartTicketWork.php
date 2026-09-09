<?php

namespace Tickets\Actions;

use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Contracts\MovesTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Assigned → InProgress. The technician starts working on the ticket.
 */
class StartTicketWork implements MovesTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::InProgress);
        $ticket->save();

        return $ticket;
    }
}
