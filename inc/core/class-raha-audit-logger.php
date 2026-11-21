<?php
if (!defined('ABSPATH')) exit;

class Raha_Audit_Logger {

    /**
     * ثبت یک رخداد در سیستم
     * @param string $action (مثلاً 'update' یا 'delete')
     * @param string $entity_type (مثلاً 'order' یا 'invoice')
     * @param int $entity_id
     * @param mixed $before وضعیت قبل از تغییر
     * @param mixed $after وضعیت بعد از تغییر
     * @param string $reason دلیل تغییر (برای اقدامات حساس)
     */
    public static function log($action, $entity_type, $entity_id, $before = null, $after = null, $reason = '') {
        global $wpdb;
        
        $current_user = wp_get_current_user();
        $actor_id = $current_user->exists() ? $current_user->ID : 0;

        $before_json = $before !== null ? wp_json_encode($before, JSON_UNESCAPED_UNICODE) : null;
        $after_json  = $after !== null ? wp_json_encode($after, JSON_UNESCAPED_UNICODE) : null;

        $wpdb->insert(
            $wpdb->prefix . 'raha_audit_logs',
            [
                'actor_id'     => $actor_id,
                'action'       => sanitize_text_field($action),
                'entity_type'  => sanitize_text_field($entity_type),
                'entity_id'    => (int) $entity_id,
                'before_state' => $before_json,
                'after_state'  => $after_json,
                'reason'       => sanitize_text_field($reason),
                'ip_address'   => self::get_ip(),
                'user_agent'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
                'created_at'   => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    private static function get_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}