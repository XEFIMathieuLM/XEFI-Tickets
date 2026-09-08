<?php

namespace Tickets\Providers;

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Lomkit\Access\Access;
use Tickets\Access\Controls\TicketControl;
use Tickets\Database\Seeders\TicketsSeeder;
use Tickets\Events\TicketAssigned;
use Tickets\Listeners\NotifyAssignedTechnician;
use Tickets\Livewire\TicketAttachments;
use Tickets\Livewire\TicketForm;
use Xefi\LaravelOSDD\LayerServiceProvider;

class TicketsServiceProvider extends LayerServiceProvider
{
    /**
     * The access control package only auto-discovers app/Access/Controls, and its
     * class-name resolver cannot cope with a layer path, so the layer registers
     * its own control explicitly.
     */
    public function register(): void
    {
        (new Access)->addControl(new TicketControl);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'tickets');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'tickets');

        Event::listen(TicketAssigned::class, NotifyAssignedTechnician::class);

        Livewire::component('tickets.ticket-form', TicketForm::class);
        Livewire::component('tickets.ticket-attachments', TicketAttachments::class);

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
            $this->loadSeeders([TicketsSeeder::class]);
        }

        $this->withRouting(
            web: __DIR__.'/../../routes/web.php',
            api: __DIR__.'/../../routes/api.php',
            commands: __DIR__.'/../../routes/console.php',
        );
    }
}
