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
        Schema::table('gameweeks', function (Blueprint $table) {
            $table->json('pundit_summary')->nullable()->after('image_path');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->json('ai_commentary')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gameweeks', function (Blueprint $table) {
            $table->dropColumn('pundit_summary');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('ai_commentary');
        });
    }
};
