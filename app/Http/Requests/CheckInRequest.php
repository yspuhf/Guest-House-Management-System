<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identity_verified' => 'required|accepted',
            'room_condition' => 'nullable|string|max:500',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}

