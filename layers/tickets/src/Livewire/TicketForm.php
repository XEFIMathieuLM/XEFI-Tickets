<?php

namespace Tickets\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Tickets\Actions\AssignTicket;
use Tickets\Actions\OpenTicket;
use Tickets\Actions\UpdateTicketDetails;
use Tickets\Enums\TicketPermission;
use Tickets\Enums\TicketPriority;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Models\Ticket;

#[Layout('tickets::layouts.app')]
class TicketForm extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public ?int $ticketId = null;

    public string $title = '';

    public string $description = '';

    public string $priority = '';

    public ?string $successMessage = null;

    public function mount(?Ticket $ticket = null): void
    {
        if ($ticket?->exists !== true) {
            $this->authorize('create', Ticket::class);

            return;
        }

        $this->authorize('view', $ticket);

        $this->ticketId = $ticket->getKey();
        $this->title = $ticket->title;
        $this->description = $ticket->description;
        $this->priority = $ticket->priority->value;
    }

    /**
     * The only place the shape of a ticket is described. The priority is
     * checked against the enum itself, never against a copied list.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'title' => __('tickets::form.fields.title'),
            'description' => __('tickets::form.fields.description'),
            'priority' => __('tickets::form.fields.priority'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $priority = TicketPriority::from($this->priority);
        $ticket = $this->ticket();

        if ($ticket === null) {
            $this->authorize('create', Ticket::class);

            $ticket = app(OpenTicket::class)->handle(
                $this->currentUser(), $this->title, $this->description, $priority,
            );

            $this->ticketId = $ticket->getKey();
            $this->successMessage = __('tickets::form.feedback.opened');

            return;
        }

        $this->authorize('update', $ticket);

        app(UpdateTicketDetails::class)->handle($ticket, $this->title, $this->description, $priority);

        $this->successMessage = __('tickets::form.feedback.updated');
    }

    /**
     * The component owns no transition rule. It calls the action and turns the
     * one business exception it expects into a message the user can read.
     */
    public function assign(int $technicianId): void
    {
        $ticket = $this->ticket();

        if ($ticket === null) {
            return;
        }

        $this->authorize('update', $ticket);
        $this->successMessage = null;

        try {
            app(AssignTicket::class)->handle($ticket, User::findOrFail($technicianId));
        } catch (IllegalTicketTransition $refusal) {
            $this->addError('transition', __('tickets::form.feedback.transition_refused', [
                'from' => __('tickets::status.'.$refusal->from()->value),
                'target' => __('tickets::status.'.$refusal->target()->value),
            ]));

            return;
        }

        $this->successMessage = __('tickets::form.feedback.assigned');
    }

    public function render(): View
    {
        return view('tickets::livewire.ticket-form', [
            'ticket' => $this->ticket(),
            'priorities' => TicketPriority::cases(),
            'technicians' => $this->technicians(),
        ]);
    }

    private function ticket(): ?Ticket
    {
        return $this->ticketId === null ? null : Ticket::find($this->ticketId);
    }

    /**
     * Candidates are picked by the permission that defines them, never by a
     * role name.
     *
     * @return Collection<int, User>
     */
    private function technicians(): Collection
    {
        return User::permission(TicketPermission::ViewAssigned->value)->orderBy('name')->get();
    }

    private function currentUser(): User
    {
        return User::findOrFail(auth()->id());
    }
}
