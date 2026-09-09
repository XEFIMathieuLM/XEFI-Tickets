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
