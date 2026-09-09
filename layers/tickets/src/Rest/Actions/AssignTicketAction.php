<?php

namespace Tickets\Rest\Actions;

use App\Models\User;
use Lomkit\Rest\Http\Requests\RestRequest;
use Tickets\Actions\AssignTicket;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

class AssignTicketAction extends TicketTransitionAction
{
    /**
     * @return array<string, mixed>
     */
    public function fields(RestRequest $request): array
    {
        return [
            'technician_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    protected function permission(): TicketPermission
    {
        return TicketPermission::Assign;
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function applyTo(Ticket $ticket, array $fields): void
    {
        app(AssignTicket::class)->handle($ticket, User::findOrFail($fields['technician_id']));
    }
}
