<?php

namespace Tickets\Rest\Resources;

use Illuminate\Database\Eloquent\Model;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\Relation;
use Tickets\Models\Comment;

class CommentResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Comment::class;

    /**
     * @return array<int, string>
     */
    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'body',
            'created_at',
        ];
    }

    /**
     * @return array<int, Relation>
     */
    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('author', UserResource::class),
            BelongsTo::make('ticket', TicketResource::class),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(RestRequest $request): array
    {
        return [
            'body' => ['string'],
        ];
    }
}
