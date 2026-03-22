<x-guest-layout>
    <div class="mb-8 space-y-2">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Welcome back</h2>
        <p class="text-sm text-slate-600">Sign in to continue to your dashboard.</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-sm font-semibold text-slate-700" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-xl border-slate-300 bg-white/90 px-4 py-3 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="you@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-sm font-semibold text-slate-700" />

            <x-text-input
                id="password"
                class="mt-2 block w-full rounded-xl border-slate-300 bg-white/90 px-4 py-3 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-sky-700 transition hover:text-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 rounded-md" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end gap-4 pt-2">
            <x-primary-button class="rounded-xl bg-sky-600 px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-800 focus:ring-sky-500">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
