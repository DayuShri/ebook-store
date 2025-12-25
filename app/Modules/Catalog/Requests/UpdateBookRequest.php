<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isbn' => 'nullable|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'synopsis' => 'nullable|string',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'cover_image_url' => 'nullable|url',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'publication_date' => 'nullable|date',
            'page_count' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:50',
            'file_format' => 'nullable|string|max:50',
            'file_size_mb' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',

            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:book_categories,id',
        ];
    }
}
