<?php

namespace Tickets\Actions;

use App\Models\User;
use Tickets\Events\TicketAssigned;
use Tickets\Exceptions\TicketIsClosed;
use Tickets\Models\Ticket;

/**
 * Hands a ticket to a technician. Holding a ticket is not a step of the
 * lifecycle, so the status stays where the support team put it. A closed
 * ticket is handed to nobody.
 */
class AssignTicket
{
    public function handle(Ticket $ticket, User $technician): Ticket
    {
        if ($ticket->status->isTerminal()) {
            throw TicketIsClosed::already();
        }

        $ticket->assigned_technician_id = $technician->getKey();
        $ticket->save();

        TicketAssigned::dispatch($ticket, $technician);

        return $ticket;
    }
}
