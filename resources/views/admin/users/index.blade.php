@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-black text-white">Users</h1>
        <p class="text-slate-400 text-sm mt-0.5">Manage overseers who can update game info</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition-colors active:scale-95">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New User
    </a>
</div>

@if($users->isEmpty())
<div class="text-center py-20 bg-[#1e293b] rounded-2xl border border-white/5">
    <div class="text-5xl mb-4">👤</div>
    <p class="text-slate-300 font-semibold">No users yet</p>
    <p class="text-slate-500 text-sm mt-1 mb-6">Create the first overseer to get started</p>
    <a href="{{ route('admin.users.create') }}"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold px-5 py-3 rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Create User
    </a>
</div>
@else
<div class="space-y-2">
    @foreach($users as $user)
    <div class="bg-[#1e293b] rounded-2xl border border-white/5 p-4 flex items-center gap-4">
        {{-- Avatar --}}
        <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center shrink-0">
            <span class="text-blue-400 font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-white text-sm truncate">{{ $user->name }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
        </div>

        {{-- Role badge --}}
        <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-full bg-slate-700/80 text-slate-400">
            Overseer
        </span>

        {{-- Actions --}}
        <div class="shrink-0 flex items-center gap-1">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="p-2 rounded-xl text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection
