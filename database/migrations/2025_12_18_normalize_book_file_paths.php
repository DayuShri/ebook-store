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
        // Normalize book_files.file_path to match Supabase bucket structure: books/books/<id>/<file>
        
        DB::table('book_files')->whereNotNull('file_path')->orderBy('id')->chunk(100, function ($records) {
            foreach ($records as $record) {
                $path = $record->file_path;
                
                // Skip external URLs
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    continue;
                }
                
                // Normalize: ensure path starts with books/
                // If path is just "c42cc919-.../file.epub", prepend "books/"
                if (!str_starts_with($path, 'books/')) {
                    $path = 'books/' . ltrim($path, '/');
                }
                
                // If path is "books/c42cc919-...", prepend another "books/" to match Supabase structure
                // (Supabase UI shows: bucket/books/books/<id>/<file>)
                if (str_starts_with($path, 'books/') && !str_starts_with($path, 'books/books/')) {
                    $path = 'books/' . $path;
                }
                
                // Update if changed
                if ($path !== $record->file_path) {
                    DB::table('book_files')
                        ->where('id', $record->id)
                        ->update(['file_path' => $path]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: strip leading "books/" prefix to go back to original format
        DB::table('book_files')->whereNotNull('file_path')->orderBy('id')->chunk(100, function ($records) {
            foreach ($records as $record) {
                $path = $record->file_path;
                
                // Skip external URLs
                if (filter_var($path, FILTER_VALIDATE_URL)) {
                    continue;
                }
                
                // Remove one "books/" prefix if path starts with "books/books/"
                if (str_starts_with($path, 'books/books/')) {
                    $path = substr($path, 6); // remove first "books/"
                }
                
                // Update if changed
                if ($path !== $record->file_path) {
                    DB::table('book_files')
                        ->where('id', $record->id)
                        ->update(['file_path' => $path]);
                }
            }
        });
    }
};
