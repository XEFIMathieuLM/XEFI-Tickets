<?php

namespace Tickets\Access\Perimeters;

use Lomkit\Access\Perimeters\OverlayPerimeter;

/**
 * The tickets handed to the user. An overlay, so it stacks with the other
 * narrow perimeters instead of hiding them.
 */
class AssignedTicketsPerimeter extends OverlayPerimeter {}
