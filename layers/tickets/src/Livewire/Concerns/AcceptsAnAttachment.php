<?php

namespace Tickets\Livewire\Concerns;

use App\Models\User;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Models\Ticket;

/**
 * What a screen needs to take a file from the reader. The ceiling and the
 * allow list are read from the action that stores it, so the rule and the
 * domain cannot disagree.
 */
trait AcceptsAnAttachment
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $upload = null;

    /**
     * The constraints every upload answers to. A screen prepends whether it
     * insists on a file or merely accepts one.
     *
     * @return array<int, string>
     */
    protected function attachmentRules(): array
    {
        return [
            'file',
            sprintf('max:%d', AttachFileToTicket::MAX_SIZE_IN_KILOBYTES),
            sprintf('extensions:%s', implode(',', AttachFileToTicket::ALLOWED_EXTENSIONS)),
        ];
    }

    protected function storeTheUpload(Ticket $ticket, User $uploader): void
    {
        if ($this->upload === null) {
            return;
        }

        app(AttachFileToTicket::class)->handle($ticket, $uploader, $this->upload);

        $this->reset('upload');
    }
}
