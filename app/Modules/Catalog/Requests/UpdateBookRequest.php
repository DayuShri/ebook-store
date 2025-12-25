<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:500',
            'price' => 'sometimes|numeric|min:0',
            'isbn' => 'sometimes|nullable|string|max:20',
            'discount_percentage' => 'sometimes|numeric|min:0|max:100',
            'is_active' => 'sometimes|boolean',
            'author' => 'sometimes|nullable|string|max:500',
            'publisher' => 'sometimes|nullable|string|max:255',

            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'uuid|exists:book_categories,id',

            'author_ids' => 'sometimes|array',
            'author_ids.*' => 'string', 
            'author_ids.*' => 'uuid',
        ];
    }
}

