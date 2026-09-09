<?php

namespace Tickets\Rest\Resources;

use Lomkit\Rest\Http\Resource as RestResource;

/**
 * Base resource for the ticketing layer. The *Query hooks come from the
 * package; a resource overrides only the ones it constrains.
 */
abstract class Resource extends RestResource {}
