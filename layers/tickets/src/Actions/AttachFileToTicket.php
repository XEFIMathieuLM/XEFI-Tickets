<?php

namespace Tickets\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tickets\Exceptions\AttachmentStorageFailed;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

/**
 * Puts the bytes on a private disk and records where they went.
 */
class AttachFileToTicket
{
    public const DISK = 'local';

    public const MAX_SIZE_IN_KILOBYTES = 5120;

    /**
     * @var array<int, string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'csv', 'log', 'zip'];

    public const UNKNOWN_MIME_TYPE = 'application/octet-stream';

    private const DIRECTORY = 'ticket-attachments';

    public function handle(Ticket $ticket, User $uploader, UploadedFile $file): Attachment
    {
        $path = $file->store(sprintf('%s/%s', self::DIRECTORY, $ticket->getKey()), self::DISK);

        if ($path === false) {
            throw AttachmentStorageFailed::forTicket($ticket);
        }

        return Attachment::create([
            'ticket_id' => $ticket->getKey(),
            'uploaded_by_id' => $uploader->getKey(),
            'disk' => self::DISK,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? self::UNKNOWN_MIME_TYPE,
            'size_in_bytes' => $file->getSize(),
        ]);
    }
}
