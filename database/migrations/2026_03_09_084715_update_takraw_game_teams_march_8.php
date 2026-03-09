<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team;
use App\Models\Game;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    // 1. Get the Team IDs using their names
    $pinkTeamId = \App\Models\Team::where('name', 'Pink')->value('id');
    $purpleTeamId = \App\Models\Team::where('name', 'Purple')->value('id');

    // 2. Get the Takraw Men Category ID using the unique slug we just found
    $takrawCategoryId = \App\Models\Category::where('slug', 'takraw-men')->value('id');

    // 3. Update the specific game from March 8th at 19:30
    \App\Models\Game::where('category_id', $takrawCategoryId)
        ->where('scheduled_at', '2026-03-08 19:30:00')
        ->where('team_home_id', $purpleTeamId)
        ->update([
            'team_home_id' => $pinkTeamId
        ]);
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $pinkTeamId = Team::where('name', 'Pink')->value('id');
        $purpleTeamId = Team::where('name', 'Purple')->value('id');

        Game::where('event_title', 'Takraw')
            ->whereDate('scheduled_at', '2026-03-08')
            ->where('team_home_id', $pinkTeamId)
            ->update([
                'team_home_id' => $purpleTeamId
            ]);
    }
};
