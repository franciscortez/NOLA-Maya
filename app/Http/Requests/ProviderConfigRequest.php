<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_id' => 'required|exists:location_tokens,location_id',
            'maya_test_public_key' => 'nullable|string',
            'maya_test_secret_key' => 'nullable|string',
            'maya_live_public_key' => 'nullable|string',
            'maya_live_secret_key' => 'nullable|string',
        ];
    }
}
