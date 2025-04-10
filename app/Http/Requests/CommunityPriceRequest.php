<?php

namespace App\Http\Requests;

use App\Models\Community;
use Illuminate\Foundation\Http\FormRequest;

class CommunityPriceRequest extends FormRequest
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
            'priceId' => 'integer|nullable',
            'amountMonthly' => ['integer', 'nullable', function ($attribute, $value, $fail) {
                if ($value <= 0 || $value > 999) {
                    $fail(__('The monthly price must be between 0 and 999 €.'));
                }
            }],
            'amountYearly' => ['integer', 'nullable', function ($attribute, $value, $fail) {
                if ($value <= 0 || $value > 4999) {
                    $fail(__('The yearly price must be between 0 and 4999 €.'));
                }
            }],
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
            'communityId.required' => __('Community ID is required.')
        ];
    }
}
