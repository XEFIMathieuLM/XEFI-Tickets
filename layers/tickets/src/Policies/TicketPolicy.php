<?php

namespace Tickets\Policies;

use Lomkit\Access\Policies\ControlledPolicy;
use Tickets\Access\Controls\TicketControl;

/**
 * Every ability is delegated to the control, so the gate and the query
 * restriction can never drift apart: both read the same perimeters.
 */
class TicketPolicy extends ControlledPolicy
{
    protected string $control = TicketControl::class;
}
