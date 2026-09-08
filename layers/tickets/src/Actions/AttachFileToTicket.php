<?php

namespace Tickets\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tickets\Exceptions\AttachmentStorageFailed;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

/**
 * Puts the bytes on a disk and records where they went. The disk is private:
 * nothing under it is served directly by the web server.
 */
class AttachFileToTicket
{
    public const DISK = 'local';

    private const DIRECTORY = 'ticket-attachments';

    public function handle(Ticket $ticket, User $uploader, UploadedFile $file): Attachment
    {
        $path = $file->store(self::DIRECTORY.'/'.$ticket->getKey(), self::DISK);

        if ($path === false) {
            throw AttachmentStorageFailed::forTicket($ticket);
        }

        return Attachment::create([
            'ticket_id' => $ticket->getKey(),
            'uploaded_by_id' => $uploader->getKey(),
            'disk' => self::DISK,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? Attachment::UNKNOWN_MIME_TYPE,
            'size_in_bytes' => $file->getSize(),
        ]);
    }
}
