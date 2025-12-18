<?php

namespace App\Modules\Review_Reading\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string'
        ];
    }
}
