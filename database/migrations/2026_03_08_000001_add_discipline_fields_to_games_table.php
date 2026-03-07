<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedTinyInteger('fouls_home')->nullable()->after('score_away');
            $table->unsignedTinyInteger('fouls_away')->nullable()->after('fouls_home');
            $table->unsignedTinyInteger('yellow_cards_home')->nullable()->after('fouls_away');
            $table->unsignedTinyInteger('yellow_cards_away')->nullable()->after('yellow_cards_home');
            $table->unsignedTinyInteger('red_cards_home')->nullable()->after('yellow_cards_away');
            $table->unsignedTinyInteger('red_cards_away')->nullable()->after('red_cards_home');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'fouls_home',
                'fouls_away',
                'yellow_cards_home',
                'yellow_cards_away',
                'red_cards_home',
                'red_cards_away',
            ]);
        });
    }
};

