<?php

namespace Tickets\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tickets\Database\Factories\CommentFactory;
use Tickets\Models\Attributes\TracksChanges;
use Tickets\Models\Concerns\RecordsChanges;
use Tickets\Policies\CommentPolicy;

#[Fillable(['ticket_id', 'author_id', 'body'])]
#[UseFactory(CommentFactory::class)]
#[UsePolicy(CommentPolicy::class)]
#[TracksChanges(['body'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    use RecordsChanges;

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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
