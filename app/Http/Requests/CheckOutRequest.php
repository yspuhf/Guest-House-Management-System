<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_condition' => 'required|string|max:500',
            'additional_charges' => 'nullable|array',
            'additional_charges.*.description' => 'required_with:additional_charges|string|max:150',
            'additional_charges.*.amount' => 'required_with:additional_charges|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}

