@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="flex flex-col items-center gap-2">
            <x-xefi-wordmark tone="dark" />
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-xefi-steel">
                La proximité informatique
            </p>
        </div>

        <div class="mt-8 border-t-4 border-xefi-red bg-white p-8 shadow-sm">
            <h1 class="text-xl font-bold tracking-tight text-xefi-ink">{{ config('app.name') }}</h1>
            <p class="mt-1 text-sm text-xefi-steel">{{ __('Sign in to continue') }}</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                        {{ __('Email') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        required
                        class="mt-2 w-full rounded-sm border border-xefi-line bg-white px-3 py-2.5 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
                    >
                    @error('email')
                        <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-xefi-graphite">
                        {{ __('Password') }}
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="mt-2 w-full rounded-sm border border-xefi-line bg-white px-3 py-2.5 text-sm text-xefi-ink focus:border-xefi-red focus:outline-none"
                    >
                    @error('password')
                        <p role="alert" class="mt-1.5 text-sm font-medium text-xefi-red">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-sm bg-xefi-red px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-white hover:bg-xefi-red-dark"
                >
                    {{ __('Sign in') }}
                </button>
            </form>
        </div>

        @if ($accounts !== [])
            <div class="mt-6 border border-xefi-line bg-white p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-xefi-steel">
                    {{ __('Seeded accounts') }}
                </p>

                <ul class="mt-4 space-y-2">
                    @foreach ($accounts as $account)
                        <li>
                            <form method="POST" action="{{ route('login.as') }}">
                                @csrf
                                <input type="hidden" name="email" value="{{ $account['email'] }}">
                                <button
                                    type="submit"
                                    class="w-full border-l-2 border-xefi-line bg-xefi-mist px-4 py-3 text-left hover:border-xefi-red hover:bg-xefi-red-tint"
                                >
                                    <span class="text-sm font-bold text-xefi-ink">{{ $account['name'] }}</span>
                                    <span class="block text-xs text-xefi-steel">{{ $account['email'] }}</span>
                                    <span class="mt-1 block text-xs text-xefi-steel">{{ $account['can'] }}</span>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-xs text-xefi-steel">{{ __('Password for all three: password') }}</p>
            </div>
        @endif
    </div>
@endsection
