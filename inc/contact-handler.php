<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_raha_submit_consultation', 'raha_handle_consultation');
add_action('wp_ajax_nopriv_raha_submit_consultation', 'raha_handle_consultation');

function raha_handle_consultation() {
    // ۱. Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'raha_consultation_nonce')) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    // ۲. Sanitize inputs
    $name    = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $phone   = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $service = sanitize_text_field(wp_unslash($_POST['service'] ?? ''));
    $method  = sanitize_text_field(wp_unslash($_POST['method'] ?? ''));
    $desc    = sanitize_textarea_field(wp_unslash($_POST['desc'] ?? ''));

    // ۳. Validation
    if (strlen($name) < 3) {
        wp_send_json(['ok' => false, 'message' => 'نام باید حداقل ۳ کاراکتر باشد.']);
    }
    if (!preg_match('/^09\d{9}$/', $phone)) {
        wp_send_json(['ok' => false, 'message' => 'شماره موبایل معتبر نیست.']);
    }
    if ($email && !is_email($email)) {
        wp_send_json(['ok' => false, 'message' => 'ایمیل معتبر نیست.']);
    }

    // ۴. ذخیره در Custom Post Type (raha_consultation)
    $post_id = wp_insert_post([
        'post_type'   => 'raha_consultation', // ← تغییر کرد
        'post_status' => 'publish',
        'post_title'  => 'مشاوره: ' . $name . ' — ' . $phone,
        'meta_input'  => [
            'lead_name'    => $name,
            'lead_contact' => $phone,
            'lead_email'   => $email,
            'lead_service' => $service,
            'lead_method'  => $method,
            'lead_desc'    => $desc,
            'lead_source'  => 'Consultation Form',
            'lead_status'  => 'new', // ← وضعیت جدید
        ],
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json(['ok' => false, 'message' => 'خطا در ذخیره‌سازی.']);
    }

    // ۵. ارسال ایمیل به ادمین (با سیستم Raha_Email)
    if (class_exists('Raha_Email')) {
        $service_labels = [
            'design'   => 'طراحی سایت',
            'seo'      => 'سئو',
            'redesign' => 'بازطراحی',
            'consult'  => 'مشاوره / استراتژی',
            'other'    => 'سایر',
        ];
        $method_labels = ['phone' => 'تلفنی', 'telegram' => 'تلگرام'];

        $email_data = [
            'name'    => $name,
            'contact' => $phone,
            'source'  => 'فرم مشاوره — ' . ($service_labels[$service] ?? $service),
            'desc'    => $desc,
        ];

        Raha_Email::send('cta_admin', get_option('admin_email'), $email_data);
    }

    wp_send_json([
        'ok' => true,
        'message' => 'درخواست شما با موفقیت ثبت شد.'
    ]);
}