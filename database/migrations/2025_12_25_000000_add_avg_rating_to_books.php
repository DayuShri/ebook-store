<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add avg_rating column to books table
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('avg_rating', 3, 2)->nullable()->after('price');
        });

        // Calculate and populate avg_rating for existing books with reviews
        DB::statement("
            UPDATE books b
            SET avg_rating = (
                SELECT AVG(rating)
                FROM reviews r
                WHERE r.book_id = b.id
            )
            WHERE EXISTS (
                SELECT 1 FROM reviews r WHERE r.book_id = b.id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('avg_rating');
        });
    }
};
