<?php

namespace Tickets\Actions;

use App\Models\User;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;

/**
 * Opens a new ticket. Not a transition: this is where a ticket enters the
 * lifecycle, always at Open.
 */
class OpenTicket
{
    public function handle(
        User $requester,
        string $title,
        string $description,
        TicketPriority $priority,
    ): Ticket {
        return Ticket::create([
            'requester_id' => $requester->getKey(),
            'title' => $title,
            'description' => $description,
            'status' => TicketStatus::Open,
            'priority' => $priority,
        ]);
    }
}
