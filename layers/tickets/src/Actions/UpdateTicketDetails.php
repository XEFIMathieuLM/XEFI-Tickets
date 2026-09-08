<?php

namespace Tickets\Actions;

use Tickets\Enums\TicketPriority;
use Tickets\Models\Ticket;

/**
 * Edits what the requester wrote. The lifecycle columns are untouched here:
 * a status only ever moves through a transition action.
 */
class UpdateTicketDetails
{
    public function handle(
        Ticket $ticket,
        string $title,
        string $description,
        TicketPriority $priority,
    ): Ticket {
        $ticket->fill([
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
        ]);

        $ticket->save();

        return $ticket;
    }
}
