<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\UserFrontendService;
use App\Services\Frontend\CartService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    protected UserFrontendService $userService;
    protected CartService $cartService;

    public function __construct(UserFrontendService $userService, CartService $cartService)
    {
        $this->userService = $userService;
        $this->cartService = $cartService;
    }

    /**
     * Display user profile
     */
    public function index()
    {
        $profile = $this->userService->getProfile();

        return view('frontend.profile.index', [
            'profile' => $profile,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Display edit profile form
     */
    public function edit()
    {
        $profile = $this->userService->getProfile();

        return view('frontend.profile.edit', [
            'profile' => $profile,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
        ]);

        $result = $this->userService->updateProfile([
            'full_name' => $request->input('full_name'),
            'phone_number' => $request->input('phone_number'),
            'date_of_birth' => $request->input('date_of_birth'),
        ]);

        if ($result['success']) {
            return redirect()->route('profile.index')->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message'] ?? 'Gagal memperbarui profil');
    }

    /**
     * Display wishlist
     */
    public function wishlist()
    {
        $wishlistItems = $this->userService->getWishlist();

        return view('frontend.profile.wishlist', [
            'wishlistItems' => $wishlistItems,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Add to wishlist
     */
    public function addToWishlist(string $bookId)
    {
        $result = $this->userService->addToWishlist($bookId);

        if (request()->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Remove from wishlist
     */
    public function removeFromWishlist(string $bookId)
    {
        $result = $this->userService->removeFromWishlist($bookId);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('wishlist')->with('success', $result['message']);
    }
}
