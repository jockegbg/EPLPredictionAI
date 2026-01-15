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
        Schema::table('predictions', function (Blueprint $table) {
            $table->integer('points_adjustment')->default(0)->after('points_awarded');
        });

        Schema::table('gameweeks', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropColumn('points_adjustment');
        });

        Schema::table('gameweeks', function (Blueprint $table) {
            $table->dropColumn('is_custom');
        });
    }
};
