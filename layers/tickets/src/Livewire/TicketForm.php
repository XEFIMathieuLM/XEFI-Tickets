<?php

namespace Tickets\Livewire;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;
use Tickets\Actions\AssignTicket;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Actions\OpenTicket;
use Tickets\Actions\UpdateTicketDetails;
use Tickets\Enums\TicketPermission;
use Tickets\Enums\TicketPriority;
use Tickets\Exceptions\IllegalTicketTransition;
use Tickets\Exceptions\TicketIsClosed;
use Tickets\Livewire\Concerns\AcceptsAnAttachment;
use Tickets\Models\Ticket;

class TicketForm extends Component
{
    use AcceptsAnAttachment;
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
     * The only place the shape of a ticket is described.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ];

        if ($this->maySetPriority()) {
            $rules['priority'] = ['required', Rule::enum(TicketPriority::class)];
        }

        if ($this->ticketId === null) {
            $rules['upload'] = array_merge(['nullable'], $this->attachmentRules());
        }

        return $rules;
    }

    /**
     * Whoever opens a ticket does not weigh it; the support team does.
     */
    public function maySetPriority(): bool
    {
        return Gate::allows(TicketPermission::Handle->value);
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

        $priority = $this->maySetPriority() ? TicketPriority::from($this->priority) : null;
        $ticket = $this->ticket();

        if ($ticket === null) {
            $this->authorize('create', Ticket::class);

            $author = $this->currentUser();

            $opened = app(OpenTicket::class)->handle(
                $author, $this->title, $this->description, $priority,
            );

            $this->storeTheUpload($opened, $author);

            $this->ticketId = $opened->getKey();
            $this->successMessage = __('tickets::form.feedback.opened');

            return;
        }

        $this->authorize('update', $ticket);

        app(UpdateTicketDetails::class)->handle($ticket, $this->title, $this->description, $priority);

        $this->successMessage = __('tickets::form.feedback.updated');
    }

    public function assign(int $technicianId): void
    {
        $ticket = $this->ticket();

        if ($ticket === null) {
            return;
        }

        $this->authorize('update', $ticket);
        Gate::authorize(TicketPermission::Assign->value);

        $this->successMessage = null;

        app(AssignTicket::class)->handle($ticket, User::findOrFail($technicianId));

        $this->successMessage = __('tickets::form.feedback.assigned');
    }

    /**
     * Livewire hands the component any error raised during an action. Only the
     * refusal this screen can explain is turned into a message; the rest keeps
     * travelling untouched.
     */
    public function exception(Throwable $e, Closure $stopPropagation): void
    {
        if ($e instanceof TicketIsClosed) {
            $this->addError('transition', __('tickets::form.feedback.ticket_is_closed'));

            $stopPropagation();

            return;
        }

        if (! $e instanceof IllegalTicketTransition) {
            return;
        }

        $this->addError('transition', __('tickets::form.feedback.transition_refused', [
            'from' => __($e->from()->translationKey()),
            'target' => __($e->target()->translationKey()),
        ]));

        $stopPropagation();
    }

    public function render(): View
    {
        return view('tickets::livewire.ticket-form', [
            'ticket' => $this->ticket(),
            'priorities' => TicketPriority::cases(),
            'maySetPriority' => $this->maySetPriority(),
            'maxSizeInKilobytes' => AttachFileToTicket::MAX_SIZE_IN_KILOBYTES,
            'technicians' => $this->technicians(),
        ]);
    }

    private function ticket(): ?Ticket
    {
        return $this->ticketId === null ? null : Ticket::find($this->ticketId);
    }

    /**
     * Candidates come from the permission that defines them, not a role name.
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
