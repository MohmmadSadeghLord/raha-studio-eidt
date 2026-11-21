<?php
if (!defined('ABSPATH')) exit;

class Raha_Email {

    public static function is_enabled($type) {
        $defaults = [
            'order_admin'      => '1',
            'order_customer'   => '1',
            'register_welcome' => '1',
            'reset_password'   => '1',
            'ticket_admin'     => '1',
            'ticket_reply'     => '1',
            'cta_admin'        => '1',
        ];
        return get_option('raha_email_enable_' . $type, $defaults[$type] ?? '1') === '1';
    }

    public static function get_recipient($type, $data = []) {
        if (!empty($data['to'])) return $data['to'];

        $admin_email = get_option('raha_email_admin_recipient') ?: get_option('admin_email');

        switch ($type) {
            case 'order_admin':
            case 'ticket_admin':
            case 'cta_admin':
                return $admin_email;
            case 'order_customer':
            case 'register_welcome':
            case 'reset_password':
            case 'ticket_reply':
                return $data['to'] ?? '';
            default:
                return $admin_email;
        }
    }

    /**
     * ارسال ایمیل (آنی / همگام)
     *
     * @param string $type  نوع ایمیل (order_admin, register_welcome, ...)
     * @param string $to    ایمیل مقصد
     * @param array  $data  داده‌های قالب
     * @param array  $args  ['log_only' => bool, 'skip_enabled_check' => bool]
     * @return bool         نتیجهٔ واقعی wp_mail()
     */
    public static function send($type, $to, $data = [], $args = []) {
        if (empty($args['skip_enabled_check']) && !self::is_enabled($type)) {
            return false;
        }
        if (!$to || !is_email($to)) {
            return false;
        }

        $subject = self::build_subject($type, $data);
        $body    = self::build_body($type, $data);

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        $from_email = get_option('raha_smtp_from_email') ?: get_option('admin_email');
        $from_name  = get_option('raha_smtp_from_name') ?: get_bloginfo('name');
        $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);

        // لاگ اولیه
        $log_id = raha_email_log_create($type, $to, $subject, $body, $headers, $data);

        // ارسال آنی
        $sent = wp_mail($to, $subject, $body, $headers);

        if ($sent) {
            raha_email_log_update($log_id, 'sent');
        } else {
            global $phpmailer;
            $error = ($phpmailer && !empty($phpmailer->ErrorInfo))
                ? $phpmailer->ErrorInfo
                : 'wp_mail() returned false';
            raha_email_log_update($log_id, 'failed', $error);
        }

        return $sent;
    }

    public static function build_subject($type, $data) {
        $site = get_bloginfo('name');
        $map  = [
            'order_admin'      => sprintf('[%s] سفارش جدید — %s', $site, $data['code'] ?? ''),
            'order_customer'   => sprintf('درخواست شما با موفقیت ثبت شد — %s', $data['code'] ?? ''),
            'register_welcome' => sprintf('به %s خوش آمدید!', $site),
            'reset_password'   => sprintf('[%s] کد بازیابی رمز عبور', $site),
            'ticket_admin'     => sprintf('[%s] تیکت جدید: %s', $site, $data['subject'] ?? ''),
            'ticket_reply'     => sprintf('[%s] پاسخ تیم پشتیبانی', $site),
            'cta_admin'        => sprintf('[%s] درخواست تماس جدید', $site),
        ];
        return $map[$type] ?? sprintf('[%s] اعلان', $site);
    }

    public static function build_body($type, $data) {
        if (!function_exists('raha_email_template_' . $type)) {
            return '<p>Template not found for: ' . esc_html($type) . '</p>';
        }
        return call_user_func('raha_email_template_' . $type, $data);
    }
}