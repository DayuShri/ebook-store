<?php

namespace App\Modules\Review_Reading\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartReadingRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'device_info' => 'nullable|string|max:255'
        ];
    }
}
