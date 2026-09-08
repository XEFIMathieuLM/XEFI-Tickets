<?php

namespace Tickets\Rest\Resources;

use Illuminate\Database\Eloquent\Model;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\Relation;
use Tickets\Models\Attachment;

class AttachmentResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Attachment::class;

    /**
     * What the client is told about a file. The disk and the path stay inside
     * the layer: they say where the bytes live, which is nobody else's business.
     *
     * @return array<int, string>
     */
    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'original_name',
            'mime_type',
            'size_in_bytes',
            'created_at',
        ];
    }

    /**
     * @return array<int, Relation>
     */
    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('uploadedBy', UserResource::class),
        ];
    }
}
