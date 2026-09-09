<?php

namespace Tickets\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Actions\RemoveTicketAttachment;
use Tickets\Livewire\Concerns\AcceptsAnAttachment;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

class TicketAttachments extends Component
{
    use AcceptsAnAttachment;
    use AuthorizesRequests;

    #[Locked]
    public int $ticketId;

    public ?string $successMessage = null;

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);

        $this->ticketId = $ticket->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return ['upload' => array_merge(['required'], $this->attachmentRules())];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ['upload' => __('tickets::attachments.fields.upload')];
    }

    public function attach(): void
    {
        $this->validate();

        $ticket = $this->ticket();
        $this->authorize('view', $ticket);

        $this->storeTheUpload($ticket, $this->currentUser());

        $this->successMessage = __('tickets::attachments.feedback.attached');
    }

    public function remove(int $attachmentId): void
    {
        $attachment = Attachment::findOrFail($attachmentId);
        $this->authorize('delete', $attachment);

        app(RemoveTicketAttachment::class)->handle($attachment);

        $this->successMessage = __('tickets::attachments.feedback.removed');
    }

    public function render(): View
    {
        return view('tickets::livewire.ticket-attachments', [
            'attachments' => $this->attachments(),
            'maxSizeInKilobytes' => AttachFileToTicket::MAX_SIZE_IN_KILOBYTES,
        ]);
    }

    private function ticket(): Ticket
    {
        return Ticket::findOrFail($this->ticketId);
    }

    /**
     * @return Collection<int, Attachment>
     */
    private function attachments(): Collection
    {
        return $this->ticket()->attachments()->with('uploadedBy')->latest()->get();
    }

    private function currentUser(): User
    {
        return User::findOrFail(auth()->id());
    }
}
