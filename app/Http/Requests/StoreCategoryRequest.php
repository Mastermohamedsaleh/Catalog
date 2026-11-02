<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
return [
        'name'        => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:1000'],
        'image'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // أقصى حجم 2 ميجا بصيغ آمنة
        'is_active'   => ['nullable', 'boolean'],
    ];
    }
}