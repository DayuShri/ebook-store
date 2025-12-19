<?php

namespace App\Modules\Review_Reading\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Review_Reading\Requests\StoreReviewRequest;
use App\Modules\Review_Reading\Requests\ReviewHelpfulRequest;
use App\Modules\Review_Reading\Requests\UpdateReviewRequest;
use App\Modules\Review_Reading\Services\ReviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, ReviewService $service)
    {
        try {
            $service->store(
                Auth::id(),
                $request->validated()
            );

            return $this->successResponse(
                'Review submitted'
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function helpful(ReviewHelpfulRequest $request, ReviewService $service)
    {
        try {
            $service->helpful(
                Auth::id(),
                $request->review_id,
                $request->is_helpful
            );

            return $this->successResponse(
                'Review feedback saved'
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function index(string $bookId, ReviewService $service)
    {
        try {
            $reviews = $service->getByBook($bookId);

            return $this->successResponse(
                'Review list fetched',
                $reviews
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(
        string $reviewId,
        UpdateReviewRequest $request,
        ReviewService $service
    ) {
        try {
            $service->update(
                $reviewId,
                Auth::id(),
                $request->validated()
            );

            return $this->successResponse(
                'Review updated'
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy(
        string $reviewId,
        ReviewService $service
    ) {
        try {
            $service->delete(
                $reviewId,
                Auth::id()
            );

            return $this->successResponse(
                'Review deleted'
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function userReviews(ReviewService $service)
    {
        try {
            $reviews = $service->getUserReviews(Auth::id());

            return $this->successResponse(
                'User reviews fetched',
                $reviews
            );

        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /* =======================
       RESPONSE FORMATTER
    ======================= */

    protected function successResponse(
        string $message,
        $data = null,
        int $status = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function errorResponse(Throwable $e)
    {
        // Validation error
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // Data not found
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
}
