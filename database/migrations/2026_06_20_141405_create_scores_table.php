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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->integer('round_score')->default(0);
            $table->integer('cumulative_score')->default(0);
            $table->boolean('has_perfect')->default(false);
            $table->unsignedTinyInteger('unique_answers_count')->default(0);
            $table->timestamps();

            $table->unique(['round_id', 'player_id']);
            $table->index(['game_room_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
