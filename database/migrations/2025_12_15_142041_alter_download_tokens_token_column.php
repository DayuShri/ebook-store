<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('download_tokens', function (Blueprint $table) {
            // hapus unique lama dulu (nama index default laravel biasanya: download_tokens_token_unique)
            $table->dropUnique('download_tokens_token_unique');

            // ubah token dari text -> string(191)
            $table->string('token', 191)->change();

            // buat unique lagi
            $table->unique('token');
        });
    }

    public function down(): void
    {
        Schema::table('download_tokens', function (Blueprint $table) {
            $table->dropUnique('download_tokens_token_unique');
            $table->text('token')->change();
            // NOTE: kalau balik ke text dan tetap unique, MySQL bakal error lagi.
            // Jadi down ini opsional / biarkan tanpa unique.
        });
    }
};
