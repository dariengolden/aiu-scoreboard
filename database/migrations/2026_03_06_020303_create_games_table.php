<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('match_number');
            $table->foreignId('team_home_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('team_away_id')->constrained('teams')->cascadeOnDelete();
            $table->unsignedTinyInteger('score_home')->nullable();
            $table->unsignedTinyInteger('score_away')->nullable();
            $table->enum('status', ['upcoming', 'in_progress', 'completed'])->default('upcoming');
            $table->dateTime('scheduled_at')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'match_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
