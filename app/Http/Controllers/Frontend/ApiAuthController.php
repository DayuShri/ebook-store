<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ApiAuthController extends Controller
{
    /**
     * Login and create web session
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Find user
            $user = User::where('email', $request->email)->first();
            
            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password tidak valid',
                ], 401);
            }

            // Login user (without remember token)
            Auth::login($user, false);
            $request->session()->regenerate();
            
            // Update last login manually
            $user->timestamps = false;
            $user->last_login_at = now();
            $user->save();
            $user->timestamps = true;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => route('library.index'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
