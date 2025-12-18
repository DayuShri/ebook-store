<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'title' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',

            'isbn' => 'nullable|string|max:20|unique:books,isbn',
            'subtitle' => 'nullable|string|max:500',
            'synopsis' => 'nullable|string',
            'author' => 'nullable|string|max:500',
            'publisher' => 'nullable|string|max:255',
            'cover_image_url' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'publication_date' => 'nullable|date',
            'page_count' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:10',
            'file_format' => 'nullable|string|max:20',
            'file_size_mb' => 'nullable|numeric|min:0',

            'category_ids' => 'nullable|array',
            'category_ids.*' => 'uuid|exists:book_categories,id',
        ];
    }
}

