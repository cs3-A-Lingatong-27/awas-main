<x-guest-layout>
    <div class="mb-8 space-y-2">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Create your account</h2>
        <p class="text-sm text-slate-600">Set up your profile to access classes, assessments, and grades.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Full Name')" class="text-sm font-semibold text-slate-700" />
            <x-text-input
                id="name"
                class="mt-2 block w-full rounded-xl border-slate-300 bg-white/90 px-4 py-3 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Juan Dela Cruz"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-sm font-semibold text-slate-700" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-xl border-slate-300 bg-white/90 px-4 py-3 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                type="email"
                name="email"
                :value="old('email')"
                required
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
                autocomplete="new-password"
                placeholder="Create a secure password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-semibold text-slate-700" />

            <x-text-input
                id="password_confirmation"
                class="mt-2 block w-full rounded-xl border-slate-300 bg-white/90 px-4 py-3 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Re-enter your password"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <a class="text-sm text-slate-600 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 rounded-md" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="rounded-xl bg-sky-600 px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-800 focus:ring-sky-500">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
