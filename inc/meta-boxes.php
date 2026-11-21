<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* ==================== خدمات ==================== */
    raha_meta_box([
        'id'         => 'raha_service_box',
        'title'      => 'جزئیات خدمت',
        'post_types' => ['raha_service'],
        'fields'     => [
            ['id' => 'icon',       'label' => 'آیکن (کد SVG)', 'type' => 'textarea', 'desc' => 'کد SVG را پیست کنید'],
            ['id' => 'short_desc', 'label' => 'توضیح کوتاه',   'type' => 'textarea'],
            ['id' => 'link',       'label' => 'لینک (اختیاری)', 'type' => 'url'],
        ],
    ]);

    /* ==================== نمونه‌کار ==================== */
    raha_meta_box([
        'id'         => 'raha_portfolio_box',
        'title'      => 'جزئیات نمونه‌کار',
        'post_types' => ['raha_portfolio'],
        'fields'     => [
            ['id' => 'tag',       'label' => 'دسته',       'type' => 'text'],
            ['id' => 'year',      'label' => 'سال',        'type' => 'text'],
            ['id' => 'desc',      'label' => 'توضیح کوتاه', 'type' => 'textarea'],
            ['id' => 'demo_url',  'label' => 'لینک دمو',   'type' => 'url'],
            ['id' => 'cover',     'label' => 'تصویر کاور', 'type' => 'image'],
            ['id' => 'featured',  'label' => 'نمایش در صفحه اصلی', 'type' => 'checkbox'],
            ['id' => 'gallery',   'label' => 'گالری تصاویر', 'type' => 'repeater',
                'add_label' => '+ افزودن تصویر',
                'fields' => [
                    ['id' => 'image',   'label' => 'تصویر', 'type' => 'image'],
                    ['id' => 'caption', 'label' => 'کپشن',  'type' => 'text'],
                ],
            ],
        ],
    ]);

    /* ==================== پکیج قیمت ==================== */
    raha_meta_box([
        'id'         => 'raha_pricing_box',
        'title'      => 'جزئیات پکیج',
        'post_types' => ['raha_pricing'],
        'fields'     => [
            ['id' => 'desc',         'label' => 'توضیح',        'type' => 'textarea'],
            ['id' => 'price_prefix', 'label' => 'پیشوند قیمت',  'type' => 'text', 'desc' => 'مثلاً «شروع از»'],
            ['id' => 'price_value',  'label' => 'مبلغ',         'type' => 'text'],
            ['id' => 'price_unit',   'label' => 'واحد',         'type' => 'text', 'desc' => 'مثلاً «میلیون تومان»'],
            ['id' => 'popular',      'label' => 'پیشنهاد ویژه', 'type' => 'checkbox'],
            ['id' => 'btn_text',     'label' => 'متن دکمه',    'type' => 'text'],
            ['id' => 'btn_link',     'label' => 'لینک دکمه',   'type' => 'url'],
            ['id' => 'features',     'label' => 'ویژگی‌ها',    'type' => 'repeater',
                'add_label' => '+ افزودن ویژگی',
                'fields' => [
                    ['id' => 'label',    'label' => 'عنوان',      'type' => 'text'],
                    ['id' => 'included', 'label' => 'شامل می‌شود', 'type' => 'checkbox'],
                ],
            ],
        ],
    ]);

    /* ==================== سوال متداول ==================== */
    raha_meta_box([
        'id'         => 'raha_faq_box',
        'title'      => 'جزئیات سوال',
        'post_types' => ['raha_faq'],
        'fields'     => [
            ['id' => 'answer',   'label' => 'پاسخ', 'type' => 'textarea'],
            ['id' => 'category', 'label' => 'دسته (اختیاری)', 'type' => 'text', 'desc' => 'مثلاً pricing'],
        ],
    ]);

    /* ==================== قالب ==================== */
    raha_meta_box([
        'id'         => 'raha_template_box',
        'title'      => 'جزئیات قالب',
        'post_types' => ['raha_template'],
        'fields'     => [
            // بخش هیرو
            ['id' => 'tag_line',      'label' => 'دسته‌بندی (برچسب بالای عنوان)',   'type' => 'text'],
            ['id' => 'short_desc',    'label' => 'توضیح کوتاه (زیر عنوان)', 'type' => 'textarea'],
            
            // نوار اطلاعات (Info Strip)
            ['id' => 'delivery_time', 'label' => 'زمان پیاده‌سازی (مثلاً ۲ تا ۴ هفته)', 'type' => 'text'],
            ['id' => 'mobile_status', 'label' => 'وضعیت موبایل (مثلاً کاملاً ریسپانسیو)', 'type' => 'text'],
            ['id' => 'speed_score',   'label' => 'امتیاز سرعت (مثلاً ۹۵+ در PageSpeed)',  'type' => 'text'],
            ['id' => 'tech',          'label' => 'تکنولوژی (مثلاً وردپرس / Next.js)',    'type' => 'text'],

            // لیست‌ها و مشخصات
            ['id' => 'features', 'label' => 'امکانات کلیدی', 'type' => 'repeater',
                'fields' => [ ['id' => 'label', 'label' => 'امکان', 'type' => 'text'] ],
            ],
            ['id' => 'specs', 'label' => 'مشخصات فنی', 'type' => 'repeater',
                'fields' => [
                    ['id' => 'label', 'label' => 'عنوان', 'type' => 'text'],
                    ['id' => 'value', 'label' => 'مقدار', 'type' => 'text'],
                ],
            ],
            ['id' => 'gallery', 'label' => 'پیش‌نمایش صفحات (گالری)', 'type' => 'repeater',
                'fields' => [
                    ['id' => 'image', 'label' => 'تصویر', 'type' => 'image'],
                    ['id' => 'label', 'label' => 'برچسب (مثلاً صفحه اصلی)', 'type' => 'text'],
                ],
            ],
            ['id' => 'use_cases', 'label' => 'مناسب برای چه کسب‌وکارهایی؟', 'type' => 'repeater',
                'fields' => [ ['id' => 'label', 'label' => 'کاربرد', 'type' => 'text'] ],
            ],
            ['id' => 'faq', 'label' => 'سوالات متداول', 'type' => 'repeater',
                'fields' => [
                    ['id' => 'question', 'label' => 'سوال', 'type' => 'text'],
                    ['id' => 'answer', 'label' => 'پاسخ', 'type' => 'textarea'],
                ],
            ],

            // سایدبار (خرید)
            ['id' => 'price_prefix', 'label' => 'پیشوند قیمت (مثلاً شروع قیمت از)', 'type' => 'text'],
            ['id' => 'price',        'label' => 'قیمت (مثلاً ۲۵ میلیون تومان)', 'type' => 'text'],
            ['id' => 'purchase_features', 'label' => 'امکانات سایدبار (خرید)', 'type' => 'repeater',
                'fields' => [ ['id' => 'label', 'label' => 'امکان', 'type' => 'text'] ],
            ],
            ['id' => 'demo_url',     'label' => 'لینک دمو زنده', 'type' => 'url'],
            
            // CTA پایانی
            ['id' => 'cta_title',    'label' => 'CTA پایانی — عنوان', 'type' => 'text'],
            ['id' => 'cta_desc',     'label' => 'CTA پایانی — توضیح', 'type' => 'textarea'],
            ['id' => 'cta_btn_text', 'label' => 'CTA پایانی — متن دکمه', 'type' => 'text'],
            ['id' => 'cta_btn_url',  'label' => 'CTA پایانی — لینک دکمه', 'type' => 'url'],
        ],
    ]);
    /* ==================== پیام تماس / مشاوره ==================== */
    raha_meta_box([
        'id'         => 'raha_lead_details',
        'title'      => 'جزئیات پیام تماس / مشاوره',
        'post_types' => ['raha_lead'],
        'fields'     => [
            ['id' => 'lead_name',    'label' => 'نام و نام خانوادگی', 'type' => 'text'],
            ['id' => 'lead_contact', 'label' => 'راه ارتباطی (شماره/ایمیل)', 'type' => 'text'],
            ['id' => 'lead_email',   'label' => 'ایمیل', 'type' => 'text'],
            ['id' => 'lead_service', 'label' => 'موضوع درخواست', 'type' => 'text'],
            ['id' => 'lead_method',  'label' => 'روش ارتباط ترجیحی', 'type' => 'text'],
            ['id' => 'lead_desc',    'label' => 'توضیحات کامل کاربر', 'type' => 'textarea'],
            ['id' => 'lead_source',  'label' => 'منبع درخواست', 'type' => 'text'],
        ],
    ]);
    

}, 20);