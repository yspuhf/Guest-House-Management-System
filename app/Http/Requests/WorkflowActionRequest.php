<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:verify,approve,reject,return',
            'remarks' => 'required_if:action,reject,return|nullable|string|min:3|max:1000',
            'room_id' => 'nullable|integer|exists:rooms,id',
            'room_ids' => 'nullable|array',
            'room_ids.*' => 'integer|exists:rooms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'remarks.required_if' => 'Mandatory remarks are strictly required when rejecting or returning a booking for correction.',
            'room_id.exists' => 'The selected room does not exist in the guest house inventory.',
        ];
    }
}
