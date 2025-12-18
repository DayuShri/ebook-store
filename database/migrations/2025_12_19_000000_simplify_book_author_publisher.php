<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key and pivot table first
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['publisher_id']);
            $table->dropColumn('publisher_id');
        });

        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('publishers');

        // Add simple string columns
        Schema::table('books', function (Blueprint $table) {
            $table->string('author', 500)->nullable()->after('synopsis');
            $table->string('publisher', 255)->nullable()->after('author');
        });
    }

    public function down(): void
    {
        // Remove string columns
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['author', 'publisher']);
        });

        // Recreate publishers table
        Schema::create('publishers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->integer('established_year')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Recreate authors table
        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('biography')->nullable();
            $table->text('photo_url')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Recreate book_authors pivot
        Schema::create('book_authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id');
            $table->uuid('author_id');
            $table->integer('author_order')->default(1);
            $table->unique(['book_id', 'author_id']);

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('author_id')
                ->references('id')->on('authors')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // Add back publisher_id to books
        Schema::table('books', function (Blueprint $table) {
            $table->uuid('publisher_id')->nullable()->after('file_size_mb');

            $table->foreign('publisher_id')
                ->references('id')->on('publishers')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }
};
