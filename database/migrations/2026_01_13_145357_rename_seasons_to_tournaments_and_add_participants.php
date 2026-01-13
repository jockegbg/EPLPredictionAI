<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename 'seasons' to 'tournaments'
        Schema::rename('seasons', 'tournaments');

        // 2. Rename column in 'gameweeks'
        Schema::table('gameweeks', function (Blueprint $table) {
            // Drop foreign key first if it exists (Laravel default naming convention)
            // Note: Adjust the FK name if it differs in your DB
            $table->dropForeign(['season_id']);
            $table->renameColumn('season_id', 'tournament_id');

            // Re-add foreign key linking to new table name
            $table->foreign('tournament_id')->references('id')->on('tournaments')->onDelete('cascade');
        });

        // 3. Create pivot table 'tournament_user'
        Schema::create('tournament_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop pivot table
        Schema::dropIfExists('tournament_user');

        // 2. Revert column in 'gameweeks'
        Schema::table('gameweeks', function (Blueprint $table) {
            $table->dropForeign(['tournament_id']);
            $table->renameColumn('tournament_id', 'season_id');
            // We can't easily re-add the FK to 'seasons' until we rename 'tournaments' back
        });

        // 3. Rename 'tournaments' back to 'seasons'
        Schema::rename('tournaments', 'seasons');

        // 4. Re-add FK
        Schema::table('gameweeks', function (Blueprint $table) {
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });
    }
};
