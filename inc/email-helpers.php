<?php
if (!defined('ABSPATH')) exit;

/**
 * رمزنگاری امن — با استفاده از sodium (اولویت) یا openssl (fallback)
 */
function raha_encrypt($value) {
    if ($value === '' || $value === null) return '';
    $key = hash('sha256', wp_salt('auth'), true);

    if (function_exists('sodium_crypto_secretbox')) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox((string) $value, $nonce, $key);
        return 's1:' . base64_encode($nonce . $cipher);
    }
    if (function_exists('openssl_encrypt')) {
        $iv = random_bytes(16);
        $cipher = openssl_encrypt((string) $value, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return 'o1:' . base64_encode($iv . $cipher);
    }
    return 'p1:' . base64_encode((string) $value);
}

function raha_decrypt($value) {
    if ($value === '' || $value === null) return '';

    if (strpos($value, 's1:') === 0 && function_exists('sodium_crypto_secretbox_open')) {
        $data = base64_decode(substr($value, 3), true);
        if ($data === false || strlen($data) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
        $nonce  = substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key    = hash('sha256', wp_salt('auth'), true);
        $plain  = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        return $plain === false ? '' : $plain;
    }
    if (strpos($value, 'o1:') === 0) {
        $data = base64_decode(substr($value, 3), true);
        if ($data === false || strlen($data) <= 16) return '';
        $iv     = substr($data, 0, 16);
        $cipher = substr($data, 16);
        $key    = hash('sha256', wp_salt('auth'), true);
        $plain  = openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $plain === false ? '' : $plain;
    }
    if (strpos($value, 'p1:') === 0) {
        return base64_decode(substr($value, 3), true) ?: '';
    }

    // مهاجرت: مقدار قدیمی به‌صورت plain text ذخیره شده
    return $value;
}

/**
 * ساخت جدول لاگ ایمیل
 */
add_action('after_setup_theme', 'raha_ensure_email_tables');
function raha_ensure_email_tables() {
    $version = get_option('raha_email_db_version', '0');
    if (version_compare($version, '1.0.0', '>=')) return;

    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $log_table = $wpdb->prefix . 'raha_email_log';
    dbDelta("CREATE TABLE IF NOT EXISTS $log_table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body LONGTEXT NULL,
        headers TEXT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'queued',
        error_message TEXT NULL,
        template_data LONGTEXT NULL,
        retry_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
        ip VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        created_at DATETIME NOT NULL,
        sent_at DATETIME NULL,
        INDEX idx_status (status),
        INDEX idx_created (created_at),
        INDEX idx_type (type)
    ) $charset;");

    update_option('raha_email_db_version', '1.0.0');
}