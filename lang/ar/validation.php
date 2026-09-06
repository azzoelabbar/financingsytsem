<?php

declare(strict_types=1);

return [
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'يجب أن يكون حقل :attribute نصاً.',
    'email' => 'يجب أن يكون حقل :attribute بريداً إلكترونياً صالحاً.',
    'confirmed' => 'تأكيد حقل :attribute غير متطابق.',
    'unique' => 'قيمة :attribute مستخدمة من قبل.',
    'max' => [
        'string' => 'يجب ألا يزيد حقل :attribute على :max حرفاً.',
        'numeric' => 'يجب ألا تزيد قيمة :attribute على :max.',
    ],
    'min' => [
        'string' => 'يجب ألا يقل حقل :attribute عن :min أحرف.',
        'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.',
    ],
    'size' => [
        'string' => 'يجب أن يتكون حقل :attribute من :size أحرف.',
        'numeric' => 'يجب أن تساوي قيمة :attribute :size.',
    ],
    'numeric' => 'يجب أن يكون حقل :attribute رقماً.',
    'integer' => 'يجب أن يكون حقل :attribute عدداً صحيحاً.',
    'date' => 'يجب أن يكون حقل :attribute تاريخاً صالحاً.',
    'after_or_equal' => 'يجب أن يكون حقل :attribute تاريخاً بعد :date أو مساوياً له.',
    'before_or_equal' => 'يجب أن يكون حقل :attribute تاريخاً قبل :date أو مساوياً له.',
    'in' => 'القيمة المحددة في حقل :attribute غير صالحة.',
    'gt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من أو تساوي :value.',
    ],
    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'code' => 'الرمز',
        'name_ar' => 'الاسم العربي',
        'legal_name' => 'الاسم القانوني',
        'currency' => 'العملة',
    ],
];
