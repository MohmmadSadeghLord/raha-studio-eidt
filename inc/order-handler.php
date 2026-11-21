<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   AJAX: ثبت سفارش
   ============================================================ */
add_action('wp_ajax_nopriv_raha_submit_order', 'raha_handle_submit_order');
add_action('wp_ajax_raha_submit_order',        'raha_handle_submit_order');

function raha_handle_submit_order() {
    if (!check_ajax_referer('raha_order_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    $f = function ($key, $type = 'text') {
        $val = $_POST[$key] ?? '';
        if ($type === 'email')  return sanitize_email($val);
        if ($type === 'url')    return esc_url_raw($val);
        if ($type === 'area')   return sanitize_textarea_field($val);
        return sanitize_text_field($val);
    };

    $data = [
        'full_name'            => $f('fullName'),
        'company_name'         => $f('companyName'),
        'contact_method'       => $f('contactMethod'),
        'contact_value'        => $f('contactValue'),
        'alt_contact'          => $f('altContact'),
        'best_time'            => $f('bestTime'),
        'service'              => $f('service'),
        'current_site_url'     => $f('currentSiteUrl', 'url'),
        'purpose'              => $f('purpose'),
        'business_name'        => $f('businessName'),
        'business_description' => $f('businessDescription', 'area'),
        'features_unsure'      => !empty($_POST['featuresUnsure']) ? 1 : 0,
        'features_other'       => $f('featuresOther', 'area'),
        'design_has_samples'   => !empty($_POST['designHasSamples']) ? 1 : 0,
        'has_logo_brand'       => !empty($_POST['hasLogoBrand']) ? 1 : 0,
        'design_suggest'       => !empty($_POST['designSuggestByYou']) ? 1 : 0,
        'design_notes'         => $f('designNotes', 'area'),
        'content_status'       => $f('contentStatus'),
        'domain_hosting'       => $f('domainHosting'),
        'timeline'             => $f('timeline'),
        'timeline_date'        => $f('timelineDate'),
        'budget'               => $f('budget'),
        'extra_notes'          => $f('extraNotes', 'area'),
        'order_status'         => 'new',
        'amount'               => 0,
        'amount_paid'          => 0,
    ];

    /* features (آرایه) */
    $features = array_map('sanitize_text_field', (array) ($_POST['features'] ?? []));
    $data['features'] = $features;

    /* design samples (آرایه) */
    $samples = array_map('esc_url_raw', (array) ($_POST['designSamples'] ?? []));
    $samples = array_filter($samples);
    if ($samples) $data['design_samples'] = $samples;

    /* اعتبارسنجی */
    if (!$data['full_name'])            wp_send_json_error(['message' => 'نام و نام خانوادگی را وارد کنید.']);
    if (!$data['service'])              wp_send_json_error(['message' => 'نوع خدمت را انتخاب کنید.']);
    if (!$data['purpose'])              wp_send_json_error(['message' => 'هدف سایت را انتخاب کنید.']);
    if (!$data['business_description']) wp_send_json_error(['message' => 'توضیحات کسب‌وکار را وارد کنید.']);
    if (!$data['contact_method'] || !$data['contact_value']) {
        wp_send_json_error(['message' => 'راه تماس را کامل کنید.']);
    }

    /* کد پیگیری */
    $code = 'REQ-' . date('ymd') . '-' . strtoupper(wp_generate_password(4, false, false));

    /* نویسنده: اگر لاگین است */
    $author = is_user_logged_in() ? get_current_user_id() : 0;

    $post_id = wp_insert_post([
        'post_type'   => 'raha_order',
        'post_status' => 'publish',
        'post_title'  => $code . ' — ' . $data['full_name'],
        'post_author' => $author,
        'meta_input'  => array_merge($data, ['tracking_code' => $code]),
    ], true);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'خطا در ذخیره‌سازی. لطفاً دوباره تلاش کنید.']);
    }

    /* آپلود فایل‌ها */
    raha_save_order_files($post_id);

    /* ایمیل */
    raha_send_order_emails($post_id, $data, $code);

    $thankyou = raha_page_url('thank-you');
    wp_send_json_success([
        'tracking' => $code,
        'redirect' => add_query_arg('tracking', $code, $thankyou),
    ]);
}

/* ---------- آپلود فایل ---------- */
function raha_save_order_files($order_id) {
    if (empty($_FILES)) return;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $map = ['logoFile' => 'logo_file', 'extraFile' => 'extra_file'];
    foreach ($map as $input => $meta_key) {
        if (empty($_FILES[$input]['name'])) continue;
        $upload = wp_handle_upload($_FILES[$input], ['test_form' => false]);
        if (isset($upload['error'])) continue;

        $attach_id = wp_insert_attachment([
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name(basename($upload['file'])),
            'post_status'    => 'inherit',
        ], $upload['file'], $order_id);

        if (!is_wp_error($attach_id)) {
            wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));
            update_post_meta($order_id, $meta_key, $attach_id);
        }
    }
}

/* ---------- ایمیل ---------- */
function raha_send_order_emails($order_id, $data, $code) {
    $admin_email = get_option('admin_email');
    $site_name   = get_bloginfo('name');
    $edit_link   = admin_url('post.php?post=' . $order_id . '&action=edit');

    $admin_body  = "سفارش جدید ثبت شد.\n\n";
    $admin_body .= "کد پیگیری: $code\n";
    $admin_body .= "نام: {$data['full_name']}\n";
    if ($data['company_name']) $admin_body .= "شرکت: {$data['company_name']}\n";
    $admin_body .= "تماس: {$data['contact_value']} ({$data['contact_method']})\n";
    $admin_body .= "نوع خدمت: {$data['service']}\n";
    $admin_body .= "بودجه: {$data['budget']}\n\n";
    $admin_body .= "مشاهده سفارش: $edit_link\n";

    wp_mail($admin_email, "[{$site_name}] سفارش جدید — {$code}", $admin_body);

    /* اگر تماس ایمیل است، به مشتری هم ایمیل بزن */
    if ($data['contact_method'] === 'email' && is_email($data['contact_value'])) {
        $cust_body  = "سلام {$data['full_name']},\n\n";
        $cust_body .= "درخواست شما با کد پیگیری {$code} دریافت شد.\n";
        $cust_body .= "به‌زودی برای هماهنگی با شما تماس می‌گیریم.\n\n";
        $cust_body .= "پیگیری وضعیت: " . add_query_arg('tracking', $code, raha_page_url('thank-you')) . "\n";
        wp_mail($data['contact_value'], "دریافت درخواست شما — {$site_name}", $cust_body);
    }
}