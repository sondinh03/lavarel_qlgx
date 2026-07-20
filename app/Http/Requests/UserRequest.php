<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
    public function rules()
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $this->route('id'),
            'parish_id' => 'required|exists:parishes,id',
            'roles'         => 'nullable|string',
            'assigned_role' => 'required|string|exists:roles,name',
            'is_active'     => 'nullable|boolean',
        ];

        // Tạo mới → password bắt buộc, update → không bắt buộc
        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        return $rules;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
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
