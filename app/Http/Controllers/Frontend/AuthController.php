<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('frontend.auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            // Log before login
            \Log::info('Login attempt for user: ' . $user->email);
            
            // Manually login the user
            Auth::login($user, $remember);
            
            // Log after login
            \Log::info('User authenticated: ' . Auth::check());
            \Log::info('User ID: ' . Auth::id());
            
            // Regenerate session to prevent fixation
            $request->session()->regenerate();

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Redirect to library after successful login
            \Log::info('Redirecting to library.index');
            return redirect()->route('library.index')->with('success', 'Selamat datang kembali!');
        }

        \Log::warning('Login failed for: ' . $credentials['email']);
        
        return back()->withErrors([
            'email' => 'Email atau password tidak valid.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('frontend.auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user
        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'email' => $request->input('email'),
            'password_hash' => Hash::make($request->input('password')),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Create profile
        $user->profile()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'full_name' => $request->input('name'),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Pendaftaran berhasil! Selamat berbelanja.');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Revoke all Sanctum tokens for this user
        if (auth()->check()) {
            auth()->user()->tokens()->delete();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar.');
    }
}
