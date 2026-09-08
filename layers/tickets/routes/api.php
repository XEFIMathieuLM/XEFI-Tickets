<?php

use Illuminate\Support\Facades\Route;
use Lomkit\Rest\Facades\Rest;
use Tickets\Http\Controllers\ExportResolvedTicketsController;
use Tickets\Rest\Controllers\TicketsController;

Route::middleware('auth')->prefix('v1')->group(function (): void {
    Rest::resource('tickets', TicketsController::class);

    Route::get('tickets/exports/resolved-this-month', ExportResolvedTicketsController::class)
        ->name('tickets.exports.resolved-this-month');
});
