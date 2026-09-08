<?php

namespace Tickets\Actions;

use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Assigned → Open. Takes the ticket back from its technician.
 */
class UnassignTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::Open);

        $ticket->assigned_technician_id = null;
        $ticket->save();

        return $ticket;
    }
}
