<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Sport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $sportCategories = [
            'Basketball' => ['Men', 'Women'],
            'Soccer' => ['Men', 'Women'],
            'Volleyball' => ['Men', 'Women'],
            'Takraw' => ['Men'],
            'Table Tennis' => [
                'Women\'s Singles',
                'Men\'s Singles',
                'Mixed',
                'Men\'s Doubles',
                'Women\'s Doubles',
            ],
            'Badminton' => [
                'Men\'s Singles',
                'Women\'s Singles',
                'Men\'s Doubles',
                'Women\'s Doubles',
                'Mixed Doubles',
            ],
            'Events' => ['Ceremonies'],
            'Running' => [
                '100m Men',
                '100m Women',
                '200m Men',
                '200m Women',
                '400m Men',
                '400m Women',
                'Relay 4x100m Men',
                'Relay 4x100m Women',
                'Relay 4x100m Mixed',
            ],
        ];

        foreach ($sportCategories as $sportName => $categories) {
            $sport = Sport::where('name', $sportName)->first();
            if (! $sport) {
                continue;
            }

            foreach ($categories as $categoryName) {
                Category::firstOrCreate(
                    ['sport_id' => $sport->id, 'name' => $categoryName],
                    ['slug' => Str::slug($sportName.'-'.$categoryName)]
                );
            }
        }
    }
}
