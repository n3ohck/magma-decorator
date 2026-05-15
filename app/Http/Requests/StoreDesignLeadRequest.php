<?php

namespace App\Http\Requests;
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'environment_id' => ['required', 'exists:environments,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
            'snapshot' => ['nullable', 'array'],
            'final_image' => ['nullable', 'string'],
        ];
    }
}
