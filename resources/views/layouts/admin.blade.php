<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0c1445">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — 2026 INTRAMURALS</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f172a] text-white min-h-screen font-sans antialiased">

    {{-- Top bar --}}
    <header class="sticky top-0 z-50 bg-[#020617]/95 backdrop-blur border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('sc-white.png') }}" alt="AIU Student Council" class="h-8 w-8 rounded">
                <span class="text-lg font-bold text-white">SCOREBOARD <span class="text-blue-400">ADMIN</span></span>
            </div>

            <div class="flex items-center gap-3">
                <span class="hidden sm:block text-sm text-slate-400">{{ auth()->user()->name }}</span>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                   class="hidden md:inline-flex text-sm px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                    Users
                </a>
                <a href="{{ route('admin.sports.index') }}"
                   class="hidden md:inline-flex text-sm px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.sports.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                    Sports
                </a>
                <a href="{{ route('admin.standings.index') }}"
                   class="hidden md:inline-flex text-sm px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('admin.standings.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                    Standings
                </a>
                @endif
                <a href="{{ route('dashboard') }}"
                   class="hidden md:inline-flex text-sm px-3 py-1.5 rounded-lg font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-white/10' }}">
                     Dashboard
                </a>
                <a href="{{ route('home') }}"
                   class="hidden md:inline-flex text-sm px-3 py-1.5 rounded-lg font-medium transition-colors text-slate-300 hover:bg-white/10">
                    Public View
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-400 hover:text-white transition-colors">Logout</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-green-500/20 border border-green-500/40 text-green-300 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    </div>
    @endif
    @if($errors->any())
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-red-500/20 border border-red-500/40 text-red-300 rounded-xl px-4 py-3 text-sm font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <main class="max-w-7xl mx-auto px-4 py-6 pb-24 md:pb-8">
        @yield('content')
    </main>

    {{-- Mobile bottom nav for admin --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-[#0c1445]/95 backdrop-blur border-t border-white/10">
        <div class="flex items-stretch h-16">
            <a href="{{ route('dashboard') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                 Dashboard
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.users.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>
            <a href="{{ route('admin.sports.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('admin.sports.*') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Sports
            </a>
            <a href="{{ route('admin.standings.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('admin.standings.*') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Standings
            </a>
            @endif
            <a href="{{ route('home') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Public
            </a>
        </div>
    </nav>

</body>
</html>
