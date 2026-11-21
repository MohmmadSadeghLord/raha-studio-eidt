<?php
if (!defined('ABSPATH')) exit;

// افزودن منو به پیشخوان
add_action('admin_menu', function() {
    add_menu_page(
        'پنل مدیریت سفارشات',
        'پنل سفارشات (جدید)',
        'manage_options',
        'raha-orders-panel',
        'raha_render_admin_orders_panel',
        'dashicons-clipboard',
        56
    );
});

// رندر صفحه
function raha_render_admin_orders_panel() {
    if (!current_user_can('manage_options')) return;

    $orders = get_posts([
        'post_type'      => 'raha_order',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $grouped_data = [];
    $total_orders = count($orders);
    $new_orders = 0;
    $total_budget = 0;

    foreach ($orders as $order) {
        $id = $order->ID;
        $status = get_post_meta($id, 'order_status', true) ?: 'new';
        if ($status === 'new') $new_orders++;
        
        $budget_val = (int) get_post_meta($id, 'amount', true);
        $total_budget += $budget_val;
        
        $name = get_post_meta($id, 'full_name', true);
        $contact = get_post_meta($id, 'contact_value', true);
        $user_id = $order->post_author;
        
        // ساخت شناسه یکتا برای کاربر (اگر مهمان باشد با ایمیل، اگر لاگین باشد با ID)
        $uid = $user_id ? 'user_' . $user_id : 'guest_' . md5($contact);
        
        if (!isset($grouped_data[$uid])) {
            $grouped_data[$uid] = [
                'uid' => $uid,
                'name' => $name ?: 'کاربر مهمان',
                'contact' => $contact,
                'orders' => [],
                'total_spent' => 0
            ];
        }

        $features = get_post_meta($id, 'features', true) ?: [];
        
        $order_data = [
            'id' => $id,
            'tracking' => get_post_meta($id, 'tracking_code', true),
            'date' => get_the_date('Y/m/d', $id),
            'status' => $status,
            'companyName' => get_post_meta($id, 'company_name', true),
            'contactMethod' => get_post_meta($id, 'contact_method', true),
            'contactValue' => get_post_meta($id, 'contact_value', true),
            'service' => get_post_meta($id, 'service', true),
            'purpose' => get_post_meta($id, 'purpose', true),
            'businessName' => get_post_meta($id, 'business_name', true),
            'businessDescription' => get_post_meta($id, 'business_description', true),
            'features' => is_array($features) ? $features : [],
            'featuresOther' => get_post_meta($id, 'features_other', true),
            'designNotes' => get_post_meta($id, 'design_notes', true),
            'contentStatus' => get_post_meta($id, 'content_status', true),
            'domainHosting' => get_post_meta($id, 'domain_hosting', true),
            'timeline' => get_post_meta($id, 'timeline', true),
            'budget' => get_post_meta($id, 'budget', true) ?: ($budget_val ? number_format($budget_val) . ' تومان' : 'نامشخص'),
            'extraNotes' => get_post_meta($id, 'extra_notes', true),
            // فیلدهای تعاملی
            'amount' => $budget_val,
            'amount_paid' => (int) get_post_meta($id, 'amount_paid', true),
            'admin_notes' => get_post_meta($id, 'admin_notes', true),
        ];
        
        $grouped_data[$uid]['orders'][] = $order_data;
        $grouped_data[$uid]['total_spent'] += (int) get_post_meta($id, 'amount_paid', true);
    }

    $json_data = wp_json_encode(array_values($grouped_data));
    ?>
    
    <div class="wrap" style="margin: 0; padding: 0;">
        <div id="raha-admin-orders-app">
            
            <!-- هدر و آمار -->
            <header class="admin-header">
                <div class="header-title">
                    <h1>پنل مدیریت سفارشات</h1>
                    <span class="badge">استودیو</span>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <h3>کل سفارشات</h3>
                            <div class="value"><?php echo esc_html(raha_fa_num($total_orders)); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🔔</div>
                        <div class="stat-info">
                            <h3>سفارشات جدید</h3>
                            <div class="value"><?php echo esc_html(raha_fa_num($new_orders)); ?></div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3>مجموع مبالغ (تومان)</h3>
                            <div class="value"><?php echo esc_html(raha_money($total_budget)); ?></div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- کنترل‌ها و مسیر (Breadcrumb) -->
            <div class="admin-controls">
                <div class="breadcrumb" id="adminBreadcrumb" style="display:none;">
                    <a href="#" data-view="users">لیست مشتریان</a>
                    <span>›</span>
                    <span id="breadcrumbCurrent"></span>
                </div>
                
                <div class="search-box" id="searchBoxWrapper">
                    <input type="text" id="searchInput" placeholder="جستجوی نام کارفرما، برند یا نوع خدمت...">
                </div>
            </div>

            <!-- گرید‌ها (کاربران یا سفارشات) -->
            <div class="orders-grid" id="mainGrid"></div>

            <!-- مودال جزئیات سفارش -->
            <div class="modal-overlay" id="orderModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">جزئیات سفارش</h2>
                        <button class="close-btn" id="closeModalBtn">✕</button>
                    </div>
                    <div class="modal-body" id="modalBody"></div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" id="cancelModalBtn">بستن</button>
                        <button class="btn btn-primary" id="saveOrderBtn">💾 ذخیره تغییرات</button>
                    </div>
                </div>
            </div>

            <!-- پیام موفقیت AJAX -->
            <div id="ajaxMessage" class="ajax-message"></div>

        </div>
    </div>

    <script>
        window.RahaAdminOrdersData = <?php echo $json_data; ?>;
    </script>
    <?php
}

// ============================================================
// AJAX Handler برای ذخیره تغییرات
// ============================================================
add_action('wp_ajax_raha_update_order_admin', 'raha_update_order_admin_handler');
function raha_update_order_admin_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'دسترسی غیرمجاز.']);
    }
    
    if (!check_ajax_referer('raha_admin_order_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'نشست منقضی شده.']);
    }

    $order_id = absint($_POST['order_id'] ?? 0);
    if (!$order_id || get_post_type($order_id) !== 'raha_order') {
        wp_send_json_error(['message' => 'سفارش نامعتبر است.']);
    }

    $status = sanitize_text_field($_POST['status'] ?? '');
    $amount = (int) ($_POST['amount'] ?? 0);
    $amount_paid = (int) ($_POST['amount_paid'] ?? 0);
    $admin_notes = sanitize_textarea_field($_POST['admin_notes'] ?? '');

    update_post_meta($order_id, 'order_status', $status);
    update_post_meta($order_id, 'amount', $amount);
    update_post_meta($order_id, 'amount_paid', $amount_paid);
    update_post_meta($order_id, 'admin_notes', $admin_notes);

    wp_send_json_success(['message' => 'تغییرات با موفقیت ذخیره شد.']);
}