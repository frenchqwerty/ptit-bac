<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('games_won')->default(0);
            $table->integer('best_score')->default(0);
            $table->bigInteger('total_score')->default(0);
            $table->unsignedInteger('total_rounds_played')->default(0);
            $table->unsignedInteger('unique_answers_found')->default(0);
            $table->unsignedInteger('perfect_rounds')->default(0);
            $table->unsignedTinyInteger('current_win_streak')->default(0);
            $table->unsignedTinyInteger('best_win_streak')->default(0);
            $table->unsignedInteger('total_answers_submitted')->default(0);
            $table->unsignedInteger('stop_rounds_called')->default(0);
            $table->timestamps();

            $table->unique('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
