<?php

namespace App\Modules\Review_Reading\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinishReadingRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'book_id' => 'required|uuid',
            'last_page_read' => 'required|integer|min:0'
        ];
    }
}
