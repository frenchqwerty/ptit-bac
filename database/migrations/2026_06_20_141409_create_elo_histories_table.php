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
        Schema::create('elo_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_room_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('elo_before');
            $table->integer('elo_after');
            $table->integer('elo_change');
            $table->unsignedTinyInteger('position')->nullable(); // finish position
            $table->unsignedTinyInteger('total_players')->nullable();
            $table->timestamps();

            $table->index('player_id');
            $table->index(['player_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elo_histories');
    }
};
