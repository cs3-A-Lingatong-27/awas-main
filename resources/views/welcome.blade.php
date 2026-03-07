<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWAS - Welcome</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/build-placeholder.js'])
</head>
<body class="antialiased text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="relative min-h-screen overflow-hidden bg-slate-100">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,#dbeafe_0%,transparent_35%),radial-gradient(circle_at_90%_15%,#fde68a_0%,transparent_30%),radial-gradient(circle_at_80%_85%,#bfdbfe_0%,transparent_30%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="auth-card grid w-full max-w-5xl overflow-hidden md:grid-cols-2">
                <div class="hidden flex-col justify-between bg-slate-900 px-10 py-10 text-slate-100 md:flex">
                    <div class="inline-flex items-center gap-3">
                        <span class="rounded-xl bg-white/10 p-1.5">
                            <img src="{{ asset('pshs.png') }}" alt="PSHS Logo" class="h-9 w-9 rounded-lg object-cover">
                        </span>
                        <span class="text-sm font-semibold tracking-wide">AWAS</span>
                    </div>

                    <div class="space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-200">Philippine Science High School</p>
                        <h1 class="text-3xl font-extrabold leading-tight">Assessment and workflow system for your school.</h1>
                        <p class="text-sm leading-relaxed text-slate-300">Manage schedules, subjects, and academic tracking from one streamlined portal.</p>
                    </div>

                    <p class="text-xs text-slate-400">Caraga Region Campus in Butuan City</p>
                </div>

                <div class="px-6 py-8 sm:px-10 sm:py-10">
                    <div class="mb-8 space-y-2">
                        <div class="mb-4 inline-flex items-center gap-2 md:hidden">
                            <img src="{{ asset('pshs.png') }}" alt="PSHS Logo" class="h-8 w-8 rounded-lg object-cover">
                            <span class="text-sm font-semibold tracking-wide text-slate-700">AWAS</span>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Welcome to AWAS</h2>
                        <p class="text-sm text-slate-600">Choose how you want to continue.</p>
                    </div>

                    <div class="space-y-4">
                        <a href="{{ route('login') }}" class="auth-btn inline-flex w-full items-center justify-center">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-700 transition hover:bg-slate-50">
                            Teacher/Admin Registration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
