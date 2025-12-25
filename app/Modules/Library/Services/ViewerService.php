<?php

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\ViewerSession;
use App\Modules\Library\Models\BookFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ViewerService
{
    public function createSession(string $userId, string $bookId, ?string $format = null, ?string $deviceInfo = null, ?string $ip = null, int $minutes = 120): ViewerSession
    {
        $fileQuery = BookFile::where('book_id', $bookId);
        if ($format) {
            $fileQuery->where('file_format', $format);
        }

        $file = $fileQuery->first();
        if (! $file) {
            $file = BookFile::where('book_id', $bookId)->first();
        }
        
        if (! $file) {
            throw new \RuntimeException('File not found for book');
        }

        $token = Str::uuid()->toString();
        
        $session = ViewerSession::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'book_id' => $bookId,
            'file_id' => $file->id,
            'file_format' => $file->file_format,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes($minutes),
            'created_at' => Carbon::now(),
            'device_info' => $deviceInfo,
            'ip_address' => $ip,
        ]);

        return $session;
    }

    public function validateTokenAndGetFile(string $token)
    {
        $session = ViewerSession::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $session) {
            return null;
        }

        if (!empty($session->file_id)) {
            $file = BookFile::find($session->file_id);
        } elseif (!empty($session->file_format)) {
            $file = BookFile::where('book_id', $session->book_id)
                ->where('file_format', $session->file_format)
                ->first();
        } else {
            $file = BookFile::where('book_id', $session->book_id)->first();
        }
        
        if (! $file) {
            return null;
        }

        return [
            'session'   => $session,
            'file_path' => $file->file_path,
            'file_format' => $file->file_format
        ];
    }
}