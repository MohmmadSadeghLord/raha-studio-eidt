<?php
if (!defined('ABSPATH')) exit;

function raha_email_log_table() {
    global $wpdb;
    return $wpdb->prefix . 'raha_email_log';
}

/**
 * ایجاد رکورد لاگ اولیه (queued)
 */
function raha_email_log_create($type, $to, $subject, $body, $headers, $data = []) {
    global $wpdb;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 250) : null;

    $wpdb->insert(raha_email_log_table(), [
        'type'          => $type,
        'recipient'     => $to,
        'subject'       => $subject,
        'body'          => $body,
        'headers'       => is_array($headers) ? implode("\n", $headers) : (string) $headers,
        'status'        => 'queued',
        'template_data' => wp_json_encode($data),
        'ip'            => $ip,
        'user_agent'    => $ua,
        'created_at'    => current_time('mysql'),
    ]);

    return (int) $wpdb->insert_id;
}

/**
 * به‌روزرسانی نتیجه ارسال
 */
function raha_email_log_update($log_id, $status, $error = null) {
    global $wpdb;
    $update = ['status' => $status];
    if ($error !== null) $update['error_message'] = $error;
    if ($status === 'sent') $update['sent_at'] = current_time('mysql');

    $wpdb->update(raha_email_log_table(), $update, ['id' => $log_id]);
}

/**
 * دریافت لاگ‌ها با صفحه‌بندی
 */
function raha_email_get_logs($per_page = 30, $page = 1, $type = '', $status = '') {
    global $wpdb;
    $table = raha_email_log_table();
    $where = ['1=1'];
    $params = [];

    if ($type)   { $where[] = 'type = %s';   $params[] = $type; }
    if ($status) { $where[] = 'status = %s'; $params[] = $status; }

    $where_sql = implode(' AND ', $where);
    $offset = ($page - 1) * $per_page;

    $sql = "SELECT * FROM $table WHERE $where_sql ORDER BY id DESC LIMIT %d OFFSET %d";
    $params[] = $per_page;
    $params[] = $offset;

    return $wpdb->get_results($wpdb->prepare($sql, $params));
}

function raha_email_count_logs($type = '', $status = '') {
    global $wpdb;
    $table = raha_email_log_table();
    $where = ['1=1'];
    $params = [];
    if ($type)   { $where[] = 'type = %s';   $params[] = $type; }
    if ($status) { $where[] = 'status = %s'; $params[] = $status; }
    $sql = "SELECT COUNT(*) FROM $table WHERE " . implode(' AND ', $where);
    return (int) ($params ? $wpdb->get_var($wpdb->prepare($sql, $params)) : $wpdb->get_var($sql));
}