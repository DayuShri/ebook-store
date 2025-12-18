<?php

namespace App\Modules\Library\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Library\Services\ViewerService;
use App\Modules\Library\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Modules\Library\Traits\ApiResponse;

class ViewerController extends Controller
{
    use ApiResponse;

    protected $service;
    protected $supabase;

    public function __construct(ViewerService $service, SupabaseService $supabase)
    {
        $this->service = $service;
        $this->supabase = $supabase;
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', null, 401);
        }

        $data = $request->only(['book_id', 'format']);

        $validator = Validator::make($data, [
            'book_id' => ['required', 'uuid'],
            'format' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors()->messages(), 422);
        }

        try {
            $session = $this->service->createSession(
                $user->id,
                $data['book_id'],
                $data['format'] ?? null,
                $request->header('User-Agent'),
                $request->ip()
            );
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }

        $streamUrl = url('/api/v1/library/stream/' . $session->token);

        return $this->successResponse('Viewer session created', [
            'token' => $session->token,
            'expires_at' => $session->expires_at,
            'stream_url' => $streamUrl,
        ], 201);
    }

    public function stream(Request $request, $token)
    {
        \Log::info("=== Stream Request Start === Token: {$token}");

        // 1. Validate token and get file path
        $res = $this->service->validateTokenAndGetFile($token);

        if (! $res || empty($res['file_path'])) {
            \Log::warning("Stream request failed - Invalid or expired token: {$token}");
            return $this->errorResponse('Invalid or expired token', null, 403);
        }

        $filePath = $res['file_path'];
        \Log::info("Token valid - File path from DB: {$filePath}");

        // 2. Generate signed URL from Supabase
        try {
            $signedUrl = $this->supabase->signedUrl($filePath);
            
            if (! $signedUrl) {
                throw new \RuntimeException('Signed URL is empty');
            }
            
            \Log::info("Signed URL generated successfully: {$signedUrl}");
            \Log::info("=== Stream Request Success ===");

            // 3. Redirect user to Supabase signed URL
            return redirect()->away($signedUrl);

        } catch (\Throwable $e) {
            \Log::error("Failed to generate signed URL: " . $e->getMessage());
            \Log::error("=== Stream Request Failed ===");
            return $this->errorResponse('Failed to generate secure link: ' . $e->getMessage(), null, 500);
        }
    }
}