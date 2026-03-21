@extends('layouts.admin')

@section('title', 'Announcements')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Announcements</h1>
    <a href="{{ route('admin.announcements.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Create Announcement
    </a>
</div>

<div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Title</th>
                <th class="text-left px-4 py-3">Content</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-right px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($announcement as $item)
            <tr class="border-b border-white/5 last:border-b-0">
                <td class="px-4 py-3 font-medium text-white">{{ $item->title }}</td>
                <td class="px-4 py-3 text-slate-400">
                    {{ Str::limit(strip_tags($item->content), 50) }}
                </td>
                <td class="px-4 py-3">
                    @if($item->is_active)
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>
                        Active
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400">
                        Inactive
                    </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.announcements.edit', $item) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-500 text-white transition-colors">
                        Edit
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                    No announcements yet. Create one using the create button below.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
