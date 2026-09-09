<?php

namespace Tickets\Access;

/**
 * Questions the layer answers about an account that no single permission
 * covers on its own. Each one is defined once, on the gate.
 */
enum TicketAbility: string
{
    case ReachesOthersTickets = 'tickets.reaches-others-tickets';
}
