<?php

namespace App\Modules\Review_Reading\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewHelpfulRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'review_id' => 'required|uuid',
            'is_helpful' => 'required|boolean'
        ];
    }
}
