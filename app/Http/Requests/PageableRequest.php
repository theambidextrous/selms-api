<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PageableRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => 'integer|min:1',
            'size' => 'integer|min:1|max:1000',
        ];
    }

    public function defaults()
    {
        return [
            'page' => $this->page ?? 1,
            'size' => $this->size ?? 10,
        ];
    }
}