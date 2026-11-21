<?php
/**
 * Restrict wp-admin access for non-administrators.
 * Ensures that users who log in via the custom frontend forms
 * cannot access the WordPress dashboard.
 */
if (!defined('ABSPATH')) exit;

/**
 * 1. Redirect non-admin users trying to access wp-admin
 */
add_action('admin_init', function () {
    // Let AJAX requests through (for order-handler, cta-handler, etc.)
    if (wp_doing_ajax()) return;

    // Let cron and CLI tasks run
    if (defined('DOING_CRON') && DOING_CRON) return;
    if (defined('WP_CLI') && WP_CLI) return;

    // Not logged in? Let WordPress handle the auth redirect to wp-login
    if (!is_user_logged_in()) return;

    // Admins (and anyone with manage_options) can pass
    if (current_user_can('manage_options')) return;

    // Everyone else gets redirected to the account page
    $redirect_url = function_exists('raha_page_url') 
        ? raha_page_url('account') 
        : home_url('/');

    wp_safe_redirect($redirect_url);
    exit;
}, 999);

/**
 * 2. Hide the admin bar on the frontend for non-admin users
 */
add_filter('show_admin_bar', function ($show) {
    if (!is_user_logged_in()) return $show;
    if (!current_user_can('manage_options')) {
        return false;
    }
    return $show;
});

/**
 * 3. Redirect non-admin users after standard wp-login.php login
 */
add_filter('login_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    if (!is_wp_error($user) && !user_can($user, 'manage_options')) {
        return function_exists('raha_page_url') 
            ? raha_page_url('account') 
            : home_url('/');
    }
    return $redirect_to;
}, 10, 3);

/**
 * 4. Send users to login page after logout
 */
add_filter('logout_redirect', function ($redirect_to, $requested_redirect_to, $user) {
    return function_exists('raha_page_url') 
        ? raha_page_url('login') 
        : home_url('/');
}, 10, 3);