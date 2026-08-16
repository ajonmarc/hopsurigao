<?php

namespace App\Http\Requests\Tourist;

use App\Models\PickupSchedule;
use App\Models\TourDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Booking is already route-model-bound; ownership + editable
        // status are checked in the controller so we can return a
        // friendly flash message instead of a bare 403.
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_schedule_id' => ['required', 'exists:pickup_schedules,id'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'phone_number' => ['required', 'string', 'max:30'],
            'nationality' => ['required', 'string', 'max:100'],
            'special_request' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Cross-field checks:
     * 1. New guest count still fits in the tour date's remaining capacity
     *    (excluding this booking's own current guest count from the tally).
     * 2. The chosen pickup schedule actually belongs to this booking's
     *    tour date — tourists can't change tour_date_id here, so a
     *    schedule from a different tour date would be invalid.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var \App\Models\Booking $booking */
            $booking = $this->route('booking');

            if (!$booking) {
                return;
            }

            $tourDate = $booking->tourDate ?? TourDate::find($booking->tour_date_id);

            if (!$tourDate) {
                return;
            }

            $confirmedGuestsExcludingThis = $tourDate->bookings()
                ->where('booking_status', 'confirmed')
                ->where('id', '!=', $booking->id)
                ->sum('number_of_guests');

            $availableSpots = $tourDate->capacity - $confirmedGuestsExcludingThis;
            $requestedGuests = (int) $this->input('number_of_guests');

            if ($requestedGuests > $availableSpots) {
                $validator->errors()->add(
                    'number_of_guests',
                    "Only {$availableSpots} spot(s) available for this tour date."
                );
            }

            // NEW: pickup schedule must belong to this booking's tour date
            $pickupScheduleId = $this->input('pickup_schedule_id');
            if ($pickupScheduleId) {
                $belongs = PickupSchedule::where('id', $pickupScheduleId)
                    ->where('tour_date_id', $booking->tour_date_id)
                    ->exists();

                if (!$belongs) {
                    $validator->errors()->add(
                        'pickup_schedule_id',
                        'The selected pickup schedule does not belong to this booking\'s tour date.'
                    );
                }
            }
        });
    }
}