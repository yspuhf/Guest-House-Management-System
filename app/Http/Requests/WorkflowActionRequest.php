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
        ];
    }

    public function messages(): array
    {
        return [
            'remarks.required_if' => 'Mandatory remarks are strictly required when rejecting or returning a booking for correction.',
        ];
    }
}

