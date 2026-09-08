<?php

namespace Tickets\Rest\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lomkit\Rest\Actions\Action;
use Tickets\Models\Ticket;

/**
 * Exposes one business transition over the API. The action itself holds no
 * rule: it hands each ticket to the matching action class, which refuses an
 * illegal move by throwing, and the exception carries the 409 on its own.
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
        foreach ($models as $ticket) {
            $this->applyTo($ticket, $fields);
        }
    }

    public function uriKey(): string
    {
        return Str::kebab(Str::before(class_basename($this), 'Action'));
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    abstract protected function applyTo(Ticket $ticket, array $fields): void;
}
