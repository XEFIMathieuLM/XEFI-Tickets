<?php

namespace Tickets\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Tickets\Imports\ImportReport;

class TicketImportFinished
{
    use Dispatchable;

    public function __construct(public readonly ImportReport $report) {}
}
