<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Red',    'color_hex' => '#EF4444'],
            ['name' => 'Blue',   'color_hex' => '#3B82F6'],
            ['name' => 'Purple', 'color_hex' => '#A855F7'],
            ['name' => 'Pink',   'color_hex' => '#EC4899'],
            ['name' => 'Event',  'color_hex' => '#94A3B8'], // Placeholder for ceremony events
        ];

        foreach ($teams as $team) {
            Team::firstOrCreate(['name' => $team['name']], $team);
        }
    }
}
