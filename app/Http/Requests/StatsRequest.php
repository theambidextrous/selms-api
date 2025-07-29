<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'monthsAgo' => 'integer|min:1|max:12',
        ];
    }

    public function defaults()
    {
        return [
            'monthsAgo' => $this->monthsAgo ?? 6,
        ];
    }
}
