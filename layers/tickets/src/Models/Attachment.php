<?php

namespace Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tickets\Database\Factories\AttachmentFactory;
use Tickets\Policies\AttachmentPolicy;

/**
 * A file kept alongside a ticket. The row records where the bytes are, never
 * the bytes themselves.
 *
 * @property int $size_in_bytes
 */
#[Table('ticket_attachments')]
#[Fillable(['ticket_id', 'uploaded_by_id', 'disk', 'path', 'original_name', 'mime_type', 'size_in_bytes'])]
#[UseFactory(AttachmentFactory::class)]
#[UsePolicy(AttachmentPolicy::class)]
class Attachment extends Model
{
    /** @use HasFactory<AttachmentFactory> */
    use HasFactory;

    /**
     * The largest upload the interface accepts, in kilobytes. Declared once and
     * read by the validation rule, so the limit is never a loose number.
     */
    public const MAX_SIZE_IN_KILOBYTES = 5120;

    /**
     * What a ticket may carry. An allow list, so a file type nobody thought
     * about is refused rather than accepted by default.
     *
     * @var array<int, string>
     */
    public const ALLOWED_EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'csv', 'log', 'zip'];

    /**
     * What the row records when the upload declares no usable type.
     */
    public const UNKNOWN_MIME_TYPE = 'application/octet-stream';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_in_bytes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
