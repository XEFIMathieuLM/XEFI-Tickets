<?php

namespace Tickets\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Tickets\Access\Controls\TicketControl;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Mcp\TicketPresenter;
use Tickets\Models\Ticket;

#[Description('List the tickets the signed-in account is allowed to see, optionally narrowed by status or priority.')]
class SearchTicketsTool extends Tool
{
    private const DEFAULT_LIMIT = 25;

    public function handle(Request $request): Response
    {
        $criteria = $request->validate([
            'status' => ['nullable', Rule::enum(TicketStatus::class)],
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $presenter = app(TicketPresenter::class);

        $tickets = app(TicketControl::class)
            ->forCurrentUser(Ticket::query())
            ->when(
                isset($criteria['status']),
                fn (Builder $query) => $query->where('status', $criteria['status']),
            )
            ->when(
                isset($criteria['priority']),
                fn (Builder $query) => $query->where('priority', $criteria['priority']),
            )
            ->latest()
            ->limit($criteria['limit'] ?? self::DEFAULT_LIMIT)
            ->get();

        return Response::json($tickets->map($presenter->toArray(...))->all());
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->enum(array_column(TicketStatus::cases(), 'value'))
                ->description('Keep only the tickets in this status.'),
            'priority' => $schema->string()
                ->enum(array_column(TicketPriority::cases(), 'value'))
                ->description('Keep only the tickets at this priority.'),
            'limit' => $schema->integer()
                ->min(1)
                ->max(50)
                ->description('How many tickets to return, 25 by default.'),
        ];
    }
}
