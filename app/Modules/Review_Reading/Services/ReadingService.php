<?php

namespace App\Modules\Review_Reading\Services;

use App\Modules\Review_Reading\Models\ReadingProgress;
use App\Modules\Review_Reading\Models\ReadingSession;
use App\Modules\Review_Reading\Contracts\LibraryAccessService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReadingService
{
    protected $libraryAccess;

    public function __construct(LibraryAccessService $libraryAccess)
    {
        $this->libraryAccess = $libraryAccess;
    }
    public function start(string $userId, string $bookId, ?string $deviceInfo = null): void
    {
        // 🔐 CEK AKSES BACA
        $this->ensureUserCanRead($userId, $bookId);

        $progress = ReadingProgress::firstOrCreate(
            [
                'user_id' => $userId,
                'book_id' => $bookId
            ],
            [
                'id' => Str::uuid(),
                'total_pages' => 200, // nanti ambil dari books.page_count
                'last_page_read' => 0,
                'progress_percentage' => 0,
                'last_read_at' => now(),
                'device_info' => $deviceInfo
            ]
        );

        ReadingSession::create([
            'id' => Str::uuid(),
            'reading_progress_id' => $progress->id,
            'started_at' => now()
        ]);
    }


    public function finish(string $userId, string $bookId, int $lastPage): void
    {
        $this->ensureUserCanRead($userId, $bookId);


        $progress = ReadingProgress::where([
            'user_id' => $userId,
            'book_id' => $bookId
        ])->firstOrFail();

        // Clamp halaman
        $lastPage = min($lastPage, $progress->total_pages);
        $lastPage = max($lastPage, 0);

        $percentage = round(
            ($lastPage / max($progress->total_pages, 1)) * 100,
            2
        );

        $progress->update([
            'last_page_read' => $lastPage,
            'progress_percentage' => $percentage,
            'last_read_at' => now()
        ]);

        $session = ReadingSession::where('reading_progress_id', $progress->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($session) {
            $session->update([
                'ended_at' => now(),
                'duration_minutes' => max(
                    1,
                    now()->diffInMinutes($session->started_at)
                ),
                'pages_read' => max(
                    0,
                    $lastPage - $progress->last_page_read
                )
            ]);
        }
    }

    private function ensureUserCanRead(string $userId, string $bookId): void
    {
        $hasAccess = $this->libraryAccess->userHasBook($userId, $bookId);

        if (!$hasAccess) {
            throw ValidationException::withMessages([
                'book_id' => 'You do not have access to read this book.'
            ]);
        }
    }

    public function getProgress($userId, $bookId)
    {
        return ReadingProgress::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->firstOrFail();
    }

    public function updateProgress(string $userId, string $bookId, int $currentPage): ReadingProgress
    {
        $this->ensureUserCanRead($userId, $bookId);

        $progress = ReadingProgress::where([
            'user_id' => $userId,
            'book_id' => $bookId
        ])->firstOrFail();

        $currentPage = min($currentPage, $progress->total_pages);
        $currentPage = max($currentPage, 0);

        $percentage = round(
            ($currentPage / max($progress->total_pages, 1)) * 100,
            2
        );

        $progress->update([
            'last_page_read' => $currentPage,
            'progress_percentage' => $percentage,
            'last_read_at' => now()
        ]);

        return $progress->fresh();
    }
}
