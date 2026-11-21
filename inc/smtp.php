<?php
if (!defined('ABSPATH')) exit;

add_action('phpmailer_init', function ($phpmailer) {
    if (get_option('raha_smtp_enabled') !== '1') return;

    $host   = get_option('raha_smtp_host');
    $port   = get_option('raha_smtp_port', '587');
    $secure = get_option('raha_smtp_secure', 'tls');
    $user   = get_option('raha_smtp_user');
    $pass   = raha_decrypt(get_option('raha_smtp_pass', ''));

    if (empty($host) || empty($user) || empty($pass)) return;

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = (int) $port;
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->SMTPSecure = $secure;
    $phpmailer->Timeout    = 15;

    // Debug: capture SMTP responses in error_message
    $phpmailer->SMTPDebug  = 0;

    $from_email = get_option('raha_smtp_from_email');
    $from_name  = get_option('raha_smtp_from_name', 'استودیو رها');

    if ($from_email && is_email($from_email)) {
        $phpmailer->setFrom($from_email, $from_name);
    } else {
        $phpmailer->setFrom($user, $from_name);
    }
});

/* ============================================================
   Test email via admin-post (legacy - kept for backward compat)
   ============================================================ */
add_action('admin_post_raha_test_email', function () {
    if (!current_user_can('manage_options')) wp_die('دسترسی غیرمجاز');

    $to      = get_option('admin_email');
    $subject = 'تست ارسال ایمیل — ' . get_bloginfo('name');
    $message = 'اگر این ایمیل را می‌بینید، تنظیمات SMTP به درستی کار می‌کند. 🎉';

    $sent = wp_mail($to, $subject, $message);

    $status = $sent ? 'success' : 'error';
    wp_safe_redirect(add_query_arg('email_test', $status, admin_url('admin.php?page=raha-options&tab=email')));
    exit;
});