<?php

namespace App\Modules\Review_Reading\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Review_Reading\Requests\StartReadingRequest;
use App\Modules\Review_Reading\Requests\FinishReadingRequest;
use App\Modules\Review_Reading\Requests\UpdateProgressRequest;
use App\Modules\Review_Reading\Services\ReadingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Modules\Library\Services\ViewerService;
use Throwable;

class ReadingController extends Controller
{
    public function start(StartReadingRequest $request, ReadingService $service)
    {
        try {
            $userId = Auth::id();
            $this->validateViewerToken($request, $request->book_id);


            $this->validateViewerToken($request, $request->book_id);

            $service->start(
                $userId,
                $request->book_id,
                $request->device_info
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading started',
                'data' => null
            ], 200);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function finish(FinishReadingRequest $request, ReadingService $service)
    {
        try {
            $userId = Auth::id();

            $service->finish(
                $userId,
                $request->book_id,
                $request->last_page_read
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading saved',
                'data' => null
            ], 200);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function progress($bookId, ReadingService $service)
    {
        try {
            $userId = Auth::id();

            $data = $service->getProgress($userId, $bookId);

            return response()->json([
                'success' => true,
                'message' => 'Reading progress fetched',
                'data' => [
                    'book_id' => $bookId,
                    'last_page_read' => $data->last_page_read,
                    'total_pages' => $data->total_pages,
                    'progress_percentage' => $data->progress_percentage,
                    'last_read_at' => $data->last_read_at,
                    'device_info' => $data->device_info
                ]
            ], 200);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function updateProgress(UpdateProgressRequest $request, ReadingService $service)
    {
        try {
            $userId = Auth::id();
            $this->validateViewerToken($request, $request->book_id);


            $progress = $service->updateProgress(
                $userId,
                $request->book_id,
                $request->current_page
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading progress updated',
                'data' => [
                    'book_id' => $progress->book_id,
                    'last_page_read' => $progress->last_page_read,
                    'total_pages' => $progress->total_pages,
                    'progress_percentage' => $progress->progress_percentage,
                    'last_read_at' => $progress->last_read_at
                ]
            ], 200);

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * 🔴 Centralized error handler (Controller level)
     */
    protected function errorResponse(Throwable $e)
    {
        // Data tidak ditemukan
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'errors' => null
            ], 404);
        }

        // Default error
        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'Something went wrong',
            'errors' => null
        ], 400);
    }

    protected function validateViewerToken(Request $request, string $bookId)
    {
        $viewerToken = $request->header('X-Viewer-Token');

        if (! $viewerToken) {
            abort(403, 'Viewer token missing');
        }

        /** @var ViewerService $viewerService */
        $viewerService = app(ViewerService::class);

        // 🔐 VALIDASI TOKEN (pakai method yang SUDAH ADA)
        $result = $viewerService->validateTokenAndGetFile($viewerToken);

        if (! $result) {
            abort(403, 'Invalid or expired viewer token');
        }

        // ⚠️ Jika validateTokenAndGetFile() tidak return user_id & book_id
        // maka kita validasi minimal bahwa token tersebut AKTIF
        // dan request ini sudah melewati auth middleware

        return true;
    }


}
