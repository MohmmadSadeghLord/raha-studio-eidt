<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   ستون‌های سفارش
   ============================================================ */
add_filter('manage_raha_order_posts_columns', function ($cols) {
    $new = [];
    $new['cb']             = $cols['cb'];
    $new['tracking_code']  = 'کد پیگیری';
    $new['customer']       = 'مشتری';
    $new['service']        = 'نوع خدمت';
    $new['order_status']   = 'وضعیت';
    $new['amount']         = 'مبلغ (تومان)';
    $new['date']           = 'تاریخ';
    return $new;
});

add_action('manage_raha_order_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'tracking_code':
            echo '<strong style="font-family:monospace; font-size:13px;">' . esc_html(get_post_meta($post_id, 'tracking_code', true)) . '</strong>';
            break;
        case 'customer':
            echo '<strong>' . esc_html(get_post_meta($post_id, 'full_name', true)) . '</strong>';
            $company = get_post_meta($post_id, 'company_name', true);
            if ($company) echo '<br><span style="color:#666; font-size:11px;">' . esc_html($company) . '</span>';
            break;
        case 'service':
            $services = [
                'new' => 'طراحی جدید', 'redesign' => 'بازطراحی', 'development' => 'توسعه', 'consult' => 'مشاوره'
            ];
            $s = get_post_meta($post_id, 'service', true);
            echo esc_html($services[$s] ?? $s);
            break;
        case 'order_status':
            $status = get_post_meta($post_id, 'order_status', true) ?: 'new';
            $label  = raha_status_label($status);
            
            // رنگ‌بندی اختصاصی
            $bg = '#fff'; $color = '#14110f'; $border = '#14110f';
            if ($status === 'new')      { $bg = '#facc15'; }
            if ($status === 'review')   { $bg = '#e0f2fe'; $color = '#0c4a6e'; }
            if ($status === 'progress') { $bg = '#7dd3fc'; }
            if ($status === 'done')     { $bg = '#dcfce7'; $color = '#14532d'; }
            if ($status === 'canceled') { $bg = '#fee2e2'; $color = '#7f1d1d'; }

            printf(
                '<span style="display:inline-block; padding:4px 10px; border:2px solid %s; border-radius:6px; background:%s; color:%s; font-weight:800; font-size:11px; box-shadow:1px 1px 0 %s;">%s</span>',
                esc_attr($border), esc_attr($bg), esc_attr($color), esc_attr($border), esc_html($label)
            );
            break;
        case 'amount':
            $amount = (int) get_post_meta($post_id, 'amount', true);
            echo $amount ? '<strong>' . raha_money($amount) . '</strong>' : '<span style="color:#999;">—</span>';
            break;
    }
}, 10, 2);

/* ============================================================
   استایل‌دهی به جدول ادمین (CSS)
   ============================================================ */
add_action('admin_head', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-raha_order') return;
    ?>
    <style>
        /* هدر جدول */
        .wp-list-table.posts th { font-weight: 800; color: #14110f; }
        /* ردیف‌ها */
        .wp-list-table.posts tr { transition: background 0.2s; }
        .wp-list-table.posts tr:hover { background: #f8fafc !important; }
        /* فاصله‌گذاری سلول‌ها */
        .wp-list-table.posts td { vertical-align: middle; padding-top: 12px; padding-bottom: 12px; }
        /* استایل کد پیگیری */
        .column-tracking_code { width: 140px; }
        .column-amount { width: 120px; }
    </style>
    <?php
});

/* ============================================================
   ستون‌های تیکت
   ============================================================ */
add_filter('manage_raha_ticket_posts_columns', function ($cols) {
    $new = [];
    $new['cb']             = $cols['cb'];
    $new['ticket_subject'] = 'موضوع';
    $new['author']         = 'مشتری';
    $new['ticket_status']  = 'وضعیت';
    $new['priority']       = 'اولویت';
    $new['date']           = 'تاریخ';
    return $new;
});

add_action('manage_raha_ticket_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'ticket_subject':
            echo '<strong><a href="' . get_edit_post_link($post_id) . '">' . esc_html(get_the_title($post_id)) . '</a></strong>';
            break;
        case 'author':
            $user = get_userdata(get_post_field('post_author', $post_id));
            echo $user ? esc_html($user->display_name) : '—';
            break;
        case 'ticket_status':
            $s = get_post_meta($post_id, 'ticket_status', true) ?: 'open';
            $labels = ['open' => 'باز', 'progress' => 'در حال بررسی', 'waiting' => 'در انتظار مشتری', 'closed' => 'بسته‌شده'];
            echo '<span style="font-weight:800; font-size:11px;">' . esc_html($labels[$s] ?? $s) . '</span>';
            break;
        case 'priority':
            $p = get_post_meta($post_id, 'priority', true) ?: 'normal';
            $labels = ['low' => 'کم', 'normal' => 'متوسط', 'high' => 'زیاد'];
            echo esc_html($labels[$p] ?? $p);
            break;
    }
}, 10, 2);


/* ============================================================
   ستون‌های پیام‌های تماس (حرفه‌ای شده)
   ============================================================ */
add_filter('manage_raha_lead_posts_columns', function ($cols) {
    $new = [];
    $new['cb']           = $cols['cb'];
    $new['lead_name']    = 'نام مشتری';
    $new['lead_contact'] = 'راه ارتباطی';
    $new['lead_service'] = 'موضوع';
    $new['lead_status']  = 'وضعیت';
    $new['date']         = 'تاریخ';
    return $new;
});

add_action('manage_raha_lead_posts_custom_column', function ($col, $post_id) {
    switch ($col) {
        case 'lead_name':
            $name = get_post_meta($post_id, 'lead_name', true);
            if ($name) {
                echo '<strong style="font-size:13px;">' . esc_html($name) . '</strong>';
            } else {
                echo '<span style="color:#999;">— کاربر مهمان —</span>';
            }
            break;
            
        case 'lead_contact':
            echo '<strong style="font-family:monospace; font-size:13px; direction:ltr;">' . esc_html(get_post_meta($post_id, 'lead_contact', true)) . '</strong>';
            break;
            
        case 'lead_service':
            $service = get_post_meta($post_id, 'lead_service', true);
            $map = [
                'design'   => 'طراحی سایت', 'seo' => 'سئو', 'redesign' => 'بازطراحی', 
                'consult'  => 'مشاوره', 'other' => 'سایر', 'cta' => 'فرم CTA'
            ];
            echo esc_html($map[$service] ?? ($service ?: 'نامشخص'));
            break;

        case 'lead_status':
            // برای پیام‌های قدیمی که این فیلد را ندارند، پیش‌فرض unread در نظر می‌گیریم
            $status = get_post_meta($post_id, 'lead_status', true) ?: 'unread';
            if ($status === 'unread') {
                echo '<span style="background:#facc15; color:#14110f; border:2px solid #14110f; border-radius:4px; padding:2px 8px; font-weight:900; font-size:10.5px; box-shadow:1px 1px 0 #14110f;">جدید</span>';
            } else {
                echo '<span style="background:#f1f5f9; color:#64748b; border:2px solid #cbd5e1; border-radius:4px; padding:2px 8px; font-weight:700; font-size:10.5px;">خوانده شده</span>';
            }
            break;
    }
}, 10, 2);

/* استایل‌دهی به جدول پیشخوان برای هماهنگی با تم */
add_action('admin_head', function () {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'edit-raha_lead') return;
    ?>
    <style>
        .wp-list-table.posts th { font-weight: 900; color: #0c4a6e; background: #e0f2fe; padding: 12px 10px; border-bottom: 2px solid #14110f; }
        .wp-list-table.posts td { vertical-align: middle; padding-top: 14px; padding-bottom: 14px; }
        .wp-list-table.posts tr:hover { background: #f8fafc !important; }
        .column-lead_name { width: 20%; }
        .column-lead_contact { width: 15%; }
        .column-lead_status { width: 10%; text-align: center; }
        .column-date { width: 15%; }
    </style>
    <?php
});

/* تغییر وضعیت به "خوانده شده" وقتی روی پیام کلیک می‌کنید */
add_action('edit_post', function ($post_id) {
    if (get_post_type($post_id) === 'raha_lead') {
        update_post_meta($post_id, 'lead_status', 'read');
    }
});