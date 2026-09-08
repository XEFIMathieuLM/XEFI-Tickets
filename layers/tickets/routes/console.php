<?php

use Illuminate\Support\Facades\Schedule;
use Tickets\Models\Ticket;

Schedule::command('model:prune', ['--model' => [Ticket::class]])
    ->daily()
    ->name('tickets:purge-soft-deleted');
