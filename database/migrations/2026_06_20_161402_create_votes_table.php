<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('answer_player_id')->constrained('players')->cascadeOnDelete();
            $table->string('category', 50);
            $table->boolean('is_valid');
            $table->timestamps();

            $table->unique(
                ['round_id', 'voter_id', 'answer_player_id', 'category'],
                'votes_unique',
            );
            $table->index(['round_id', 'voter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
