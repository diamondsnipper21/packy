<?php

namespace App\Http\Requests\Medias;

use Illuminate\Foundation\Http\FormRequest;

class MediaOrderUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'community' => 'required',
            'from' => 'required',
            'to' => 'required'
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
            'community.required' => __('Community is required.'),
            'from.required' => __('Parameter "from" is required.'),
            'to.required' => __('Parameter "to" is required.'),
        ];
    }
}
