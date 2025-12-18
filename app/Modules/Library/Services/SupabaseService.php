<?php

namespace App\Modules\Library\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SupabaseService
{
    protected string $url;
    protected string $key;
    protected string $serviceRoleKey;
    protected string $defaultBucket;

    public function __construct()
    {
        $rawUrl = env('SUPABASE_URL', env('AWS_URL', ''));
        $this->url = $rawUrl ? rtrim($rawUrl, '/') : '';
        $this->key = env('SUPABASE_KEY', '');
        $this->serviceRoleKey = env('SUPABASE_SERVICE_ROLE_KEY', '');
        $this->defaultBucket = env('SUPABASE_BUCKET', env('AWS_BUCKET', 'bukuku'));
    }

    public function uploadFile(UploadedFile $file, string $folder, string $filename): string
    {
        try {
            $disk = Storage::disk('supabase');

            $path = $disk->putFileAs(
                $folder,
                $file,
                $filename
            );

            if ($path) {
                return $path;
            }
        } catch (\Throwable $e) {
            // Fallback to manual upload if S3 driver fails
        }

        $bucket = $this->defaultBucket;
        $objectPath = trim($folder, '/') . '/' . ltrim($filename, '/');

        $base = rtrim($this->url, '/');
        if ($base === '') {
            throw new \RuntimeException('Supabase URL is not configured');
        }

        if (Str::endsWith($base, '/storage/v1/s3')) {
            $apiBase = Str::replaceLast('/s3', '', $base); 
            $uploadUrl = $apiBase . '/object/' . $bucket . '/' . $objectPath;
        } elseif (Str::contains($base, '/storage/v1')) {
             $uploadUrl = $base . '/object/' . $bucket . '/' . $objectPath;
        } else {
             $uploadUrl = $base . '/storage/v1/object/' . $bucket . '/' . $objectPath;
        }

        $mime = $file->getClientMimeType() ?? 'application/octet-stream';
        $body = file_get_contents($file->getRealPath());

        $uploadKey = !empty($this->serviceRoleKey) ? $this->serviceRoleKey : $this->key;

        $put = Http::withoutVerifying() 
            ->withHeaders([
                'Authorization' => 'Bearer ' . $uploadKey,
                'Content-Type' => $mime,
            ])
            ->withBody($body, $mime)
            ->put($uploadUrl);

        if (! $put->successful()) {
            throw new \RuntimeException('Failed to upload to Supabase (fallback). URL: ' . $uploadUrl . ' ; Error: ' . $put->body());
        }

        return $objectPath;
    }

    public function uploadFromUrl(string $fileUrl, ?string $objectPath = null, ?string $bucket = null): string
    {
        $bucket = $bucket ?: $this->defaultBucket;
        $mime = 'application/octet-stream';

        $resp = Http::withoutVerifying()->get($fileUrl);
        
        if (! $resp->ok()) {
            throw new \RuntimeException('Failed to fetch remote file');
        }

        $body = $resp->body();
        $mime = $resp->header('Content-Type') ?? $mime;

        $objectPath = $objectPath ?: Str::uuid()->toString() . ($this->guessExtension($mime) ? '.' . $this->guessExtension($mime) : '');

        $uploadUrl = $this->url . '/storage/v1/object/' . $bucket . '/' . $objectPath;
        $uploadKey = !empty($this->serviceRoleKey) ? $this->serviceRoleKey : $this->key;

        $put = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $uploadKey,
                'Content-Type' => $mime,
            ])
            ->withBody($body, $mime)
            ->put($uploadUrl);

        if (! $put->successful()) {
            throw new \RuntimeException('Failed to upload to Supabase: ' . $put->body());
        }

        return $objectPath;
    }

    public function fileExists(string $storagePath): bool
    {
        $bucket = $this->defaultBucket;
        $base = rtrim($this->url, '/');
        
        $objectPath = ltrim($storagePath, '/');
        $encodedPath = implode('/', array_map('urlencode', explode('/', $objectPath)));
        
        // URL: {base}/storage/v1/object/{bucket}/{object_path}
        $checkUrl = $base . '/storage/v1/object/' . $bucket . '/' . $encodedPath;
        
        \Log::info("Checking file exists - Bucket: {$bucket}, Path: {$objectPath}, URL: {$checkUrl}");
        
        try {
            $resp = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->key,
                    'apikey' => $this->key,
                ])
                ->head($checkUrl);
            
            $exists = $resp->successful();
            \Log::info("File exists check result: " . ($exists ? 'YES' : 'NO'));
            return $exists;
        } catch (\Throwable $e) {
            \Log::error("File exists check error: " . $e->getMessage());
            return false;
        }
    }

    public function signedUrl(string $storagePath, int $expiresIn = 3600): ?string
    {
        if (filter_var($storagePath, FILTER_VALIDATE_URL)) {
            return $storagePath;
        }
        
        $bucket = $this->defaultBucket;
        $base = rtrim($this->url, '/');
        
        if (empty($base)) {
            throw new \RuntimeException('Supabase URL is not configured');
        }

        if (empty($this->key)) {
            throw new \RuntimeException('Supabase key is not configured');
        }

        $objectPath = ltrim($storagePath, '/');
        
        \Log::info("Generating signed URL - Bucket: {$bucket}, Object path: {$objectPath}");

        $encodedPath = implode('/', array_map('urlencode', explode('/', $objectPath)));
        
        $signUrl = $base . '/storage/v1/object/sign/' . $bucket . '/' . $encodedPath;
        
        \Log::info("Sign endpoint URL: {$signUrl}");

        try {
            $resp = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->key,
                    'apikey' => $this->key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($signUrl, ['expiresIn' => $expiresIn]);

            \Log::info('SupabaseService::signedUrl - Response status: ' . $resp->status() . ' | Body: ' . $resp->body());

            if (! $resp->ok()) {
                $errorMsg = 'Failed to generate signed URL. Path: ' . $objectPath . ' | URL: ' . $signUrl . ' | Status: ' . $resp->status() . ' | Response: ' . $resp->body();
                \Log::error($errorMsg);
                
                $jsonError = $resp->json();
                if (is_array($jsonError) && isset($jsonError['error'])) {
                    throw new \RuntimeException('Supabase error: ' . $jsonError['error'] . ' | Path: ' . $objectPath);
                }
                
                throw new \RuntimeException($errorMsg);
            }

            $json = $resp->json();
            if (is_array($json)) {
                foreach (['signedURL','signedUrl','signed_url','url'] as $k) {
                    if (isset($json[$k]) && ! empty($json[$k])) {
                        $val = $json[$k];
                        
                        if (! filter_var($val, FILTER_VALIDATE_URL)) {
                            if (Str::startsWith($val, '/object/sign/')) {
                                $val = $base . '/storage/v1' . $val;
                            } else {
                                $val = $base . (Str::startsWith($val, '/') ? $val : '/' . $val);
                            }
                        }
                        
                        if (filter_var($val, FILTER_VALIDATE_URL)) {
                            return $val;
                        }
                    }
                }
            }

            $errorMsg = 'No signed URL found in response: ' . $resp->body();
            \Log::error($errorMsg);
            throw new \RuntimeException($errorMsg);

        } catch (\Throwable $e) {
            \Log::error('Exception generating signed URL for path: ' . $objectPath . ' | Error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function guessExtension(string $mime): ?string
    {
        $map = [
            'application/pdf' => 'pdf',
            'application/epub+zip' => 'epub',
            'application/x-mobipocket-ebook' => 'mobi',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        return $map[$mime] ?? null;
    }
}