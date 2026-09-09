<?php

namespace Tickets\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Resource;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;

/**
 * Tells the agent what a ticket accepts, so it fills the fields instead of
 * guessing them. The values come from the enums, never from a copied list.
 */
#[Description('The rules a ticket must satisfy: fields, accepted priorities, statuses and their legal moves.')]
class TicketInputRules extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::json([
            'fields' => [
                'title' => 'required, at most 255 characters',
                'description' => 'required',
                'priority' => 'required, one of the priorities below',
            ],
            'priorities' => $this->priorities(),
            'statuses' => $this->statuses(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function priorities(): array
    {
        return array_map(
            fn (TicketPriority $priority): array => [
                'value' => $priority->value,
                'target_handling_hours' => $priority->targetHandlingHours(),
            ],
            TicketPriority::cases(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statuses(): array
    {
        return array_map(
            fn (TicketStatus $status): array => [
                'value' => $status->value,
                'may_move_to' => array_column($status->allowedTransitions(), 'value'),
            ],
            TicketStatus::cases(),
        );
    }
}
