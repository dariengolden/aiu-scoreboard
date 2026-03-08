<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['name' => 'Basketball',   'slug' => 'basketball',   'icon' => '🏀', 'order' => 1],
            ['name' => 'Soccer',       'slug' => 'soccer',       'icon' => '⚽', 'order' => 2],
            ['name' => 'Volleyball',   'slug' => 'volleyball',   'icon' => '🏐', 'order' => 3],
            ['name' => 'Takraw',       'slug' => 'takraw',       'icon' => '🏐', 'order' => 4],
            ['name' => 'Table Tennis', 'slug' => 'table-tennis', 'icon' => '🏓', 'order' => 5],
            ['name' => 'Badminton',    'slug' => 'badminton',    'icon' => '🏸', 'order' => 6],
            ['name' => 'Running',      'slug' => 'running',      'icon' => '🏃', 'order' => 7],
            ['name' => 'Events',      'slug' => 'events',       'icon' => '🎉', 'order' => 8],
        ];

        foreach ($sports as $sport) {
            Sport::firstOrCreate(['name' => $sport['name']], $sport);
        }
    }
}
