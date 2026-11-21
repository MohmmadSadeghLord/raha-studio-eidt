<?php
if (!defined('ABSPATH')) exit;

class Raha_Security {

    /**
     * بررسی امنیتی درخواست‌های AJAX
     */
    public static function verify_ajax_request($capability = 'read') {
        // ۱. بررسی Nonce (جلوگیری از CSRF)
        if (!check_ajax_referer('raha_admin_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'نشست منقضی شده است. لطفا صفحه را رفرش کنید.'], 403);
        }

        // ۲. بررسی مجوز کاربر
        if (!current_user_can($capability)) {
            wp_send_json_error(['message' => 'شما دسترسی لازم برای این عملیات را ندارید.'], 403);
        }
        
        return true;
    }

    /**
     * پاکسازی آرایه‌های عمیق (Recursive Sanitization)
     */
    public static function sanitize_array($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sanitize_array($value);
            } else {
                $array[$key] = sanitize_text_field(wp_unslash($value));
            }
        }
        return $array;
    }
}