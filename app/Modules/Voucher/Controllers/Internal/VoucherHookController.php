<?php

namespace App\Modules\Voucher\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Modules\Voucher\Models\Voucher;
use App\Modules\Voucher\Services\VoucherCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal HMVC controller for inter-module voucher operations.
 * Called by other modules (e.g., Order) via HTTP.
 */
class VoucherHookController extends Controller
{
    protected VoucherCalculator $calculator;

    public function __construct(VoucherCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Validate a voucher code and calculate discount.
     * Called by Order module during checkout.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->calculator->validateAndCalculate(
            $request->code,
            (float) $request->subtotal
        );

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['reason'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher valid',
            'data' => [
                'voucher_id' => $result['voucher']->id,
                'discount_amount' => $result['discount_amount'],
            ],
        ]);
    }

    /**
     * Mark a voucher as used (increment used_count).
     * Called by Order module after successful checkout.
     */
    public function markAsUsed(Request $request): JsonResponse
    {
        $request->validate([
            'voucher_id' => ['required', 'uuid'],
        ]);

        $voucher = Voucher::find($request->voucher_id);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found',
            ], 404);
        }

        $voucher->increment('used_count');

        return response()->json([
            'success' => true,
            'message' => 'Voucher usage recorded',
            'data' => [
                'voucher_id' => $voucher->id,
                'used_count' => $voucher->used_count,
            ],
        ]);
    }
}
