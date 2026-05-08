<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeLevelRequest extends FormRequest
{
    public function authorize() { return backpack_auth()->check(); }

    public function rules()
    {
        return [
            'name'       => 'required|min:2|max:255',
            'code'       => 'nullable|max:50|unique:grade_levels,code,' . $this->route('id'),
            'sort_order' => 'nullable|integer|min:0',
            'is_active'  => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên khối không được để trống.',
            'code.unique'   => 'Mã khối đã tồn tại.',
        ];
    }
}