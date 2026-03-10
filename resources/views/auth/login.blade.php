@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>
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
<div class="relative">
        <input type="password" id="password" name="password" required
               class="w-full bg-[#0f172a] border {{ $errors->has('password') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 pr-10 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-colors"
               placeholder="••••••••">
        <button type="button" onclick="togglePassword()"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition-colors">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        </button>
    </div>
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
