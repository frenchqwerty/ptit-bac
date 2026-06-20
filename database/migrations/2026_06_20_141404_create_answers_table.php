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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('category', 50);
            $table->string('value', 100)->nullable();
            $table->boolean('is_valid')->nullable();
            $table->boolean('is_unique')->nullable();
            $table->unsignedTinyInteger('points')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['round_id', 'player_id', 'category']);
            $table->index(['round_id', 'category']);
            $table->index('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
