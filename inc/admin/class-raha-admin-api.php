<?php
if (!defined('ABSPATH')) exit;

class Raha_Admin_API {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        // مسیر داشبورد
        register_rest_route('raha/v1', '/dashboard', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_dashboard_data'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر سفارش‌ها
        register_rest_route('raha/v1', '/orders', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_orders_data'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر تغییر وضعیت سفارش
        register_rest_route('raha/v1', '/orders/update-status', [
            'methods'             => WP_REST_Server::CREATABLE, // POST
            'callback'            => [__CLASS__, 'update_order_status'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر فاکتورها
        register_rest_route('raha/v1', '/invoices', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_invoices_data'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر تیکت‌ها
        register_rest_route('raha/v1', '/tickets', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_tickets_data'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر لاگ‌های امنیتی
        register_rest_route('raha/v1', '/audit-logs', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_audit_logs'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر تنظیمات (GET برای خواندن، POST برای ذخیره)
        register_rest_route('raha/v1', '/settings', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [__CLASS__, 'get_settings'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE, // POST
                'callback'            => [__CLASS__, 'save_settings'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ]
        ]);

        // مسیر ورود به حساب مشتری
        register_rest_route('raha/v1', '/impersonate', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [__CLASS__, 'impersonate_user'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // مسیر اعلان‌های زنده
        register_rest_route('raha/v1', '/notifications', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_live_notifications'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
    }

    public static function check_permission() {
        return current_user_can('manage_options');
    }

    public static function get_dashboard_data() {
        // استفاده از Transient برای کش کردن آمار (سرعت بالا)
        $cached_data = get_transient('raha_dashboard_stats');
        if (false !== $cached_data) {
            return rest_ensure_response($cached_data);
        }

        global $wpdb;
        $total_orders = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'raha_order' AND post_status = 'publish'");
        $total_revenue = $wpdb->get_var("SELECT SUM(total) FROM {$wpdb->prefix}raha_invoices WHERE status = 'paid'");
        $active_tickets = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'raha_ticket' AND post_status = 'publish'");
        $pending_orders = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'raha_order' AND post_status = 'publish'");

        $recent_orders = get_posts(['post_type' => 'raha_order', 'posts_per_page' => 5, 'post_status' => 'publish']);
        $recent_data = [];
        foreach ($recent_orders as $order) {
            $meta = get_post_meta($order->ID);
            $recent_data[] = [
                'id'       => $order->ID,
                'code'     => $meta['tracking_code'][0] ?? '—',
                'customer' => $meta['full_name'][0] ?? 'ناشناس',
                'service'  => $meta['service'][0] ?? '—',
                'amount'   => (int) ($meta['amount'][0] ?? 0),
                'status'   => $meta['order_status'][0] ?? 'new',
                'date'     => get_the_date('Y/m/d', $order->ID),
            ];
        }

        $response_data = [
            'stats' => [
                'total_orders'   => (int) $total_orders,
                'total_revenue'  => (int) $total_revenue,
                'active_tickets' => (int) $active_tickets,
                'pending_orders' => (int) $pending_orders,
            ],
            'recent_orders' => $recent_data
        ];

        // ذخیره در کش برای ۵ دقیقه
        set_transient('raha_dashboard_stats', $response_data, 5 * MINUTE_IN_SECONDS);

        return rest_ensure_response($response_data);
    }

    public static function get_orders_data(WP_REST_Request $request) {
        $page   = (int) $request->get_param('page') ?: 1;
        $search = sanitize_text_field($request->get_param('search'));
        
        $args = [
            'post_type'      => 'raha_order',
            'posts_per_page' => 10,
            'paged'          => $page,
            'post_status'    => 'publish',
        ];

        if (!empty($search)) {
            $args['meta_query'] = [
                'relation' => 'OR',
                ['key' => 'tracking_code', 'value' => $search, 'compare' => 'LIKE'],
                ['key' => 'full_name', 'value' => $search, 'compare' => 'LIKE'],
            ];
        }

        $query = new WP_Query($args);
        $orders = [];

        foreach ($query->posts as $post) {
            $meta = get_post_meta($post->ID);
            $orders[] = [
                'id'       => $post->ID,
                'code'     => $meta['tracking_code'][0] ?? '—',
                'customer' => $meta['full_name'][0] ?? 'ناشناس',
                'contact'  => $meta['contact_value'][0] ?? '—',
                'service'  => $meta['service'][0] ?? '—',
                'budget'   => $meta['budget'][0] ?? '—',
                'amount'   => (int) ($meta['amount'][0] ?? 0),
                'status'   => $meta['order_status'][0] ?? 'new',
                'date'     => get_the_date('Y/m/d', $post->ID),
            ];
        }

        return rest_ensure_response([
            'orders' => $orders,
            'total'  => $query->found_posts,
            'pages'  => $query->max_num_pages,
            'page'   => $page
        ]);
    }

    public static function update_order_status(WP_REST_Request $request) {
        $params = $request->get_json_params();
        $order_ids = isset($params['ids']) ? array_map('absint', $params['ids']) : [];
        $new_status = sanitize_text_field($params['status'] ?? '');

        if (empty($order_ids) || empty($new_status)) {
            return new WP_Error('invalid_data', 'داده‌های ارسالی نامعتبر است.', ['status' => 400]);
        }

        $updated_count = 0;
        foreach ($order_ids as $id) {
            if (get_post_type($id) === 'raha_order') {
                $old_status = get_post_meta($id, 'order_status', true);
                update_post_meta($id, 'order_status', $new_status);
                $updated_count++;

                // پاک کردن کش آمار داشبورد پس از تغییر وضعیت
                delete_transient('raha_dashboard_stats');

                // ثبت در لاگ امنیتی (که در فاز ۱ ساختیم)
                if (class_exists('Raha_Audit_Logger')) {
                    Raha_Audit_Logger::log(
                        'update_status', 
                        'order', 
                        $id, 
                        ['status' => $old_status], 
                        ['status' => $new_status], 
                        'تغییر گروهی وضعیت از پنل ادمین'
                    );
                }
            }
        }

        return rest_ensure_response([
            'success' => true,
            'message' => "وضعیت {$updated_count} سفارش با موفقیت تغییر کرد."
        ]);
    }

    public static function get_invoices_data(WP_REST_Request $request) {
        global $wpdb;
        $page = (int) $request->get_param('page') ?: 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $table = $wpdb->prefix . 'raha_invoices';

        // استفاده از Prepare برای جلوگیری از SQL Injection
        $invoices = $wpdb->get_results($wpdb->prepare(
            "SELECT i.*, u.display_name as customer_name 
             FROM $table i 
             LEFT JOIN {$wpdb->users} u ON i.user_id = u.ID 
             ORDER BY i.created_at DESC 
             LIMIT %d OFFSET %d",
            $limit, $offset
        ));

        $total = $wpdb->get_var("SELECT COUNT(id) FROM $table");

        $data = [];
        foreach ($invoices as $inv) {
            $data[] = [
                'id'         => $inv->id,
                'number'     => $inv->invoice_number,
                'customer'   => $inv->customer_name ?: 'مهمان',
                'total'      => (float) $inv->total,
                'status'     => $inv->status,
                'date'       => date_i18n('Y/m/d', strtotime($inv->created_at)),
                'due_date'   => $inv->due_date ? date_i18n('Y/m/d', strtotime($inv->due_date)) : '—'
            ];
        }

        return rest_ensure_response([
            'invoices' => $data,
            'total'    => (int) $total,
            'pages'    => ceil($total / $limit),
            'page'     => $page
        ]);
    }

    public static function get_tickets_data(WP_REST_Request $request) {
        $page = (int) $request->get_param('page') ?: 1;
        
        $query = new WP_Query([
            'post_type'      => 'raha_ticket',
            'posts_per_page' => 15,
            'paged'          => $page,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);

        $tickets = [];
        foreach ($query->posts as $post) {
            $meta = get_post_meta($post->ID);
            $user_info = get_userdata($post->post_author);
            
            $tickets[] = [
                'id'       => $post->ID,
                'subject'  => $post->post_title,
                'customer' => $user_info ? $user_info->display_name : 'مهمان',
                'priority' => $meta['priority'][0] ?? 'normal',
                'status'   => $meta['ticket_status'][0] ?? 'open',
                'date'     => get_the_date('Y/m/d H:i', $post->ID),
            ];
        }

        return rest_ensure_response([
            'tickets' => $tickets,
            'total'   => $query->found_posts,
            'pages'   => $query->max_num_pages,
            'page'    => $page
        ]);
    }

    public static function get_audit_logs(WP_REST_Request $request) {
        global $wpdb;
        $page = (int) $request->get_param('page') ?: 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $table = $wpdb->prefix . 'raha_audit_logs';

        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT l.*, u.display_name as actor_name 
             FROM $table l 
             LEFT JOIN {$wpdb->users} u ON l.actor_id = u.ID 
             ORDER BY l.created_at DESC 
             LIMIT %d OFFSET %d",
            $limit, $offset
        ));

        $total = $wpdb->get_var("SELECT COUNT(id) FROM $table");

        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                'id'          => $log->id,
                'actor'       => $log->actor_name ?: 'سیستم',
                'action'      => $log->action,
                'entity_type' => $log->entity_type,
                'entity_id'   => $log->entity_id,
                'before'      => $log->before_state,
                'after'       => $log->after_state,
                'ip'          => $log->ip_address,
                'date'        => date_i18n('Y/m/d H:i', strtotime($log->created_at)),
            ];
        }

        return rest_ensure_response([
            'logs'  => $data,
            'total' => (int) $total,
            'pages' => ceil($total / $limit),
            'page'  => $page
        ]);
    }

    public static function get_settings() {
        return rest_ensure_response([
            'studio_name'    => get_option('raha_studio_name', 'استودیو رها'),
            'support_email'  => get_option('raha_support_email', get_option('admin_email')),
            'support_phone'  => get_option('raha_support_phone', ''),
            'gateway_key'    => get_option('raha_gateway_key', ''),
        ]);
    }

    public static function save_settings(WP_REST_Request $request) {
        $params = $request->get_json_params();

        if (isset($params['studio_name'])) {
            update_option('raha_studio_name', sanitize_text_field($params['studio_name']));
        }
        if (isset($params['support_email'])) {
            update_option('raha_support_email', sanitize_email($params['support_email']));
        }
        if (isset($params['support_phone'])) {
            update_option('raha_support_phone', sanitize_text_field($params['support_phone']));
        }
        if (isset($params['gateway_key'])) {
            update_option('raha_gateway_key', sanitize_text_field($params['gateway_key']));
        }

        return rest_ensure_response(['success' => true, 'message' => 'تنظیمات با موفقیت ذخیره شد.']);
    }

    public static function impersonate_user(WP_REST_Request $request) {
        $params = $request->get_json_params();
        $customer_id = isset($params['customer_id']) ? (int) $params['customer_id'] : 0;

        if (!$customer_id) {
            return new WP_Error('invalid_user', 'کاربر نامعتبر است.', ['status' => 400]);
        }

        $url = Raha_Impersonate::generate_token($customer_id);
        if ($url) {
            return rest_ensure_response(['success' => true, 'url' => $url]);
        }
        return new WP_Error('error', 'خطا در تولید لینک', ['status' => 500]);
    }

    public static function get_live_notifications() {
        global $wpdb;
        // در اینجا می‌توانیم چک کنیم آیا سفارش جدید یا تیکت جدیدی در ۳۰ دقیقه اخیر ثبت شده است
        $recent_orders = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'raha_order' AND post_status = 'publish' AND post_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $recent_tickets = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'raha_ticket' AND post_status = 'publish' AND post_date > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        
        return rest_ensure_response([
            'count' => (int)$recent_orders + (int)$recent_tickets
        ]);
    }
}