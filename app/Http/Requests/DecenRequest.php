<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Rule;

class DecenRequest extends FormRequest
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
        return [
            // 'name' => 'required|min:5|max:255'
            //'use' => 'unique',
            //'pid'   => 'unique',
            //'use' => 'unique:decen,use',
            //'pid' => 'unique:decen,pid',
            /*
            'use' => [
                'required',
                \Illuminate\Validation\Rule::unique('decen')->where(function ($query) {
                    $query->where('use', $this->use)
                    ->where('pid', $this->pid);
                })
            ],
            'pid' => [
                'required',
                \Illuminate\Validation\Rule::unique('decen')->where(function ($query) {
                    $query->where('use', $this->use)
                    ->where('pid', $this->pid);
                })
            ]*/
        ];
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
