<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AllocateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_ids' => 'required|array|min:1',
            'room_ids.*' => 'required|exists:rooms,id',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'room_ids.required' => 'Please select at least one physical room to allocate.',
        ];
    }
}

