<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/build-placeholder.js'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
</head>
<body class="antialiased text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="relative min-h-screen overflow-hidden bg-slate-100">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_10%_20%,#dbeafe_0%,transparent_35%),radial-gradient(circle_at_90%_15%,#fde68a_0%,transparent_30%),radial-gradient(circle_at_80%_85%,#bfdbfe_0%,transparent_30%)]"></div>
        <div class="relative mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">
            <div class="app auth-card overflow-hidden">
                <header class="topbar flex items-center justify-between px-8 py-4 bg-slate-900 text-white shadow-lg">
                    <div class="logo flex items-center gap-3">
                        <img src="{{ asset('pshs.png') }}" alt="PSHS Logo" class="h-12 w-auto object-contain">
                        <div class="logo-text">
                            <div class="font-bold leading-tight uppercase tracking-wide text-white">
                                Philippine Science High School
                            </div>
                            <div class="text-[10px] opacity-80 text-white">
                                Caraga Region Campus in Butuan City
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-8">
                        <nav class="flex gap-6 font-semibold">
                            <a href="{{ route('dashboard') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Dashboard</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.enrollment') }}" class="hover:text-blue-200 transition underline-offset-8 hover:underline">Enrollment</a>
                            @endif
                        </nav>
                    </div>
                </header>

                <main class="main" style="display: flex; align-items: flex-start;">
                    <div class="flex-1 w-full">
                        <section class="content-tab" style="display: block;">
                            <section class="calendar-section">
                                <div class="mb-6">
                                    <h2 class="text-2xl font-bold text-slate-900">Profile</h2>
                                    <p class="text-sm text-slate-600">Manage your personal preferences and access tools.</p>
                                </div>

                                <div class="grid gap-6 md:grid-cols-2">
                                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                                        <h3 class="text-lg font-semibold text-slate-800 mb-2">User Profile</h3>
                                        <p class="text-slate-600 text-sm">
                                            Welcome to your AWAS profile, {{ auth()->user()->name }}.
                                        </p>
                                        <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                            <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 font-semibold">
                                                {{ ucfirst(auth()->user()->role) }}
                                            </span>
                                            @if(auth()->user()->role === 'student')
                                                <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-semibold">
                                                    Grade {{ auth()->user()->grade_level ?? 'N/A' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                                        <h3 class="text-lg font-semibold text-slate-800 mb-3">Display</h3>
                                        <label class="flex items-center justify-between text-sm text-slate-700">
                                            <span>Dark Mode</span>
                                            <input id="darkModeToggle" type="checkbox" class="h-4 w-4">
                                        </label>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Feedback</h3>
                                        <a href="https://forms.gle/x6s7cxEmCgnKZTxU6" target="_blank" rel="noopener noreferrer" class="text-blue-700 font-semibold hover:underline">
                                            Open Feedback Survey
                                        </a>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Account</h3>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="logout-action-btn">Logout Account</button>
                                        </form>
                                    </div>
                                </div>
                            </section>
                        </section>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        const THEME_KEY = 'awas-theme';
        function applyTheme(theme) {
            const isDark = theme === 'dark';
            document.body.classList.toggle('dark-mode', isDark);
            document.documentElement.classList.toggle('dark-mode', isDark);
            document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
        }

        const toggle = document.getElementById('darkModeToggle');
        if (toggle) {
            const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
            applyTheme(savedTheme);
            toggle.checked = savedTheme === 'dark';
            toggle.addEventListener('change', (e) => {
                const nextTheme = e.target.checked ? 'dark' : 'light';
                localStorage.setItem(THEME_KEY, nextTheme);
                applyTheme(nextTheme);
            });
        }
    </script>
</body>
</html>
