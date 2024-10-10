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

    'accepted' => 'يجب قبول الـ :attribute.',
    'active_url' => 'الـ :attribute غير رابط صحيح.',
    'after' => 'يجب أن يكون الـ :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون الـ :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي الـ :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي الـ :attribute على أحرف، أرقام، شرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي الـ :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون الـ :attribute مصفوفة.',
    'before' => 'يجب أن يكون الـ :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون الـ :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن يكون الـ :attribute بين :min و :max.',
        'file' => 'يجب أن يكون الـ :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون الـ :attribute بين :min و :max حرفًا.',
        'array' => 'يجب أن يحتوي الـ :attribute بين :min و :max عنصر.',
    ],
    'boolean' => 'يجب أن يكون حقل الـ :attribute صحيحًا أو خطأ.',
    'confirmed' => 'تأكيد الـ :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'الـ :attribute ليس تاريخًا صحيحًا.',
    'date_equals' => 'يجب أن يكون الـ :attribute تاريخًا مساويًا لـ :date.',
    'date_format' => 'الـ :attribute لا يتطابق مع الشكل :format.',
    'declined' => 'يجب أن يتم رفض الـ :attribute.',
    'declined_if' => 'يجب أن يتم رفض الـ :attribute عندما يكون الـ :other هو :value.',
    'different' => 'يجب أن يكون الـ :attribute والـ :other مختلفين.',
    'digits' => 'يجب أن يحتوي الـ :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي الـ :attribute على عدد من الأرقام بين :min و :max.',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صحيحة.',
    'distinct' => 'حقل الـ :attribute يحتوي على قيمة مكررة.',
    'email' => 'يجب أن يكون الـ :attribute عنوان بريد إلكتروني صحيح.',
    'ends_with' => 'يجب أن ينتهي الـ :attribute بأحد القيم التالية: :values.',
    'enum' => 'الـ :attribute المحدد غير صحيح.',
    'exists' => 'الـ :attribute المحدد غير صحيح.',
    'file' => 'يجب أن يكون الـ :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي حقل الـ :attribute على قيمة.',
    'gt' => [
        'numeric' => 'يجب أن يكون الـ :attribute أكبر من :value.',
        'file' => 'يجب أن يكون حجم الـ :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يحتوي الـ :attribute على أكثر من :value حرف.',
        'array' => 'يجب أن يحتوي الـ :attribute على أكثر من :value عنصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون الـ :attribute أكبر من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم الـ :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يحتوي الـ :attribute على أكثر من أو يساوي :value حرف.',
        'array' => 'يجب أن يحتوي الـ :attribute على :value عنصر أو أكثر.',
    ],
    'image' => 'يجب أن يكون الـ :attribute صورة.',
    'in' => 'الـ :attribute المحدد غير صحيح.',
    'in_array' => 'حقل الـ :attribute غير موجود في الـ :other.',
    'integer' => 'يجب أن يكون الـ :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون الـ :attribute عنوان IP صحيح.',
    'ipv4' => 'يجب أن يكون الـ :attribute عنوان IPv4 صحيح.',
    'ipv6' => 'يجب أن يكون الـ :attribute عنوان IPv6 صحيح.',
    'json' => 'يجب أن يكون الـ :attribute نص JSON صحيح.',
    'lt' => [
        'numeric' => 'يجب أن يكون الـ :attribute أقل من :value.',
        'file' => 'يجب أن يكون حجم الـ :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يحتوي الـ :attribute على أقل من :value حرف.',
        'array' => 'يجب أن يحتوي الـ :attribute على أقل من :value عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن يكون الـ :attribute أقل من أو يساوي :value.',
        'file' => 'يجب أن يكون حجم الـ :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يحتوي الـ :attribute على أقل من أو يساوي :value حرف.',
        'array' => 'يجب أن لا يحتوي الـ :attribute على أكثر من :value عنصر.',
    ],
    'mac_address' => 'يجب أن يكون الـ :attribute عنوان MAC صحيح.',
    'max' => [
        'numeric' => 'يجب أن لا يكون الـ :attribute أكبر من :max.',
        'file' => 'يجب أن لا يكون حجم الـ :attribute أكبر من :max كيلوبايت.',
        'string' => 'يجب أن لا يكون الـ :attribute أكبر من :max حرفًا.',
        'array' => 'يجب أن لا يحتوي الـ :attribute على أكثر من :max عنصر.',
    ],
    'mimes' => 'يجب أن يكون الـ :attribute ملفًا من النوع: :values.',
    'mimetypes' => 'يجب أن يكون الـ :attribute ملفًا من النوع: :values.',
    'min' => [
        'numeric' => 'يجب أن يكون الـ :attribute على الأقل :min.',
        'file' => 'يجب أن يكون حجم الـ :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون الـ :attribute على الأقل :min حرفًا.',
        'array' => 'يجب أن يحتوي الـ :attribute على الأقل :min عنصر.',
    ],
    'multiple_of' => 'يجب أن يكون الـ :attribute مضاعفًا للـ :value.',
    'not_in' => 'الـ :attribute المحدد غير صحيح.',
    'not_regex' => 'صيغة الـ :attribute غير صحيحة.',
    'numeric' => 'يجب أن يكون الـ :attribute رقمًا.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون حقل الـ :attribute موجودًا.',
    'prohibited' => 'يحظر استخدام حقل الـ :attribute.',
    'prohibits' => 'حقل الـ :attribute يمنع وجود الـ :other.',
    'regex' => 'صيغة الـ :attribute غير صالحة.',
    'required' => 'حقل الـ :attribute مطلوب.',
    'required_array_keys' => 'حقل الـ :attribute يجب أن يحتوي على مفاتيح للقيم التالية: :values.',
    'required_if' => 'حقل الـ :attribute مطلوب عندما يكون الـ :other هو :value.',
    'required_unless' => 'حقل الـ :attribute مطلوب ما لم يكن الـ :other ضمن :values.',
    'required_with' => 'حقل الـ :attribute مطلوب عندما يكون :values موجودًا.',
    'required_with_all' => 'حقل الـ :attribute مطلوب عندما تكون :values موجودةً.',
    'required_without' => 'حقل الـ :attribute مطلوب عندما لا يكون :values موجودًا.',
    'required_without_all' => 'حقل الـ :attribute مطلوب عندما لا تكون أي من الـ :values موجودةً.',
    'same' => 'يجب أن يتطابق الـ :attribute والـ :other.',
    'size' => [
        'numeric' => 'يجب أن يكون الـ :attribute :size.',
        'file' => 'يجب أن يكون حجم الـ :attribute :size كيلوبايت.',
        'string' => 'يجب أن يكون الـ :attribute :size حرفًا.',
        'array' => 'يجب أن يحتوي الـ :attribute على :size عنصرًا.',
    ],
    'starts_with' => 'يجب أن يبدأ الـ :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون الـ :attribute نصًا.',
    'timezone' => 'يجب أن يكون الـ :attribute منطقة زمنية صحيحة.',
    'unique' => 'تم أخذ الـ :attribute مسبقًا.',
    'uploaded' => 'فشل تحميل الـ :attribute.',
    'url' => 'يجب أن يكون الـ :attribute رابطًا صحيحًا.',
    'uuid' => 'يجب أن يكون الـ :attribute معرف UUID صحيح.',

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
