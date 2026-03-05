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
     Schema::create('statements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('player_id')->constrained()->cascadeOnDelete();
    $table->string('content', 200);
    $table->boolean('is_lie')->default(false);
    $table->json('ai_verdict')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statements');
    }
};
