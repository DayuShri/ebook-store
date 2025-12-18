<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Voucher;
use App\Services\VoucherCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    public function index()
    {
        return ApiResponse::success(
            'Voucher list retrieved',
            Voucher::orderByDesc('created_at')->get()
        );
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
        $data['min_purchase_amount'] ??= 0;
        $data['is_active'] ??= true;
        $data['created_at'] = now();

        $voucher = Voucher::create($data);

        return ApiResponse::success(
            'Voucher created successfully',
            $voucher,
            201
        );
    }

    public function show(string $id)
    {
        return ApiResponse::success(
            'Voucher detail',
            Voucher::findOrFail($id)
        );
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

        if (
            isset($data['valid_from'], $data['valid_until']) &&
            $data['valid_until'] < $data['valid_from']
        ) {
            throw ValidationException::withMessages([
                'valid_until' => ['valid_until must be after valid_from'],
            ]);
        }

        $voucher->update($data);

        return ApiResponse::success(
            'Voucher updated',
            $voucher
        );
    }

    public function destroy(string $id)
    {
        Voucher::where('id', $id)->delete();

        return ApiResponse::success(
            'Voucher deleted'
        );
    }

    public function validateVoucher(Request $request, VoucherCalculator $calc)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $calc->validateAndCalculate(
            $data['code'],
            (float) $data['subtotal']
        );

        if (!$result['valid']) {
            return ApiResponse::error(
                'Voucher is invalid',
                ['reason' => $result['reason']],
                422
            );
        }

        return ApiResponse::success(
            'Voucher valid',
            [
                'voucher_id' => $result['voucher']->id,
                'discount_amount' => $result['discount_amount'],
            ]
        );
    }
}
