@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

<div class="max-w-lg">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors">Users</a>
        <span>/</span>
        <span class="text-white font-medium">{{ $user->name }}</span>
    </div>

    <h1 class="text-2xl font-black text-white mb-6">Edit User</h1>

    <div class="bg-[#1e293b] rounded-2xl p-6 border border-white/5">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full bg-[#0f172a] border {{ $errors->has('name') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full bg-[#0f172a] border {{ $errors->has('email') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">
                    New Password
                    <span class="text-slate-500 normal-case font-normal">(leave blank to keep current)</span>
                </label>
                <input type="password" id="password" name="password"
                       class="w-full bg-[#0f172a] border {{ $errors->has('password') ? 'border-red-500' : 'border-white/10' }} rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Min. 8 characters">
                @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="w-full bg-[#0f172a] border border-white/10 rounded-xl px-4 py-3 text-white text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Repeat new password">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 rounded-xl transition-colors text-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="flex-1 bg-transparent border border-white/10 hover:bg-white/5 text-slate-300 font-bold py-3.5 rounded-xl transition-colors text-sm text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
