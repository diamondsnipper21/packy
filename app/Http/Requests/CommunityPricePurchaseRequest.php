<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunityPricePurchaseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'communityId' => 'required|integer',
            'period' => 'required|string',
            'client_ip' => 'nullable|string',
            'token' => 'nullable|string',
            'paymentMethodId' => 'nullable|integer',
            'country' => 'nullable|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'communityId.required' => __('Community ID is required.'),
            'period.required' => __('Period is required.')
        ];
    }
}
