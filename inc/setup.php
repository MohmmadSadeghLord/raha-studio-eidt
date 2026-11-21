<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');

    load_theme_textdomain('raha-studio', RAHA_DIR . '/languages');

    register_nav_menus([
        'primary' => 'منوی اصلی',
        'footer'  => 'منوی پایین (حقوقی)',
    ]);
});

// Widget areas — هر کدام خالی → ستونش در فرانت حذف می‌شود
add_action('widgets_init', function () {
    $areas = [
        'footer-col-1' => 'فوتر — ستون ۱',
        'footer-col-2' => 'فوتر — ستون ۲',
        'footer-col-3' => 'فوتر — ستون ۳',
        'footer-newsletter' => 'فوتر — خبرنامه',
        'footer-contact'    => 'فوتر — نوار تماس',
    ];
    foreach ($areas as $id => $name) {
        register_sidebar([
            'id'            => $id,
            'name'          => $name,
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ]);
    }
});

// بدنه: کلاس‌های موردنیاز
add_filter('body_class', function ($classes) {
    $classes[] = 'raha-body';

    if (is_page_template('page-templates/page-register.php')) {
        $classes[] = 'raha-register-body';
    }
    if (is_page_template('page-templates/page-login.php')) {
        $classes[] = 'raha-login-body';
    }
    if (is_page_template('page-templates/page-forgot-password.php')) {
        $classes[] = 'raha-forgot-body';
    }
    return $classes;
});



/* ============================================================
   ایجاد داده‌های اولیه (فقط بار اول)
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_pricing_data');
function raha_seed_pricing_data() {
    // بررسی اینکه آیا قبلاً اجرا شده است یا نه
    if (get_option('raha_pricing_seeded')) return;

    // ۱. ایجاد ۳ پکیج اصلی (CPT)
    $plans = [
        [
            'title' => 'لندینگ پیج',
            'desc' => 'برای کمپین تبلیغاتی و معرفی یک محصول',
            'price' => '۸',
            'unit' => 'میلیون تومان',
            'popular' => 0,
            'features' => [
                ['label' => 'طراحی تک‌صفحه‌ای اختصاصی', 'included' => 1],
                ['label' => 'ریسپانسیو کامل', 'included' => 1],
                ['label' => 'فرم تماس و دریافت اطلاعات', 'included' => 1],
                ['label' => 'سئوی پایه و بهینه‌سازی سرعت', 'included' => 1],
                ['label' => 'پنل مدیریت محتوا', 'included' => 0],
            ],
        ],
        [
            'title' => 'سایت شرکتی',
            'desc' => 'برای برندها و کسب‌وکارهای در حال رشد',
            'price' => '۲۵',
            'unit' => 'میلیون تومان',
            'popular' => 1,
            'features' => [
                ['label' => 'طراحی اختصاصی تا ۷ صفحه', 'included' => 1],
                ['label' => 'ریسپانسیو و بهینه‌سازی کامل', 'included' => 1],
                ['label' => 'پنل مدیریت محتوا (وردپرس)', 'included' => 1],
                ['label' => 'بخش وبلاگ و مقالات', 'included' => 1],
                ['label' => 'فرم‌های تماس و درخواست', 'included' => 1],
                ['label' => '۳ ماه پشتیبانی رایگان', 'included' => 1],
            ],
        ],
        [
            'title' => 'فروشگاه آنلاین',
            'desc' => 'فروش آنلاین با سیستم ووکامرس',
            'price' => '۴۵',
            'unit' => 'میلیون تومان',
            'popular' => 0,
            'features' => [
                ['label' => 'تمام امکانات پکیج شرکتی', 'included' => 1],
                ['label' => 'سیستم فروشگاهی ووکامرس', 'included' => 1],
                ['label' => 'درگاه پرداخت و سبد خرید', 'included' => 1],
                ['label' => 'مدیریت محصولات و موجودی', 'included' => 1],
                ['label' => 'آموزش کامل پنل مدیریت', 'included' => 1],
                ['label' => '۶ ماه پشتیبانی رایگان', 'included' => 1],
            ],
        ],
    ];

    foreach ($plans as $p) {
        $post_id = wp_insert_post([
            'post_type' => 'raha_pricing',
            'post_title' => $p['title'],
            'post_status' => 'publish',
        ]);
        if ($post_id) {
            update_post_meta($post_id, 'desc', $p['desc']);
            update_post_meta($post_id, 'price_value', $p['price']);
            update_post_meta($post_id, 'price_unit', $p['unit']);
            update_post_meta($post_id, 'popular', $p['popular']);
            update_post_meta($post_id, 'features', $p['features']);
        }
    }

    // ۲. ذخیره خدمات ماهانه در تنظیمات
    $monthly_services = [
        [
            'title' => 'پشتیبانی و نگهداری',
            'desc' => 'به‌روزرسانی امنیتی، بکاپ منظم، مانیتورینگ آپتایم و گزارش ماهانه',
            'price' => '۲ <span>میلیون تومان</span>',
            'highlight' => 0,
            'icon' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        ],
        [
            'title' => 'سئو و رشد',
            'desc' => 'سئوی تکنیکال، بهینه‌سازی محتوا، لینک‌سازی داخلی و گزارش رتبه ماهانه',
            'price' => '۵ <span>میلیون تومان</span>',
            'highlight' => 1,
            'icon' => '<polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/>',
        ],
    ];
    update_option('raha_pricing_monthly_services', $monthly_services);

    // ۳. ذخیره باکس سفارشی
    update_option('raha_pricing_custom_title', 'نیاز خاصی دارید؟');
    update_option('raha_pricing_custom_desc', 'اگر پکیج اختصاصی، برندینگ، چندزبانه یا هر امکان دیگری می‌خواهید، یک جلسه کوتاه بگیرید تا دقیقاً متناسب با نیاز شما برآورد کنیم.');
    update_option('raha_pricing_custom_btn_text', 'گفتگو درباره پروژه');
    update_option('raha_pricing_custom_btn_url', '#contact');

    // ثبت اینکه داده‌ها ایجاد شدند
    update_option('raha_pricing_seeded', 1);
}

/* ============================================================
   ایجاد داده‌های اولیه برای بخش درباره من (فقط بار اول)
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_about_data');
function raha_seed_about_data() {
    // بررسی اینکه آیا قبلاً اجرا شده است یا نه
    if (get_option('raha_about_seeded')) return;

    // ۱. متون پایه
    update_option('raha_about_eyebrow', 'درباره من');
    update_option('raha_about_name', 'محمد صادق محمودی لرد');
    update_option('raha_about_slogan', 'تعهد شعار من است.');

    // ۲. ستون ۱ - خدمات
    update_option('raha_about_col_1_title', 'خدمات');
    update_option('raha_about_col_1_items', [
        ['text' => 'طراحی سایت شرکتی'],
        ['text' => 'فروشگاهی'],
        ['text' => 'لندینگ محصول'],
        ['text' => 'بازطراحی سایت قدیمی'],
    ]);

    // ۳. ستون ۲ - ابزارها
    update_option('raha_about_col_2_title', 'ابزارها');
    update_option('raha_about_col_2_items', [
        ['text' => 'Figma'],
        ['text' => 'HTML / CSS / JS'],
        ['text' => 'WordPress'],
        ['text' => 'Elementor'],
    ]);

    // ۴. ستون ۳ - سبک کار
    update_option('raha_about_col_3_title', 'سبک کار');
    update_option('raha_about_col_3_items', [
        ['text' => 'مینیمال'],
        ['text' => 'جسور'],
        ['text' => 'بروتالیست'],
        ['text' => 'عملکردمحور'],
        ['text' => 'موبایل‌فرست'],
        ['text' => 'کاربرمحور'],
    ]);

    // ۵. دکمه CTA
    update_option('raha_about_cta_text', 'تماس با من');
    update_option('raha_about_cta_url', '#contact');

    // ۶. تصویر پرتره (دانلود و آپلود به کتابخانه رسانه)
    $portrait_id = raha_sideload_about_portrait();
    if ($portrait_id) {
        update_option('raha_about_portrait', $portrait_id);
    }

    // ثبت اینکه داده‌ها ایجاد شدند
    update_option('raha_about_seeded', 1);
}

/* تابع کمکی برای دانلود تصویر پرتره از پروتوتایپ */
function raha_sideload_about_portrait() {
    $image_url = 'https://scitoday.ir/wp-content/uploads/2026/10/sCSapp5MNtZvXKw27k2Jrw_L8SQ-xiwrQEFZS4eCtEkHuaf_hg.webp';
    
    // لود کردن پیش‌نیازهای وردپرس برای آپلود
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // بررسی اینکه آیا قبلاً دانلود شده
    $existing = get_posts([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 1,
        'meta_query'     => [
            ['key' => '_raha_about_portrait', 'value' => '1']
        ]
    ]);
    if (!empty($existing)) return $existing[0]->ID;

    // دانلود فایل
    $tmp = download_url($image_url, 30);
    if (is_wp_error($tmp)) return 0;

    $file_array = [
        'name'     => 'about-portrait.webp',
        'tmp_name' => $tmp,
    ];

    $id = media_handle_sideload($file_array, 0);
    if (is_wp_error($id)) {
        @unlink($tmp);
        return 0;
    }

    // علامت‌گذاری که این تصویر مربوط به بخش درباره من است
    update_post_meta($id, '_raha_about_portrait', '1');
    return $id;
}




/* ============================================================
   ایجاد داده‌های اولیه برای بخش CTA (فقط بار اول)
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_cta_data');
function raha_seed_cta_data() {
    if (get_option('raha_cta_seeded')) return;

    // متن‌ها
    update_option('raha_cta_title', 'تماس با من');
    update_option('raha_cta_desc', 'شماره‌تان را بگذارید؛ در کمتر از ۲۴ ساعت خودم تماس می‌گیرم');
    update_option('raha_cta_trust_1', 'راهنمایی کامل');
    update_option('raha_cta_trust_2', 'ارتباط در کمتر از ۲۴ ساعت');
    update_option('raha_cta_trust_3', 'پیشنهادات مناسب');
    update_option('raha_cta_form_label', 'شماره تماس یا ایمیل');
    update_option('raha_cta_form_placeholder', '۰۹۱۲۳۴۵۶۷۸۹ یا you@email.com');
    update_option('raha_cta_btn_text', 'ثبت جهت تماس رایگان');
    update_option('raha_cta_success_title', 'ثبت شد!');
    update_option('raha_cta_success_desc', 'در کمتر از ۲۴ ساعت با شما تماس می‌گیرم.');

    // کانال‌ها
    $channels = [
        [
            'name' => 'تلگرام',
            'url' => 'https://t.me/mohmmdsadeghh',
            'icon_bg' => '#cffafe',
            'icon_color' => '#229ed9',
            'icon' => '<path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>',
        ],
        [
            'name' => 'ایمیل',
            'url' => 'lordlord905@gmail.com',
            'icon_bg' => '#e0f2fe',
            'icon_color' => '#0c4a6e',
            'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        ],
        [
            'name' => 'بله',
            'url' => 'https://beta.bale.ai/@sbale_fk',
            'icon_bg' => '#d1fae5',
            'icon_color' => '#10b981',
            'icon' => '<path d="M12 3C6.48 3 2 6.7 2 11.25c0 2.72 1.57 5.1 3.95 6.6L5 21l4.3-2.05c.86.2 1.77.3 2.7.3 5.52 0 10-3.7 10-8.25S17.52 3 12 3z"/>',
        ],
        [
            'name' => 'ایتا',
            'url' => 'https://beta.bale.ai/@sbale_fk', // لینک ایتا در فایل شما هم برابر با بله بود، اینجا همان را گذاشتم
            'icon_bg' => '#fef3c7',
            'icon_color' => '#f59e0b',
            'icon' => '<path fill-rule="evenodd" d="M20.5 2.5h-17A1.5 1.5 0 0 0 2 4v13a1.5 1.5 0 0 0 1.5 1.5h3.25V22l4.4-3.5H20.5A1.5 1.5 0 0 0 22 17V4a1.5 1.5 0 0 0-1.5-1.5zM7 12a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z"/>',
        ],
    ];
    update_option('raha_cta_channels', $channels);

    // ثبت اینکه داده‌ها ایجاد شدند
    update_option('raha_cta_seeded', 1);
}




/* ============================================================
   ایجاد داده‌های اولیه برای سوالات متداول هوم پیج (فقط بار اول)
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_home_faq_data');
function raha_seed_home_faq_data() {
    if (get_option('raha_home_faq_seeded')) return;

    // متن‌های هدر
    update_option('raha_faq_eyebrow', 'سوالات متداول');
    update_option('raha_faq_title', 'پرسش‌هایی که زیاد می‌شنوم');
    update_option('raha_faq_lead', 'اگر پاسخ سوالت را اینجا پیدا نکردی، از دکمه‌ی «تماس با من» استفاده کن.');

    // لیست سوالات پروتوتایپ
    $faqs = [
        [
            'question' => 'چقدر طول می‌کشد تا سایت آماده شود؟',
            'answer' => 'بستگی به نوع پروژه دارد. یک لندینگ ساده معمولاً ۳ تا ۵ روز کاری، یک سایت شرکتی ۲ تا ۳ هفته و یک فروشگاه کامل ۴ تا ۶ هفته زمان می‌برد. زمان‌بندی دقیق پیش از شروع هماهنگ می‌شود.'
        ],
        [
            'question' => 'هزینه طراحی سایت چگونه محاسبه می‌شود؟',
            'answer' => 'بر اساس دامنه‌ی کار، تعداد صفحات، امکانات اختصاصی و زمان تحویل. پیش از شروع یک برآورد شفاف دریافت می‌کنید و هیچ هزینه‌ی پنهانی اضافه نمی‌شود.'
        ],
        [
            'question' => 'آیا بعد از تحویل هم پشتیبانی می‌کنید؟',
            'answer' => 'بله. هر پروژه ۳ ماه پشتیبانی رایگان دارد و پس از آن می‌توانید بسته‌ی پشتیبانی ماهانه یا سالانه را انتخاب کنید.'
        ],
        [
            'question' => 'سایت را می‌توانم خودم ویرایش کنم؟',
            'answer' => 'بله. پنل مدیریت فارسی و ساده طراحی می‌شود و در جلسه‌ی تحویل، آموزش کامل ویرایش محتوا را دریافت می‌کنید.'
        ],
        [
            'question' => 'آیا سایت روی موبایل هم درست نمایش داده می‌شود؟',
            'answer' => 'بله. طراحی موبایل‌فرست است و در تمام اندازه‌ها — از گوشی کوچک تا نمایشگر عریض — بررسی و بهینه می‌شود. سرعت بارگذاری روی موبایل هم جداگانه سنجیده می‌شود.'
        ],
        [
            'question' => 'فرآیند کار از کجا شروع می‌شود؟',
            'answer' => 'اول یک جلسه‌ی کوتاه برای شناخت هدف پروژه؛ بعد پیشنهاد ساختار و برآورد شفاف؛ پس از تأیید، طراحی رابط کاربری، پیاده‌سازی و در نهایت تحویل و آموزش شروع می‌شود.'
        ],
    ];

    update_option('raha_faq_items', $faqs);
    update_option('raha_home_faq_seeded', 1);
}


/* ============================================================
   ایجاد داده‌های اولیه برای فوتر (فقط بار اول)
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_footer_data');
function raha_seed_footer_data() {
    if (get_option('raha_footer_seeded')) return;

    update_option('raha_footer_news_title', 'هر جمعه، یک ایده‌ی خام طراحی در اینباکس شما.');
    update_option('raha_footer_news_sub', 'بدون اسپم، بدون تبلیغ. فقط چیزهایی که واقعاً کار می‌کنند.');
    update_option('raha_footer_brand_desc', 'استودیوی طراحی و توسعه با خط‌های ضخیم، تصمیم‌های شجاعانه و کدِ تمیز — برای برندهایی که نمی‌خواهند دیده نشوند.');
    
    // سوشال‌ها
    update_option('raha_footer_socials', [
        ['label' => 'ایکس', 'url' => '#', 'icon' => '<path d="M17.6 3h3.1l-6.8 7.8L22 21h-6.3l-4.9-6.4L5.2 21H2.1l7.3-8.3L2 3h6.4l4.4 5.9L17.6 3z"/>'],
        ['label' => 'اینستاگرام', 'url' => '#', 'icon' => '<rect x="3" y="3" width="18" height="18" rx="5.5" fill="none" stroke="currentColor" stroke-width="2.2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2.2"/><circle cx="17.3" cy="6.7" r="0.9" fill="currentColor"/>'],
        ['label' => 'لینکدین', 'url' => '#', 'icon' => '<rect x="2.5" y="9" width="3.9" height="12.5" rx="0.6"/><circle cx="4.5" cy="4.6" r="2.3"/><path d="M9.2 9h3.7v1.8c.6-1.1 1.9-2.1 4-2.1 3 0 4.4 1.9 4.4 5.5v7.3h-3.9v-6.8c0-1.7-.6-2.8-2-2.8-1.2 0-1.9.8-2.2 1.6-.1.3-.1.7-.1 1.1v6.9H9.2V9z"/>'],
        ['label' => 'گیت‌هاب', 'url' => '#', 'icon' => '<path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.16-1.11-1.47-1.11-1.47-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.89 1.52 2.34 1.08 2.91.83.09-.65.35-1.09.63-1.34-2.22-.25-4.56-1.11-4.56-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02a9.5 9.5 0 0 1 5 0c1.91-1.29 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.69-4.57 4.94.36.31.68.92.68 1.85v2.74c0 .27.18.58.69.48A10 10 0 0 0 12 2z"/>'],
    ]);

    // ستون‌ها
    update_option('raha_footer_columns', [
        [
            'title' => 'محصولات',
            'links' => "پلتفرم طراحی | #\nکیت رابط کاربری | #\nسیستم دیزاین | #\nافزونه‌ها | #\nتغییرات نسخه | #",
        ],
        [
            'title' => 'شرکت',
            'links' => "درباره ما | #\nتیم | #\nفرصت‌های شغلی | #\nمطبوعات | #\nتماس با ما | #",
        ],
        [
            'title' => 'منابع',
            'links' => "وبلاگ | #\nمستندات | #\nراهنمای شروع | #\nانجمن | #\nپرسش‌های متداول | #",
        ],
    ]);

    update_option('raha_footer_email', 'hi@neobrutal.studio');
    update_option('raha_footer_phone', '+98 21 0000 0000');
    update_option('raha_footer_address', 'تهران، ولیعصر، پلاک ۱۲');
    update_option('raha_footer_status', 'آماده‌ی پروژه');
    update_option('raha_footer_copyright', '© ۲۰۲۵ نئوبروتال استودیو');
    update_option('raha_footer_legal', "حریم خصوصی | #\nقوانین | #\nکوکی‌ها | #");

    update_option('raha_footer_seeded', 1);
}



/* ============================================================
   ایجاد داده‌های اولیه برای صفحه فرآیند همکاری
   ============================================================ */
add_action('after_setup_theme', 'raha_seed_process_data');
function raha_seed_process_data() {
    if (get_option('raha_process_seeded')) return;

    // ۱. چک‌باکس‌های نمایش (همه روشن)
    update_option('raha_process_show_intro', '1');
    update_option('raha_process_show_philosophy', '1');
    update_option('raha_process_show_process', '1');
    update_option('raha_process_show_gantt', '1');
    update_option('raha_process_show_who', '1');
    update_option('raha_process_show_req', '1');
    update_option('raha_process_show_comm', '1');
    update_option('raha_process_show_deliv', '1');
    update_option('raha_process_show_revision', '1');
    update_option('raha_process_show_payment', '1');
    update_option('raha_process_show_guarantee', '1');
    update_option('raha_process_show_scope', '1');
    update_option('raha_process_show_risks', '1');
    update_option('raha_process_show_support', '1');
    update_option('raha_process_show_compare', '1');
    update_option('raha_process_show_faq', '1');
    update_option('raha_process_show_steps', '1');
    update_option('raha_process_show_cta', '1');

    // ۲. متن‌های اصلی (هیرو)
    update_option('raha_process_eyebrow', 'فرآیند همکاری');
    update_option('raha_process_intro_title', 'مسیر تبدیل فکرهای شما به یک وب‌سایت واقعی و سودآور.');
    update_option('raha_process_intro_lead', 'شفافیت، زمان‌بندی دقیق و ارتباط مداوم — سه اصل اصلی من در تمام پروژه‌هاست. در این صفحه دقیقاً می‌بینید که از روز اول تا تحویل، چه اتفاقی می‌افتد، چه چیزی از شما می‌خواهم، و در پایان چه چیزی تحویل می‌گیرید. هیچ مرحله‌ای بدون تایید شما رد نمی‌شود.');
    
    update_option('raha_process_btn_1_text', 'رزرو جلسه مشاوره رایگان');
    update_option('raha_process_btn_1_url', '#cta');
    update_option('raha_process_btn_2_text', 'مشاهده ۸ گام');
    update_option('raha_process_btn_2_url', '#process');

    update_option('raha_process_sticker', '100% شفاف');
    update_option('raha_process_panel_title', 'Project_Overview.exe');

    update_option('raha_process_stat_1_num', '۸');
    update_option('raha_process_stat_1_label', 'گام شفاف و مشخص');
    update_option('raha_process_stat_2_num', '۴–۶');
    update_option('raha_process_stat_2_label', 'میانگین زمان تحویل (هفته)');
    update_option('raha_process_stat_3_num', '۳۰ الی ۴۵');
    update_option('raha_process_stat_3_label', 'روز پشتیبانی رایگان');

    // ۳. فلسفه
    update_option('raha_process_philosophy_title', 'سه اصلی که هر پروژه روی آن‌ها بنا می‌شود.');
    update_option('raha_process_philosophy_items', [
        ['icon' => '◐', 'title' => 'شفافیت کامل', 'desc' => 'هر گام، خروجی مشخص، زمان‌بندی برآوردشده و مالک واضح دارد. هیچ بند پنهانی در قرارداد نیست.'],
        ['icon' => '◔', 'title' => 'زمان‌بندی دقیق', 'desc' => 'تاریخ تحویل هر فاز از قبل مشخص می‌شود. اگر تاخیری رخ دهد، اول از همه به شما اطلاع می‌دهم.'],
        ['icon' => '◉', 'title' => 'ارتباط مداوم', 'desc' => 'هر هفته یک گزارش پیشرفت می‌گیرید. سوال‌های شما در همان روز کاری پاسخ داده می‌شود.'],
    ]);

    // ۴. تایم‌لاین (۸ گام)
    update_option('raha_process_timeline_lead', 'هر گام، خروجی مشخص، زمان‌بندی برآوردشده و توضیحات واضح دارد. هیچ مرحله‌ای بدون تایید شما رد نمی‌شود.');
    update_option('raha_process_timeline_items', [
        ['phase' => 'بررسی اولیه', 'duration' => '۲–۳ روز', 'title' => 'کشف و نیازسنجی', 'desc' => 'تکمیل فرم اولیه، جلسه‌ی آگاهی از اهداف کسب‌وکار، بررسی مخاطبان هدف و تحلیل رقبا.', 'output' => 'فرم بریف تکمیل‌شده + سند اهداف و مخاطب', 'phase_num' => '1'],
        ['phase' => 'بررسی اولیه', 'duration' => '۲–۳ روز', 'title' => 'پیشنهاد و عقد قرارداد', 'desc' => 'شفاف‌سازی محدوده‌ی پروژه (Scope)، زمان‌بندی تحویل، برآورد هزینه و پرداخت پیش‌پرداخت.', 'output' => 'پیشنهاد رسمی + قرارداد امضاشده', 'phase_num' => '1'],
        ['phase' => 'طراحی', 'duration' => '۳–۵ روز', 'title' => 'معماری اطلاعات و وایرفریم', 'desc' => 'مشخص کردن نقشه‌ی سایت (Sitemap)، چیدمان بخش‌ها و مسیر حرکت کاربر — قبل از اعمال رنگ و گرافیک.', 'output' => 'نقشه‌ی سایت + وایرفریم دسکتاپ و موبایل', 'phase_num' => '2'],
        ['phase' => 'طراحی', 'duration' => '۱–۲ هفته', 'title' => 'طراحی بصری و رابط کاربری', 'desc' => 'طراحی ظاهر اصلی، انتخاب پالت رنگی، فونت‌ها و ایجاد طرح نهایی برای دسکتاپ و موبایل.', 'output' => 'فایل طراحی نهایی در Figma + راهنمای برند', 'phase_num' => '2'],
        ['phase' => 'ساخت', 'duration' => '۱–۳ هفته', 'title' => 'توسعه و کدنویسی', 'desc' => 'تبدیل طرح گرافیکی به کدهای زنده، بهینه‌سازی سرعت، ریسپانسیو کردن و اتصال امکانات مورد نیاز.', 'output' => 'سایت کدنویسی‌شده روی سرور تست', 'phase_num' => '3'],
        ['phase' => 'ساخت', 'duration' => '۲–۴ روز', 'title' => 'تست و رفع خطا', 'desc' => 'بررسی عملکرد سایت در مرورگرهای مختلف، تست فرم‌ها، بررسی امنیت و سرعت بارگذاری.', 'output' => 'چک‌لیست QA تکمیل‌شده + گزارش تست', 'phase_num' => '3'],
        ['phase' => 'راه‌اندازی', 'duration' => '۱–۲ روز', 'title' => 'تحویل نهایی و راه‌اندازی', 'desc' => 'انتقال سایت به هاست اصلی، تحویل کامل دسترسی‌ها و ارائه‌ی ویدیوهای آموزشی کار با پنل.', 'output' => 'سایت زنده + دسترسی‌ها + ویدیوهای آموزش', 'phase_num' => '4'],
        ['phase' => 'راه‌اندازی', 'duration' => '۳۰–۴۵ روز', 'title' => 'پشتیبانی و نگهداری', 'desc' => 'شروع دوره‌ی پشتیبانی رایگان برای رفع هرگونه اشکال احتمالی و اطمینان از عملکرد صحیح سایت.', 'output' => '۳۰ الی ۴۵ روز پشتیبانی رایگان + کانال ارتباطی اختصاصی', 'phase_num' => '4'],
    ]);

    // ۵. گانت
    update_option('raha_process_gantt_items', [
        ['label' => 'بررسی اولیه', 'width' => '8%', 'time_text' => '۲–۵ روز', 'color' => 'var(--p1)'],
        ['label' => 'طراحی', 'width' => '32%', 'time_text' => '۱.۵–۳ هفته', 'color' => 'var(--p2)'],
        ['label' => 'ساخت', 'width' => '48%', 'time_text' => '۱.۵–۴ هفته', 'color' => 'var(--p3)'],
        ['label' => 'راه‌اندازی', 'width' => '12%', 'time_text' => '۱ هفته', 'color' => 'var(--p4)'],
    ]);

    // ۶. تعهدات
    update_option('raha_process_who_me', [
        ['text' => 'پایبندی کامل به زمان‌بندی قرارداد.'],
        ['text' => 'کدنویسی تمیز، استاندارد و ایمن.'],
        ['text' => 'گزارش‌دهی منظم از پیشرفت کار.'],
    ]);
    update_option('raha_process_who_you', [
        ['text' => 'ارائه‌ی به‌موقع متون، لوگو و تصاویر اولیه.'],
        ['text' => 'ارائه‌ی بازخوردهای مشخص در زمان تعیین‌شده.'],
        ['text' => 'پرداخت اقساط طبق توافق.'],
    ]);

    // ۷. پیش‌نیازها
    update_option('raha_process_req_items', [
        ['title' => 'متون و محتوای اولیه', 'desc' => 'متن صفحات اصلی. اگر آماده نیست، با پیش‌نویس جلو می‌رویم.'],
        ['title' => 'لوگو و منابع برند', 'desc' => 'فایل لوگو (SVG یا PNG با کیفیت) و پالت رنگی برند.'],
        ['title' => 'دسترسی دامنه و هاست', 'desc' => 'دسترسی به پنل دامنه و هاست برای اتصال نهایی.'],
        ['title' => 'نمونه‌های مورد علاقه', 'desc' => '۲–۳ سایت محبوب و ۲–۳ سایت نامطلوب برای فهم سلیقه.'],
        ['title' => 'تصمیم‌گیرنده‌ی واحد', 'desc' => 'یک نفر که بازخورد نهایی را می‌دهد.'],
        ['title' => 'بازخورد به‌موقع', 'desc' => 'در هر فاز حداکثر ۳ روز کاری برای بازخورد.'],
    ]);

    // ۸. ارتباطات
    update_option('raha_process_comm_title', 'همواره از مراحل پروژه در پنل خود مطلع هستید.');
    update_option('raha_process_comm_items', [
        ['icon' => '◉', 'title' => 'جلسات', 'desc' => 'Google Meet یا Skype — برای جلسات شروع، تایید طرح و تحویل.'],
        ['icon' => '☰', 'title' => 'پنل اختصاصی شما', 'desc' => 'پنل اختصاصی — همه‌ی تسک‌ها، وضعیت و فایل‌ها یک‌جا.'],
        ['icon' => '✉', 'title' => 'ارتباط روزانه', 'desc' => 'پیام‌رسان‌ها یا ایمیل — پاسخ در همان روز کاری.'],
        ['icon' => '↗', 'title' => 'پیش‌نمایش کار', 'desc' => 'لینک تست مستقیم روی سرور اختصاصی — هر زمان که بخواهید.'],
    ]);

    // ۹. تحویل‌دادنی‌ها
    update_option('raha_process_deliv_items', [
        ['text' => 'فایل طراحی در Figma (قابل ویرایش)'],
        ['text' => 'سایت کدنویسی‌شده و ریسپانسیو'],
        ['text' => 'دسترسی کامل هاست و دامنه'],
        ['text' => 'ویدیوهای آموزش کار با پنل'],
        ['text' => 'راهنمای برند (رنگ، فونت، آیکون)'],
        ['text' => 'گزارش تست QA در مرورگرها'],
        ['text' => 'چک‌لیست سئوی پایه (On-page)'],
        ['text' => '۳۰ الی ۴۵ روز پشتیبانی رایگان'],
    ]);

    // ۱۰. بازخورد
    update_option('raha_process_rev_title', 'قوانین بازخورد و اصلاحات');
    update_option('raha_process_rev_desc', 'در هر فاز (وایرفریم، طراحی بصری، توسعه) دو دور ویرایش رایگان گنجانده شده است. تغییرات بیشتر با توافق و هزینه‌ی جداگانه انجام می‌شود.');

    // ۱۱. پرداخت
    update_option('raha_process_payment_items', [
        ['percent' => '۵۰٪', 'label' => 'پیش‌پرداخت', 'note' => 'هنگام عقد قرارداد'],
        ['percent' => '۲۵٪', 'label' => 'پس از تایید طرح', 'note' => 'پس از تایید طراحی اولیه'],
        ['percent' => '۲۵٪', 'label' => 'تحویل نهایی', 'note' => 'پیش از انتقال به دامنه'],
    ]);

    // ۱۲. ضمانت‌ها
    update_option('raha_process_guar_items', [
        ['icon' => '⚡', 'title' => 'سرعت زیر ۳ ثانیه', 'desc' => 'ضمانت بارگذاری زیر ۳ ثانیه — یا اصلاح رایگان.'],
        ['icon' => '▢', 'title' => 'ریسپانسیو کامل', 'desc' => 'نمایش بدون نقص در تمام موبایل‌ها و تبلت‌ها.'],
        ['icon' => '✓', 'title' => '۳۰ الی ۴۵ روز پشتیبانی رایگان', 'desc' => 'رفع خطاهای احتمالی پس از تحویل، بدون هزینه.'],
    ]);

    // ۱۳. خارج از محدوده
    update_option('raha_process_scope_items', [
        ['text' => 'تولید محتوا و متن‌نویسی'],
        ['text' => 'عکاسی صنعتی یا محصول'],
        ['text' => 'تولید ویدیو و موشن'],
        ['text' => 'ترجمه به زبان‌های دیگر'],
        ['text' => 'سئوی پیشرفته (فقط پایه)'],
        ['text' => 'کمپین تبلیغاتی و مارکتینگ'],
    ]);

    // ۱۴. ریسک‌ها
    update_option('raha_process_risk_items', [
        ['title' => 'تاخیر در تحویل محتوا', 'solve' => 'با متن پیش‌نویس و تصویر جایگزین جلو می‌رویم، اما زمان‌بندی رسماً به‌روزرسانی می‌شود.'],
        ['title' => 'تغییر Scope در میانه', 'solve' => 'مکتوب ثبت می‌شود، برآورد جدید و تاریخ جدید داده می‌شود.'],
        ['title' => 'بازخورد مبهم یا پراکنده', 'solve' => 'یک فرم بازخورد ساختاریافته در اختیارتان می‌گذارم.'],
        ['title' => 'شرایط غیرمنتظره', 'solve' => 'حداکثر ۲۴ ساعت بعد اطلاع می‌دهم و راه‌حل جایگزین ارائه می‌کنم.'],
    ]);

    // ۱۵. پشتیبانی
    update_option('raha_process_support_items', [
        ['big' => '۳۰ الی ۴۵ روز', 'title' => 'پشتیبانی رایگان', 'desc' => 'رفع هر خطا یا نقص فنی که پس از تحویل ظاهر شود.'],
        ['big' => '۲۴ ساعت', 'title' => 'زمان پاسخ', 'desc' => 'در طول دوره پشتیبانی، حداکثر یک روز کاری برای پاسخ اولیه.'],
        ['big' => 'پلن', 'title' => 'ادامه‌ی نگهداری', 'desc' => 'پس از ۳۰ الی ۴۵ روز، پلن ماهانه‌ی نگهداری و به‌روزرسانی ارائه می‌شود.'],
    ]);

    // ۱۶. مقایسه
    update_option('raha_process_compare_items', [
        ['feature' => 'خروجی مشخص در هر فاز', 'ours' => 'بله، مکتوب', 'theirs' => 'مبهم'],
        ['feature' => 'تعداد دور بازخورد', 'ours' => '۲ دور مشخص در هر فاز', 'theirs' => '«نامحدود» ادعایی'],
        ['feature' => 'زمان‌بندی برآوردی هر گام', 'ours' => 'شفاف از روز اول', 'theirs' => 'مبهم تا پایان'],
        ['feature' => 'مدل پرداخت', 'ours' => '۵۰ / ۲۵ / ۲۵', 'theirs' => '۵۰ / ۵۰ یا بیشتر'],
        ['feature' => 'گزارش پیشرفت هفتگی', 'ours' => 'بله', 'theirs' => 'معمولاً خیر'],
        ['feature' => 'تحویل فایل طراحی (Figma)', 'ours' => 'بله', 'theirs' => 'معمولاً خیر'],
    ]);

    // ۱۸. سوالات متداول
    update_option('raha_process_faq_items', [
        ['q' => 'اگر در طول پروژه نظرم درباره ظاهر تغییر کرد چه می‌شود؟', 'a' => 'در هر فاز دو دور ویرایش رایگان دارید. تغییرات بیشتر با توافق و برآورد جدید انجام می‌شود.'],
        ['q' => 'اگر محتوای من هنوز آماده نباشد چه کنیم؟', 'a' => 'با متن پیش‌نویس و تصاویر جایگزین جلو می‌رویم و بعداً جایگزین می‌کنیم. اما تاخیر در تحویل محتوا، زمان‌بندی کل پروژه را جابه‌جا می‌کند.'],
        ['q' => 'آیا بعد از تحویل خودم می‌توانم محتوا بگذارم؟', 'a' => 'بله. پنل مدیریت طوری تنظیم می‌شود که خودتان بتوانید متن، عکس و ویدیو اضافه کنید. ویدیوهای آموزشی اختصاصی هم ارائه می‌شود.'],
        ['q' => 'اگر پروژه در میانه متوقف شود چه می‌شود؟', 'a' => 'هزینه‌ی کارهای انجام‌شده تسویه می‌شود و فایل‌های مربوطه تحویل داده می‌شود. پیش‌پرداخت غیرقابل‌برگشت است.'],
        ['q' => 'آیا می‌توانم فقط بخشی از پروژه را سفارش دهم؟', 'a' => 'بله. Scope پروژه در فاز کشف تعیین می‌شود و می‌تواند فقط طراحی، فقط توسعه، یا هر ترکیبی باشد.'],
        ['q' => 'چه تضمینی هست که پروژه سر وقت تحویل شود؟', 'a' => 'تاریخ تحویل هر فاز در قرارداد مکتوب است. در صورت تاخیر از سمت من، جریمه‌ی تاخیر پیش‌بینی شده است.'],
    ]);

    // ۱۹. قدم‌های بعدی
    update_option('raha_process_step_items', [
        ['title' => 'فرم بریف را پر کنید', 'desc' => 'یک فرم کوتاه که در آن اهداف، مخاطب و سلیقه‌ی طراحی‌تان را می‌گویید.', 'time' => '۵ دقیقه'],
        ['title' => 'جلسه‌ی آشنایی', 'desc' => 'یک تماس ویدیویی رایگان برای بررسی پروژه، پاسخ به سوالات و آشنایی.', 'time' => '۳۰ دقیقه'],
        ['title' => 'دریافت پیشنهاد رسمی', 'desc' => 'سند پیشنهاد با Scope، زمان‌بندی و قیمت دقیق — برای تصمیم نهایی.', 'time' => '۴۸ ساعت'],
    ]);

    update_option('raha_process_seeded', 1);
}