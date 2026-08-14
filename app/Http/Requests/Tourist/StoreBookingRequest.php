<?php

namespace App\Http\Requests\Tourist;

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
            'tour_date_id' => ['required', 'exists:tour_dates,id'],
            'pickup_location_id' => ['required', 'exists:pickup_locations,id'],
            'number_of_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'phone_number' => ['required', 'string', 'max:20'],
            'nationality' => ['required', 'string', 'max:100'],
            'special_request' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tour_date_id.required' => 'Please select a tour date.',
            'tour_date_id.exists' => 'The selected tour date is invalid.',
            'pickup_location_id.required' => 'Please select a pickup location.',
            'pickup_location_id.exists' => 'The selected pickup location is invalid.',
            'number_of_guests.required' => 'Please enter the number of guests.',
            'number_of_guests.integer' => 'Number of guests must be a whole number.',
            'number_of_guests.min' => 'Number of guests must be at least 1.',
            'number_of_guests.max' => 'Number of guests cannot exceed 20.',
            'phone_number.required' => 'Please enter a phone number.',
            'phone_number.max' => 'Phone number must not exceed 20 characters.',
            'nationality.required' => 'Please enter your nationality.',
            'nationality.max' => 'Nationality must not exceed 100 characters.',
            'special_request.max' => 'Special request must not exceed 1000 characters.',
        ];
    }
}