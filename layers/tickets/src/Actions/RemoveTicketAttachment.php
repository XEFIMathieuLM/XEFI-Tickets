<?php

namespace Tickets\Actions;

use Illuminate\Support\Facades\Storage;
use Tickets\Models\Attachment;

/**
 * Removes the row and the bytes together, in that order, so a failure never
 * leaves a row pointing at a file that is gone.
 */
class RemoveTicketAttachment
{
    public function handle(Attachment $attachment): void
    {
        $disk = $attachment->disk;
        $path = $attachment->path;

        $attachment->delete();

        Storage::disk($disk)->delete($path);
    }
}
