<?php

namespace Tickets\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Access\Controls\TicketControl;
use Tickets\Mcp\TicketPresenter;
use Tickets\Models\Ticket;

#[Description('Read one ticket by its identifier, provided the signed-in account may see it.')]
class ShowTicketTool extends Tool
{
    public function handle(Request $request): Response
    {
        $asked = $request->validate([
            'ticket_id' => ['required', 'integer'],
        ]);

        $ticket = app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->whereKey($asked['ticket_id'])
            ->first();

        if ($ticket === null) {
            return Response::error(__('tickets::mcp.errors.out_of_reach', ['id' => $asked['ticket_id']]));
        }

        return Response::json(app(TicketPresenter::class)->toArray($ticket));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()
                ->required()
                ->description('Identifier of the ticket to read.'),
        ];
    }
}
