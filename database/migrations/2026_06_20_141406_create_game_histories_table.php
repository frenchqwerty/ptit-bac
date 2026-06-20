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
        Schema::create('game_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->json('players_data'); // [{id, name, score, position}]
            $table->json('rounds_data'); // [{letter, scores, answers}]
            $table->json('letters_used');
            $table->unsignedTinyInteger('rounds_played')->default(0);
            $table->integer('highest_score')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('game_room_id');
            $table->index('winner_id');
            $table->index('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_histories');
    }
};
