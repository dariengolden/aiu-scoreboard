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
        'location',
        'notes',
        'winner_id',
        'fouls_home',
        'fouls_away',
        'yellow_cards_home',
        'yellow_cards_away',
        'red_cards_home',
        'red_cards_away',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'score_home' => 'integer',
        'score_away' => 'integer',
        'match_number' => 'integer',
        'game_data' => 'array',
        'fouls_home' => 'integer',
        'fouls_away' => 'integer',
        'yellow_cards_home' => 'integer',
        'yellow_cards_away' => 'integer',
        'red_cards_home' => 'integer',
        'red_cards_away' => 'integer',
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
            'increment_labels' => ['FT', '+2', '+3'],
            'has_time' => true,
            'formats' => null, // no format choice
            'discipline' => [
                'fouls' => true,
                'yellow' => false,
                'red' => false,
            ],
        ],
        'soccer' => [
            'type' => 'halves',
            'periods' => 2,
            'period_labels' => ['1st Half', '2nd Half'],
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => true,
            'formats' => null,
            'discipline' => [
                'fouls' => false,
                'yellow' => true,
                'red' => true,
            ],
        ],
        'volleyball' => [
            'type' => 'sets',
            'periods' => null, // determined by format
            'period_labels' => null, // generated dynamically
            'increments' => [1],
            'increment_labels' => ['+1'],
            'has_time' => false,
            'formats' => ['best_of_3' => 'Best of 3', 'best_of_5' => 'Best of 5'],
            'default_format' => 'best_of_5',
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
        ],
        'running' => [
            'type' => 'time',
            'periods' => null,
            'period_labels' => null,
            'increments' => null,
            'increment_labels' => null,
            'has_time' => false,
            'formats' => null,
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
     */
    public function getPeriodLabelsAttribute(): array
    {
        $config = $this->sport_config;
        if (! $config) {
            return [];
        }

        if ($config['period_labels']) {
            return $config['period_labels'];
        }

        // Generate set labels
        $max = $this->max_periods;
        $type = $config['type'];
        $label = match ($type) {
            'sets' => 'Set',
            default => 'Period',
        };

        return array_map(fn ($i) => "$label ".($i + 1), range(0, $max - 1));
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
     * Recalculate total scores from game_data periods.
     * For set-based sports, score = sets won. For period-based, score = sum of period scores.
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
            // Score = number of sets won
            $setsWonHome = 0;
            $setsWonAway = 0;
            $sets = $data['sets'] ?? [];
            foreach ($sets as $set) {
                $h = $set['home'] ?? 0;
                $a = $set['away'] ?? 0;
                if ($h > $a) {
                    $setsWonHome++;
                } elseif ($a > $h) {
                    $setsWonAway++;
                }
            }
            $this->score_home = $setsWonHome;
            $this->score_away = $setsWonAway;
        } elseif (in_array($type, ['quarters', 'halves'])) {
            // Score = sum of all period scores
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
