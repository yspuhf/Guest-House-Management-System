<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtensionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_departure_date' => 'required|date',
            'reason' => 'required|string|min:5|max:1000',
        ];
    }
}

