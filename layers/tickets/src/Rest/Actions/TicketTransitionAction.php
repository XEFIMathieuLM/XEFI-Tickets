<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Lomkit\Rest\Actions\Action;
use Tickets\Enums\TicketPermission;
use Tickets\Models\Ticket;

/**
 * Exposes one business transition over the API. Which tickets the caller may
 * touch comes from the perimeters; which moves it may make comes from the
 * permission the action names.
 */
abstract class TicketTransitionAction extends Action
{
    /**
     * The caller must name the tickets it moves. A forgotten search block is a
     * 422, never a transition applied to every ticket.
     *
     * @var bool
     */
    public $targeted = true;

    /**
     * @param  array<string, mixed>  $fields
     * @param  Collection<int, Ticket>  $models
     */
    public function handle(array $fields, Collection $models): void
    {
        Gate::authorize($this->permission()->value);

        foreach ($models as $ticket) {
            $this->applyTo($ticket, $fields);
        }
    }

    abstract protected function permission(): TicketPermission;

    public function uriKey(): string
    {
        return Str::kebab(Str::before(class_basename($this), 'Action'));
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    abstract protected function applyTo(Ticket $ticket, array $fields): void;
}
