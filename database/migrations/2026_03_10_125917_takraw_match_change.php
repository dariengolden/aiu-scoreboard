<?php

use App\Models\Category;
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
        $blueTeamId = \App\Models\Team::where('name', 'Blue')->value('id');
        $purpleTeamId = \App\Models\Team::where('name', 'Purple')->value('id');

        // 2. Get the Takraw Men Category ID
        $takrawCategoryId = \App\Models\Category::where('slug', 'takraw-men')->value('id');

        // 3. Update the specific game from March 10th at 18:00 - change away team from Purple to Blue
        \App\Models\Game::where('category_id', $takrawCategoryId)
            ->where('scheduled_at', '2026-03-10 17:30:00')
            ->where('team_away_id', $purpleTeamId)
            ->update([
                'team_away_id' => $blueTeamId,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $blueTeamId = \App\Models\Team::where('name', 'Blue')->value('id');
        $purpleTeamId = \App\Models\Team::where('name', 'Purple')->value('id');

        $takrawCategoryId = \App\Models\Category::where('slug', 'takraw-men')->value('id');

        \App\Models\Game::where('category_id', $takrawCategoryId)
            ->where('scheduled_at', '2026-03-10 17:30:00')
            ->where('team_away_id', $blueTeamId)
            ->update([
                'team_away_id' => $purpleTeamId,
            ]);
    }
};
