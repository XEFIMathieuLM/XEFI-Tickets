<?php

namespace Tickets\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Actions\OpenTicket;
use Tickets\Mcp\TicketPresenter;
use Tickets\Models\Ticket;

#[Description('Open a ticket on behalf of the signed-in account, through the same action the interface uses. The importance is not chosen here: the support team weighs the ticket once it has read it.')]
class OpenTicketTool extends Tool
{
    public function handle(Request $request): Response
    {
        $submitted = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $author = $request->user();

        if (! $author instanceof User || $author->cannot('create', Ticket::class)) {
            return Response::error(__('tickets::mcp.errors.may_not_open'));
        }

        $ticket = app(OpenTicket::class)->handle(
            $author,
            $submitted['title'],
            $submitted['description'],
        );

        return Response::json(app(TicketPresenter::class)->toArray($ticket));
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required()->max(255)->description('Short summary of the problem.'),
            'description' => $schema->string()->required()->description('What happens, and since when.'),
        ];
    }
}
