<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AUTHORS
        Schema::create('authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->text('biography')->nullable();
            $table->text('photo_url')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // PUBLISHERS
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

        // BOOKS
        Schema::create('books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->text('synopsis')->nullable();
            $table->text('cover_image_url')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->date('publication_date')->nullable();
            $table->integer('page_count')->nullable();
            $table->string('language', 10)->default('id');
            $table->string('file_format', 20)->nullable(); // pdf, epub, mobi
            $table->decimal('file_size_mb', 8, 2)->nullable();
            $table->uuid('publisher_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index('title');
            $table->index('publisher_id');

            $table->foreign('publisher_id')
                ->references('id')->on('publishers')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // BOOK AUTHORS (pivot Many-to-Many)
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

        // BOOK CATEGORIES
        Schema::create('book_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->uuid('parent_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('parent_id')
                ->references('id')->on('book_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        // BOOK CATEGORY MAPPINGS (pivot)
        Schema::create('book_category_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id');
            $table->uuid('category_id');

            $table->unique(['book_id', 'category_id']);

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('category_id')
                ->references('id')->on('book_categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_category_mappings');
        Schema::dropIfExists('book_categories');
        Schema::dropIfExists('book_authors');
        Schema::dropIfExists('books');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
    }
};
