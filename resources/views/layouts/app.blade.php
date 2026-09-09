@use('Tickets\Access\TicketAbility')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-xefi-mist text-xefi-ink antialiased">
    <div class="flex min-h-full flex-col">
        <div aria-hidden="true" class="h-1 bg-xefi-red"></div>

        @auth
            <header class="bg-xefi-black">
                <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-4">
                    <a href="{{ route('tickets.index') }}" class="flex items-center gap-4">
                        <x-xefi-wordmark />
                        <span class="hidden text-xs font-semibold uppercase tracking-[0.18em] text-white/40 sm:block">
                            La proximité informatique
                        </span>
                    </a>

                    <nav class="flex items-center gap-6 text-sm">
                        <a
                            href="{{ route('tickets.index') }}"
                            class="border-b-2 border-transparent pb-0.5 font-medium text-white/70 hover:border-xefi-red hover:text-white"
                        >
                            @can(TicketAbility::ReachesOthersTickets->value)
                                {{ __('tickets::list.heading') }}
                            @else
                                {{ __('tickets::list.heading_own') }}
                            @endcan
                        </a>

                        @can('create', \Tickets\Models\Ticket::class)
                            <a
                                href="{{ route('tickets.create') }}"
                                class="border-b-2 border-transparent pb-0.5 font-medium text-white/70 hover:border-xefi-red hover:text-white"
                            >
                                {{ __('tickets::form.heading.create') }}
                            </a>
                        @endcan

                        <a
                            href="{{ route('tickets.archive') }}"
                            class="border-b-2 border-transparent pb-0.5 font-medium text-white/70 hover:border-xefi-red hover:text-white"
                        >
                            {{ __('tickets::archive.heading') }}
                        </a>

                        <span class="flex items-center gap-3 border-l border-white/15 pl-6">
                            <span class="font-semibold text-white">{{ auth()->user()->name }}</span>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-xs font-semibold uppercase tracking-wide text-white/50 hover:text-xefi-red"
                                >
                                    {{ __('Log out') }}
                                </button>
                            </form>
                        </span>
                    </nav>
                </div>
            </header>
        @endauth

        <main class="mx-auto w-full max-w-6xl grow px-6 py-10">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <footer class="border-t border-xefi-line bg-white">
            <div class="mx-auto max-w-6xl px-6 py-5 text-xs text-xefi-steel">
                XEFI — La proximité informatique
            </div>
        </footer>
    </div>
</body>
</html>
