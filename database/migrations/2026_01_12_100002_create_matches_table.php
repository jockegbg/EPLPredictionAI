<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gameweek_id')->constrained()->cascadeOnDelete();
            $table->string('home_team');
            $table->string('away_team');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->dateTime('start_time');
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
