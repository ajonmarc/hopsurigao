<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePickupLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the pickup location name.',
            'name.max' => 'Location name must not exceed 255 characters.',
            'address.max' => 'Address must not exceed 500 characters.',
            'description.max' => 'Description must not exceed 1000 characters.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected.',
        ];
    }
}