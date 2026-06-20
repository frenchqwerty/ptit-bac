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
        Schema::create('game_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->foreignId('host_id')->constrained('players')->cascadeOnDelete();
            $table->string('status')->default('waiting'); // waiting|countdown|playing|scoring|finished
            $table->string('current_letter', 1)->nullable();
            $table->unsignedTinyInteger('current_round')->default(0);
            $table->unsignedTinyInteger('total_rounds')->default(5);
            $table->unsignedSmallInteger('round_duration')->default(90);
            $table->unsignedTinyInteger('max_players')->default(10);
            $table->json('categories')->nullable();
            $table->timestamp('round_started_at')->nullable();
            $table->timestamp('round_ends_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('code');
            $table->index('status');
            $table->index('host_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_rooms');
    }
};
