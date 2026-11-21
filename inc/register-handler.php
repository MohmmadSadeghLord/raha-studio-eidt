<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_nopriv_raha_register', 'raha_handle_register');
add_action('wp_ajax_raha_register',        'raha_handle_register');

function raha_handle_register() {
    // ۱. Nonce
    if (!check_ajax_referer('raha_register', 'nonce', false)) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    // ۲. Honeypot
    if (!empty($_POST['website_url'])) {
        wp_send_json(['ok' => false, 'message' => 'خطای اعتبارسنجی.']);
    }

    // ۳. Rate limit — ۳ تلاش در ۱۵ دقیقه per IP
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_key = 'raha_reg_rate_' . md5($ip);
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 3) {
        wp_send_json(['ok' => false, 'message' => 'تعداد تلاش‌ها بیش از حد مجاز. لطفاً ۱۵ دقیقه دیگر دوباره امتحان کنید.']);
    }
    set_transient($rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);

    // ۴. Sanitize
    $name     = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
    $phone    = raha_normalize_phone(wp_unslash($_POST['phone'] ?? ''));
    $email    = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $password = wp_unslash($_POST['password'] ?? ''); // رمز رو sanitize نکن!
    $terms    = !empty($_POST['terms']);

    // ۵. اعتبارسنجی سمت سرور (اعتماد به کلاینت = حفره امنیتی)
    if (mb_strlen($name) < 3) {
        wp_send_json(['ok' => false, 'field' => 'name', 'message' => 'نام باید حداقل ۳ کاراکتر باشد.']);
    }
    if (!preg_match('/^09\d{9}$/', $phone)) {
        wp_send_json(['ok' => false, 'field' => 'phone', 'message' => 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.']);
    }
    if (!is_email($email)) {
        wp_send_json(['ok' => false, 'field' => 'email', 'message' => 'فرمت ایمیل معتبر نیست.']);
    }
    if (strlen($password) < 8) {
        wp_send_json(['ok' => false, 'field' => 'password', 'message' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.']);
    }
    if (strlen($password) > 72) { // محدودیت bcrypt
        wp_send_json(['ok' => false, 'field' => 'password', 'message' => 'رمز عبور طولانی‌تر از حد مجاز است.']);
    }
    if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        wp_send_json(['ok' => false, 'field' => 'password', 'message' => 'رمز باید شامل حرف و عدد باشد.']);
    }
    if (!$terms) {
        wp_send_json(['ok' => false, 'message' => 'پذیرش قوانین و حریم خصوصی الزامی است.']);
    }

    // ۶. یکتایی
    if (email_exists($email)) {
        wp_send_json(['ok' => false, 'field' => 'email', 'message' => 'این ایمیل قبلاً ثبت شده است.']);
    }

    $existing_by_phone = get_users([
        'meta_key'   => 'raha_phone',
        'meta_value' => $phone,
        'number'     => 1,
        'fields'     => 'ID',
    ]);
    if (!empty($existing_by_phone)) {
        wp_send_json(['ok' => false, 'field' => 'phone', 'message' => 'این شماره تماس قبلاً ثبت شده است.']);
    }

    // ۷. ساخت کاربر
    $base     = sanitize_user(current(explode('@', $email)), true) ?: 'user';
    $username = $base;
    $i        = 1;
    while (username_exists($username)) {
        $username = $base . $i++;
    }

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        wp_send_json(['ok' => false, 'message' => 'خطا در ایجاد حساب. لطفاً دوباره تلاش کنید.']);
    }

    // ۸. متادیتا
    $parts = preg_split('/\s+/', trim($name), 2);
    wp_update_user([
        'ID'           => $user_id,
        'display_name' => $name,
        'first_name'   => $parts[0] ?? '',
        'last_name'    => $parts[1] ?? '',
    ]);
    update_user_meta($user_id, 'raha_phone', $phone);
    update_user_meta($user_id, 'raha_terms_accepted', current_time('mysql'));

    // ۹. اطلاع‌رسانی (به کاربر و ادمین)
    wp_new_user_notification($user_id, null, 'both');

    // ۱۰. پاک کردن rate limit در صورت موفقیت (اختیاری — بگذارید بماند)
    // delete_transient($rate_key);

    wp_send_json([
        'ok'      => true,
        'message' => 'حساب کاربری با موفقیت ساخته شد.',
        'user_id' => $user_id,
    ]);
}

/**
 * تبدیل اعداد فارسی/عربی به انگلیسی و حذف کاراکترهای غیرعددی
 */
function raha_normalize_phone($phone) {
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $english = ['0','1','2','3','4','5','6','7','8','9'];

    $phone = str_replace($persian, $english, (string) $phone);
    $phone = str_replace($arabic,  $english, $phone);
    return preg_replace('/\D/', '', $phone);
}