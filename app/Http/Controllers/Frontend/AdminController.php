<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\AdminService;
use App\Services\Frontend\CartService;
use App\Services\Frontend\VoucherFrontendService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected AdminService $adminService;
    protected CartService $cartService;
    protected VoucherFrontendService $voucherService;

    public function __construct(
        AdminService $adminService,
        CartService $cartService,
        VoucherFrontendService $voucherService
    ) {
        $this->adminService = $adminService;
        $this->cartService = $cartService;
        $this->voucherService = $voucherService;
    }

    /**
     * Check if current user is admin.
     */
    protected function checkAdmin(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Anda bukan admin.');
        }
    }

    /**
     * Display user list.
     */
    public function users(Request $request)
    {
        $this->checkAdmin();
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        $result = $this->adminService->getUsers($perPage, $page);
        $stats = $this->adminService->getUserStatistics();

        return view('frontend.admin.users.index', [
            'users' => $result['data'] ?? [],
            'meta' => $result['meta'] ?? [],
            'stats' => $stats,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Show create user form.
     */
    public function createUserForm()
    {
        $this->checkAdmin();
        return view('frontend.admin.users.create', [
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Store new user.
     */
    public function storeUser(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'full_name' => 'required|string|max:255',
            'role' => 'required|in:user,admin',
        ]);

        $result = $this->adminService->createUser([
            'email' => $request->email,
            'password' => $request->password,
            'full_name' => $request->full_name,
            'role' => $request->role,
        ]);

        if ($result['success']) {
            return redirect()->route('admin.users')->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Show user detail.
     */
    public function showUser(string $id)
    {
        $this->checkAdmin();
        $user = $this->adminService->getUser($id);

        if (!$user) {
            return redirect()->route('admin.users')->with('error', 'Pengguna tidak ditemukan');
        }

        return view('frontend.admin.users.show', [
            'user' => $user,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Show edit user form.
     */
    public function editUserForm(string $id)
    {
        $this->checkAdmin();
        $user = $this->adminService->getUser($id);

        if (!$user) {
            return redirect()->route('admin.users')->with('error', 'Pengguna tidak ditemukan');
        }

        return view('frontend.admin.users.edit', [
            'user' => $user,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    /**
     * Update user.
     */
    public function updateUser(Request $request, string $id)
    {
        $this->checkAdmin();
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $result = $this->adminService->updateUser($id, [
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
        ]);

        if ($result['success']) {
            return redirect()->route('admin.users.show', $id)->with('success', $result['message']);
        }

        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Activate user.
     */
    public function activateUser(string $id)
    {
        $this->checkAdmin();
        $result = $this->adminService->activateUser($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Deactivate user.
     */
    public function deactivateUser(string $id)
    {
        $this->checkAdmin();
        $result = $this->adminService->deactivateUser($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Promote user to admin.
     */
    public function promoteUser(string $id)
    {
        $this->checkAdmin();
        $result = $this->adminService->promoteUser($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Demote admin to user.
     */
    public function demoteUser(string $id)
    {
        $this->checkAdmin();
        $result = $this->adminService->demoteUser($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Delete user.
     */
    public function deleteUser(string $id)
    {
        $this->checkAdmin();
        $result = $this->adminService->deleteUser($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return redirect()->route('admin.users')->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // ==========================================
    // VOUCHER MANAGEMENT
    // ==========================================

    public function vouchers(Request $request)
    {
        $this->checkAdmin();
        $vouchers = $this->voucherService->getVouchers();

        return view('frontend.admin.vouchers.index', [
            'vouchers' => $vouchers,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    public function createVoucherForm()
    {
        $this->checkAdmin();
        return view('frontend.admin.vouchers.create', [
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    public function storeVoucher(Request $request)
    {
        $this->checkAdmin();
        $data = $request->validate([
            'code' => 'required|string|unique:vouchers,code|max:50',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = $request->has('is_active'); // Handle checkbox

        $result = $this->voucherService->createVoucher($data);

        if ($result['success']) {
            return redirect()->route('admin.vouchers')->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function editVoucherForm($id)
    {
        $this->checkAdmin();
        $voucher = $this->voucherService->getVoucher($id);

        if (!$voucher) {
            return back()->with('error', 'Voucher not found');
        }

        return view('frontend.admin.vouchers.edit', [
            'voucher' => $voucher,
            'cartCount' => $this->cartService->getItemCount(),
        ]);
    }

    public function updateVoucher(Request $request, $id)
    {
        $this->checkAdmin();
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:valid_from',
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = $request->has('is_active');

        $result = $this->voucherService->updateVoucher($id, $data);

        if ($result['success']) {
            return redirect()->route('admin.vouchers')->with('success', $result['message']);
        }

        return back()->withInput()->with('error', $result['message']);
    }

    public function deleteVoucher($id)
    {
        $this->checkAdmin();
        $result = $this->voucherService->deleteVoucher($id);

        if (request()->ajax()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
