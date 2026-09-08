<?php

namespace Tickets\Enums;

/**
 * The permissions the ticketing layer checks. Authorization is decided on these
 * alone: a role only groups them, and no code ever tests a role name.
 */
enum TicketPermission: string
{
    case ViewOwn = 'tickets.view.own';
    case ViewAssigned = 'tickets.view.assigned';
    case ViewAll = 'tickets.view.all';
    case Create = 'tickets.create';
    case Assign = 'tickets.assign';
    case Close = 'tickets.close';
}
