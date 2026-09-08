<?php

namespace Tickets\Rest\Controllers;

use Lomkit\Rest\Http\Resource;
use Tickets\Rest\Resources\TicketResource;

class TicketsController extends Controller
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = TicketResource::class;
}
