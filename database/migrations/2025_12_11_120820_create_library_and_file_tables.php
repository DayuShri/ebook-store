<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // LIBRARY ITEMS
        Schema::create('library_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->uuid('order_id')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE | REVOKED
            $table->timestampTz('granted_at')->useCurrent();
            $table->timestampTz('revoked_at')->nullable();

            $table->unique(['user_id', 'book_id']);
            $table->index('user_id');
            $table->index('book_id');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        // BOOK FILES
        Schema::create('book_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id')->unique();
            $table->text('file_path');
            $table->text('file_format'); // pdf, epub, mobi
            $table->decimal('file_size_mb', 8, 2)->nullable();
            $table->text('encryption_key')->nullable();
            $table->text('checksum')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // DOWNLOAD TOKENS
        Schema::create('download_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->string('token')->unique();
            $table->timestampTz('expires_at');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('used_at')->nullable();
            $table->integer('max_downloads')->default(3);
            $table->integer('download_count')->default(0);

            $table->index('user_id');
            $table->index('book_id');
            $table->index('expires_at');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_tokens');
        Schema::dropIfExists('book_files');
        Schema::dropIfExists('library_items');
    }
};
