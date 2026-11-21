<?php
if (!defined('ABSPATH')) exit;

class Raha_DB_Installer {
    
    public static function init() {
        add_action('after_switch_theme', [__CLASS__, 'create_tables']);
        add_action('admin_init', [__CLASS__, 'check_db_version']);
    }

    public static function check_db_version() {
        if (get_option('raha_db_version') !== '1.0.0') {
            self::create_tables();
            update_option('raha_db_version', '1.0.0');
        }
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // ۱. جدول لاگ‌های امنیتی (غیرقابل تغییر)
        $table_logs = $wpdb->prefix . 'raha_audit_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            actor_id BIGINT UNSIGNED NOT NULL,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id BIGINT UNSIGNED NOT NULL,
            before_state LONGTEXT NULL,
            after_state LONGTEXT NULL,
            reason VARCHAR(255) NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            INDEX idx_actor (actor_id),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created (created_at)
        ) $charset_collate;";

        // ۲. جدول فاکتورها (ساختار مالی دقیق)
        $table_invoices = $wpdb->prefix . 'raha_invoices';
        $sql_invoices = "CREATE TABLE $table_invoices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_number VARCHAR(50) NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            order_id BIGINT UNSIGNED NOT NULL,
            subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            tax DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            discount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(20) NOT NULL DEFAULT 'unpaid', 
            due_date DATE NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY uq_invoice_number (invoice_number),
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) $charset_collate;";

        // ۳. جدول تراکنش‌ها و پرداخت‌ها
        $table_transactions = $wpdb->prefix . 'raha_transactions';
        $sql_trans = "CREATE TABLE $table_transactions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice_id BIGINT UNSIGNED NOT NULL,
            gateway VARCHAR(50) NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            ref_id VARCHAR(100) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            paid_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            INDEX idx_invoice (invoice_id),
            INDEX idx_status (status),
            INDEX idx_ref (ref_id)
        ) $charset_collate;";

        // اجرای کوئری‌ها
        dbDelta($sql_logs);
        dbDelta($sql_invoices);
        dbDelta($sql_trans);
    }
}