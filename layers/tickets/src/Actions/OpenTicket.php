<?php

namespace Tickets\Actions;

use App\Models\User;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Opens a new ticket. Not a transition: this is where a ticket enters the
 * lifecycle, always at Open. A caller who may not weigh the ticket passes no
 * priority, and the model default applies.
 */
class OpenTicket
{
    public function handle(
        User $requester,
        string $title,
        string $description,
        ?TicketPriority $priority = null,
    ): Ticket {
        $attributes = [
            'requester_id' => $requester->getKey(),
            'title' => $title,
            'description' => $description,
            'status' => TicketStatus::Open,
        ];

        if ($priority !== null) {
            $attributes['priority'] = $priority;
        }

        return Ticket::create($attributes);
    }
}
