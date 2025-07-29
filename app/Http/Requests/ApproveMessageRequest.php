<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveMessageRequest extends FormRequest
{
    
    public function rules()
    {
        return [
            'ids' => 'array|required',
        ];
    }

    public function defaults()
    {
        return [
            'ids' => $this->ids ?? [],
        ];
    }
}
