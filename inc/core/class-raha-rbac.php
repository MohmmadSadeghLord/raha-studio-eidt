<?php
if (!defined('ABSPATH')) exit;

class Raha_RBAC {

    public static function init() {
        add_action('init', [__CLASS__, 'register_roles'], 5);
    }

    public static function register_roles() {
        // نقش مدیر ارشد (همه دسترسی‌ها)
        $super_admin = get_role('administrator');
        if ($super_admin) {
            $super_admin->add_cap('raha_manage_all');
        }

        // نقش مدیر عملیات
        add_role('raha_manager', 'مدیر عملیات', [
            'read' => true,
            'raha_manage_orders' => true,
            'raha_view_reports' => true,
            'raha_manage_projects' => true,
            'raha_view_invoices' => true,
        ]);

        // نقش پشتیبانی
        add_role('raha_support', 'پشتیبان', [
            'read' => true,
            'raha_manage_tickets' => true,
            'raha_view_users' => true,
        ]);

        // نقش مالی
        add_role('raha_finance', 'مدیر مالی', [
            'read' => true,
            'raha_manage_invoices' => true,
            'raha_manage_payments' => true,
            'raha_view_reports' => true,
        ]);
    }

    /**
     * بررسی دقیق مجوزها با ساختار Resource:Action
     * نمونه: Raha_RBAC::can('orders', 'update-status')
     */
    public static function can($resource, $action) {
        $user = wp_get_current_user();
        if (!$user->exists()) return false;
        
        if (current_user_can('raha_manage_all')) return true;

        // ساخت کپبیلیتی داینامیک
        $capability = 'raha_' . $resource . '_' . $action;
        return current_user_can($capability);
    }
}