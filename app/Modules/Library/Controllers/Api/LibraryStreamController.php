<?php

namespace App\Modules\Library\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Library\Services\ViewerService;
use App\Modules\Library\Services\SupabaseService;
use Illuminate\Support\Facades\Log;

class LibraryStreamController extends Controller
{
    protected ViewerService $viewerService;
    protected SupabaseService $supabaseService;

    public function __construct(
        ViewerService $viewerService,
        SupabaseService $supabaseService
    ) {
        $this->viewerService = $viewerService;
        $this->supabaseService = $supabaseService;
    }

    /**
     * Stream book file from Supabase
     */
    public function stream(string $token)
    {
        try {
            // Validate token and get file info
            $data = $this->viewerService->validateTokenAndGetFile($token);
            
            if (!$data) {
                Log::warning('Invalid or expired viewer token', ['token' => $token]);
                abort(403, 'Invalid or expired token');
            }

            Log::info('Streaming file', [
                'token' => $token,
                'file_path' => $data['file_path'],
                'format' => $data['file_format']
            ]);

            // Get signed URL from Supabase with longer expiry for EPUBs
            $expirySeconds = $data['file_format'] === 'epub' ? 7200 : 3600;
            $signedUrl = $this->supabaseService->getSignedUrl(
                $data['file_path'],
                $expirySeconds
            );

            // For EPUB, return JSON with signed URL (ePub.js needs direct URL)
            // For PDF, redirect (PDF.js can follow redirects)
            if ($data['file_format'] === 'epub') {
                return response()->json([
                    'success' => true,
                    'url' => $signedUrl,
                    'format' => 'epub',
                    'expires_in' => $expirySeconds
                ]);
            }

            // Redirect to Supabase signed URL for PDF
            return redirect($signedUrl);
        } catch (\Exception $e) {
            Log::error('Stream error: ' . $e->getMessage(), [
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Failed to stream file: ' . $e->getMessage());
        }
    }
}
