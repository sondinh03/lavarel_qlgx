<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'active_url' => 'Trường :attribute không phải URL hợp lệ.',
    'after' => 'Trường :attribute phải là ngày sau :date.',
    'after_or_equal' => 'Trường :attribute phải là ngay sau hoặc bằng :date.',
    'alpha' => 'Trường :attribute chỉ có thể chứa chữ cái.',
    'alpha_dash' => 'Trường :attribute chỉ có thể chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới.',
    'alpha_num' => 'Trường :attribute chỉ có thể chứa chữ cái và số.',
    'array' => 'Trường :attribute phải là 1 mảng.',
    'before' => 'Trường :attribute phải là ngày trước :date.',
    'before_or_equal' => 'Trường :attribute phải là ngày trước hoặc bằng :date.',
    'between' => [
        'numeric' => 'Trường :attribute phải có từ :min tới :max.',
        'file' => 'Trường :attribute phải có từ :min tới :max kilobytes.',
        'string' => 'Trường :attribute phải có từ :min tới :max ký tự.',
        'array' => 'Trường :attribute phải có từ :min tới :max đối tượng.',
    ],
    'boolean' => 'Trường :attribute phải đúng hoặc sai.',
    'confirmed' => 'Trường :attribute xác nhận không phù hợp.',
    'date' => 'Trường :attribute không phải là ngày hợp lệ.',
    'date_equals' => 'Trường :attribute phải là một ngày bằng :date.',
    'date_format' => 'Trường :attribute không phù hợp với định dạng :format.',
    'different' => 'Trường :attribute và :other phải khác nhau.',
    'digits' => 'Trường :attribute phải có :digits chữ số.',
    'digits_between' => 'Trường :attribute phải có từ :min tới :max chữ số.',
    'dimensions' => 'Trường :attribute có kích thước hình ảnh không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị trùng lặp.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'exists' => 'Trường :attribute đã chọn không hợp lệ.',
    'file' => 'Trường :attribute phải là một tập tin.',
    'filled' => 'Trường :attribute phải có giá trị.',
    'gt' => [
        'numeric' => 'Trường :attribute phải lớn hơn :value.',
        'file' => 'Trường :attribute phải lớn hơn :value kilobytes.',
        'string' => 'Trường :attribute phải lớn hơn :value ký tự.',
        'array' => 'Trường :attribute phải có nhiều hơn :value đối tượng.',
    ],
    'gte' => [
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :value.',
        'file' => 'Trường :attribute phải lớn hơn hoặc bằng :value kilobytes.',
        'string' => 'Trường :attribute phải lớn hơn hoặc bằng :value ký tự.',
        'array' => 'Trường :attribute phải có :value đối tượng trở lên.',
    ],
    'image' => 'Trường :attribute phải là một hình ảnh.',
    'in' => 'Trường :attribute đã chọn không hợp lệ.',
    'in_array' => 'Trường :attribute không tồn tại trong :other.',
    'integer' => 'Trường :attribute phải là số nguyên.',
    'ip' => 'Trường :attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => 'Trường :attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => 'Trường :attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => 'Trường :attribute phải là một chuỗi JSON hợp lệ.',
    'lt' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn :value.',
        'file' => 'Trường :attribute phải nhỏ hơn :value kilobytes.',
        'string' => 'Trường :attribute phải nhỏ hơn :value ký tự.',
        'array' => 'Trường :attribute phải có ít hơn :value đối tượng.',
    ],
    'lte' => [
        'numeric' => 'Trường :attribute phải nhỏ hơn hoặc bằng :value.',
        'file' => 'Trường :attribute phải nhỏ hơn hoặc bằng :value kilobytes.',
        'string' => 'Trường :attribute phải nhỏ hơn hoặc bằng :value ký tự.',
        'array' => 'Trường :attribute không được có nhiều hơn :value đối tượng.',
    ],
    'max' => [
        'numeric' => 'Trường :attribute không lớn hơn :max.',
        'file' => 'Trường :attribute không lớn hơn :max kilobytes.',
        'string' => 'Trường :attribute không lớn hơn :max ký tự.',
        'array' => 'Trường :attribute không có nhiều hơn :max đối tượng.',
    ],
    'mimes' => 'Trường :attribute phải là một tập tin loại: :values.',
    'mimetypes' => 'Trường :attribute phải là một tập tin loại: :values.',
    'min' => [
        'numeric' => 'Trường :attribute ít nhất phải :min.',
        'file' => 'Trường :attribute ít nhất phải :min kilobytes.',
        'string' => 'Trường :attribute ít nhất phải :min ký tự.',
        'array' => 'Trường :attribute phải có ít nhất :min đối tượng.',
    ],
    'not_in' => 'The selected :attribute không hợp lệ.',
    'not_regex' => 'Trường :attribute định dạng không hợp lệ.',
    'numeric' => 'Trường :attribute phải là 1 số.',
    'present' => 'Trường :attribute không thể thiếu.',
    'regex' => 'Trường :attribute định dạng không hợp lệ.',
    'required' => 'Trường :attribute cần phải có.',
    'required_if' => 'Trường :attribute được yêu cầu khi :other là :value.',
    'required_unless' => 'Trường :attribute cần phải có trừ khi :other nằm trong :values.',
    'required_with' => 'Trường :attribute được yêu cầu khi :values xuất hiện.',
    'required_with_all' => 'Trường :attribute được yêu cầu khi :values xuất hiện.',
    'required_without' => 'Trường :attribute được yêu cầu khi :values không xuất hiện.',
    'required_without_all' => 'Trường :attribute được yêu cầu khi không có :values nào xuất hiện.',
    'same' => 'Trường :attribute phải giống với với :other.',
    'size' => [
        'numeric' => 'Trường :attribute cần có :size.',
        'file' => 'Trường :attribute cần có :size kilobytes.',
        'string' => 'Trường :attribute cần có :size ký tự.',
        'array' => 'Trường :attribute phải chứa :size đối tượng.',
    ],
    'starts_with' => 'Trường :attribute phải bắt đầu với một trong những điều sau đây: :values',
    'string' => 'Trường :attribute cần là 1 chuỗi.',
    'timezone' => 'Trường :attribute cần là chuỗi hợp lệ (TimeZone).',
    'unique' => 'Trường :attribute đã tồn tại.',
    'uploaded' => 'Trường :attribute không tải lên được.',
    'url' => 'Trường :attribute định dạng không hợp lệ.',
    'uuid' => 'Trường :attribute cần là 1 UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
