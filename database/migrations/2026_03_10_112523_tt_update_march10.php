<?php

use App\Models\Game;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get the Team IDs using their names
        $purpleTeamId = \App\Models\Team::where('name', 'Purple')->value('id');
        $blueTeamId = \App\Models\Team::where('name', 'Blue')->value('id');

        // 2. Get the Table Tennis Women's Singles Category ID using the unique slug we just found
        $tableTennisCategoryId = \App\Models\Category::where('slug', 'table-tennis-womens-singles')->value('id');

        // 3. Update the specific game from March 8th at 19:30
        \App\Models\Game::where('category_id', $tableTennisCategoryId)
            ->where('scheduled_at', '2026-03-10 18:00:00')
            ->where('team_away_id', $blueTeamId)
            ->update([
                'team_away_id' => $purpleTeamId,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $purpleTeamId = \App\Models\Team::where('name', 'Purple')->value('id');
        $blueTeamId = \App\Models\Team::where('name', 'Blue')->value('id');

        $tableTennisCategoryId = \App\Models\Category::where('slug', 'table-tennis-womens-singles')->value('id');

        \App\Models\Game::where('category_id', $tableTennisCategoryId)
            ->where('scheduled_at', '2026-03-10 18:00:00')
            ->where('team_away_id', $purpleTeamId)
            ->update([
                'team_away_id' => $blueTeamId,
            ]);
    }
};
