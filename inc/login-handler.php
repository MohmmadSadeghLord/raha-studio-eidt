<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_nopriv_raha_login', 'raha_handle_login');
add_action('wp_ajax_raha_login',        'raha_handle_login');

function raha_handle_login() {
    // ۱. Nonce
    if (!check_ajax_referer('raha_login', 'nonce', false)) {
        wp_send_json(['ok' => false, 'message' => 'نشست منقضی شده. صفحه را رفرش کنید.']);
    }

    // ۲. Rate limit — ۵ تلاش / ۱۵ دقیقه per IP
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_key = 'raha_login_rate_' . md5($ip);
    $attempts = (int) get_transient($rate_key);
    if ($attempts >= 5) {
        wp_send_json([
            'ok'      => false,
            'message' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز. لطفاً ۱۵ دقیقه دیگر تلاش کنید.',
        ]);
    }

    // ۳. ورودی‌ها
    $login    = sanitize_text_field(wp_unslash($_POST['username'] ?? ''));
    $password = wp_unslash($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $redirect = wp_validate_redirect(
        wp_unslash($_POST['redirect'] ?? ''),
        function_exists('raha_page_url') ? raha_page_url('account') : home_url('/')
    );

    if (!$login || !$password) {
        wp_send_json(['ok' => false, 'message' => 'نام کاربری و رمز عبور را وارد کنید.']);
    }

    // ۴. اگر ایمیل بود، تبدیل به username
    if (is_email($login)) {
        $user_by_email = get_user_by('email', $login);
        if ($user_by_email) {
            $login = $user_by_email->user_login;
        }
    }

    // ۵. تلاش برای ورود
    $creds = [
        'user_login'    => $login,
        'user_password' => $password,
        'remember'      => $remember,
    ];

    $user = wp_signon($creds, is_ssl());

    if (is_wp_error($user)) {
        // افزایش شمارنده فقط در صورت خطا
        set_transient($rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS);

        $code = $user->get_error_code();
        // پیام یکسان برای همه خطاها (جلوگیری از user enumeration)
        wp_send_json([
            'ok'      => false,
            'field'   => 'password',
            'message' => 'نام کاربری یا رمز عبور اشتباه است.',
        ]);
    }

    // ۶. موفقیت — reset rate limit
    delete_transient($rate_key);

    wp_send_json([
        'ok'       => true,
        'message'  => 'ورود موفق.',
        'redirect' => $redirect,
    ]);
}