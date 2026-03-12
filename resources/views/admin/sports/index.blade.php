@extends('layouts.admin')

@section('title', 'Sports')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Sports</h1>
</div>

<div class="bg-[#1e293b] rounded-2xl border border-white/5 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/5 text-xs text-slate-400 uppercase tracking-wider">
                <th class="text-left px-4 py-3">Sport</th>
                <th class="text-left px-4 py-3">Description</th>
                <th class="text-right px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sports as $sport)
            <tr class="border-b border-white/5 last:border-b-0">
                <td class="px-4 py-3 font-medium text-white">{{ $sport->name }}</td>
                <td class="px-4 py-3 text-slate-400">
                    {{ $sport->description ? \Illuminate\Support\Str::limit($sport->description, 50) : '—' }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.sports.edit', $sport) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-500 text-white transition-colors">
                        Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
