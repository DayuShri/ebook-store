<?php

namespace App\Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateViewerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'format' => 'nullable|string',
        ];
    }
}
