<?php

namespace Tickets\Access\Controls;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lomkit\Access\Controls\Control;
use Lomkit\Access\Perimeters\Perimeter;
use Tickets\Access\Perimeters\AllTicketsPerimeter;
use Tickets\Access\Perimeters\AssignedTicketsPerimeter;
use Tickets\Access\Perimeters\OwnTicketsPerimeter;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

/**
 * Every perimeter answers the same two questions: whom it applies to, decided
 * on permissions only, and what it lets through, expressed as a constraint the
 * database evaluates. No perimeter ever looks at a role name.
 *
 * Order matters. AllTicketsPerimeter is not an overlay, so a user who may see
 * everything short-circuits the two narrow perimeters; those two are overlays
 * and combine with OR, so a user holding both sees the union.
 */
class TicketControl extends Control
{
    /**
     * The model the control refers to.
     *
     * @var class-string<Model>
     */
    protected string $model = Ticket::class;

    /**
     * @return array<int, Perimeter>
     */
    protected function perimeters(): array
    {
        return [
            AllTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view' => $user->can(TicketPermission::ViewAll->value),
                    'update', 'delete' => $user->can(TicketPermission::Assign->value)
                        || $user->can(TicketPermission::Close->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => true)
                ->query(fn (Builder $query, User $user): Builder => $query),

            AssignedTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view' => $user->can(TicketPermission::ViewAssigned->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => $ticket->assigned_technician_id === $user->id)
                ->query(fn (Builder $query, User $user): Builder => $query->where('assigned_technician_id', $user->id)),

            OwnTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view' => $user->can(TicketPermission::ViewOwn->value),
                    'create' => $user->can(TicketPermission::Create->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => $ticket->requester_id === $user->id)
                ->query(fn (Builder $query, User $user): Builder => $query->where('requester_id', $user->id)),
        ];
    }
}
