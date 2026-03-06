@extends('layouts.auth')

@section('title', 'Login')

@section('content')

<div class="w-full max-w-sm">
    {{-- Logo / Branding --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-black text-white mb-1">AIU <span class="text-blue-400">Sports</span></h1>
        <p class="text-slate-400 text-sm">Staff login</p>
    </div>

    <div class="bg-[#1e293b] rounded-3xl p-6 border border-white/10 shadow-2xl">
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-[#0f172a] border {{ $errors->has('email') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors"
                       placeholder="your@email.com">
                @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full bg-[#0f172a] border {{ $errors->has('password') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors"
                       placeholder="••••••••">
                @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" id="remember" name="remember"
                       class="w-4 h-4 rounded border-white/20 bg-[#0f172a] text-blue-500 focus:ring-blue-500 focus:ring-offset-0">
                <label for="remember" class="text-sm text-slate-400">Remember me</label>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm">
                Sign In
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-slate-600 mt-6">
        <a href="{{ route('home') }}" class="hover:text-slate-400 transition-colors">← Back to public site</a>
    </p>
</div>

@endsection
