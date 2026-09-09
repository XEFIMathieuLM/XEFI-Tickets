<?php

namespace Tickets\Actions;

use Illuminate\Database\Eloquent\Collection;
use Tickets\Models\Ticket;

/**
 * What one escalation run found and did, for the operator and the report.
 */
class EscalationOutcome
{
    /**
     * @param  Collection<int, Ticket>  $escalated
     */
    public function __construct(
        public readonly int $examined,
        public readonly Collection $escalated,
        public readonly int $flagged,
    ) {}
}
