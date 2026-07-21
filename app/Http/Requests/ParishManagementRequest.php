<?php

namespace App\Http\Requests;

use App\Models\ParishNew;
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => ParishNew::normalizeName($this->input('name')),
        ]);
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'name' => 'required|min:2|max:255|unique:parishes,name,' . $id,
            'code' => 'required|max:20|unique:parishes,code,' . $id,
            'parish_priest_name' => 'nullable|string|max:255',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'Tên giáo xứ',
            'code' => 'Mã giáo xứ',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên giáo xứ là bắt buộc',
            'name.unique'   => 'Tên giáo xứ đã tồn tại',
            'name.min'      => 'Tên giáo xứ phải có ít nhất 2 ký tự',
            'code.required' => 'Mã giáo xứ là bắt buộc',
            'code.unique'   => 'Mã giáo xứ đã tồn tại',
            'code.max'      => 'Mã giáo xứ không được quá 20 ký tự',
        ];
    }
}
