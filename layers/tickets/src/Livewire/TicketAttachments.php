<?php

namespace Tickets\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Actions\RemoveTicketAttachment;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

class TicketAttachments extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public int $ticketId;

    public ?TemporaryUploadedFile $upload = null;

    public ?string $successMessage = null;

    public function mount(Ticket $ticket): void
    {
        $this->authorize('view', $ticket);

        $this->ticketId = $ticket->getKey();
    }

    /**
     * The size ceiling is read from the model, so the rule and the domain can
     * never disagree on what "too big" means.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'upload' => [
                'required',
                'file',
                'max:'.Attachment::MAX_SIZE_IN_KILOBYTES,
                'extensions:'.implode(',', Attachment::ALLOWED_EXTENSIONS),
            ],
        ];
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

        app(AttachFileToTicket::class)->handle($ticket, $this->currentUser(), $this->upload);

        $this->reset('upload');
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
        return Attachment::query()
            ->where('ticket_id', $this->ticketId)
            ->with('uploadedBy')
            ->latest()
            ->get();
    }

    private function currentUser(): User
    {
        return User::findOrFail(auth()->id());
    }
}
