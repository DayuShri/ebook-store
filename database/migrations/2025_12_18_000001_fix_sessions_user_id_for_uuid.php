<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This fixes the sessions table to work with UUID user IDs
     * instead of integer user IDs.
     */
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop the existing index on user_id
            $table->dropIndex('sessions_user_id_index');
        });

        // Alter the column type from bigint to string to support UUIDs
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('user_id', 36)->nullable()->change();
        });

        Schema::table('sessions', function (Blueprint $table) {
            // Re-add the index
            $table->index('user_id', 'sessions_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_user_id_index');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id', 'sessions_user_id_index');
        });
    }
};
