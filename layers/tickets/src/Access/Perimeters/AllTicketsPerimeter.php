<?php

namespace Tickets\Access\Perimeters;

use Lomkit\Access\Perimeters\Perimeter;

/**
 * Whoever may see every ticket. Not an overlay: matching it short-circuits the
 * others, since there is nothing left to add to an unrestricted query.
 */
class AllTicketsPerimeter extends Perimeter {}
