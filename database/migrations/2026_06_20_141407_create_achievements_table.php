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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name', 100);
            $table->text('description');
            $table->string('icon', 50)->default('trophy');
            $table->string('category', 30)->default('general');
            $table->json('criteria'); // {type, threshold, ...}
            $table->integer('reward_elo')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('key');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
