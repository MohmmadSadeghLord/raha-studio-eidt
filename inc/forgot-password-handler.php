<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   AJAX: ارسال کد بازیابی
   ============================================================ */
add_action('wp_ajax_nopriv_raha_forgot_send', 'raha_handle_forgot_send');

function raha_handle_forgot_send() {
    if (!check_ajax_referer('raha_forgot_send', 'nonce', false)) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if (!is_email($email)) {
        wp_send_json(['ok' => false, 'message' => 'فرمت ایمیل معتبر نیست.']);
    }

    // Rate limit — ۳ ارسال در ۱۵ دقیقه per IP+email
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_key = 'raha_forgot_send_' . md5($email . '|' . $ip);
    $sends    = (int) get_transient($rate_key);
    if ($sends >= 3) {
        wp_send_json([
            'ok'      => false,
            'message' => 'تعداد درخواست‌ها بیش از حد مجاز. لطفاً ۱۵ دقیقه دیگر تلاش کنید.',
        ]);
    }
    set_transient($rate_key, $sends + 1, 15 * MINUTE_IN_SECONDS);

    // پیدا کردن کاربر
    $user = get_user_by('email', $email);

    // حتی اگر کاربر نبود، پیام موفقیت برمی‌گردانیم (anti-enumeration)
    // ولی در این حالت ترنزینت ذخیره نمی‌شود → تأیید کد همیشه شکست می‌خورد

    if ($user) {
        $code = wp_rand(100000, 999999);

        $transient_key = 'raha_forgot_code_' . md5($email);
        set_transient($transient_key, [
            'user_id'     => $user->ID,
            'email'       => $email,
            'code_hash'   => wp_hash_password((string) $code),
            'attempts'    => 0,
            'created'     => time(),
        ], 10 * MINUTE_IN_SECONDS);

        raha_send_forgot_email($user, $code);
    }

    wp_send_json([
        'ok'      => true,
        'message' => 'اگر این ایمیل در سیستم ثبت شده باشد، کد بازیابی ارسال شد.',
    ]);
}

/* ============================================================
   AJAX: تأیید کد
   ============================================================ */
add_action('wp_ajax_nopriv_raha_forgot_verify', 'raha_handle_forgot_verify');

function raha_handle_forgot_verify() {
    if (!check_ajax_referer('raha_forgot_verify', 'nonce', false)) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $code  = preg_replace('/\D/', '', wp_unslash($_POST['code'] ?? ''));

    if (!is_email($email) || strlen($code) !== 6) {
        wp_send_json(['ok' => false, 'message' => 'کد نامعتبر است.']);
    }

    $transient_key = 'raha_forgot_code_' . md5($email);
    $data = get_transient($transient_key);

    if (!$data) {
        wp_send_json(['ok' => false, 'message' => 'کد منقضی شده. لطفاً کد جدید دریافت کنید.']);
    }

    if ($data['attempts'] >= 5) {
        delete_transient($transient_key);
        wp_send_json(['ok' => false, 'message' => 'تعداد تلاش‌ها بیش از حد مجاز. کد جدید دریافت کنید.']);
    }

    if (!wp_check_password($code, $data['code_hash'])) {
        $data['attempts']++;
        set_transient($transient_key, $data, 10 * MINUTE_IN_SECONDS);
        wp_send_json(['ok' => false, 'message' => 'کد وارد شده معتبر نیست.']);
    }

    // تأیید موفق — reset attempts ولی نگه‌داری کد برای مرحله ۳
    $data['attempts'] = 0;
    $data['verified'] = true;
    set_transient($transient_key, $data, 10 * MINUTE_IN_SECONDS);

    wp_send_json(['ok' => true, 'message' => 'کد تأیید شد.']);
}

/* ============================================================
   AJAX: تغییر رمز
   ============================================================ */
add_action('wp_ajax_nopriv_raha_forgot_reset', 'raha_handle_forgot_reset');

function raha_handle_forgot_reset() {
    if (!check_ajax_referer('raha_forgot_reset', 'nonce', false)) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    $email    = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $code     = preg_replace('/\D/', '', wp_unslash($_POST['code'] ?? ''));
    $password = wp_unslash($_POST['password'] ?? '');

    if (!is_email($email) || strlen($code) !== 6) {
        wp_send_json(['ok' => false, 'message' => 'درخواست نامعتبر است.']);
    }

    if (strlen($password) < 8) {
        wp_send_json(['ok' => false, 'message' => 'رمز باید حداقل ۸ کاراکتر باشد.']);
    }
    if (strlen($password) > 72) {
        wp_send_json(['ok' => false, 'message' => 'رمز عبور طولانی‌تر از حد مجاز است.']);
    }
    if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
        wp_send_json(['ok' => false, 'message' => 'رمز باید شامل حرف و عدد باشد.']);
    }

    $transient_key = 'raha_forgot_code_' . md5($email);
    $data = get_transient($transient_key);

    if (!$data || empty($data['verified'])) {
        wp_send_json(['ok' => false, 'message' => 'جلسه بازیابی معتبر نیست. از ابتدا شروع کنید.']);
    }

    if (!wp_check_password($code, $data['code_hash'])) {
        wp_send_json(['ok' => false, 'message' => 'کد نامعتبر است.']);
    }

    $user = get_user_by('id', $data['user_id']);
    if (!$user) {
        delete_transient($transient_key);
        wp_send_json(['ok' => false, 'message' => 'کاربر یافت نشد.']);
    }

    // تغییر رمز
    reset_password($user, $password);
    delete_transient($transient_key);

    // لاگ‌اوت کردن تمام sessionهای قبلی برای امنیت
    $sessions = WP_Session_Tokens::get_instance($user->ID);
    $sessions->destroy_all();

    // پیام موفقیت + لینک ورود
    $redirect = isset($_POST['redirect']) ? wp_validate_redirect(wp_unslash($_POST['redirect']), '') : '';
    $login_url = function_exists('raha_page_url') ? raha_page_url('login') : wp_login_url();
    if ($redirect) {
        $login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
    }

    wp_send_json([
        'ok'       => true,
        'message'  => 'رمز عبور با موفقیت تغییر کرد.',
        'loginUrl' => $login_url,
    ]);
}

/* ============================================================
   ارسال ایمیل کد بازیابی (با استفاده از سیستم ایمیل جدید)
   ============================================================ */
function raha_send_forgot_email($user, $code) {
    // استفاده از سیستم جدید ایمیل که در پیشخوان لاگ می‌شود
    if (class_exists('Raha_Email')) {
        Raha_Email::send('reset_password', $user->user_email, [
            'name' => $user->display_name,
            'code' => $code,
        ]);
    } else {
        // Fallback: اگر کلاس لود نشده بود
        $site_name = get_bloginfo('name');
        $subject   = '[' . $site_name . '] کد بازیابی رمز عبور';
        $body      = "کد بازیابی شما: {$code}";
        wp_mail($user->user_email, $subject, $body);
    }
}

/* ============================================================
   فیلتر: لینک "فراموشی رمز" در ورود به صفحه سفارشی
   ============================================================ */
add_filter('lostpassword_url', function ($url, $redirect) {
    if (!function_exists('raha_page_url')) return $url;
    $page_url = raha_page_url('forgot-password');
    if (!$page_url || $page_url === home_url('/forgot-password/') && !get_page_by_path('forgot-password')) {
        return $url;
    }
    if ($redirect) {
        $page_url = add_query_arg('redirect_to', urlencode($redirect), $page_url);
    }
    return $page_url;
}, 10, 2);