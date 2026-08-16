<?php

namespace App\Http\Requests\Tourist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'pickup_schedule_id' => ['required', 'exists:pickup_schedules,id'],
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
            'pickup_schedule_id.required' => 'Please select a pickup schedule.',
            'pickup_schedule_id.exists' => 'The selected pickup schedule is invalid.',
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

    /**
     * NEW: make sure the chosen pickup schedule actually belongs to the
     * tour date being booked — otherwise a tampered request could pair
     * a valid schedule ID with an unrelated tour date.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tourDateId = $this->input('tour_date_id');
            $pickupScheduleId = $this->input('pickup_schedule_id');

            if (!$tourDateId || !$pickupScheduleId) {
                return;
            }

            $belongs = \App\Models\PickupSchedule::where('id', $pickupScheduleId)
                ->where('tour_date_id', $tourDateId)
                ->exists();

            if (!$belongs) {
                $validator->errors()->add(
                    'pickup_schedule_id',
                    'The selected pickup schedule does not belong to this tour date.'
                );
            }
        });
    }
}