<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:Cash,Online (Payment Gateway),POS / Card,UPI / QR,Demand Draft / Cheque,Book Transfer / University Account',
            'transaction_reference' => 'nullable|string|max:100',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ];
    }
}

