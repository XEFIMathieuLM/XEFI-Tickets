<?php

namespace Tickets\Rest\Resources;

use Lomkit\Rest\Http\Resource as RestResource;

/**
 * Base resource for the ticketing layer.
 *
 * The *Query hooks come from the package's PerformsQueries trait; a resource
 * overrides the ones it actually constrains — see TicketResource, which applies
 * the control perimeters on search and destroy.
 */
abstract class Resource extends RestResource
{
    //
}
