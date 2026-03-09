<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Game;
use App\Models\Sport;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::pluck('id', 'name'); // ['Red' => 1, 'Blue' => 2, ...]

        // Helper to resolve category by sport name + category name
        $categoryCache = [];
        $cat = function (string $sportName, string $categoryName) use (&$categoryCache) {
            $key = "$sportName|$categoryName";
            if (! isset($categoryCache[$key])) {
                $sport = Sport::where('name', $sportName)->first();
                $categoryCache[$key] = Category::where('sport_id', $sport->id)
                    ->where('name', $categoryName)
                    ->first();
            }

            return $categoryCache[$key];
        };

        // Helper to build a datetime in 2026 (all dates are March 2026, Asia/Bangkok timezone)
        $dt = function (int $day, string $time) {
            // $time like '18:30' or '19:30'
            [$hour, $minute] = explode(':', $time);

            return Carbon::create(2026, 3, $day, (int) $hour, (int) $minute, 0, 'Asia/Bangkok');
        };

        // Track match numbers per category
        $matchNumbers = [];
        $nextMatch = function ($categoryId) use (&$matchNumbers) {
            if (! isset($matchNumbers[$categoryId])) {
                $matchNumbers[$categoryId] = 0;
            }

            return ++$matchNumbers[$categoryId];
        };

        // Helper to create a game (idempotent — uses firstOrCreate to avoid duplicates)
        $game = function (string $sportName, string $categoryName, string $home, string $away, int $day, string $startTime, ?string $location = null) use ($teams, $cat, $dt, $nextMatch) {
            $category = $cat($sportName, $categoryName);
            Game::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'match_number' => $nextMatch($category->id),
                ],
                [
                    'team_home_id' => $teams[$home],
                    'team_away_id' => $teams[$away],
                    'status' => 'upcoming',
                    'scheduled_at' => $dt($day, $startTime),
                    'location' => $location,
                ]
            );
        };

        // =====================================================================
        // CEREMONY EVENTS (no scores, display-only)
        // =====================================================================
        $eventTeamId = $teams['Event'] ?? null;
        if ($eventTeamId) {
            $ceremonyCat = $cat('Events', 'Ceremonies');
            if ($ceremonyCat) {
                $opening = Game::firstOrCreate(
                    [
                        'category_id' => $ceremonyCat->id,
                        'match_number' => 1,
                    ],
                    [
                        'team_home_id' => $eventTeamId,
                        'team_away_id' => $eventTeamId,
                        'status' => 'upcoming',
                        'scheduled_at' => $dt(8, '17:30'),
                        'scheduled_end_at' => $dt(8, '19:30'),
                        'location' => null,
                        'event_type' => 'opening_ceremony',
                        'event_title' => 'Opening Ceremony',
                    ]
                );
                $opening->update(['scheduled_at' => $dt(8, '17:30'), 'scheduled_end_at' => $dt(8, '19:30')]);

                $closing = Game::firstOrCreate(
                    [
                        'category_id' => $ceremonyCat->id,
                        'match_number' => 2,
                    ],
                    [
                        'team_home_id' => $eventTeamId,
                        'team_away_id' => $eventTeamId,
                        'status' => 'upcoming',
                        'scheduled_at' => $dt(22, '15:00'),
                        'scheduled_end_at' => $dt(22, '21:00'),
                        'location' => null,
                        'event_type' => 'closing_ceremony',
                        'event_title' => 'Closing Ceremony',
                    ]
                );
                $closing->update(['scheduled_at' => $dt(22, '15:00'), 'scheduled_end_at' => $dt(22, '21:00')]);
            }
        }

        // =====================================================================
        // MARCH 8 (SUN) — Round Robin Day 1
        // =====================================================================
        $game('Basketball', 'Women', 'Red', 'Purple', 8, '19:30');
        $game('Soccer', 'Men', 'Red', 'Pink', 8, '19:30');
        $game('Volleyball', 'Women', 'Pink', 'Blue', 8, '19:30');
        $game('Takraw', 'Men', 'Pink', 'Blue', 8, '19:30');
        $game('Table Tennis', "Women's Singles", 'Purple', 'Blue', 8, '19:30');
        $game('Table Tennis', "Men's Singles", 'Purple', 'Blue', 8, '20:00');

        // =====================================================================
        // MARCH 9 (MON) — Round Robin Day 2
        // =====================================================================
        $game('Basketball', 'Men', 'Purple', 'Blue', 9, '18:30');
        $game('Soccer', 'Women', 'Pink', 'Blue', 9, '18:30');
        $game('Volleyball', 'Men', 'Red', 'Pink', 9, '18:00');
        $game('Takraw', 'Men', 'Purple', 'Pink', 9, '18:00');
        $game('Table Tennis', "Women's Singles", 'Pink', 'Red', 9, '18:00');
        $game('Table Tennis', "Men's Singles", 'Pink', 'Red', 9, '18:30');

        // =====================================================================
        // MARCH 10 (TUE) — Round Robin Day 3
        // =====================================================================
        $game('Basketball', 'Women', 'Pink', 'Blue', 10, '18:30');
        $game('Soccer', 'Men', 'Purple', 'Blue', 10, '18:30');
        $game('Volleyball', 'Women', 'Red', 'Purple', 10, '18:00');
        $game('Takraw', 'Men', 'Red', 'Purple', 10, '18:00');
        $game('Table Tennis', "Women's Singles", 'Red', 'Blue', 10, '18:00');
        $game('Table Tennis', "Men's Singles", 'Purple', 'Red', 10, '18:30');

        // =====================================================================
        // MARCH 11 (WED) — Round Robin Day 4
        // =====================================================================
        $game('Basketball', 'Men', 'Red', 'Pink', 11, '18:30');
        $game('Soccer', 'Women', 'Purple', 'Pink', 11, '18:30');
        $game('Volleyball', 'Men', 'Purple', 'Blue', 11, '18:00');
        $game('Takraw', 'Men', 'Red', 'Purple', 11, '18:00');
        $game('Table Tennis', "Women's Singles", 'Blue', 'Pink', 11, '18:00');
        $game('Table Tennis', "Men's Singles", 'Blue', 'Pink', 11, '18:30');

        // =====================================================================
        // MARCH 12 (THU) — Round Robin Day 5
        // =====================================================================
        $game('Basketball', 'Women', 'Purple', 'Pink', 12, '18:30');
        $game('Soccer', 'Men', 'Red', 'Purple', 12, '18:30');
        $game('Volleyball', 'Women', 'Red', 'Blue', 12, '18:00');
        $game('Takraw', 'Men', 'Purple', 'Blue', 12, '18:00');
        $game('Table Tennis', "Women's Singles", 'Red', 'Blue', 12, '18:00');
        $game('Table Tennis', "Men's Singles", 'Red', 'Blue', 12, '18:30');

        // =====================================================================
        // MARCH 14 (SAT) — Round Robin Day 6
        // =====================================================================
        $game('Basketball', 'Men', 'Red', 'Purple', 14, '18:30');
        $game('Soccer', 'Women', 'Red', 'Pink', 14, '18:30');
        $game('Volleyball', 'Men', 'Pink', 'Blue', 14, '18:00');
        $game('Takraw', 'Men', 'Red', 'Pink', 14, '18:00');
        $game('Table Tennis', "Women's Singles", 'Purple', 'Pink', 14, '18:00');
        $game('Table Tennis', "Men's Singles", 'Purple', 'Pink', 14, '18:30');

        // =====================================================================
        // MARCH 15 (SUN) — Badminton Week 1 (morning) + Evening Games
        // =====================================================================
        // Evening round-robin games
        $game('Basketball', 'Women', 'Red', 'Pink', 15, '18:30');
        $game('Soccer', 'Men', 'Purple', 'Pink', 15, '18:30');
        $game('Volleyball', 'Women', 'Purple', 'Blue', 15, '18:00');
        // Table Tennis Mixed — 3 matches
        $game('Table Tennis', 'Mixed', 'Red', 'Purple', 15, '18:00');
        $game('Table Tennis', 'Mixed', 'Blue', 'Pink', 15, '18:30');
        $game('Table Tennis', 'Mixed', 'Red', 'Pink', 15, '19:00');

        // Badminton Week 1 — Morning at TT Muak Lek Court
        // Men's Singles (6 round-robin matches)
        $game('Badminton', "Men's Singles", 'Blue', 'Red', 15, '08:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Singles", 'Purple', 'Pink', 15, '08:30', 'TT Muak Lek Court');
        $game('Badminton', "Men's Singles", 'Blue', 'Purple', 15, '09:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Singles", 'Blue', 'Pink', 15, '09:30', 'TT Muak Lek Court');
        $game('Badminton', "Men's Singles", 'Red', 'Pink', 15, '10:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Singles", 'Red', 'Purple', 15, '10:30', 'TT Muak Lek Court');

        // Women's Singles (6 round-robin matches)
        $game('Badminton', "Women's Singles", 'Purple', 'Pink', 15, '08:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Singles", 'Blue', 'Red', 15, '08:30', 'TT Muak Lek Court');
        $game('Badminton', "Women's Singles", 'Red', 'Pink', 15, '09:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Singles", 'Purple', 'Red', 15, '09:30', 'TT Muak Lek Court');
        $game('Badminton', "Women's Singles", 'Blue', 'Purple', 15, '10:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Singles", 'Blue', 'Pink', 15, '10:30', 'TT Muak Lek Court');

        // Men's Doubles (6 round-robin matches)
        $game('Badminton', "Men's Doubles", 'Blue', 'Purple', 15, '08:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Doubles", 'Red', 'Pink', 15, '08:30', 'TT Muak Lek Court');
        $game('Badminton', "Men's Doubles", 'Purple', 'Pink', 15, '09:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Doubles", 'Blue', 'Red', 15, '09:30', 'TT Muak Lek Court');
        $game('Badminton', "Men's Doubles", 'Blue', 'Pink', 15, '10:00', 'TT Muak Lek Court');
        $game('Badminton', "Men's Doubles", 'Red', 'Purple', 15, '10:30', 'TT Muak Lek Court');

        // Women's Doubles (4 matches played on March 15 morning)
        $game('Badminton', "Women's Doubles", 'Blue', 'Red', 15, '11:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Doubles", 'Purple', 'Pink', 15, '11:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Doubles", 'Blue', 'Purple', 15, '11:30', 'TT Muak Lek Court');
        $game('Badminton', "Women's Doubles", 'Red', 'Pink', 15, '11:30', 'TT Muak Lek Court');

        // Mixed Doubles (2 matches played on March 15 morning)
        $game('Badminton', 'Mixed Doubles', 'Blue', 'Purple', 15, '11:00', 'TT Muak Lek Court');
        $game('Badminton', 'Mixed Doubles', 'Red', 'Pink', 15, '11:30', 'TT Muak Lek Court');

        // =====================================================================
        // MARCH 16 (MON) — Round Robin Day 8
        // =====================================================================
        $game('Basketball', 'Men', 'Red', 'Blue', 16, '18:30');
        $game('Soccer', 'Women', 'Purple', 'Blue', 16, '18:30');
        $game('Volleyball', 'Men', 'Purple', 'Pink', 16, '18:00');
        // Table Tennis Mixed — 3 matches
        $game('Table Tennis', 'Mixed', 'Purple', 'Blue', 16, '18:00');
        $game('Table Tennis', 'Mixed', 'Red', 'Blue', 16, '18:30');
        $game('Table Tennis', 'Mixed', 'Purple', 'Pink', 16, '19:00');

        // =====================================================================
        // MARCH 17 (TUE) — Round Robin Day 9
        // =====================================================================
        $game('Basketball', 'Women', 'Red', 'Blue', 17, '18:30');
        $game('Soccer', 'Men', 'Red', 'Blue', 17, '18:30');
        $game('Volleyball', 'Women', 'Purple', 'Pink', 17, '18:00');
        // Table Tennis Doubles
        $game('Table Tennis', "Men's Doubles", 'Red', 'Purple', 17, '18:00');
        $game('Table Tennis', "Women's Doubles", 'Blue', 'Pink', 17, '18:30');
        $game('Table Tennis', "Men's Doubles", 'Red', 'Pink', 17, '19:00');

        // =====================================================================
        // MARCH 18 (WED) — Round Robin Day 10
        // =====================================================================
        $game('Basketball', 'Men', 'Purple', 'Pink', 18, '18:30');
        $game('Soccer', 'Women', 'Red', 'Purple', 18, '18:30');
        $game('Volleyball', 'Men', 'Red', 'Blue', 18, '18:00');
        // Table Tennis Doubles
        $game('Table Tennis', "Women's Doubles", 'Red', 'Purple', 18, '18:00');
        $game('Table Tennis', "Men's Doubles", 'Blue', 'Pink', 18, '18:30');
        $game('Table Tennis', "Women's Doubles", 'Red', 'Pink', 18, '19:00');

        // =====================================================================
        // MARCH 19 (THU) — Round Robin Day 11
        // =====================================================================
        $game('Basketball', 'Women', 'Purple', 'Blue', 19, '18:30');
        $game('Soccer', 'Men', 'Pink', 'Blue', 19, '18:30');
        $game('Volleyball', 'Women', 'Red', 'Pink', 19, '18:00');
        // Table Tennis Doubles
        $game('Table Tennis', "Men's Doubles", 'Purple', 'Blue', 19, '18:00');
        $game('Table Tennis', "Women's Doubles", 'Red', 'Blue', 19, '18:30');
        $game('Table Tennis', "Men's Doubles", 'Purple', 'Pink', 19, '19:00');

        // =====================================================================
        // MARCH 21 (SAT) — Round Robin Day 12
        // =====================================================================
        $game('Basketball', 'Men', 'Pink', 'Blue', 21, '18:30');
        $game('Soccer', 'Women', 'Red', 'Blue', 21, '18:30');
        $game('Volleyball', 'Men', 'Red', 'Purple', 21, '18:00');
        // Table Tennis Doubles
        $game('Table Tennis', "Women's Doubles", 'Purple', 'Blue', 21, '18:00');
        $game('Table Tennis', "Men's Doubles", 'Red', 'Blue', 21, '18:30');
        $game('Table Tennis', "Women's Doubles", 'Purple', 'Pink', 21, '19:00');

        // =====================================================================
        // MARCH 22 (SUN) — Badminton Week 2 (morning) + Running / Closing Ceremony
        // =====================================================================
        // Badminton Week 2 — Morning at TT Muak Lek Court
        // Women's Doubles (remaining 2 matches)
        $game('Badminton', "Women's Doubles", 'Blue', 'Pink', 22, '08:00', 'TT Muak Lek Court');
        $game('Badminton', "Women's Doubles", 'Red', 'Purple', 22, '08:30', 'TT Muak Lek Court');

        // Mixed Doubles (remaining 4 matches)
        $game('Badminton', 'Mixed Doubles', 'Blue', 'Red', 22, '08:00', 'TT Muak Lek Court');
        $game('Badminton', 'Mixed Doubles', 'Purple', 'Pink', 22, '08:00', 'TT Muak Lek Court');
        $game('Badminton', 'Mixed Doubles', 'Blue', 'Pink', 22, '08:30', 'TT Muak Lek Court');
        $game('Badminton', 'Mixed Doubles', 'Red', 'Purple', 22, '08:30', 'TT Muak Lek Court');

        // Running — Closing Ceremony (starts 6:20 PM)
        // Running events are not head-to-head games; all 4 teams compete simultaneously.
        // We seed them as 1 "game" per event for tracking purposes (no home/away distinction).
        // Using Red as home and Purple as away as placeholders — all 4 teams compete.
        $runningEvents = [
            ['100m Men', '18:20'],
            ['100m Women', '18:25'],
            ['200m Men', '18:35'],
            ['200m Women', '18:45'],
            ['400m Men', '18:55'],
            ['400m Women', '19:05'],
            ['Relay 4x100m Men', '19:15'],
            ['Relay 4x100m Women', '19:30'],
            ['Relay 4x100m Mixed', '19:45'],
        ];

        foreach ($runningEvents as $event) {
            $category = $cat('Running', $event[0]);
            Game::firstOrCreate(
                [
                    'category_id' => $category->id,
                    'match_number' => $nextMatch($category->id),
                ],
                [
                    'team_home_id' => $teams['Red'],
                    'team_away_id' => $teams['Purple'],
                    'status' => 'upcoming',
                    'scheduled_at' => (function () use ($event) {
                        [$h, $m] = explode(':', $event[1]);

                        return Carbon::create(2026, 3, 22, (int) $h, (int) $m, 0, 'Asia/Bangkok');
                    })(),
                    'location' => 'Track',
                    'notes' => 'All 4 teams compete — not a head-to-head match.',
                ]
            );
        }
    }
}
