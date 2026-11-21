<?php
if (!defined('ABSPATH')) exit;

/* ---------- Helper: URL یک صفحه با slug ---------- */
function raha_page_url($slug) {
    static $cache = [];
    if (isset($cache[$slug])) return $cache[$slug];
    $page = get_page_by_path($slug);
    return $cache[$slug] = $page ? get_permalink($page) : home_url('/' . $slug . '/');
}

/* ---------- Helper: لیبل وضعیت ---------- */
function raha_status_label($status) {
    $map = [
        'new'      => 'جدید',
        'review'   => 'در حال بررسی',
        'progress' => 'در حال انجام',
        'done'     => 'تکمیل شده',
        'canceled' => 'لغو شده',
    ];
    return $map[$status] ?? 'نامشخص';
}

/* ---------- Helper: سفارش‌های کاربر ---------- */
function raha_get_user_orders($user_id = null, $limit = -1) {
    if ($user_id === null) $user_id = get_current_user_id();
    if (!$user_id) return [];
    return get_posts([
        'post_type'      => 'raha_order',
        'author'         => $user_id,
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'publish',
    ]);
}

/* ---------- Helper: شماره فارسی ---------- */
function raha_fa_num($n) {
    return strtr((string) $n, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
}

/* ---------- Helper: مبلغ فرمت‌شده ---------- */
function raha_money($n) {
    return raha_fa_num(number_format((int) $n));
}

/* ---------- محافظت: پنل کاربر نیاز به لاگین ---------- */
function raha_require_login() {
    if (!is_user_logged_in()) {
        $login_url = raha_page_url('login');
        wp_safe_redirect(add_query_arg('redirect_to', urlencode($_SERVER['REQUEST_URI'] ?? ''), $login_url));
        exit;
    }
}