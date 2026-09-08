<?php

namespace Tickets\Actions;

use App\Models\User;
use Tickets\Actions\Concerns\TransitionsTicket;
use Tickets\Enums\TicketStatus;
use Tickets\Events\TicketAssigned;
use Tickets\Models\Ticket;

/**
 * Open → Assigned. Hands an open ticket to a technician.
 */
class AssignTicket
{
    use TransitionsTicket;

    public function handle(Ticket $ticket, User $technician): Ticket
    {
        $this->moveTo($ticket, TicketStatus::Assigned);

        $ticket->assigned_technician_id = $technician->getKey();
        $ticket->save();

        TicketAssigned::dispatch($ticket, $technician);

        return $ticket;
    }
}
