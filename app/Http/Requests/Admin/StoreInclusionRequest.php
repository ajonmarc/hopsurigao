<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreInclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'exists:packages,id'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Please select a package.',
            'package_id.exists' => 'The selected package is invalid.',
            'description.required' => 'Please enter the inclusion description.',
            'description.max' => 'Description must not exceed 1000 characters.',
        ];
    }
}