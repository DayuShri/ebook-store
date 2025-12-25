<?php

namespace App\Services;

use App\Modules\Library\Models\BookFile;
use App\Modules\Library\Services\SupabaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class LibraryFileUploader
{
    public function __construct(
        protected SupabaseService $supabase
    ) {}

    /**
     * Upload/replace book file (pdf/epub) + simpan metadata ke table book_files
     */
    public function upload(string $bookId, UploadedFile $file): BookFile
    {
        $extension = strtolower($file->getClientOriginalExtension()); // pdf / epub

        if (!in_array($extension, ['pdf', 'epub'])) {
            throw new \RuntimeException('Format file harus PDF atau EPUB');
        }

        // folder: book/{bookId}
        $folderPath = "book/{$bookId}";
        $filename   = Str::random(40) . '.' . $extension;

        // 1) upload ke Supabase
        $storagePath = $this->supabase->uploadFile($file, $folderPath, $filename);
        $storagePath = ltrim($storagePath, '/');

        // normalisasi supaya konsisten diawali "book/"
        if (strpos($storagePath, 'book/') !== 0) {
            $storagePath = 'book/' . $storagePath;
        }

        // optional: verifikasi ada di Supabase
        if (!$this->supabase->fileExists($storagePath)) {
            throw new \RuntimeException('Upload berhasil, tapi verifikasi file di storage gagal.');
        }

        // 2) hitung metadata
        $sizeInMb  = round($file->getSize() / 1024 / 1024, 2);
        $checksum  = md5_file($file->getRealPath());
        $encKey    = Str::random(32);

        // 3) replace jika sudah ada format sama
        $existing = BookFile::where('book_id', $bookId)
            ->where('file_format', $extension)
            ->first();

        if ($existing) {
            $existing->update([
                'file_path'      => $storagePath,
                'file_size_mb'   => $sizeInMb,
                'checksum'       => $checksum,
                'encryption_key' => $encKey,
            ]);

            return $existing->fresh();
        }

        return BookFile::create([
            'id'            => (string) Str::uuid(),
            'book_id'       => $bookId,
            'file_path'     => $storagePath,
            'file_format'   => $extension,
            'file_size_mb'  => $sizeInMb,
            'checksum'      => $checksum,
            'encryption_key'=> $encKey,
        ]);
    }
}
