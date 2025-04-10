<?php

namespace App\Http\Requests\Medias;

use Illuminate\Foundation\Http\FormRequest;

class MediaDeleteRequest extends FormRequest
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
            'mediaId' => 'required',
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
            'mediaId.required' => __('Media ID is required.'),
        ];
    }
}
