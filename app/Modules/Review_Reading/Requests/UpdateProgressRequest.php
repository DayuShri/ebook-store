<?php

namespace App\Modules\Review_Reading\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'current_page' => 'required|integer|min:0',
        ];
    }
}
