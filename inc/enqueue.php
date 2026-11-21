<?php
/**
 * Raha Studio — Enqueue Assets
 *
 * Edited version — رفع تداخل blog.css با آرشیوهای CPT
 */
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {

    /* ---------- ۱. پایه: در همه صفحات ---------- */
    wp_enqueue_style('raha-base', RAHA_URI . '/assets/css/base.css', [], RAHA_VERSION);
    wp_enqueue_script('raha-front', RAHA_URI . '/assets/js/raha-front.js', [], RAHA_VERSION, true);

    wp_localize_script('raha-front', 'RahaThemeAnim', [
        'enabled'      => raha_opt('dark_mode_enable_anim', '1') === '1',
        'speed'        => (int) raha_opt('dark_mode_anim_speed', 800),
        'waveStrength' => (int) raha_opt('dark_mode_wave_strength', 50),
    ]);

    /* ---------- ۲. هدر ---------- */
    wp_enqueue_style('raha-header', RAHA_URI . '/assets/css/header.css', ['raha-base'], RAHA_VERSION);
    wp_enqueue_script('raha-header-js', RAHA_URI . '/assets/js/header.js', ['raha-front'], RAHA_VERSION, true);

    /* ---------- ۳. هیرو (فقط صفحه اصلی) ---------- */
    if (is_front_page()) {
        wp_enqueue_style('raha-hero', RAHA_URI . '/assets/css/hero.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-hero-js', RAHA_URI . '/assets/js/hero.js', [], RAHA_VERSION, true);
    }

    /* ---------- ۴. پورتفولیو (صفحه اصلی + آرشیو نمونه‌کار) ---------- */
    if (is_front_page() || is_post_type_archive('raha_portfolio')) {
        wp_enqueue_style('raha-portfolio', RAHA_URI . '/assets/css/portfolio.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-portfolio-js', RAHA_URI . '/assets/js/portfolio.js', [], RAHA_VERSION, true);
    }

    /* ---------- ۵. قالب‌های آماده (آرشیو) ---------- */
    // آرشیو قالب‌ها از .raha-portfolio__grid و .raha-portfolio-card استفاده می‌کنه
    // که هر دو در base.css تعریف شدن → کار می‌کنه، ولی برای هم‌شکل شدن با کارت‌های
    // صفحه‌ی اصلی، استایل پورتفولیو رو هم لود می‌کنیم
    if (is_post_type_archive('raha_template')) {
        wp_enqueue_style('raha-portfolio', RAHA_URI . '/assets/css/portfolio.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۶. صفحه تک‌قالب ---------- */
    if (is_singular('raha_template')) {
        wp_enqueue_style('raha-template-single', RAHA_URI . '/assets/css/template-single.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۷. پکیج‌ها (فقط صفحه اصلی) ---------- */
    if (is_front_page()) {
        wp_enqueue_style('raha-pricing', RAHA_URI . '/assets/css/pricing.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۸. فرم سفارش ---------- */
    if (is_page_template('page-templates/page-order-form.php')) {
        wp_enqueue_style('raha-order-form', RAHA_URI . '/assets/css/order-form.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-order-form', RAHA_URI . '/assets/js/raha-order-form.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-order-form', 'RahaOrderConfig', [
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce('raha_order_nonce'),
            'thankyou'   => function_exists('raha_page_url') ? raha_page_url('thank-you') : home_url('/thank-you/'),
            'isLoggedIn' => is_user_logged_in(),
        ]);
    }

    /* ---------- ۹. صفحه فرآیند ---------- */
    if (is_page_template('page-templates/page-process.php')) {
        wp_enqueue_style('raha-process', RAHA_URI . '/assets/css/process.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۱۰. پنل کاربر ---------- */
    if (is_page_template('page-templates/page-account.php')) {
        wp_enqueue_style('raha-account', RAHA_URI . '/assets/css/account.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-account', RAHA_URI . '/assets/js/raha-account.js', [], RAHA_VERSION, true);
    }

    /* ---------- ۱۱. صفحه ثبت‌نام ---------- */
    if (is_page_template('page-templates/page-register.php')) {
        wp_enqueue_style('raha-register', RAHA_URI . '/assets/css/register.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-register', RAHA_URI . '/assets/js/register.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-register', 'RahaRegisterConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('raha_register'),
            'action'  => 'raha_register',
        ]);
    }

    /* ---------- ۱۲. صفحه ورود ---------- */
    if (is_page_template('page-templates/page-login.php')) {
        wp_enqueue_style('raha-login', RAHA_URI . '/assets/css/login.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-login', RAHA_URI . '/assets/js/login.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-login', 'RahaLoginConfig', [
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('raha_login'),
            'action'   => 'raha_login',
            'redirect' => isset($_GET['redirect_to'])
                ? wp_validate_redirect(wp_unslash($_GET['redirect_to']), raha_page_url('account'))
                : raha_page_url('account'),
        ]);
    }

    /* ---------- ۱۳. صفحه بازیابی رمز ---------- */
    if (is_page_template('page-templates/page-forgot-password.php')) {
        wp_enqueue_style('raha-forgot', RAHA_URI . '/assets/css/forgot-password.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-forgot', RAHA_URI . '/assets/js/forgot-password.js', [], RAHA_VERSION, true);

        $redirect_param = isset($_GET['redirect_to'])
            ? wp_validate_redirect(wp_unslash($_GET['redirect_to']), '')
            : '';

        $login_url = raha_page_url('login');
        if ($redirect_param) {
            $login_url = add_query_arg('redirect_to', urlencode($redirect_param), $login_url);
        }

        wp_localize_script('raha-forgot', 'RahaForgotConfig', [
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'sendNonce'   => wp_create_nonce('raha_forgot_send'),
            'verifyNonce' => wp_create_nonce('raha_forgot_verify'),
            'resetNonce'  => wp_create_nonce('raha_forgot_reset'),
            'loginUrl'    => $login_url,
        ]);
    }

    /* ---------- ۱۴. صفحه تماس با من ---------- */
    if (is_page_template('page-templates/page-contact-me.php')) {
        wp_enqueue_style('raha-contact-me', RAHA_URI . '/assets/css/contact-me.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-contact-me', RAHA_URI . '/assets/js/contact-me.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-contact-me', 'RahaContactConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /* ---------- ۱۵. صفحه تشکر ---------- */
    if (is_page_template('page-templates/page-thankyou.php')) {
        wp_enqueue_style('raha-thankyou', RAHA_URI . '/assets/css/order-form.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۱۶. درباره من (فقط صفحه اصلی) ---------- */
    if (is_front_page()) {
        wp_enqueue_style('raha-about', RAHA_URI . '/assets/css/about.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۱۷. CTA (فقط صفحه اصلی) ---------- */
    if (is_front_page()) {
        wp_enqueue_style('raha-cta', RAHA_URI . '/assets/css/cta.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-cta-js', RAHA_URI . '/assets/js/cta.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-cta-js', 'RahaCtaConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('raha_cta_nonce'),
        ]);
    }

    /* ---------- ۱۸. سوالات متداول (فقط صفحه اصلی) ---------- */
    if (is_front_page()) {
        wp_enqueue_style('raha-faq', RAHA_URI . '/assets/css/faq.css', ['raha-base'], RAHA_VERSION);
    }

    /* ---------- ۱۹. فوتر (همه صفحات) ---------- */
    wp_enqueue_style('raha-footer', RAHA_URI . '/assets/css/footer.css', ['raha-base'], RAHA_VERSION);
    wp_enqueue_script('raha-footer-js', RAHA_URI . '/assets/js/footer.js', [], RAHA_VERSION, true);

    /* ---------- ۲۰. وبلاگ (فقط آرشیوهای بلاگ، نه CPT) ---------- */
    // 🔴 اصلاح مهم: is_archive() قبلاً همه‌ی آرشیوها رو می‌گرفت،
    // حالا فقط آرشیوهای بلاگ (category, tag, author, date) رو می‌گیره
    if (is_home() || (is_archive() && !is_post_type_archive())) {
        wp_enqueue_style('raha-blog', RAHA_URI . '/assets/css/blog.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-blog-js', RAHA_URI . '/assets/js/blog.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-blog-js', 'RahaBlogConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('raha_blog_nonce'),
        ]);
    }

    /* ---------- ۲۱. صفحه تک‌نوشته (وبلاگ) ---------- */
    if (is_singular('post')) {
        wp_enqueue_style('raha-single', RAHA_URI . '/assets/css/single.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-single-js', RAHA_URI . '/assets/js/single.js', [], RAHA_VERSION, true);
    }

    /* ---------- ۲۲. جست‌وجو ---------- */
    if (is_search()) {
        wp_enqueue_style('raha-search', RAHA_URI . '/assets/css/search.css', ['raha-base'], RAHA_VERSION);
        wp_enqueue_script('raha-search-js', RAHA_URI . '/assets/js/search.js', ['raha-front'], RAHA_VERSION, true);

        $search_posts = get_posts([
            'post_type'      => 'post',
            'posts_per_page' => 200,
            'post_status'    => 'publish',
        ]);

        $data_posts = [];
        foreach ($search_posts as $p) {
            $cats = get_the_category($p->ID);
            $cat_name = !empty($cats) ? $cats[0]->name : 'عمومی';

            $data_posts[] = [
                'id'    => $p->ID,
                'title' => $p->post_title,
                'desc'  => wp_strip_all_tags(get_the_excerpt($p->ID)),
                'tag'   => $cat_name,
                'date'  => get_the_date('', $p->ID),
                'ts'    => get_post_time('U', true, $p->ID),
                'icon'  => 'book',
                'url'   => get_permalink($p->ID),
                'thumb' => get_the_post_thumbnail_url($p->ID, 'medium'),
            ];
        }

        $categories = get_categories(['hide_empty' => false]);
        $data_cats  = ['all' => 'همه'];

        foreach ($categories as $cat) {
            $data_cats[$cat->name] = $cat->name;
        }

        wp_localize_script('raha-search-js', 'RahaSearchData', [
            'posts' => $data_posts,
            'cats'  => $data_cats,
        ]);
    }

    /* ---------- ۲۳. صفحه ۴۰۴ ---------- */
    if (is_404()) {
        wp_enqueue_style('raha-404', RAHA_URI . '/assets/css/404.css', ['raha-base'], RAHA_VERSION);
    }

}, 10);

/* ============================================================
   بارگذاری asset در پیشخوان
   ============================================================ */
add_action('admin_enqueue_scripts', function ($hook) {

    /* ---------- پنل سفارش‌ها ---------- */
    if (isset($_GET['page']) && $_GET['page'] === 'raha-orders-panel') {
        wp_enqueue_style('raha-admin-orders', RAHA_URI . '/assets/css/admin-orders.css', [], RAHA_VERSION);
        wp_enqueue_script('raha-admin-orders-js', RAHA_URI . '/assets/js/admin-orders.js', [], RAHA_VERSION, true);
        wp_localize_script('raha-admin-orders-js', 'RahaAdminConfig', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('raha_admin_order_nonce'),
        ]);
        return;
    }

    /* ---------- پنل مدیریت ارتباطات ---------- */
    if ($hook === 'toplevel_page_raha-communications') {
        wp_enqueue_style('raha-admin-comm', RAHA_URI . '/assets/css/admin-communications.css', [], RAHA_VERSION);
        wp_enqueue_script('raha-admin-comm', RAHA_URI . '/assets/js/admin-communications.js', [], RAHA_VERSION, true);
        return;
    }

    /* ---------- فرم‌های پکیج قیمت ---------- */
    $is_pricing_page = ($hook === 'post.php' || $hook === 'post-new.php')
        && (
            (isset($_GET['post_type']) && $_GET['post_type'] === 'raha_pricing')
            || (isset($_GET['post']) && get_post_type($_GET['post']) === 'raha_pricing')
        );

    $is_pricing_dash = isset($_GET['page']) && $_GET['page'] === 'raha-pricing-dashboard';

    if ($is_pricing_page || $is_pricing_dash) {
        wp_enqueue_style('raha-admin-pricing', RAHA_URI . '/assets/css/admin-pricing.css', [], RAHA_VERSION);
    }

    /* ---------- بارگذاری عمومی صفحات راها ---------- */
    $should_load = in_array($hook, ['post.php', 'post-new.php', 'nav-menus.php'], true)
        || (isset($_GET['page']) && strpos($_GET['page'], 'raha-') === 0);

    if (!$should_load) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('raha-admin', RAHA_URI . '/assets/js/raha-admin.js', ['jquery', 'jquery-ui-sortable'], RAHA_VERSION, true);
    wp_enqueue_style('raha-admin-css', RAHA_URI . '/assets/css/base.css', [], RAHA_VERSION);
});

/* ============================================================
   Preload فونت
   ============================================================ */
add_action('wp_head', function () {
    $font_path = '/assets/fonts/vazirmatn/Vazirmatn-Regular.woff2';
    if (!file_exists(RAHA_DIR . $font_path)) return;

    printf(
        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
        esc_url(RAHA_URI . $font_path)
    );
}, 5);