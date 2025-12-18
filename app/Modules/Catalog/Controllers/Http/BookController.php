<?php

namespace App\Modules\Catalog\Controllers\Http;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\BookService;
use App\Modules\Catalog\Requests\StoreBookRequest;
use App\Modules\Catalog\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function __construct(
        protected BookService $service
    ) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->listActive()
        ]);
    }

    public function store(StoreBookRequest $request)
    {
        $book = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Book created',
            'data' => $book
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->detail($id)
        ]);
    }

    public function update(UpdateBookRequest $request, $id)
    {
        $book = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Book updated',
            'data' => $book
        ]);
    }

    public function destroy($id)
    {
        $this->service->deactivate($id);

        return response()->json([
            'success' => true,
            'message' => 'Book deactivated'
        ]);
    }
}
