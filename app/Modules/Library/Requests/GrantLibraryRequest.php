<?php

namespace App\Modules\Library\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GrantLibraryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'order_id' => 'nullable|uuid',
            // when called via HMVC, module may pass user_id explicitly
            'user_id' => 'nullable|uuid',
        ];
    }
}
