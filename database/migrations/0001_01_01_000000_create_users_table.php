<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // USERS
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // pakai UUID, bukan increments
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->enum('role', ['user', 'admin'])->default('user'); // silakan sesuaikan jika perlu
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // REFRESH TOKENS
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 500);
            $table->timestamp('expires_at');
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique('token');
            $table->index('user_id');

            // FK (boleh dihapus kalau pengen pure microservice)
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('users');
    }
};
