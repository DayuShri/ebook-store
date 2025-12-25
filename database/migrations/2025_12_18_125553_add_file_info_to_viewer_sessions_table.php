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
        Schema::table('viewer_sessions', function (Blueprint $table) {
            $table->uuid('file_id')->nullable()->after('book_id');
            $table->string('file_format', 10)->nullable()->after('file_id'); // pdf, epub, mobi
            
            $table->foreign('file_id')
                ->references('id')->on('book_files')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viewer_sessions', function (Blueprint $table) {
            $table->dropForeign(['file_id']);
            $table->dropColumn(['file_id', 'file_format']);
        });
    }
};
