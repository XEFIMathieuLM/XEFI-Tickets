<?php

namespace Tickets\Livewire;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;
use Tickets\Enums\TicketTransition;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Models\Ticket;

class TicketTransitions extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public int $ticketId;

    public ?string $successMessage = null;

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);

        $this->ticketId = $ticket->getKey();
    }

    public function moveTo(string $status): void
    {
        $destination = TicketTransition::tryFrom($status);

        abort_if($destination === null, 403);

        $ticket = $this->ticket();

        $this->authorize('update', $ticket);
        Gate::authorize($destination->permission()->value);

        $this->successMessage = null;

        app($destination->action())->handle($ticket);

        $this->successMessage = __('tickets::transition.feedback.applied', [
            'status' => __($destination->status()->translationKey()),
        ]);
    }

    /**
     * Livewire hands the component any error raised during an action. Only the
     * refusal this screen can explain is turned into a message.
     */
    public function exception(Throwable $e, Closure $stopPropagation): void
    {
        if (! $e instanceof IllegalTicketTransition) {
            return;
        }

        $this->addError('transition', __('tickets::transition.feedback.refused', [
            'from' => __($e->from()->translationKey()),
            'target' => __($e->target()->translationKey()),
        ]));

        $stopPropagation();
    }

    public function render(): View
    {
        return view('tickets::livewire.ticket-transitions', [
            'destinations' => $this->offered(),
        ]);
    }

    /**
     * The destinations this account may send the ticket to.
     *
     * @return array<int, TicketTransition>
     */
    private function offered(): array
    {
        return array_values(array_filter(
            TicketTransition::reachableFrom($this->ticket()->status),
            fn (TicketTransition $destination): bool => Gate::allows($destination->permission()->value),
        ));
    }

    private function ticket(): Ticket
    {
        return Ticket::findOrFail($this->ticketId);
    }
}
