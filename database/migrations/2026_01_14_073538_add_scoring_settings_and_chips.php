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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->integer('score_correct_score')->default(40);
            $table->integer('score_correct_outcome')->default(10);
            $table->integer('score_goal_difference')->default(0);
            $table->integer('score_wrong_outcome_penalty')->default(0);
            $table->boolean('is_cashout_enabled')->default(false);
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->boolean('is_defence_chip')->default(false);
            $table->timestamp('cashed_out_at')->nullable();
            $table->integer('cashout_points')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'score_correct_score',
                'score_correct_outcome',
                'score_goal_difference',
                'score_wrong_outcome_penalty',
                'is_cashout_enabled',
            ]);
        });

        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn(['is_defence_chip', 'cashed_out_at', 'cashout_points']);
        });
    }
};
