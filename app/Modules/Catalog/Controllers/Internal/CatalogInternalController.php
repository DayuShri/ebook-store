<?php

namespace App\Modules\Catalog\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\BookService;
use Illuminate\Http\Request;

class CatalogInternalController extends Controller
{
    public function __construct(
        protected BookService $service
    ) {}

    public function price($id)
    {
        return response()->json($this->service->price($id));
    }

    public function exists($id)
    {
        return response()->json([
            'book_id' => $id,
            'exists' => $this->service->exists($id),
        ]);
    }

    public function basic($id)
    {
        return response()->json($this->service->basic($id));
    }

    public function bulkPrice(Request $request)
    {
        $data = $request->validate([
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'uuid',
        ]);

        return response()->json([
            'data' => $this->service->bulkPrice($data['book_ids'])
        ]);
    }
}
