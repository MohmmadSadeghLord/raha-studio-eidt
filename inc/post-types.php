<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    /* ---------- خدمات ---------- */
    register_post_type('raha_service', [
        'labels' => [
            'name'          => 'خدمات',
            'singular_name' => 'خدمت',
            'add_new'       => 'افزودن خدمت',
            'add_new_item'  => 'افزودن خدمت جدید',
            'edit_item'     => 'ویرایش خدمت',
            'all_items'     => 'همه خدمات',
            'menu_name'     => 'خدمات',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-screenoptions',
        'supports'     => ['title', 'page-attributes'],
        'has_archive'  => false,
    ]);

    /* ---------- نمونه‌کار ---------- */
    register_post_type('raha_portfolio', [
        'labels' => [
            'name'          => 'نمونه‌کارها',
            'singular_name' => 'نمونه‌کار',
            'add_new_item'  => 'افزودن نمونه‌کار جدید',
            'edit_item'     => 'ویرایش نمونه‌کار',
            'all_items'     => 'همه نمونه‌کارها',
            'menu_name'     => 'نمونه‌کارها',
        ],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-portfolio',
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'portfolio'],
        'show_in_rest' => false,
    ]);

    /* ---------- پکیج قیمت ---------- */
    register_post_type('raha_pricing', [
        'labels' => [
            'name'          => 'پکیج‌های قیمت',
            'singular_name' => 'پکیج',
            'add_new_item'  => 'افزودن پکیج جدید',
            'edit_item'     => 'ویرایش پکیج',
            'all_items'     => 'همه پکیج‌ها',
            'menu_name'     => 'پکیج‌های قیمت',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-tag',
        'supports'     => ['title', 'page-attributes'],
        'has_archive'  => false,
    ]);

    /* ---------- سوال متداول ---------- */
    register_post_type('raha_faq', [
        'labels' => [
            'name'          => 'سوالات متداول',
            'singular_name' => 'سوال',
            'add_new_item'  => 'افزودن سوال جدید',
            'edit_item'     => 'ویرایش سوال',
            'all_items'     => 'همه سوالات',
            'menu_name'     => 'سوالات متداول',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-editor-help',
        'supports'     => ['title', 'page-attributes'],
        'has_archive'  => false,
    ]);

    /* ---------- قالب آماده ---------- */
    register_post_type('raha_template', [
        'labels' => [
            'name'          => 'قالب‌های آماده',
            'singular_name' => 'قالب',
            'add_new_item'  => 'افزودن قالب جدید',
            'edit_item'     => 'ویرایش قالب',
            'all_items'     => 'همه قالب‌ها',
            'menu_name'     => 'قالب‌های آماده',
        ],
        'public'       => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-layout',
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'templates'],
        'show_in_rest' => false,
    ]);

    /* ---------- سفارش (فاز ۳) ---------- */
    register_post_type('raha_order', [
        'labels' => [
            'name'          => 'سفارش‌ها',
            'singular_name' => 'سفارش',
            'all_items'     => 'همه سفارش‌ها',
            'menu_name'     => 'سفارش‌ها',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => false, // ← از منوی اصلی مخفی شد (نمایش در پنل یکپارچه)
        'menu_icon'       => 'dashicons-clipboard',
        'supports'        => ['title'],
        'capability_type' => 'post',
        'capabilities'    => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'    => true,
    ]);

    /* ---------- تیکت (فاز ۳) ---------- */
    register_post_type('raha_ticket', [
        'labels' => [
            'name'          => 'تیکت‌ها',
            'singular_name' => 'تیکت',
            'all_items'     => 'همه تیکت‌ها',
            'menu_name'     => 'تیکت‌ها',
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-format-chat',
        'supports'     => ['title', 'author', 'comments'],
    ]);

    /* ---------- پیام‌های تماس (CTA) ---------- */
    register_post_type('raha_lead', [
        'labels' => [
            'name'          => 'پیام‌های تماس',
            'singular_name' => 'پیام',
            'all_items'     => 'همه پیام‌ها',
            'menu_name'     => 'پیام‌های تماس',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => false, // ← از منوی اصلی مخفی شد (نمایش در پنل یکپارچه)
        'menu_icon'       => 'dashicons-email-alt',
        'supports'        => ['title'],
        'capability_type' => 'post',
        'capabilities'    => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'    => true,
    ]);

    /* ---------- وقت مشاوره (جدید) ---------- */
    register_post_type('raha_consultation', [
        'labels' => [
            'name'          => 'وقت مشاوره',
            'singular_name' => 'مشاوره',
            'all_items'     => 'همه مشاوره‌ها',
            'menu_name'     => 'وقت مشاوره',
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => false, // ← از منوی اصلی مخفی شد (نمایش در پنل یکپارچه)
        'menu_icon'       => 'dashicons-calendar-alt',
        'supports'        => ['title'],
        'capability_type' => 'post',
        'capabilities'    => ['create_posts' => 'do_not_allow'],
        'map_meta_cap'    => true,
    ]);

});