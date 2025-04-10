<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'communityId' => 'required'
        ];
    }

    /**
     * Get the validation error messages for the form request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'communityId.required' => __('Community is required.'),
        ];
    }
}
