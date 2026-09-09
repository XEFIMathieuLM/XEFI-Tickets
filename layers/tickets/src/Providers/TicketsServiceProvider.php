<?php

namespace Tickets\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Facades\Mcp;
use Livewire\Livewire;
use Lomkit\Access\Access;
use Tickets\Access\Controls\TicketControl;
use Tickets\Access\TicketAbility;
use Tickets\Console\Commands\EscalateOverdueTicketsCommand;
use Tickets\Console\Commands\ImportTicketsCommand;
use Tickets\Database\Seeders\TicketsSeeder;
use Tickets\Enums\TicketPermission;
use Tickets\Events\TicketAssigned;
use Tickets\Events\TicketsEscalated;
use Tickets\Listeners\NotifyAssignedTechnician;
use Tickets\Listeners\NotifyManagersOfEscalation;
use Tickets\Livewire\TicketArchive;
use Tickets\Livewire\TicketAttachments;
use Tickets\Livewire\TicketForm;
use Tickets\Livewire\TicketList;
use Tickets\Livewire\TicketTransitions;
use Tickets\Mcp\Servers\TicketsServer;
use Xefi\LaravelOSDD\LayerServiceProvider;

class TicketsServiceProvider extends LayerServiceProvider
{
    /**
     * Control discovery only reaches app/Access/Controls, so the layer
     * registers its own.
     */
    public function register(): void
    {
        (new Access)->addControl(new TicketControl);
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom($this->layerPath('lang'), 'tickets');
        $this->loadViewsFrom($this->layerPath('resources/views'), 'tickets');

        Gate::define(
            TicketAbility::ReachesOthersTickets->value,
            fn (User $user): bool => $user->canAny([
                TicketPermission::ViewAll->value,
                TicketPermission::ViewAssigned->value,
            ]),
        );

        Event::listen(TicketAssigned::class, NotifyAssignedTechnician::class);
        Event::listen(TicketsEscalated::class, NotifyManagersOfEscalation::class);

        Mcp::web('mcp/tickets', TicketsServer::class)->middleware(['web', 'auth']);

        Livewire::component('tickets.ticket-list', TicketList::class);
        Livewire::component('tickets.ticket-archive', TicketArchive::class);
        Livewire::component('tickets.ticket-form', TicketForm::class);
        Livewire::component('tickets.ticket-transitions', TicketTransitions::class);
        Livewire::component('tickets.ticket-attachments', TicketAttachments::class);

        if ($this->app->runningInConsole()) {
            $this->commands([EscalateOverdueTicketsCommand::class, ImportTicketsCommand::class]);
            $this->loadMigrationsFrom($this->layerPath('database/migrations'));
            $this->loadSeeders([TicketsSeeder::class]);
        }

        $this->withRouting(
            web: $this->layerPath('routes/web.php'),
            api: $this->layerPath('routes/api.php'),
            commands: $this->layerPath('routes/console.php'),
            channels: $this->layerPath('routes/channels.php'),
        );
    }

    private function layerPath(string $relative): string
    {
        return sprintf('%s/../../%s', __DIR__, $relative);
    }
}
