<?php

namespace Tickets\Rest\Resources;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Lomkit\Rest\Actions\Action;
use Lomkit\Rest\Http\Requests\RestRequest;
use Lomkit\Rest\Relations\BelongsTo;
use Lomkit\Rest\Relations\HasMany;
use Lomkit\Rest\Relations\Relation;
use Tickets\Access\Controls\TicketControl;
use Tickets\Enums\TicketPermission;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Rest\Actions\AssignTicketAction;
use Tickets\Rest\Actions\CloseTicketAction;
use Tickets\Rest\Actions\ResolveTicketAction;
use Tickets\Rest\Actions\StartTicketWorkAction;
use Tickets\Rest\Actions\UnassignTicketAction;

class TicketResource extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Ticket::class;

    /**
     * What the API exposes, and therefore what the client may select, filter and
     * sort on. Internal columns stay out: the foreign keys are reached through
     * the declared relations, and updated_at / deleted_at never leave the layer.
     *
     * @return array<int, string>
     */
    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'title',
            'description',
            'status',
            'priority',
            'created_at',
            'resolved_at',
        ];
    }

    /**
     * @return array<int, Relation>
     */
    public function relations(RestRequest $request): array
    {
        return [
            BelongsTo::make('requester', UserResource::class)
                ->requiredOnCreation(),
            BelongsTo::make('assignedTechnician', UserResource::class),
            HasMany::make('comments', CommentResource::class),
            HasMany::make('attachments', AttachmentResource::class),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(RestRequest $request): array
    {
        return [
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'status' => [Rule::enum(TicketStatus::class)],
            'priority' => [Rule::enum(TicketPriority::class)],
            'resolved_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function createRules(RestRequest $request): array
    {
        return [
            'title' => ['required'],
            'description' => ['required'],
            'status' => ['required'],
            'priority' => $this->priorityRules($request),
        ];
    }

    /**
     * The lifecycle columns are closed to the generic mutate: a status only
     * moves through the transition actions below, which enforce the table.
     *
     * @return array<string, mixed>
     */
    public function updateRules(RestRequest $request): array
    {
        return [
            'status' => ['prohibited'],
            'resolved_at' => ['prohibited'],
            'priority' => $this->priorityRules($request),
        ];
    }

    /**
     * Whoever opens a ticket does not weigh it: only the support team may name
     * an importance, and the model default covers everybody else.
     *
     * @return array<int, string>
     */
    private function priorityRules(RestRequest $request): array
    {
        $author = $request->user();

        return $author !== null && $author->can(TicketPermission::Handle->value)
            ? []
            : ['prohibited'];
    }

    /**
     * @return array<int, Action>
     */
    public function actions(RestRequest $request): array
    {
        return [
            AssignTicketAction::make(),
            UnassignTicketAction::make(),
            StartTicketWorkAction::make(),
            ResolveTicketAction::make(),
            CloseTicketAction::make(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function defaultOrderBy(RestRequest $request): array
    {
        return ['created_at' => 'desc'];
    }

    /**
     * The perimeters of TicketControl are applied here, on the query, so a user
     * never loads a row it may not see. The controller stays empty.
     */
    public function searchQuery(RestRequest $request, Builder $query): Builder
    {
        /** @var EloquentBuilder $query */
        return app(TicketControl::class)->forCurrentUser($query);
    }

    public function destroyQuery(RestRequest $request, Builder $query): Builder
    {
        /** @var EloquentBuilder $query */
        return app(TicketControl::class)->forCurrentUser($query);
    }
}
