<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tour_date_id' => ['required', 'exists:tour_dates,id'],
            'time' => [
                'required',
                'date_format:H:i',
                Rule::unique('times')->where(function ($query) {
                    return $query->where('tour_date_id', $this->tour_date_id);
                }),
            ],
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tour_date_id.required' => 'Please select a tour date.',
            'tour_date_id.exists' => 'The selected tour date is invalid.',
            'time.required' => 'Please select a time.',
            'time.date_format' => 'Please enter a valid time in HH:MM format.',
            'time.unique' => 'This time slot is already set for the selected tour date.',
            'description.required' => 'Please enter a description.',
            'description.max' => 'Description must not exceed 255 characters.',
        ];
    }
}