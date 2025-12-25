<?php

namespace App\Modules\Voucher\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Voucher\Models\Voucher;
use App\Modules\Voucher\Services\VoucherCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index()
    {
        $data = Voucher::query()->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'message' => 'Vouchers fetched', 'data' => $data]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['min_purchase_amount'] = $data['min_purchase_amount'] ?? 0;
        $data['used_count'] = 0;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_at'] = now();

        $voucher = Voucher::create($data);

        return response()->json(['success' => true, 'message' => 'Voucher created', 'data' => $voucher], 201);
    }

    public function show(string $id)
    {
        $voucher = Voucher::findOrFail($id);
        return response()->json(['success' => true, 'message' => 'Voucher fetched', 'data' => $voucher]);
    }

    public function update(Request $request, string $id)
    {
        $voucher = Voucher::findOrFail($id);

        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'min_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (isset($data['valid_from']) && isset($data['valid_until']) && $data['valid_until'] < $data['valid_from']) {
            throw ValidationException::withMessages(['valid_until' => ['valid_until must be after valid_from']]);
        }

        $voucher->update($data);

        return response()->json(['success' => true, 'message' => 'Voucher updated', 'data' => $voucher->fresh()]);
    }

    public function destroy(string $id)
    {
        Voucher::query()->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Voucher deleted']);
    }

    public function validateVoucher(Request $request, VoucherCalculator $calc)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $res = $calc->validateAndCalculate($data['code'], (float)$data['subtotal']);

        if (!$res['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher',
                'errors' => ['code' => [$res['reason']]],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher valid',
            'data' => [
                'voucher_id' => $res['voucher']->id,
                'discount_amount' => $res['discount_amount'],
            ],
        ]);
    }
}
