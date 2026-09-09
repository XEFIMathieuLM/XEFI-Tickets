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
 * Order matters: the first perimeter is not an overlay and short-circuits the
 * two others, which combine with OR. Everything it grants therefore rests on
 * seeing every ticket, or it would hand the whole base to whoever holds one of
 * the acting permissions.
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
     * Applies the perimeters to a query for whoever is signed in. A caller
     * with no identity is given nothing, which is the safe default.
     *
     * @param  Builder<Ticket>  $query
     * @return Builder<Ticket>
     */
    public function forCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user instanceof Model) {
            return $this->noResultQuery($query);
        }

        return $this->queried($query, $user);
    }

    /**
     * @return array<int, Perimeter>
     */
    protected function perimeters(): array
    {
        return [
            AllTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view', 'update', 'delete' => $user->can(TicketPermission::ViewAll->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => true)
                ->query(fn (Builder $query, User $user): Builder => $query),

            AssignedTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view' => $user->can(TicketPermission::ViewAssigned->value),
                    'update' => $user->can(TicketPermission::Handle->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => $ticket->assigned_technician_id === $user->id)
                ->query(fn (Builder $query, User $user): Builder => $query->where('assigned_technician_id', $user->id)),

            OwnTicketsPerimeter::new()
                ->allowed(fn (User $user, string $method): bool => match ($method) {
                    'view' => $user->can(TicketPermission::ViewOwn->value),
                    'create' => $user->can(TicketPermission::Create->value),
                    'update' => $user->can(TicketPermission::Close->value),
                    default => false,
                })
                ->should(fn (User $user, Ticket $ticket): bool => $ticket->requester_id === $user->id)
                ->query(fn (Builder $query, User $user): Builder => $query->where('requester_id', $user->id)),
        ];
    }
}
