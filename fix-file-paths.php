<?php

/**
 * Script untuk memperbaiki file_path di database
 * Menghapus prefix 'books/' dari path yang sudah tersimpan
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Library\Models\BookFile;
use Illuminate\Support\Facades\DB;

echo "Fixing file paths in book_files table...\n\n";

$files = BookFile::all();

if ($files->isEmpty()) {
    echo "No files found in database.\n";
    exit(0);
}

$updated = 0;
$skipped = 0;

foreach ($files as $file) {
    $oldPath = $file->file_path;
    
    // Skip if it's a URL
    if (filter_var($oldPath, FILTER_VALIDATE_URL)) {
        echo "Skipping URL: {$oldPath}\n";
        $skipped++;
        continue;
    }
    
    // Normalize: should be "book/{book_id}/{filename}" (singular "book")
    $newPath = ltrim($oldPath, '/');
    
    // Change "books/" to "book/" (plural to singular)
    if (str_starts_with($newPath, 'books/')) {
        $newPath = 'book/' . substr($newPath, 6);
    }
    
    // Ensure it starts with 'book/' (add if missing)
    if (!str_starts_with($newPath, 'book/') && !filter_var($newPath, FILTER_VALIDATE_URL)) {
        $newPath = 'book/' . $newPath;
    }
    
    if ($oldPath !== $newPath) {
        echo "Updating: {$oldPath} -> {$newPath}\n";
        $file->file_path = $newPath;
        $file->save();
        $updated++;
    } else {
        echo "No change needed: {$oldPath}\n";
        $skipped++;
    }
}

echo "\n";
echo "Summary:\n";
echo "- Updated: {$updated}\n";
echo "- Skipped: {$skipped}\n";
echo "- Total: " . ($updated + $skipped) . "\n";
