<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/build-placeholder.js'])
    </head>
    <body class="antialiased text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <div class="relative min-h-screen overflow-hidden bg-slate-100">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,#dbeafe_0%,transparent_35%),radial-gradient(circle_at_90%_15%,#fde68a_0%,transparent_30%),radial-gradient(circle_at_80%_85%,#bfdbfe_0%,transparent_30%)]"></div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="auth-card grid w-full max-w-5xl overflow-hidden md:grid-cols-2">
                    <div class="hidden flex-col justify-between bg-slate-900 px-10 py-10 text-slate-100 md:flex">
                        <div>
                            <a href="/" class="inline-flex items-center gap-3">
                                <span class="rounded-xl bg-white/10 p-1.5">
                                    <img src="{{ asset('pshs.png') }}" alt="{{ config('app.name', 'AWAS') }} logo" class="h-9 w-9 rounded-lg object-cover" />
                                </span>
                                <span class="text-sm font-semibold tracking-wide">{{ config('app.name', 'Laravel') }}</span>
                            </a>
                        </div>

                        <div class="space-y-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-200">AWAS Student Portal</p>
                            <h1 class="text-3xl font-extrabold leading-tight">Track classes, grades, and assessments in one place.</h1>
                            <p class="text-sm leading-relaxed text-slate-300">Built for school administrators, teachers, and students to manage learning progress with less friction.</p>
                        </div>

                        <p class="text-xs text-slate-400">Secure school management platform powered by AWAS</p>
                    </div>

                    <div class="px-6 py-8 sm:px-10 sm:py-10">
                        <div class="mb-6 md:hidden">
                            <a href="/" class="inline-flex items-center gap-2">
                                <img src="{{ asset('pshs.png') }}" alt="{{ config('app.name', 'AWAS') }} logo" class="h-8 w-8 rounded-lg object-cover" />
                                <span class="text-sm font-semibold tracking-wide text-slate-700">{{ config('app.name', 'Laravel') }}</span>
                            </a>
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
