<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    protected $fillable = [
        'category_id',
        'match_number',
        'team_home_id',
        'team_away_id',
        'score_home',
        'score_away',
        'game_data',
        'game_format',
        'current_period',
        'status',
        'scheduled_at',
        'scheduled_end_at',
        'location',
        'notes',
        'winner_id',
        'disqualified_team',
        'disqualification_reason',
        'event_type',
        'event_title',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
        'score_home' => 'integer',
        'score_away' => 'integer',
        'match_number' => 'integer',
        'game_data' => 'array',
    ];

    /**
     * Sport-specific scoring configuration.
     * Keyed by sport slug.
     */
    public const SPORT_CONFIG = [
        'basketball' => [
            'type' => 'quarters',
            'periods' => 4,
            'period_labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
            'increments' => [1, 2, 3],
            'increment_labels' => ['+1', '+2', '+3'],
            'has_time' => true,
            'formats' => null,
        ],
        'soccer' => [
            'type' => 'halves',
            'periods' => 2,
            'period_labels' => ['1st Half', '2nd Half'],
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => true,
            'formats' => null,
        ],
        'volleyball' => [
            'type' => 'sets',
            'periods' => null,
            'period_labels' => null,
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => false,
            'formats' => ['best_of_3' => 'Best of 3', 'best_of_5' => 'Best of 5'],
            'default_format' => 'best_of_5',
            'winning_score' => 25,
            'lead_required' => 2,
        ],
        'takraw' => [
            'type' => 'sets',
            'periods' => null,
            'period_labels' => null,
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => false,
            'formats' => ['best_of_3' => 'Best of 3', 'best_of_5' => 'Best of 5'],
            'default_format' => 'best_of_3',
            'winning_score' => 21,
            'lead_required' => 2,
        ],
        'table-tennis' => [
            'type' => 'sets',
            'periods' => null,
            'period_labels' => null,
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => false,
            'formats' => ['best_of_3' => 'Best of 3', 'best_of_5' => 'Best of 5'],
            'default_format' => 'best_of_5',
            'winning_score' => 11,
            'lead_required' => 2,
        ],
        'badminton' => [
            'type' => 'sets',
            'periods' => null,
            'period_labels' => null,
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => false,
            'formats' => ['best_of_3' => 'Best of 3'],
            'default_format' => 'best_of_3',
            'winning_score' => 21,
            'lead_required' => 2,
        ],
        'running' => [
            'type' => 'places',
            'periods' => null,
            'period_labels' => null,
            'increments' => null,
            'increment_labels' => null,
            'has_time' => false,
            'formats' => null,
            'places' => [1, 2, 3, 4],
            'place_labels' => ['1st', '2nd', '3rd', '4th'],
        ],
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function teamHome(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_home_id');
    }

    public function teamAway(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_away_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getMatchLabelAttribute(): string
    {
        return 'Match '.$this->match_number;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'upcoming' => 'Upcoming',
            'in_progress' => 'Live',
            'completed' => 'Completed',
            default => $this->status,
        };
    }

    /**
     * Get the sport config for this game's sport.
     */
    public function getSportConfigAttribute(): ?array
    {
        $slug = $this->category?->sport?->slug;

        return $slug ? (self::SPORT_CONFIG[$slug] ?? null) : null;
    }

    /**
     * Get the sport slug for this game.
     */
    public function getSportSlugAttribute(): ?string
    {
        return $this->category?->sport?->slug;
    }

    /**
     * Get number of max sets/periods for this game's format.
     */
    public function getMaxPeriodsAttribute(): int
    {
        $config = $this->sport_config;
        if (! $config) {
            return 1;
        }

        if ($config['periods']) {
            return $config['periods'];
        }

        // Set-based sports: derive from format
        $format = $this->game_format ?? ($config['default_format'] ?? 'best_of_3');

        return match ($format) {
            'best_of_5' => 5,
            'best_of_3' => 3,
            default => 3,
        };
    }

    /**
     * Get period labels for this game.
     * For basketball: base Q1-Q4, then OT1-OT5 if overtime periods exist.
     */
    public function getPeriodLabelsAttribute(): array
    {
        $config = $this->sport_config;
        if (! $config) {
            return [];
        }

        $baseLabels = $config['period_labels'];
        if ($baseLabels === null) {
            // Generate set labels
            $max = $this->max_periods;
            $type = $config['type'];
            $label = match ($type) {
                'sets' => 'Set',
                default => 'Period',
            };

            return array_map(fn ($i) => "$label ".($i + 1), range(0, $max - 1));
        }

        // Basketball: extend with OT periods from game_data if present
        if ($config['type'] === 'quarters') {
            $periods = $this->game_data['periods'] ?? [];
            $otCount = max(0, count($periods) - 4);

            if ($otCount > 0) {
                $labels = $baseLabels;
                for ($i = 1; $i <= min(5, $otCount); $i++) {
                    $labels[] = "OT$i";
                }

                return $labels;
            }
        }

        return $baseLabels;
    }

    /**
     * Get max periods including possible OT (for basketball: 4 + up to 5 OT = 9).
     */
    public function getMaxPeriodsIncludingOTAttribute(): int
    {
        if ($this->sport_slug === 'basketball') {
            $periods = $this->game_data['periods'] ?? [];
            $currentCount = count($periods);

            return max(4, min(9, $currentCount > 0 ? $currentCount : 4));
        }

        return $this->max_periods;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isLive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Whether this game is an event (ceremony, etc.) — no scores.
     */
    public function isEvent(): bool
    {
        return ! empty($this->event_type);
    }

    /**
     * Whether an event is currently "live" (within its scheduled time window).
     */
    public function isEventLiveNow(): bool
    {
        if (! $this->isEvent() || ! $this->scheduled_at || ! $this->scheduled_end_at) {
            return false;
        }

        $now = now();

        return $now->gte($this->scheduled_at) && $now->lte($this->scheduled_end_at);
    }

    /**
     * Whether this should appear as "live" (regular game in_progress, or event within its time window).
     */
    public function isLiveOrEventLive(): bool
    {
        if ($this->isEvent()) {
            return $this->isEventLiveNow();
        }

        return $this->isLive();
    }

    /**
     * Determine winner from scores. Returns null for draws.
     */
    public function determineWinnerFromScore(): ?int
    {
        if ($this->score_home === null || $this->score_away === null) {
            return null;
        }

        if ($this->score_home > $this->score_away) {
            return $this->team_home_id;
        }

        if ($this->score_away > $this->score_home) {
            return $this->team_away_id;
        }

        return null; // Draw
    }

    /**
     * Get set winner from home/away scores (higher score wins).
     * Returns: 'home', 'away', or null if draw.
     */
    public function getSetWinner(array $set): ?string
    {
        $home = (int) ($set['home'] ?? 0);
        $away = (int) ($set['away'] ?? 0);

        if ($home > $away) {
            return 'home';
        }
        if ($away > $home) {
            return 'away';
        }

        return null;
    }

    /**
     * Recalculate total scores from game_data.
     * For set-based sports: score = sets won (not raw points).
     * For period-based sports: score = sum of period scores.
     */
    public function recalculateTotalsFromGameData(): void
    {
        $data = $this->game_data;
        $config = $this->sport_config;

        if (! $data || ! $config) {
            return;
        }

        $type = $config['type'];

        if ($type === 'sets') {
            $setsWonHome = 0;
            $setsWonAway = 0;
            $sets = $data['sets'] ?? [];

            // Total = sets won. Only count sets user marked as complete.
            foreach ($sets as $index => $set) {
                if (empty($set['complete'])) {
                    continue;
                }

                $winner = $this->getSetWinner($set);

                if ($winner) {
                    $sets[$index]['winner'] = $winner;
                    if ($winner === 'home') {
                        $setsWonHome++;
                    } else {
                        $setsWonAway++;
                    }
                }
            }

            // Update game_data with winner tracking
            $this->game_data = array_merge($data, ['sets' => $sets]);

            $this->score_home = $setsWonHome;
            $this->score_away = $setsWonAway;
        } elseif (in_array($type, ['quarters', 'halves'])) {
            $totalHome = 0;
            $totalAway = 0;
            $periods = $data['periods'] ?? [];

            foreach ($periods as $period) {
                $totalHome += $period['home'] ?? 0;
                $totalAway += $period['away'] ?? 0;
            }

            $this->score_home = $totalHome;
            $this->score_away = $totalAway;
        }
    }

    /**
     * Get the number of completed sets.
     */
    public function getCompletedSetsCount(): int
    {
        $data = $this->game_data;
        if (! $data) {
            return 0;
        }

        $sets = $data['sets'] ?? [];
        $completed = 0;

        foreach ($sets as $set) {
            if (! empty($set['complete'])) {
                $completed++;
            }
        }

        return $completed;
    }

    /**
     * Get sets data for display (only completed sets with scores).
     */
    public function getCompletedSets(): array
    {
        $data = $this->game_data;
        if (! $data) {
            return [];
        }

        $sets = $data['sets'] ?? [];
        $completed = [];

        foreach ($sets as $set) {
            $home = $set['home'] ?? 0;
            $away = $set['away'] ?? 0;

            // Include any set with scores (not just completed ones)
            if ($home > 0 || $away > 0) {
                $completed[] = [
                    'home' => $home,
                    'away' => $away,
                    'winner' => $this->getSetWinner($set),
                ];
            }
        }

        return $completed;
    }

    /**
     * Initialize empty game_data structure based on sport config.
     */
    public function initializeGameData(): array
    {
        $config = $this->sport_config;
        if (! $config) {
            return [];
        }

        $type = $config['type'];
        $max = $this->max_periods;

        if ($type === 'sets') {
            return [
                'sets' => array_fill(0, $max, ['home' => 0, 'away' => 0]),
                'current_set' => 0,
            ];
        }

        if (in_array($type, ['quarters', 'halves'])) {
            return [
                'periods' => array_fill(0, $max, ['home' => 0, 'away' => 0]),
                'current_period' => 0,
            ];
        }

        return [];
    }
}
