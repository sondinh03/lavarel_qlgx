<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NamHocRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        return [
            'name'           => 'required|string|max:255',
            'parish_id' => 'required|exists:parishes,id',
            'status'         => 'required|boolean',
            'start_date_one' => 'required|date',
            'end_date_one'   => 'required|date|after:start_date_one',
            'start_date_two' => 'required|date|after:end_date_one',
            'end_date_two'   => 'required|date|after:start_date_two',
        ];
    }

    public function attributes()
    {
        return [
            'name'           => 'Tên năm học',
            'parish_id' => 'Giáo xứ',
            'status'         => 'Trạng thái',
            'start_date_one' => 'Ngày bắt đầu HK1',
            'end_date_one'   => 'Ngày kết thúc HK1',
            'start_date_two' => 'Ngày bắt đầu HK2',
            'end_date_two'   => 'Ngày kết thúc HK2',
        ];
    }

    public function messages()
    {
        return [
            'end_date_one.after'   => 'Ngày kết thúc HK1 phải sau ngày bắt đầu HK1.',
            'start_date_two.after' => 'Ngày bắt đầu HK2 phải sau ngày kết thúc HK1.',
            'end_date_two.after'   => 'Ngày kết thúc HK2 phải sau ngày bắt đầu HK2.',
        ];
    }
}