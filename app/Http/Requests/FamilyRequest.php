<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FamilyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'parish_id' => 'required|integer|exists:parishes,id',
            'parish_group_id' => 'nullable|integer|exists:parish_groups,id',
            'head_id' => 'nullable|integer|exists:parishioners_new,id',
            'address' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'ward_id' => 'nullable|integer',
            'phone' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:2000',
            'status' => 'nullable|boolean',
            'is_included_in_stats' => 'nullable|boolean',
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
