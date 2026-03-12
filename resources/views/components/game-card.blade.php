@props(['game'])

@php
    $homeTeam = $game->teamHome;
    $awayTeam = $game->teamAway;
    $sportSlug = $game->category?->sport?->slug;
    $sportConfig = $game->sport_config;
    $sportType = $sportConfig['type'] ?? null;
    $gameData = $game->game_data ?? [];
    $places = $gameData['places'] ?? [];
    $isRunning = $sportType === 'places';
    $isLive = $game->isLiveOrEventLive();
    $matchParam = $game->match_number ?? $game->id;
    $isCeremony = !empty($game->event_type);
@endphp

@if($isCeremony || !$sportSlug || !$game->category?->slug)
    {{-- Ceremony OR missing category/sport: non-clickable card --}}
    <div class="game-card block bg-[#1e293b] rounded-2xl overflow-hidden border border-white/5 flex flex-col h-full">
        {{-- Sport + Category + Status header --}}
        <div class="px-4 pt-3 pb-2">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-slate-400">
                    @if($game->category && $game->category->sport)
                        <x-sport-icon :sport="$game->category->sport" size="xs" /> {{ $game->category->sport->name }} &mdash; {{ $game->category->name }}
                    @endif
                </span>
                <x-status-badge :status="$game->status" />
            </div>
        </div>

        {{-- Ceremony title --}}
        <div class="px-4 pb-3 flex-grow flex items-center justify-center">
            <div class="py-3 text-center">
                <span class="text-lg font-bold text-white">{{ $game->event_title ?? 'Ceremony' }}</span>
            </div>
        </div>

        {{-- Footer: time + location --}}
        @if($game->scheduled_at || $game->location)
        <div class="px-4 py-2 bg-white/[0.03] border-t border-white/5 flex items-center gap-3 text-xs text-slate-500">
            @if($game->scheduled_at)
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $game->scheduled_at->format('D, M j · g:ia') }}
            </span>
            @endif
            @if($game->location)
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                {{ $game->location }}
            </span>
            @endif
        </div>
        @endif
    </div>
@elseif($sportSlug && $game->category?->slug)
    {{-- Regular game with valid sport/category: clickable card --}}
    <a href="{{ route('games.show', ['sport' => $sportSlug, 'category' => $game->category->slug, 'match' => $matchParam]) }}"
       class="game-card block bg-[#1e293b] rounded-2xl overflow-hidden border border-white/5 hover:border-blue-400/60 hover:shadow-lg hover:shadow-blue-500/10 transition-all transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900 {{ $isLive ? 'border-green-500/20' : '' }}"
       data-game-id="{{ $game->id }}"
       data-is-live="{{ $isLive ? '1' : '0' }}"
       data-period-labels='@json($game->period_labels ?? [])'
       data-home-name="{{ $isRunning ? '' : ($homeTeam?->name ?? '') }}"
       data-away-name="{{ $isRunning ? '' : ($awayTeam?->name ?? '') }}"
       data-home-color="{{ $isRunning ? '#6b7280' : ($homeTeam?->color_hex ?? '#6b7280') }}"
       data-away-color="{{ $isRunning ? '#6b7280' : ($awayTeam?->color_hex ?? '#6b7280') }}"
       aria-label="{{ $isRunning ? 'Running - ' . $game->category->name : ($homeTeam?->name ?? '') . ' vs ' . ($awayTeam?->name ?? '') }} - {{ $game->match_label }}">

        {{-- Sport + Category + Status header --}}
        <div class="px-4 pt-3 pb-2">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-slate-400">
                    @if($game->category && $game->category->sport)
                        <x-sport-icon :sport="$game->category->sport" size="xs" /> {{ $game->category->sport->name }} &mdash; {{ $game->category->name }}
                    @endif
                </span>
                <x-status-badge :status="$game->status" />
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $game->match_label }}</span>
                @if(($isLive || $game->current_period) && !$game->isCompleted())
                    <span class="game-card-period text-xs font-bold text-blue-400">{{ $game->current_period ?? '' }}</span>
                @endif
            </div>
        </div>

        {{-- Teams + Score (or Places for Running) --}}
        <div class="px-4 pb-3">
            @if($isRunning)
                {{-- Running: Show places 1st-4th --}}
                @for($i = 1; $i <= 4; $i++)
                @php $placeTeamId = $places[$i] ?? null; @endphp
                @php $placeTeam = $placeTeamId ? App\Models\Team::find($placeTeamId) : null; @endphp
                <div class="flex items-center justify-between py-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        @if($i === 1)
                            <span class="text-sm">🥇</span>
                        @elseif($i === 2)
                            <span class="text-sm">🥈</span>
                        @elseif($i === 3)
                            <span class="text-sm">🥉</span>
                        @else
                            <span class="text-sm text-slate-500">4th</span>
                        @endif
                        <span class="font-semibold text-sm truncate {{ $placeTeam ? 'text-white' : 'text-slate-500' }}">
                            {{ $placeTeam?->name ?? '—' }}
                        </span>
                    </div>
                </div>
                @endfor
            @else
            {{-- Home team --}}
            <div class="flex items-center justify-between py-1.5">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $homeTeam?->color_hex ?? '#6b7280' }}"></span>
                    <span class="font-semibold text-sm truncate {{ $game->winner_id === $homeTeam?->id ? 'text-white' : 'text-slate-300' }}">
                        {{ $homeTeam?->name ?? '—' }}
                    </span>
                    @if($game->winner_id === $homeTeam?->id)
                        <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endif
                </div>
                <span class="game-card-score-home text-lg font-bold tabular-nums {{ $game->winner_id === $homeTeam?->id ? 'text-white' : 'text-slate-400' }}">
                    {{ $game->score_home ?? '—' }}
                </span>
            </div>

            <div class="border-t border-white/5 mx-0"></div>

            {{-- Away team --}}
            <div class="flex items-center justify-between py-1.5">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $awayTeam?->color_hex ?? '#6b7280' }}"></span>
                    <span class="font-semibold text-sm truncate {{ $game->winner_id === $awayTeam?->id ? 'text-white' : 'text-slate-300' }}">
                        {{ $awayTeam?->name ?? '—' }}
                    </span>
                    @if($game->winner_id === $awayTeam?->id)
                        <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endif
                </div>
                <span class="game-card-score-away text-lg font-bold tabular-nums {{ $game->winner_id === $awayTeam?->id ? 'text-white' : 'text-slate-400' }}">
                    {{ $game->score_away ?? '—' }}
                </span>
            </div>
            @endif

            {{-- Period/Set breakdown --}}
            @if($gameData && ($sportType === 'sets' || $sportType === 'quarters' || $sportType === 'halves'))
                @php
                    $dataKey = $sportType === 'sets' ? 'sets' : 'periods';
                    $items = $gameData[$dataKey] ?? [];
                    $itemsWithScores = array_filter($items, fn($item) => ($item['home'] ?? 0) > 0 || ($item['away'] ?? 0) > 0);
                @endphp
                @if(count($itemsWithScores) > 0)
                    <div class="game-card-breakdown mt-2 pt-2 border-t border-white/5">
                        <div class="flex items-center gap-1.5 text-xs tabular-nums">
                            @foreach($itemsWithScores as $item)
                                <span class="px-1.5 py-0.5 rounded bg-white/5 text-slate-400 font-medium">
                                    {{ $item['home'] ?? 0 }}-{{ $item['away'] ?? 0 }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            {{-- Draw indicator --}}
            @if($game->isCompleted() && $game->score_home !== null && $game->score_home === $game->score_away)
                <div class="text-center pt-1">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Draw</span>
                </div>
            @endif
        </div> {{-- End Teams + Score --}}

        {{-- Footer: time + location --}}
        @if($game->scheduled_at || $game->location)
            <div class="px-4 py-2 bg-white/[0.03] border-t border-white/5 flex items-center gap-3 text-xs text-slate-500">
                @if($game->scheduled_at)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $game->scheduled_at->format('D, M j · g:ia') }}
                    </span>
                @endif
                @if($game->location)
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ $game->location }}
                    </span>
                @endif
            </div>
        @endif
    </a>
@endif
