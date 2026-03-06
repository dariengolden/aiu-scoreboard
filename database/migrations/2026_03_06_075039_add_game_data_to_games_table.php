<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // JSON blob for sport-specific period/set/quarter data
            // e.g. basketball: {"quarters": [{"home":20,"away":18}, ...], "current_quarter": 2}
            // e.g. volleyball: {"sets": [{"home":25,"away":20}, ...], "current_set": 2}
            $table->json('game_data')->nullable()->after('score_away');

            // Format choice for set-based sports (best_of_3, best_of_5, etc.)
            $table->string('game_format', 20)->nullable()->after('game_data');

            // Current period label for display (e.g. "Q2", "2nd Half", "Set 3")
            $table->string('current_period', 30)->nullable()->after('game_format');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['game_data', 'game_format', 'current_period']);
        });
    }
};
