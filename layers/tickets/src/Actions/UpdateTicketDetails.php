<?php

namespace Tickets\Actions;

use Tickets\Enums\TicketPriority;
use Tickets\Models\Ticket;

/**
 * Edits what the requester wrote. The lifecycle columns are untouched here:
 * a status only ever moves through a transition action, and a caller who may
 * not weigh the ticket passes no priority, which leaves it where it stands.
 */
class UpdateTicketDetails
{
    public function handle(
        Ticket $ticket,
        string $title,
        string $description,
        ?TicketPriority $priority = null,
    ): Ticket {
        $ticket->fill([
            'title' => $title,
            'description' => $description,
            'priority' => $priority ?? $ticket->priority,
        ]);

        $ticket->save();

        return $ticket;
    }
}
