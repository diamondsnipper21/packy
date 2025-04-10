<?php

namespace App\Http\Requests\Medias;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
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
            'type' => 'required',
            'path' => 'required',
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
            'type.required' => __('Media type is required.'),
            'path.required' => __('Media path is required.')
        ];
    }
}
