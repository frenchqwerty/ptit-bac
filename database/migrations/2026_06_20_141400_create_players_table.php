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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 50);
            $table->string('display_name', 50);
            $table->string('avatar_color', 7)->default('#7C3AED');
            $table->string('session_token', 64)->unique()->nullable();
            $table->integer('elo_rating')->default(1000);
            $table->integer('best_elo')->default(1000);
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('session_token');
            $table->index('elo_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
