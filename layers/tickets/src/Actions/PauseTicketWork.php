<?php

namespace Tickets\Actions;

use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * InProgress → Assigned. Work stops, the ticket stays with its technician.
 */
class PauseTicketWork
{
    use TransitionsTicket;

    public function handle(Ticket $ticket): Ticket
    {
        $this->moveTo($ticket, TicketStatus::Assigned);
        $ticket->save();

        return $ticket;
    }
}
