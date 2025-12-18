<?php

namespace App\Modules\Library\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Library\Models\BookFile;
use Illuminate\Http\Request;
use App\Modules\Library\Traits\ApiResponse;
use App\Modules\Library\Services\SupabaseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BookFileController extends Controller
{
    use ApiResponse;
    
    protected SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function show(Request $request, $bookId, $format)
    {
        $file = BookFile::where('book_id', $bookId)
            ->where('file_format', $format)
            ->first();

        if (! $file) {
            return $this->errorResponse('File not found', null, 404);
        }

        $downloadUrl = null;
        if ($request->query('with_link')) {
            $downloadUrl = $this->supabase->signedUrl($file->file_path);
        }

        return $this->successResponse('File retrieved', [
            'id' => $file->id,
            'file_path' => $file->file_path,
            'file_format' => $file->file_format,
            'file_size_mb' => $file->file_size_mb,
            'checksum' => $file->checksum,
            'download_url' => $downloadUrl
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user || ($user->role ?? '') !== 'admin') {
            return $this->errorResponse('Forbidden: only admin can upload files', null, 403);
        }

        $validator = Validator::make($request->all(), [
            'book_id' => ['required', 'uuid'],
            'file'    => ['required', 'file', 'mimes:pdf,epub', 'max:100000'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->messages(), 422);
        }

        $file = $request->file('file');
        $bookId = $request->input('book_id');
        $extension = $file->getClientOriginalExtension();

        $existingFile = BookFile::where('book_id', $bookId)
            ->where('file_format', $extension)
            ->first();

        try {
            $filename = Str::random(40) . '.' . $extension;
            $folderPath = "book/{$bookId}";
            
            \Log::info("Uploading - Folder: {$folderPath}, File: {$filename}");

            $storagePath = $this->supabase->uploadFile($file, $folderPath, $filename);
            $storagePath = ltrim($storagePath, '/');
            
            if (strpos($storagePath, 'book/') !== 0) {
                $storagePath = 'book/' . $storagePath;
            }

            if (!$this->supabase->fileExists($storagePath)) {
                throw new \RuntimeException('File verification failed');
            }

            $sizeInMb = round($file->getSize() / 1024 / 1024, 2);
            $checksum = md5_file($file->getRealPath());
            $encryptionKey = Str::random(32);

        } catch (\Throwable $e) {
            \Log::error('Upload failed: ' . $e->getMessage());
            return $this->errorResponse('Upload failed: ' . $e->getMessage(), null, 500);
        }

        if ($existingFile) {
            $existingFile->update([
                'file_path' => $storagePath,
                'file_size_mb' => $sizeInMb,
                'checksum' => $checksum,
                'encryption_key' => $encryptionKey,
            ]);
            $bookFile = $existingFile;
            $message = 'File replaced successfully';
        } else {
            $bookFile = BookFile::create([
                'id' => Str::uuid()->toString(),
                'book_id' => $bookId,
                'file_path' => $storagePath,
                'file_format' => $extension,
                'file_size_mb' => $sizeInMb,
                'checksum' => $checksum,
                'encryption_key' => $encryptionKey
            ]);
            $message = 'File uploaded successfully';
        }

        return $this->successResponse($message, [
            'id' => $bookFile->id,
            'file_path' => $bookFile->file_path,
            'file_format' => $bookFile->file_format,
            'file_size_mb' => $bookFile->file_size_mb,
        ], 201);
    }
}
