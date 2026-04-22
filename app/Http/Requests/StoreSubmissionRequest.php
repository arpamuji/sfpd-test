<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    private const MIN_FILES = 3;

    private const MAX_FILES = 10;

    private const MAX_FILE_SIZE_KB = 5120; // 5MB

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_name' => ['required', 'string', 'max:255'],
            'warehouse_address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'budget_estimate' => ['required', 'numeric', 'min:0', 'max:999999999999999'], // Max 999 trillion (15 digits)
            'description' => ['nullable', 'string', 'max:1000'],
            'files' => ['required', 'array', 'min:'.self::MIN_FILES, 'max:'.self::MAX_FILES],
            'files.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.self::MAX_FILE_SIZE_KB],
        ];
    }

    public function messages(): array
    {
        return [
            'files.min' => 'At least '.self::MIN_FILES.' documents are required.',
            'files.max' => 'Maximum '.self::MAX_FILES.' files allowed.',
            'files.*.mimes' => 'Files must be PDF, JPG, JPEG, or PNG only.',
            'files.*.max' => 'Each file must not exceed 5MB.',
        ];
    }
}
