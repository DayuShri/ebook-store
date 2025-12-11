<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // REVIEWS
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->integer('rating'); // 1-5
            $table->text('review_text')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['user_id', 'book_id']);
            $table->index('book_id');
            $table->index('rating');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // REVIEW HELPFULNESS
        Schema::create('review_helpfulness', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_id');
            $table->uuid('user_id');
            $table->boolean('is_helpful');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['review_id', 'user_id']);

            $table->foreign('review_id')
                ->references('id')->on('reviews')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // READING PROGRESS
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->integer('last_page_read')->default(0);
            $table->integer('total_pages');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('last_read_at')->useCurrent();
            $table->string('device_info', 255)->nullable();

            $table->unique(['user_id', 'book_id']);
            $table->index('user_id');
            $table->index('last_read_at');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // READING SESSIONS
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reading_progress_id');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('pages_read')->default(0);

            $table->index('reading_progress_id');
            $table->index('started_at');

            $table->foreign('reading_progress_id')
                ->references('id')->on('reading_progress')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_sessions');
        Schema::dropIfExists('reading_progress');
        Schema::dropIfExists('review_helpfulness');
        Schema::dropIfExists('reviews');
    }
};
