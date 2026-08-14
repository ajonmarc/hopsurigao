<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTourDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'exists:packages,id'],
            'tour_date' => [
                'required',
                'date',
                'after_or_equal:today',
                Rule::unique('tour_dates')->where(function ($query) {
                    return $query->where('package_id', $this->package_id);
                }),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'The selected package is invalid.',
            'tour_date.required' => 'Please select a tour date.',
            'tour_date.date' => 'Please enter a valid date.',
            'tour_date.after_or_equal' => 'Tour date must be today or a future date.',
            'tour_date.unique' => 'This date is already set for the selected package.',
            'capacity.required' => 'Please enter the capacity.',
            'capacity.integer' => 'Capacity must be a whole number.',
            'capacity.min' => 'Capacity must be at least 1.',
            'capacity.max' => 'Capacity cannot exceed 999.',
        ];
    }
}