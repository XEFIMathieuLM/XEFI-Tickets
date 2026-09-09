<?php

namespace Tickets\Access\Perimeters;

use Lomkit\Access\Perimeters\OverlayPerimeter;

/**
 * The tickets the user opened. An overlay, so a user who is both a requester
 * and a technician sees the union of the two sets rather than one of them.
 */
class OwnTicketsPerimeter extends OverlayPerimeter {}
