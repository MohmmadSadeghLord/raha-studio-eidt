<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* ============ سفارش ============ */
    raha_meta_box([
        'id'         => 'raha_order_details',
        'title'      => 'جزئیات سفارش',
        'post_types' => ['raha_order'],
        'fields'     => [
            ['id' => 'tracking_code', 'label' => 'کد پیگیری',    'type' => 'text'],
            ['id' => 'order_status',  'label' => 'وضعیت سفارش',   'type' => 'select', 'options' => [
                'new'      => 'جدید',
                'review'   => 'در حال بررسی',
                'progress' => 'در حال انجام',
                'done'     => 'تکمیل شده',
                'canceled' => 'لغو شده',
            ]],
            ['id' => 'amount',      'label' => 'مبلغ کل (تومان)',     'type' => 'number'],
            ['id' => 'amount_paid', 'label' => 'مبلغ پرداخت‌شده (تومان)', 'type' => 'number'],
            ['id' => 'admin_notes', 'label' => 'یادداشت داخلی',        'type' => 'textarea'],
        ],
    ]);

    /* ============ اطلاعات مشتری ============ */
    raha_meta_box([
        'id'         => 'raha_order_customer',
        'title'      => 'اطلاعات مشتری',
        'post_types' => ['raha_order'],
        'fields'     => [
            ['id' => 'full_name',     'label' => 'نام',              'type' => 'text'],
            ['id' => 'company_name',  'label' => 'شرکت',             'type' => 'text'],
            ['id' => 'contact_method','label' => 'روش تماس',         'type' => 'select', 'options' => [
                'phone' => 'تلفن', 'whatsapp' => 'واتساپ', 'telegram' => 'تلگرام', 'email' => 'ایمیل',
            ]],
            ['id' => 'contact_value', 'label' => 'اطلاعات تماس',    'type' => 'text'],
            ['id' => 'alt_contact',   'label' => 'تماس جایگزین',    'type' => 'text'],
            ['id' => 'best_time',     'label' => 'زمان مناسب تماس', 'type' => 'text'],
        ],
    ]);

    /* ============ اطلاعات پروژه ============ */
    raha_meta_box([
        'id'         => 'raha_order_project',
        'title'      => 'اطلاعات پروژه',
        'post_types' => ['raha_order'],
        'fields'     => [
            ['id' => 'service',              'label' => 'نوع خدمت',           'type' => 'text'],
            ['id' => 'current_site_url',     'label' => 'سایت فعلی',          'type' => 'url'],
            ['id' => 'purpose',              'label' => 'هدف سایت',           'type' => 'text'],
            ['id' => 'business_name',        'label' => 'نام کسب‌وکار',       'type' => 'text'],
            ['id' => 'business_description', 'label' => 'توضیحات کسب‌وکار',   'type' => 'textarea'],
            ['id' => 'features',             'label' => 'امکانات',             'type' => 'text', 'desc' => 'با کاما جدا شده'],
            ['id' => 'features_unsure',      'label' => 'امکانات نامشخص',     'type' => 'checkbox'],
            ['id' => 'features_other',       'label' => 'سایر امکانات',        'type' => 'textarea'],
            ['id' => 'content_status',       'label' => 'وضعیت محتوا',         'type' => 'text'],
            ['id' => 'domain_hosting',       'label' => 'دامنه/هاست',          'type' => 'text'],
            ['id' => 'timeline',             'label' => 'زمان تحویل',          'type' => 'text'],
            ['id' => 'timeline_date',        'label' => 'تاریخ مشخص',          'type' => 'date'],
            ['id' => 'budget',               'label' => 'بودجه',               'type' => 'text'],
            ['id' => 'extra_notes',          'label' => 'توضیحات تکمیلی',      'type' => 'textarea'],
        ],
    ]);

    /* ============ تیکت ============ */
    raha_meta_box([
        'id'         => 'raha_ticket_details',
        'title'      => 'جزئیات تیکت',
        'post_types' => ['raha_ticket'],
        'fields'     => [
            ['id' => 'ticket_status', 'label' => 'وضعیت',   'type' => 'select', 'options' => [
                'open'     => 'باز',
                'progress' => 'در حال بررسی',
                'waiting'  => 'در انتظار مشتری',
                'closed'   => 'بسته‌شده',
            ]],
            ['id' => 'priority',   'label' => 'اولویت',    'type' => 'select', 'options' => [
                'low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد',
            ]],
            ['id' => 'category',   'label' => 'دسته',      'type' => 'text'],
            ['id' => 'order_id',   'label' => 'شماره سفارش مرتبط (ID)', 'type' => 'number'],
        ],
    ]);

}, 20);