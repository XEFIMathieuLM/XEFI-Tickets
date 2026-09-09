<?php

use Illuminate\Support\Facades\Schedule;
use Tickets\Models\ChangeLog;
use Tickets\Models\Ticket;

Schedule::command('model:prune', ['--model' => [Ticket::class]])
    ->daily()
    ->name('tickets:purge-soft-deleted');

Schedule::command('model:prune', ['--model' => [ChangeLog::class]])
    ->weekly()
    ->name('tickets:purge-change-logs');

Schedule::command('tickets:escalate-overdue')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->name('tickets:escalate-overdue');
