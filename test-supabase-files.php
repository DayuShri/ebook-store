<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Modules\Library\Services\SupabaseService;
use Illuminate\Support\Facades\DB;

$service = app(SupabaseService::class);

echo "Testing Supabase file access for test books...\n\n";

$books = DB::table('book_files')
    ->whereIn('book_id', ['book-001', 'book-002', 'book-003'])
    ->get(['book_id', 'file_path', 'file_format']);

foreach ($books as $book) {
    echo "Testing {$book->book_id} ({$book->file_format})...\n";
    echo "Path: {$book->file_path}\n";
    
    try {
        $url = $service->getSignedUrl($book->file_path, 60);
        echo "✓ SUCCESS - Signed URL generated\n";
        echo "URL: $url\n";
    } catch (Exception $e) {
        echo "✗ FAILED - {$e->getMessage()}\n";
    }
    
    echo "\n";
}
