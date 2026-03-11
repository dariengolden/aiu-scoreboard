<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $runningCategoryIds = DB::table('categories')
            ->join('sports', 'categories.sport_id', '=', 'sports.id')
            ->where('sports.slug', 'running')
            ->pluck('categories.id');

        DB::statement('ALTER TABLE games MODIFY team_home_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE games MODIFY team_away_id BIGINT UNSIGNED NULL');

        DB::table('games')
            ->whereIn('category_id', $runningCategoryIds)
            ->update([
                'team_home_id' => null,
                'team_away_id' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $redTeam = DB::table('teams')->where('name', 'Red')->first();
        $purpleTeam = DB::table('teams')->where('name', 'Purple')->first();

        if ($redTeam && $purpleTeam) {
            $runningCategoryIds = DB::table('categories')
                ->join('sports', 'categories.sport_id', '=', 'sports.id')
                ->where('sports.slug', 'running')
                ->pluck('categories.id');

            DB::table('games')
                ->whereIn('category_id', $runningCategoryIds)
                ->update([
                    'team_home_id' => $redTeam->id,
                    'team_away_id' => $purpleTeam->id,
                ]);
        }

        DB::statement('ALTER TABLE games MODIFY team_home_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE games MODIFY team_away_id BIGINT UNSIGNED NOT NULL');
    }
};
