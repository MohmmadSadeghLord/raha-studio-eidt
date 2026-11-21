<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_nopriv_raha_submit_lead', 'raha_handle_cta_form');
add_action('wp_ajax_raha_submit_lead',        'raha_handle_cta_form');

function raha_handle_cta_form() {
    if (!check_ajax_referer('raha_cta_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'نشست منقضی شده است. صفحه را رفرش کنید.']);
    }

    $contact = sanitize_text_field($_POST['contact'] ?? '');

    if (empty($contact)) {
        wp_send_json_error(['message' => 'لطفاً راه ارتباطی خود را وارد کنید.']);
    }

    // ذخیره در دیتابیس (پست تایپ raha_lead)
    $post_id = wp_insert_post([
        'post_type'   => 'raha_lead',
        'post_status' => 'publish',
        'post_title'  => 'تماس جدید — ' . $contact,
        'meta_input'  => [
            'lead_contact' => $contact,
            'lead_source'  => 'CTA Final Form',
            'lead_status'  => 'unread',
        ],
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'خطا در ذخیره‌سازی. لطفاً دوباره تلاش کنید.']);
    }

    // ارسال ایمیل به ادمین
    $admin_email = get_option('admin_email');
    $subject = 'درخواست تماس جدید از صفحه اصلی';
    $message = "راه ارتباطی جدید: $contact\n\nاین پیام از فرم CTA پایانی ارسال شده است.";
    wp_mail($admin_email, $subject, $message);

    wp_send_json_success(['message' => 'درخواست شما با موفقیت ثبت شد.']);
}