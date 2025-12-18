<?php

namespace App\Modules\Library\Controllers\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReaderController extends Controller
{
    public function show(Request $request, $bookId)
    {
        // Get book details (basic info for page title)
        $book = DB::table('books')->where('id', $bookId)->first();
        
        $bookTitle = $book ? $book->title : 'E-Book Reader';
        
        // Get primary file format (prefer PDF, fallback to EPUB)
        $fileFormat = DB::table('book_files')
            ->where('book_id', $bookId)
            ->orderByRaw("CASE WHEN file_format = 'PDF' THEN 1 WHEN file_format = 'EPUB' THEN 2 ELSE 3 END")
            ->value('file_format') ?? 'PDF';
        
        // Return reader view - authentication will be handled by JavaScript
        return view('reader.viewer', [
            'bookId' => $bookId,
            'bookTitle' => $bookTitle,
            'fileFormat' => $fileFormat
        ]);
    }
}
