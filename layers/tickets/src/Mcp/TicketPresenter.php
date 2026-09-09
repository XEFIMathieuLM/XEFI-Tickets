<?php

namespace Tickets\Mcp;

use Tickets\Models\Ticket;

/**
 * The shape an agent reads. Declared once so the tools agree with each other.
 */
class TicketPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Ticket $ticket): array
    {
        return [
            'id' => $ticket->getKey(),
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status->value,
            'priority' => $ticket->priority->value,
            'target_handling_hours' => $ticket->priority->targetHandlingHours(),
            'opened_at' => $ticket->created_at?->toIso8601String(),
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
        ];
    }
}
