<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // already gated by role:Admin middleware
    }

    public function rules(): array
    {
        return [
            'package_name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}