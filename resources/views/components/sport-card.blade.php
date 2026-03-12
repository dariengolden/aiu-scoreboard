@props(['sport'])

<a href="{{ route('scores.show', $sport) }}"
   class="group block bg-[#1e293b] hover:bg-[#243044] rounded-2xl p-5 border border-white/5 hover:border-blue-500/30 transition-all active:scale-95">
    <div class="mb-3"><x-sport-icon :sport="$sport" size="xl" /></div>
    <h3 class="font-bold text-white text-base group-hover:text-blue-300 transition-colors">{{ $sport->name }}</h3>
    <p class="text-xs text-slate-500 mt-1">{{ $sport->categories->count() }} categories</p>
</a>
