<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AIU Sports Day') — AIU Sports Day</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0f172a] text-white min-h-screen font-sans antialiased flex flex-col">

    {{-- Top Navigation --}}
    <header class="sticky top-0 z-50 bg-transparent backdrop-blur border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('logo.png') }}" alt="AIU Scoreboard" class="h-8 w-8 rounded">
                <span class="text-2xl font-bold tracking-tight text-white">AIU <span class="text-white">Scoreboard</span></span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-blue-500/15 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    Home
                </a>

                <a href="{{ route('scores.index') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('scores.*') ? 'bg-blue-500/15 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    Scores
                </a>

                <a href="{{ route('schedule') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('schedule') ? 'bg-blue-500/15 text-blue-400' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    Schedule
                </a>
                {{-- <a href="{{ route('results') }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('results') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-white/10' }}">
                    Results
                </a> --}}
            </nav>

            {{-- Auth indicator (desktop) --}}
            @auth
            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-sm text-blue-400 hover:text-blue-300 font-medium">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-400 hover:text-white">Logout</button>
                </form>
            </div>
            @endauth

            {{-- Mobile hamburger placeholder (bottom nav handles mobile) --}}
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
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-red-500/20 border border-red-500/40 text-red-300 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm0-4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 pb-4 md:pb-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-6 md:mt-16 bg-[#060b18] border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-6 pb-20 md:pb-6 flex items-center justify-between gap-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0">
                    <img src="{{ asset('sc-white.png') }}" alt="AIU Sports Day" class="h-5 w-auto">
                </a>

                {{-- Copyright --}}
                <p class="text-xs text-slate-500 min-w-0 truncate">&copy; {{ date('Y') }} AIU Student Council. All rights reserved.</p>

                {{-- Social links --}}
                <div class="flex items-center gap-4">
                    {{-- Instagram --}}
                    <a href="https://www.instagram.com/aiu.studentcouncil/" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white transition-colors" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.17.054 1.97.24 2.43.403a4.088 4.088 0 011.47.96c.458.458.779.924.96 1.47.163.46.35 1.26.403 2.43.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.054 1.17-.24 1.97-.403 2.43a4.088 4.088 0 01-.96 1.47 4.088 4.088 0 01-1.47.96c-.46.163-1.26.35-2.43.403-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.17-.054-1.97-.24-2.43-.403a4.088 4.088 0 01-1.47-.96 4.088 4.088 0 01-.96-1.47c-.163-.46-.35-1.26-.403-2.43C2.175 15.584 2.163 15.204 2.163 12s.012-3.584.07-4.85c.054-1.17.24-1.97.403-2.43a4.088 4.088 0 01.96-1.47 4.088 4.088 0 011.47-.96c.46-.163 1.26-.35 2.43-.403C8.416 2.175 8.796 2.163 12 2.163zM12 0C8.741 0 8.333.014 7.053.072 5.775.13 4.902.333 4.14.63a5.882 5.882 0 00-2.126 1.384A5.882 5.882 0 00.63 4.14C.333 4.902.13 5.775.072 7.053.014 8.333 0 8.741 0 12s.014 3.667.072 4.947c.058 1.278.261 2.15.558 2.913a5.882 5.882 0 001.384 2.126 5.882 5.882 0 002.126 1.384c.763.297 1.635.5 2.913.558C8.333 23.986 8.741 24 12 24s3.667-.014 4.947-.072c1.278-.058 2.15-.261 2.913-.558a5.882 5.882 0 002.126-1.384 5.882 5.882 0 001.384-2.126c.297-.763.5-1.635.558-2.913.058-1.28.072-1.688.072-4.947s-.014-3.667-.072-4.947c-.058-1.278-.261-2.15-.558-2.913a5.882 5.882 0 00-1.384-2.126A5.882 5.882 0 0019.86.63C19.097.333 18.225.13 16.947.072 15.667.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                    </a>

                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/aiu.studentcouncil" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white transition-colors" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                </div>
        </div>
    </footer>

    {{-- Mobile bottom navigation --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-transparent backdrop-blur border-t border-white/10 safe-area-bottom">
        <div class="flex items-stretch h-16">
            <a href="{{ route('home') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('home') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>

            <a href="{{ route('scores.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('scores.*') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Scores
            </a>

            <a href="{{ route('schedule') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('schedule') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Schedule
            </a>

            {{-- <a href="{{ route('results') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('results') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Results
            </a> --}}

            @auth
            <a href="{{ route('dashboard') }}"
               class="flex-1 flex flex-col items-center justify-center gap-0.5 text-xs font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Manage
            </a>
            @endauth
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
