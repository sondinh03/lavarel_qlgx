<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParishManagementRequest extends FormRequest
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
        //return [
            // 'name' => 'required|min:5|max:255'
        //];
        return [
            // 'name' => 'required|min:5|max:255'
            //'name' => 'required|min:5|max:255',
            /*'ward' => 'required',
            'province' => 'required',*/
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
        /*
        return [
            'name' => __('backend.name'),
            'ward' => __('backend.ward'),
            'province' => __('backend.province'),
        ];*/
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
