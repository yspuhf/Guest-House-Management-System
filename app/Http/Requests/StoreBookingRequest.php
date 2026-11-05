<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_house_id' => 'required|exists:guest_houses,id',
            'room_type_id' => 'required|exists:room_types,id',
            'guest_type_id' => 'required|exists:guest_types,id',
            'expenditure_head_id' => 'nullable|exists:expenditure_heads,id',
            'purpose' => 'required|string|min:5|max:1000',
            'arrival_date' => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date',
            'rooms_requested' => 'required|integer|min:1|max:20',
            'total_guests' => 'required|integer|min:1|max:50',
            'meal_preference' => 'required|in:None,Breakfast,Breakfast & Dinner,All Meals (Breakfast, Lunch, Dinner)',
            'special_requests' => 'nullable|string|max:500',
            'accepted_terms' => 'accepted',

            // Official Application Form Fields (Image & Notification 1615-40)
            'visit_type' => 'nullable|in:Official,Private',
            'institution_address' => 'nullable|string|max:500',
            'home_address' => 'nullable|string|max:500',
            'aadhar_number' => 'nullable|string|max:20',
            'sharing_preference' => 'nullable|in:Independent Suite,Can Be Shared',

            // Primary Guest
            'primary_guest_name' => 'required|string|min:3|max:150',
            'primary_guest_designation' => 'nullable|string|max:100',
            'primary_guest_phone' => 'required|string|min:7|max:20',
            'primary_guest_email' => 'nullable|email|max:150',
            'primary_guest_id_type' => 'nullable|string|max:50',
            'primary_guest_id_number' => 'nullable|string|max:100',

            // Accompanying Guests
            'accompanying_guests' => 'nullable|array',
            'accompanying_guests.*.name' => 'required_with:accompanying_guests|string|max:150',
            'accompanying_guests.*.relationship' => 'nullable|string|max:50',

            // Document attachment
            'approval_document' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120', // max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'departure_date.after' => 'Departure date must be after arrival date.',
            'arrival_date.after_or_equal' => 'Arrival date cannot be in the past.',
            'accepted_terms.accepted' => 'You must accept the guest house rules and booking terms.',
            'primary_guest_name.required' => 'Primary guest name is mandatory.',
            'primary_guest_name.min' => 'Primary guest name must be at least 3 characters.',
            'primary_guest_phone.required' => 'Primary guest contact number is required.',
            'primary_guest_phone.min' => 'Contact phone number must be at least 7 digits.',
        ];
    }
}

