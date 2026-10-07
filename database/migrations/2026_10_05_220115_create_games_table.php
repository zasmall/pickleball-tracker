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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('played_on');
            $table->string('format'); // singles | doubles
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('team_a_score');
            $table->unsignedTinyInteger('team_b_score');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'played_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
