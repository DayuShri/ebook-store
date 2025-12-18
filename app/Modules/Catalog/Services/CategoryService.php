<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\BookCategory;

class CategoryService
{
    public function list()
    {
        return BookCategory::with('children')->get();
    }

    public function create(array $data)
    {
        return BookCategory::create($data);
    }

    public function update(string $id, array $data)
    {
        $cat = BookCategory::findOrFail($id);
        $cat->update($data);
        return $cat;
    }

    public function delete(string $id)
    {
        $cat = BookCategory::findOrFail($id);
        $cat->delete();
        return true;
    }

    public function detail(string $id)
{
    return BookCategory::findOrFail($id);
}

}
