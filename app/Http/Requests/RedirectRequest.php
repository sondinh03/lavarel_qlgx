<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedirectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[ArrayShape(['old_url' => 'string', 'new_url' => 'string', 'type' => 'string'])]
    public function rules()
    {
        return [
            'old_url' => 'required|min:1|max:255',
            'new_url' => 'required|min:1|max:255',
            'type' => 'required|digits:3',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    #[ArrayShape(['old_url' => 'string', 'new_url' => 'string', 'type' => 'string'])]
    public function attributes()
    {
        return [
            'old_url' => 'Đường dẫn cũ',
            'new_url' => 'Đường dẫn mới',
            'type' => 'Kiểu',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
