<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. LIBRARY ITEMS
        Schema::create('library_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->uuid('order_id')->nullable(); // ID dari Order Service (External)
            $table->string('status')->default('ACTIVE'); // ACTIVE | REVOKED
            $table->timestampTz('granted_at')->useCurrent();
            $table->timestampTz('revoked_at')->nullable();

            // Indexes
            $table->unique(['user_id', 'book_id']);
            $table->index('user_id');
            $table->index('book_id');

            // Foreign Keys
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            
            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            
            // Note: order_id tidak diberi foreign key jika merujuk ke database/service berbeda
        });

        // 2. BOOK FILES (Support Multi-Format)
        Schema::create('book_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('book_id'); // Unique dihapus di sini
            $table->text('file_path'); // Path di Supabase Storage
            $table->string('file_format'); // pdf, epub, mobi
            $table->decimal('file_size_mb', 8, 2)->nullable();
            $table->text('encryption_key')->nullable();
            $table->text('checksum')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Index: Satu buku hanya boleh punya satu file per format
            $table->unique(['book_id', 'file_format']);

            $table->foreign('book_id')
                ->references('id')->on('books')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // 3. VIEWER SESSIONS (Menggantikan Download Tokens)
        Schema::create('viewer_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('book_id');
            $table->string('token')->unique(); // Token secure untuk akses API Stream
            $table->timestampTz('expires_at');
            $table->timestampTz('created_at')->useCurrent();
            
            // Security & Limit Logic
            $table->timestampTz('last_activity_at')->nullable();
            $table->text('device_info')->nullable(); // e.g., "Chrome on MacOS"
            $table->string('ip_address', 45)->nullable(); // Support IPv4 & IPv6

            // Indexes
            $table->index('user_id');
            $table->index('book_id');
            $table->index('expires_at');

            // Foreign Keys
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
        Schema::dropIfExists('viewer_sessions');
        Schema::dropIfExists('book_files');
        Schema::dropIfExists('library_items');
    }
};