<?php

namespace App\Services\Frontend;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Admin Frontend Service
 * 
 * This service provides admin functionality by calling Auth HMVC endpoints.
 */
class AdminService
{
    /**
     * Get the HMVC base URL for admin endpoints.
     */
    protected function apiUrl(string $path): string
    {
        $baseUrl = rtrim(config('app.url'), '/');
        return $baseUrl . '/api/v1/admin' . $path;
    }

    /**
     * Get authorization headers for API calls.
     */
    protected function getHeaders(): array
    {
        $user = auth()->user();
        if ($user) {
            $token = $user->createToken('admin_api')->plainTextToken;
            return [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ];
        }
        return ['Accept' => 'application/json'];
    }

    /**
     * Get all users with pagination.
     */
    public function getUsers(int $perPage = 15, int $page = 1): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->apiUrl('/users'), [
                    'per_page' => $perPage,
                    'page' => $page,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to get users', ['response' => $response->body()]);
            return ['data' => [], 'meta' => []];
        } catch (\Exception $e) {
            Log::error('Failed to get users', ['error' => $e->getMessage()]);
            return ['data' => [], 'meta' => []];
        }
    }

    /**
     * Get single user by ID.
     */
    public function getUser(string $id): ?array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->apiUrl("/users/{$id}"));

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get user', ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get user statistics.
     */
    public function getUserStatistics(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->get($this->apiUrl('/users/statistics'));

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get user statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->apiUrl('/users'), $data);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil dibuat', 'data' => $response->json()];
            }

            // Log the full response for debugging
            Log::error('Failed to create user', [
                'status' => $response->status(),
                'response' => $response->json(),
                'data_sent' => $data,
            ]);

            // Return detailed error message
            $responseData = $response->json();
            $message = $responseData['message'] ?? 'Gagal membuat pengguna';
            
            // If there are validation errors, include them in the message
            if (isset($responseData['errors'])) {
                $errors = [];
                foreach ($responseData['errors'] as $field => $messages) {
                    $errors[] = implode(', ', (array)$messages);
                }
                $message .= ': ' . implode('; ', $errors);
            }

            return ['success' => false, 'message' => $message];
        } catch (\Exception $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal membuat pengguna: ' . $e->getMessage()];
        }
    }

    /**
     * Update user profile.
     */
    public function updateUser(string $id, array $data): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->put($this->apiUrl("/users/{$id}"), $data);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil diperbarui', 'data' => $response->json()];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal memperbarui pengguna'];
        } catch (\Exception $e) {
            Log::error('Failed to update user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal memperbarui pengguna'];
        }
    }

    /**
     * Activate a user.
     */
    public function activateUser(string $id): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->apiUrl("/users/{$id}/activate"));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil diaktifkan'];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal mengaktifkan pengguna'];
        } catch (\Exception $e) {
            Log::error('Failed to activate user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal mengaktifkan pengguna'];
        }
    }

    /**
     * Deactivate a user.
     */
    public function deactivateUser(string $id): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->apiUrl("/users/{$id}/deactivate"));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil dinonaktifkan'];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal menonaktifkan pengguna'];
        } catch (\Exception $e) {
            Log::error('Failed to deactivate user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menonaktifkan pengguna'];
        }
    }

    /**
     * Promote user to admin.
     */
    public function promoteUser(string $id): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->apiUrl("/users/{$id}/promote"));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil dijadikan admin'];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal mempromosikan pengguna'];
        } catch (\Exception $e) {
            Log::error('Failed to promote user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal mempromosikan pengguna'];
        }
    }

    /**
     * Demote admin to user.
     */
    public function demoteUser(string $id): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->post($this->apiUrl("/users/{$id}/demote"));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Admin berhasil dijadikan pengguna biasa'];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal menurunkan admin'];
        } catch (\Exception $e) {
            Log::error('Failed to demote user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menurunkan admin'];
        }
    }

    /**
     * Delete a user.
     */
    public function deleteUser(string $id): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->delete($this->apiUrl("/users/{$id}"));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Pengguna berhasil dihapus'];
            }

            return ['success' => false, 'message' => $response->json()['message'] ?? 'Gagal menghapus pengguna'];
        } catch (\Exception $e) {
            Log::error('Failed to delete user', ['id' => $id, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menghapus pengguna'];
        }
    }
}
