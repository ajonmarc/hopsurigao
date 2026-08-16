<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePickupScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pickupScheduleId = $this->route('pickup_schedule')?->id;

        return [
            'tour_date_id' => ['required', 'exists:tour_dates,id'],
            'pickup_location_id' => [
                'required',
                'exists:pickup_locations,id',
                Rule::unique('pickup_schedules')
                    ->where(fn ($query) => $query->where('tour_date_id', $this->input('tour_date_id')))
                    ->ignore($pickupScheduleId),
            ],
            'pickup_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_location_id.unique' => 'This pickup location already has a schedule for the selected tour date.',
        ];
    }
}