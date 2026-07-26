<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HolymanagementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return backpack_auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('holymanagements', 'name')->ignore($this->id ?? $this->route('id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'name' => 'tên thánh',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên thánh.',
            'name.unique'   => 'Tên thánh này đã tồn tại.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => Str::title(trim((string) $this->input('name'))),
            ]);
        }
    }
}
