<?php

namespace Tickets\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Access\Controls\TicketControl;
use Tickets\Actions\AssignTicket;
use Tickets\Mcp\TicketPresenter;
use Tickets\Models\Ticket;

/**
 * The transition rule is nowhere in here: the action refuses an illegal move by
 * throwing, and the protocol turns that into an error the agent can read.
 */
#[Description('Hand an open ticket to a technician, through the same action the interface uses.')]
class AssignTicketTool extends Tool
{
    public function handle(Request $request): Response
    {
        $asked = $request->validate([
            'ticket_id' => ['required', 'integer'],
            'technician_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $ticket = app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->whereKey($asked['ticket_id'])
            ->first();

        if ($ticket === null) {
            return Response::error(__('tickets::mcp.errors.out_of_reach', ['id' => $asked['ticket_id']]));
        }

        if ($request->user()?->cannot('update', $ticket) ?? true) {
            return Response::error(__('tickets::mcp.errors.may_not_assign'));
        }

        $assigned = app(AssignTicket::class)->handle($ticket, User::findOrFail($asked['technician_id']));

        return Response::json(app(TicketPresenter::class)->toArray($assigned));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ticket_id' => $schema->integer()->required()->description('Ticket to hand over.'),
            'technician_id' => $schema->integer()->required()->description('Account that will take it.'),
        ];
    }
}
