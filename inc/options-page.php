<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {

    new Raha_Options_Page([
        'id'       => 'raha-options',
        'title'    => 'تنظیمات استودیو',
        'menu_title' => 'تنظیمات استودیو',
        'icon'     => 'dashicons-admin-settings',
        'position' => 58,
        'tabs'     => [

            /* ============ ۱. عمومی ============ */
            'general' => [
                'id'    => 'general',
                'label' => 'عمومی',
                'fields' => [
                    ['id' => 'site_status', 'label' => 'وضعیت سایت', 'type' => 'select', 'options' => [
                        'open'  => 'آماده همکاری',
                        'busy'  => 'ظرفیت تکمیل است',
                        'away'  => 'موقتاً غیرفعال',
                    ], 'default' => 'open'],
                    ['id' => 'contact_phone',    'label' => 'شماره تماس',   'type' => 'tel',   'desc' => 'مثلاً ۰۹۱۲۳۴۵۶۷۸۹'],
                    ['id' => 'contact_whatsapp', 'label' => 'شماره واتساپ', 'type' => 'tel'],
                    ['id' => 'contact_email',    'label' => 'ایمیل',         'type' => 'email'],
                    ['id' => 'contact_address',  'label' => 'آدرس',          'type' => 'text'],
                    ['id' => 'social_instagram', 'label' => 'اینستاگرام', 'type' => 'url'],
                    ['id' => 'social_telegram',  'label' => 'تلگرام',     'type' => 'url'],
                    ['id' => 'social_linkedin',  'label' => 'لینکدین',    'type' => 'url'],
                    ['id' => 'social_x',         'label' => 'ایکس',        'type' => 'url'],
                    ['id' => 'social_github',    'label' => 'گیت‌هاب',    'type' => 'url'],
                    ['id' => 'footer_copyright', 'label' => 'متن کپی‌رایت فوتر', 'type' => 'text', 'default' => '© ۱۴۰۴ استودیو رها — تمامی حقوق محفوظ است.'],
                ],
            ],

            /* ============ تب جدید: انیمیشن تم ============ */
            'theme_anim' => [
                'id'    => 'theme_anim',
                'label' => 'انیمیشن تم تاریک',
                'fields' => [
                    ['id' => 'dark_mode_enable_anim', 'label' => 'فعال‌سازی انیمیشن گذار', 'type' => 'checkbox', 'default' => '1'],
                    ['id' => 'dark_mode_anim_speed', 'label' => 'سرعت انیمیشن (میلی‌ثانیه)', 'type' => 'text', 'default' => '800', 'desc' => 'مثلاً ۸۰۰ برای ۰.۸ ثانیه. عدد کمتر = سریع‌تر.'],
                    ['id' => 'dark_mode_wave_strength', 'label' => 'شدت موج‌ها (۰ تا ۱۰۰)', 'type' => 'text', 'default' => '50', 'desc' => '۰ یعنی دایره کاملاً صاف، ۱۰۰ یعنی موج‌های بسیار شدید.'],
                ],
            ],

            /* ============ ۲. هیرو (دارای زیرتب) ============ */
            'hero' => [
                'id'    => 'hero',
                'label' => 'هیرو',
                'sub_tabs' => [
                    'hero_content' => [
                        'id'    => 'hero_content',
                        'label' => 'محتوای هیرو',
                        'fields' => [
                            ['id' => 'hero_eyebrow', 'label' => 'متن بالای عنوان (Eyebrow)', 'type' => 'text', 'default' => 'لرد وب · طراح سایت'],
                            ['id' => 'hero_title',   'label' => 'عنوان اصلی (H1)',  'type' => 'text', 'default' => 'طراحی با سلیقه شما'],
                            ['id' => 'hero_lead',    'label' => 'توضیحات زیر عنوان',  'type' => 'textarea', 'default' => 'هر چیزی که در ذهن شما باشد، متعهد به پیاده‌سازی آن خواهم بود — طراحی‌های خاص، متناسب با شخصیت و نیاز کسب‌وکار شما.'],
                            ['id' => 'hero_cta_1_text', 'label' => 'دکمه ۱ — متن', 'type' => 'text', 'default' => 'دیدن نمونه‌کارها'],
                            ['id' => 'hero_cta_1_url',  'label' => 'دکمه ۱ — لینک', 'type' => 'url', 'default' => '#portfolio'],
                            ['id' => 'hero_cta_2_text', 'label' => 'دکمه ۲ — متن', 'type' => 'text', 'default' => 'رزرو مشاوره'],
                            ['id' => 'hero_cta_2_url',  'label' => 'دکمه ۲ — لینک', 'type' => 'url', 'default' => '#contact'],
                            ['id' => 'hero_tags', 'label' => 'برچسب‌های مهارت (Tags)', 'type' => 'repeater', 'add_label' => '+ افزودن برچسب',
                                'fields' => [
                                    ['id' => 'text', 'label' => 'متن برچسب', 'type' => 'text'],
                                ],
                            ],
                            ['id' => 'hero_mockup_url', 'label' => 'آدرس سایت (داخل ماکاپ مرورگر)', 'type' => 'text', 'default' => 'wanderlust.ir'],
                            ['id' => 'hero_terminal_filename', 'label' => 'نام فایل (نوار بالای ترمینال)', 'type' => 'text', 'default' => 'index.html'],
                            ['id' => 'hero_terminal_code', 'label' => 'کدهای داخل ترمینال', 'type' => 'textarea', 'desc' => 'کدهای HTML یا متنی که قرار است در ترمینال تایپ شوند.'],
                        ],
                    ],
                    'hero_style' => [
                        'id'    => 'hero_style',
                        'label' => 'استایل و تنظیمات',
                        'fields' => [
                            ['id' => 'hero_bg_color', 'label' => 'رنگ پس‌زمینه هیرو', 'type' => 'color', 'default' => '#e0f2fe'],
                            ['id' => 'hero_title_color', 'label' => 'رنگ عنوان اصلی', 'type' => 'color', 'default' => '#14110f'],
                            ['id' => 'hero_title_size', 'label' => 'اندازه فونت عنوان (مثلاً 4rem)', 'type' => 'text', 'default' => 'clamp(2.5rem, 6vw, 4.5rem)'],
                            ['id' => 'hero_lead_size', 'label' => 'اندازه فونت توضیحات (مثلاً 1.1rem)', 'type' => 'text', 'default' => '1.0625rem'],
                            ['id' => 'hero_show_mockup', 'label' => 'نمایش ماکاپ مرورگر', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'hero_custom_mockup', 'label' => 'تصویر اختصاصی (جایگزین ماکاپ CSS)', 'type' => 'image', 'desc' => 'اگر تصویری آپلود کنید، به جای ماکاپ CSS این تصویر نمایش داده می‌شود.'],
                            ['id' => 'hero_show_terminal', 'label' => 'نمایش ترمینال', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'hero_show_floating', 'label' => 'نمایش آیکون‌های شناور', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'hero_show_sparkles', 'label' => 'نمایش ستاره‌های تزئینی', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'hero_typing_speed', 'label' => 'سرعت تایپ ترمینال', 'type' => 'select', 'options' => [
                                'slow' => 'کند',
                                'normal' => 'عادی',
                                'fast' => 'سریع',
                            ], 'default' => 'normal'],
                            ['id' => 'hero_custom_css', 'label' => 'کدهای CSS اختصاصی', 'type' => 'textarea', 'desc' => 'هر کد CSS که اینجا بنویسید، به صورت مستقیم در هدر سایت لود می‌شود.', 'default' => ''],
                        ],
                    ]
                ]
            ],

            /* ============ ۳. درباره من ============ */
            'about' => [
                'id'    => 'about',
                'label' => 'درباره من',
                'fields' => [
                    ['id' => 'about_eyebrow', 'label' => 'متن بالای عنوان (Eyebrow)', 'type' => 'text', 'default' => 'درباره من'],
                    ['id' => 'about_name',    'label' => 'نام و نام خانوادگی', 'type' => 'text', 'default' => 'محمد صادق محمودی لرد'],
                    ['id' => 'about_portrait','label' => 'تصویر پرتره', 'type' => 'image'],
                    ['id' => 'about_slogan',  'label' => 'شعار', 'type' => 'text', 'default' => 'تعهد شعار من است.'],

                    // ستون ۱
                    ['id' => 'about_col_1_title', 'label' => 'ستون ۱ — عنوان', 'type' => 'text', 'default' => 'خدمات'],
                    ['id' => 'about_col_1_items', 'label' => 'ستون ۱ — آیتم‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن آیتم',
                        'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                    ],

                    // ستون ۲
                    ['id' => 'about_col_2_title', 'label' => 'ستون ۲ — عنوان', 'type' => 'text', 'default' => 'ابزارها'],
                    ['id' => 'about_col_2_items', 'label' => 'ستون ۲ — آیتم‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن آیتم',
                        'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                    ],

                    // ستون ۳
                    ['id' => 'about_col_3_title', 'label' => 'ستون ۳ — عنوان', 'type' => 'text', 'default' => 'سبک کار'],
                    ['id' => 'about_col_3_items', 'label' => 'ستون ۳ — آیتم‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن آیتم',
                        'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                    ],

                    // دکمه پایانی
                    ['id' => 'about_cta_text', 'label' => 'دکمه — متن', 'type' => 'text', 'default' => 'تماس با من'],
                    ['id' => 'about_cta_url',  'label' => 'دکمه — لینک', 'type' => 'url', 'default' => '#contact'],
                ],
            ],

            /* ============ ۴. پکیج‌ها ============ */
            'pricing' => [
                'id'    => 'pricing',
                'label' => 'پکیج‌ها',
                'fields' => [
                    ['id' => 'pricing_title', 'label' => 'عنوان بخش', 'type' => 'text', 'default' => 'پکیج ها'],

                    // فیلد تکرارشونده برای خدمات ماهانه
                    ['id' => 'pricing_monthly_services', 'label' => 'خدمات ماهانه', 'type' => 'repeater',
                        'add_label' => '+ افزودن خدمت ماهانه',
                        'fields' => [
                            ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                            ['id' => 'desc', 'label' => 'توضیح', 'type' => 'textarea'],
                            ['id' => 'price', 'label' => 'قیمت (مثلاً ۲ <span>میلیون تومان</span>)', 'type' => 'text'],
                            ['id' => 'highlight', 'label' => 'رنگ زرد (هایلایت)', 'type' => 'checkbox'],
                            ['id' => 'icon', 'label' => 'کدهای داخلی SVG آیکون', 'type' => 'textarea', 'desc' => 'فقط تگ‌های داخلی مثل path یا polyline را پیست کنید.'],
                        ],
                    ],

                    // باکس سفارشی
                    ['id' => 'pricing_custom_title',    'label' => 'باکس سفارشی — عنوان', 'type' => 'text', 'default' => 'نیاز خاصی دارید؟'],
                    ['id' => 'pricing_custom_desc',     'label' => 'باکس سفارشی — توضیح', 'type' => 'textarea', 'default' => 'اگر پکیج اختصاصی، برندینگ، چندزبانه یا هر امکان دیگری می‌خواهید، یک جلسه کوتاه بگیرید تا دقیقاً متناسب با نیاز شما برآورد کنیم.'],
                    ['id' => 'pricing_custom_btn_text', 'label' => 'باکس سفارشی — دکمه متن', 'type' => 'text', 'default' => 'گفتگو درباره پروژه'],
                    ['id' => 'pricing_custom_btn_url',  'label' => 'باکس سفارشی — دکمه لینک', 'type' => 'url', 'default' => '#contact'],
                ],
            ],

            /* ============ ۵. CTA ============ */
            'cta' => [
                'id'    => 'cta',
                'label' => 'CTA پایانی',
                'fields' => [
                    ['id' => 'cta_title', 'label' => 'عنوان اصلی',  'type' => 'text', 'default' => 'تماس با من'],
                    ['id' => 'cta_desc',  'label' => 'توضیحات',  'type' => 'textarea', 'default' => 'شماره‌تان را بگذارید؛ در کمتر از ۲۴ ساعت خودم تماس می‌گیرم'],

                    ['id' => 'cta_trust_1', 'label' => 'متن اعتماد ۱', 'type' => 'text', 'default' => 'راهنمایی کامل'],
                    ['id' => 'cta_trust_2', 'label' => 'متن اعتماد ۲', 'type' => 'text', 'default' => 'ارتباط در کمتر از ۲۴ ساعت'],
                    ['id' => 'cta_trust_3', 'label' => 'متن اعتماد ۳', 'type' => 'text', 'default' => 'پیشنهادات مناسب'],

                    ['id' => 'cta_form_label', 'label' => 'فرم — لیبل', 'type' => 'text', 'default' => 'شماره تماس یا ایمیل'],
                    ['id' => 'cta_form_placeholder', 'label' => 'فرم — متن راهنما', 'type' => 'text', 'default' => '۰۹۱۲۳۴۵۶۷۸۹ یا you@email.com'],
                    ['id' => 'cta_btn_text', 'label' => 'فرم — متن دکمه', 'type' => 'text', 'default' => 'ثبت جهت تماس رایگان'],

                    ['id' => 'cta_success_title', 'label' => 'پیام موفقیت — عنوان', 'type' => 'text', 'default' => 'ثبت شد!'],
                    ['id' => 'cta_success_desc', 'label' => 'پیام موفقیت — توضیح', 'type' => 'textarea', 'default' => 'در کمتر از ۲۴ ساعت با شما تماس می‌گیرم.'],

                    ['id' => 'cta_channels', 'label' => 'کانال‌های ارتباطی', 'type' => 'repeater', 'add_label' => '+ افزودن کانال',
                        'fields' => [
                            ['id' => 'name', 'label' => 'نام کانال', 'type' => 'text'],
                            ['id' => 'url', 'label' => 'لینک', 'type' => 'text'],
                            ['id' => 'icon_bg', 'label' => 'کد رنگ پس‌زمینه آیکون', 'type' => 'text'],
                            ['id' => 'icon_color', 'label' => 'کد رنگ آیکون', 'type' => 'text'],
                            ['id' => 'icon', 'label' => 'کدهای SVG آیکون', 'type' => 'svg', 'desc' => 'کد SVG کامل را اینجا پیست کنید.'],
                        ],
                    ],
                ],
            ],

            /* ============ ۶. سوالات متداول هوم پیج ============ */
            'home_faq' => [
                'id'    => 'home_faq',
                'label' => 'سوالات متداول هوم پیج',
                'fields' => [
                    ['id' => 'faq_eyebrow', 'label' => 'متن بالای عنوان', 'type' => 'text', 'default' => 'سوالات متداول'],
                    ['id' => 'faq_title',   'label' => 'عنوان اصلی', 'type' => 'text', 'default' => 'پرسش‌هایی که زیاد می‌شنوم'],
                    ['id' => 'faq_lead',    'label' => 'توضیحات زیر عنوان', 'type' => 'textarea', 'default' => 'اگر پاسخ سوالت را اینجا پیدا نکردی، از دکمه‌ی «تماس با من» استفاده کن.'],

                    ['id' => 'faq_items', 'label' => 'لیست سوالات', 'type' => 'repeater', 'add_label' => '+ افزودن سوال',
                        'fields' => [
                            ['id' => 'question', 'label' => 'سوال', 'type' => 'text'],
                            ['id' => 'answer', 'label' => 'پاسخ', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],

            /* ============ ۷. فوتر ============ */
            'footer' => [
                'id'    => 'footer',
                'label' => 'فوتر',
                'fields' => [
                    // خبرنامه
                    ['id' => 'footer_news_title', 'label' => 'خبرنامه — عنوان', 'type' => 'text', 'default' => 'هر جمعه، یک ایده‌ی خام طراحی در اینباکس شما.'],
                    ['id' => 'footer_news_sub',   'label' => 'خبرنامه — توضیح', 'type' => 'text', 'default' => 'بدون اسپم، بدون تبلیغ. فقط چیزهایی که واقعاً کار می‌کنند.'],

                    // برند
                    ['id' => 'footer_brand_desc', 'label' => 'توضیح برند (زیر لوگو)', 'type' => 'textarea', 'default' => 'استودیوی طراحی و توسعه با خط‌های ضخیم، تصمیم‌های شجاعانه و کدِ تمیز — برای برندهایی که نمی‌خواهند دیده نشوند.'],

                    // سوشال‌ها (تکرارشونده)
                    ['id' => 'footer_socials', 'label' => 'شبکه‌های اجتماعی', 'type' => 'repeater', 'add_label' => '+ افزودن شبکه',
                        'fields' => [
                            ['id' => 'label', 'label' => 'نام (مثلاً اینستاگرام)', 'type' => 'text'],
                            ['id' => 'url', 'label' => 'لینک', 'type' => 'text'],
                            ['id' => 'icon', 'label' => 'کد SVG آیکون', 'type' => 'svg'],
                        ],
                    ],

                    // ستون‌های لینک (تکرارشونده - ۳ ستون)
                    ['id' => 'footer_columns', 'label' => 'ستون‌های لینک', 'type' => 'repeater', 'add_label' => '+ افزودن ستون',
                        'fields' => [
                            ['id' => 'title', 'label' => 'عنوان ستون (مثلاً محصولات)', 'type' => 'text'],
                            ['id' => 'links', 'label' => 'لینک‌ها (هر خط یک لینک: عنوان | لینک)', 'type' => 'textarea', 'desc' => 'فرمت: عنوان لینک | آدرس لینک'],
                        ],
                    ],

                    // اطلاعات تماس
                    ['id' => 'footer_email',   'label' => 'ایمیل', 'type' => 'text', 'default' => 'hi@neobrutal.studio'],
                    ['id' => 'footer_phone',   'label' => 'تلفن', 'type' => 'text', 'default' => '+98 21 0000 0000'],
                    ['id' => 'footer_address', 'label' => 'آدرس', 'type' => 'text', 'default' => 'تهران، ولیعصر، پلاک ۱۲'],
                    ['id' => 'footer_status',  'label' => 'وضعیت (نوار سبز)', 'type' => 'text', 'default' => 'آماده‌ی پروژه'],

                    // پایین‌ترین نوار
                    ['id' => 'footer_copyright', 'label' => 'متن کپی‌رایت', 'type' => 'text', 'default' => '© ۲۰۲۵ نئوبروتال استودیو'],
                    ['id' => 'footer_legal', 'label' => 'لینک‌های حقوقی (هر خط: عنوان | لینک)', 'type' => 'textarea', 'default' => "حریم خصوصی | #\nقوانین | #\nکوکی‌ها | #"],
                ],
            ],

            /* ============ ۸. ایمیل و اعلان‌ها ============ */
            'email' => [
                'id'    => 'email',
                'label' => 'ایمیل و اعلان‌ها',
                'sub_tabs' => [

                    'email_smtp' => [
                        'id'    => 'email_smtp',
                        'label' => 'تنظیمات SMTP',
                        'fields' => [
                            ['id' => 'smtp_enabled', 'label' => 'فعال‌سازی ارسال ایمیل با SMTP', 'type' => 'checkbox', 'default' => '0'],
                            ['id' => 'smtp_host',    'label' => 'میزبان (Host)', 'type' => 'text'],
                            ['id' => 'smtp_port',    'label' => 'پورت (Port)',  'type' => 'text', 'default' => '587'],
                            ['id' => 'smtp_secure',  'label' => 'نوع امنیت',   'type' => 'select',
                                'options' => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'بدون امنیت'],
                                'default' => 'tls'],
                            ['id' => 'smtp_user',    'label' => 'نام کاربری', 'type' => 'text'],
                            ['id' => 'smtp_pass',    'label' => 'رمز عبور',   'type' => 'password',
                                'desc' => 'پس از ذخیره، رمز نمایش داده نمی‌شود. اگر خالی بگذارید، مقدار قبلی حفظ می‌شود.'],
                            ['id' => 'smtp_from_email', 'label' => 'ایمیل فرستنده', 'type' => 'email'],
                            ['id' => 'smtp_from_name',  'label' => 'نام فرستنده',   'type' => 'text', 'default' => 'استودیو رها'],
                        ],
                    ],

                    'email_notifications' => [
                        'id'    => 'email_notifications',
                        'label' => 'اعلان‌ها',
                        'fields' => [
                            ['id' => 'email_admin_recipient', 'label' => 'ایمیل دریافت اعلان‌های ادمین', 'type' => 'email',
                                'desc' => 'اگر خالی باشد، از ایمیل اصلی وردپرس استفاده می‌شود.'],
                            ['id' => 'email_enable_order_admin',      'label' => 'اعلان سفارش جدید به ادمین', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_order_customer',   'label' => 'ایمیل تایید سفارش به مشتری', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_register_welcome', 'label' => 'ایمیل خوش‌آمد پس از ثبت‌نام', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_reset_password',   'label' => 'ارسال کد بازیابی رمز',      'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_ticket_admin',     'label' => 'اعلان تیکت جدید به ادمین',   'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_ticket_reply',     'label' => 'اطلاع مشتری از پاسخ تیکت',   'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'email_enable_cta_admin',        'label' => 'اعلان درخواست تماس جدید',    'type' => 'checkbox', 'default' => '1'],
                        ],
                    ],

                    'email_templates' => [
                        'id'    => 'email_templates',
                        'label' => 'ظاهر ایمیل‌ها',
                        'fields' => [
                            ['id' => 'email_logo',          'label' => 'لوگو ایمیل',       'type' => 'image',
                                'desc' => 'اگر خالی باشد، فقط نام سایت در هدر نمایش داده می‌شود.'],
                            ['id' => 'email_accent_color',  'label' => 'رنگ اصلی',         'type' => 'color', 'default' => '#0c4a6e'],
                            ['id' => 'email_footer_text',   'label' => 'متن فوتر ایمیل',   'type' => 'text', 'default' => 'این ایمیل به‌صورت خودکار ارسال شده است.'],
                        ],
                    ],

                    'email_newsletter' => [
                        'id'    => 'email_newsletter',
                        'label' => 'خبرنامه',
                        'render_callback' => function ($page) {
                            $count = wp_count_posts('raha_newsletter')->publish ?? 0;
                            ?>
                            <div class="raha-admin-field">
                                <label class="raha-admin-label">مشترکین فعلی خبرنامه</label>
                                <p style="font-size: 28px; font-weight: 900; color: #0c4a6e; margin: 8px 0;">
                                    <?php echo esc_html(number_format_i18n($count)); ?> نفر
                                </p>
                                <p class="raha-admin-desc">
                                    برای مشاهده و مدیریت مشترکین، به
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=raha_newsletter')); ?>">لیست مشترکین</a>
                                    بروید.
                                </p>
                                <p style="margin-top: 16px;">
                                    <a href="<?php echo esc_url(admin_url('admin-post.php?action=raha_export_newsletter')); ?>"
                                       class="button button-primary">
                                        📥 خروجی CSV مشترکین
                                    </a>
                                </p>
                            </div>
                            <?php
                        },
                    ],

                    'email_logs' => [
                        'id'    => 'email_logs',
                        'label' => 'لاگ ارسال',
                        'render_callback' => 'raha_render_email_logs',
                    ],

                    'email_test' => [
                        'id'    => 'email_test',
                        'label' => 'تست ارسال',
                        'render_callback' => 'raha_render_email_test',
                    ],
                ],
            ],

            /* ============ ۹. فرآیند همکاری ============ */
            'process' => [
                'id'    => 'process',
                'label' => 'فرآیند همکاری',
                'sub_tabs' => [
                    'process_general' => [
                        'id'    => 'process_general',
                        'label' => 'تنظیمات کلی و نمایش بخش‌ها',
                        'fields' => [
                            // چک‌باکس‌های نمایش
                            ['id' => 'process_show_intro', 'label' => 'نمایش سکشن مقدمه', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_philosophy', 'label' => 'نمایش سکشن فلسفه', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_process', 'label' => 'نمایش تایم‌لاین ۸ گام', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_gantt', 'label' => 'نمایش گانت چارت', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_who', 'label' => 'نمایش تعهدات', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_req', 'label' => 'نمایش پیش‌نیازها', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_comm', 'label' => 'نمایش کانال‌های ارتباطی', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_deliv', 'label' => 'نمایش تحویل‌دادنی‌ها', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_revision', 'label' => 'نمایش قوانین بازخورد', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_payment', 'label' => 'نمایش مدل پرداخت', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_guarantee', 'label' => 'نمایش ضمانت‌ها', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_scope', 'label' => 'نمایش خارج از محدوده', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_risks', 'label' => 'نمایش ریسک‌ها', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_support', 'label' => 'نمایش پشتیبانی', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_compare', 'label' => 'نمایش جدول مقایسه', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_faq', 'label' => 'نمایش سوالات متداول', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_steps', 'label' => 'نمایش قدم‌های بعدی', 'type' => 'checkbox', 'default' => '1'],
                            ['id' => 'process_show_cta', 'label' => 'نمایش CTA پایانی', 'type' => 'checkbox', 'default' => '1'],

                            // فیلدهای جدید هیرو
                            ['id' => 'process_eyebrow', 'label' => 'متن بالای عنوان (Eyebrow)', 'type' => 'text', 'default' => 'فرآیند همکاری'],
                            ['id' => 'process_intro_title', 'label' => 'عنوان مقدمه (H1)', 'type' => 'text', 'default' => 'مسیر تبدیل فکرهای شما به یک وب‌سایت واقعی و سودآور.'],
                            ['id' => 'process_intro_lead', 'label' => 'توضیحات مقدمه', 'type' => 'textarea', 'default' => 'شفافیت، زمان‌بندی دقیق و ارتباط مداوم — سه اصل اصلی من در تمام پروژه‌هاست. در این صفحه دقیقاً می‌بینید که از روز اول تا تحویل، چه اتفاقی می‌افتد، چه چیزی از شما می‌خواهم، و در پایان چه چیزی تحویل می‌گیرید. هیچ مرحله‌ای بدون تایید شما رد نمی‌شود.'],
                            
                            ['id' => 'process_btn_1_text', 'label' => 'دکمه ۱ — متن', 'type' => 'text', 'default' => 'رزرو جلسه مشاوره رایگان'],
                            ['id' => 'process_btn_1_url', 'label' => 'دکمه ۱ — لینک', 'type' => 'url', 'default' => '#cta'],
                            ['id' => 'process_btn_2_text', 'label' => 'دکمه ۲ — متن', 'type' => 'text', 'default' => 'مشاهده ۸ گام'],
                            ['id' => 'process_btn_2_url', 'label' => 'دکمه ۲ — لینک', 'type' => 'url', 'default' => '#process'],

                            // تنظیمات پنل آمار
                            ['id' => 'process_sticker', 'label' => 'متن استیکر (روی پنل)', 'type' => 'text', 'default' => '100% شفاف'],
                            ['id' => 'process_panel_title', 'label' => 'نام فایل (نوار بالای پنل)', 'type' => 'text', 'default' => 'Project_Overview.exe'],
                            
                            ['id' => 'process_stat_1_num', 'label' => 'آمار ۱ — عدد', 'type' => 'text', 'default' => '۸'],
                            ['id' => 'process_stat_1_label', 'label' => 'آمار ۱ — برچسب', 'type' => 'text', 'default' => 'گام شفاف و مشخص'],
                            
                            ['id' => 'process_stat_2_num', 'label' => 'آمار ۲ — عدد', 'type' => 'text', 'default' => '۴–۶'],
                            ['id' => 'process_stat_2_label', 'label' => 'آمار ۲ — برچسب', 'type' => 'text', 'default' => 'میانگین زمان تحویل (هفته)'],
                            
                            ['id' => 'process_stat_3_num', 'label' => 'آمار ۳ — عدد', 'type' => 'text', 'default' => '۳۰ الی ۴۵'],
                            ['id' => 'process_stat_3_label', 'label' => 'آمار ۳ — برچسب', 'type' => 'text', 'default' => 'روز پشتیبانی رایگان'],
                        ],
                    ],
                    'process_content' => [
                        'id'    => 'process_content',
                        'label' => 'محتوای بخش‌ها',
                        'fields' => [
                            // فلسفه
                            ['id' => 'process_philosophy_title', 'label' => 'فلسفه — عنوان', 'type' => 'text', 'default' => 'سه اصلی که هر پروژه روی آن‌ها بنا می‌شود.'],
                            ['id' => 'process_philosophy_items', 'label' => 'فلسفه — آیتم‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن اصل',
                                'fields' => [
                                    ['id' => 'icon', 'label' => 'آیکون (متن یا ایموجی)', 'type' => 'text'],
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                ],
                            ],
                            // تایم‌لاین
                            ['id' => 'process_timeline_lead', 'label' => 'تایم‌لاین — توضیحات', 'type' => 'text', 'default' => 'هر گام، خروجی مشخص، زمان‌بندی برآوردشده و توضیحات واضح دارد. هیچ مرحله‌ای بدون تایید شما رد نمی‌شود.'],
                            ['id' => 'process_timeline_items', 'label' => 'تایم‌لاین — ۸ گام', 'type' => 'repeater', 'add_label' => '+ افزودن گام',
                                'fields' => [
                                    ['id' => 'phase', 'label' => 'فاز (مثلاً بررسی اولیه)', 'type' => 'text'],
                                    ['id' => 'duration', 'label' => 'مدت زمان', 'type' => 'text'],
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                    ['id' => 'output', 'label' => 'خروجی', 'type' => 'text'],
                                    ['id' => 'phase_num', 'label' => 'شماره فاز (۱ تا ۴)', 'type' => 'text'],
                                ],
                            ],
                            // گانت
                            ['id' => 'process_gantt_items', 'label' => 'گانت — نوارها', 'type' => 'repeater', 'add_label' => '+ افزودن نوار',
                                'fields' => [
                                    ['id' => 'label', 'label' => 'نام فاز', 'type' => 'text'],
                                    ['id' => 'width', 'label' => 'عرض (درصد)', 'type' => 'text'],
                                    ['id' => 'time_text', 'label' => 'متن روی نوار', 'type' => 'text'],
                                    ['id' => 'color', 'label' => 'رنگ (p1, p2, p3, p4)', 'type' => 'text'],
                                ],
                            ],
                            // تعهدات
                            ['id' => 'process_who_me', 'label' => 'تعهدات من', 'type' => 'repeater', 'add_label' => '+ افزودن تعهد',
                                'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                            ],
                            ['id' => 'process_who_you', 'label' => 'تعهدات شما', 'type' => 'repeater', 'add_label' => '+ افزودن تعهد',
                                'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                            ],
                            // پیش‌نیازها
                            ['id' => 'process_req_items', 'label' => 'پیش‌نیازها', 'type' => 'repeater', 'add_label' => '+ افزودن مورد',
                                'fields' => [
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                ],
                            ],
                            // ارتباطات
                            ['id' => 'process_comm_title', 'label' => 'ارتباطات — عنوان', 'type' => 'text', 'default' => 'همواره از مراحل پروژه در پنل خود مطلع هستید.'],
                            ['id' => 'process_comm_items', 'label' => 'ارتباطات — کانال‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن کانال',
                                'fields' => [
                                    ['id' => 'icon', 'label' => 'آیکون', 'type' => 'text'],
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                ],
                            ],
                            // تحویل‌دادنی‌ها
                            ['id' => 'process_deliv_items', 'label' => 'تحویل‌دادنی‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن مورد',
                                'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                            ],
                            // بازخورد
                            ['id' => 'process_rev_title', 'label' => 'بازخورد — عنوان', 'type' => 'text', 'default' => 'قوانین بازخورد و اصلاحات'],
                            ['id' => 'process_rev_desc', 'label' => 'بازخورد — توضیحات', 'type' => 'textarea', 'default' => 'در هر فاز (وایرفریم، طراحی بصری، توسعه) دو دور ویرایش رایگان گنجانده شده است. تغییرات بیشتر با توافق و هزینه‌ی جداگانه انجام می‌شود.'],
                            // پرداخت
                            ['id' => 'process_payment_items', 'label' => 'پرداخت — مراحل', 'type' => 'repeater', 'add_label' => '+ افزودن مرحله',
                                'fields' => [
                                    ['id' => 'percent', 'label' => 'درصد', 'type' => 'text'],
                                    ['id' => 'label', 'label' => 'برچسب', 'type' => 'text'],
                                    ['id' => 'note', 'label' => 'توضیح', 'type' => 'text'],
                                ],
                            ],
                            // ضمانت‌ها
                            ['id' => 'process_guar_items', 'label' => 'ضمانت‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن ضمانت',
                                'fields' => [
                                    ['id' => 'icon', 'label' => 'آیکون', 'type' => 'text'],
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                ],
                            ],
                            // خارج از محدوده
                            ['id' => 'process_scope_items', 'label' => 'خارج از محدوده', 'type' => 'repeater', 'add_label' => '+ افزودن مورد',
                                'fields' => [['id' => 'text', 'label' => 'متن', 'type' => 'text']],
                            ],
                            // ریسک‌ها
                            ['id' => 'process_risk_items', 'label' => 'ریسک‌ها', 'type' => 'repeater', 'add_label' => '+ افزودن ریسک',
                                'fields' => [
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'solve', 'label' => 'راه‌حل', 'type' => 'textarea'],
                                ],
                            ],
                            // پشتیبانی
                            ['id' => 'process_support_items', 'label' => 'پشتیبانی', 'type' => 'repeater', 'add_label' => '+ افزودن کارت',
                                'fields' => [
                                    ['id' => 'big', 'label' => 'متن بزرگ', 'type' => 'text'],
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                ],
                            ],
                            // مقایسه
                            ['id' => 'process_compare_items', 'label' => 'جدول مقایسه', 'type' => 'repeater', 'add_label' => '+ افزودن ردیف',
                                'fields' => [
                                    ['id' => 'feature', 'label' => 'ویژگی', 'type' => 'text'],
                                    ['id' => 'ours', 'label' => 'این فرآیند', 'type' => 'text'],
                                    ['id' => 'theirs', 'label' => 'آژانس‌های معمول', 'type' => 'text'],
                                ],
                            ],
                            // سوالات متداول
                            ['id' => 'process_faq_items', 'label' => 'سوالات متداول', 'type' => 'repeater', 'add_label' => '+ افزودن سوال',
                                'fields' => [
                                    ['id' => 'q', 'label' => 'سوال', 'type' => 'text'],
                                    ['id' => 'a', 'label' => 'پاسخ', 'type' => 'textarea'],
                                ],
                            ],
                            // قدم‌های بعدی
                            ['id' => 'process_step_items', 'label' => 'قدم‌های بعدی', 'type' => 'repeater', 'add_label' => '+ افزودن قدم',
                                'fields' => [
                                    ['id' => 'title', 'label' => 'عنوان', 'type' => 'text'],
                                    ['id' => 'desc', 'label' => 'توضیحات', 'type' => 'textarea'],
                                    ['id' => 'time', 'label' => 'زمان', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'process_custom' => [
                        'id'    => 'process_custom',
                        'label' => 'بخش‌های سفارشی (HTML/CSS/JS)',
                        'fields' => [
                            ['id' => 'process_custom_sections', 'label' => 'سکشن‌های سفارشی', 'type' => 'repeater', 'add_label' => '+ افزودن سکشن سفارشی',
                                'fields' => [
                                    ['id' => 'name', 'label' => 'نام (فقط برای شناسایی)', 'type' => 'text'],
                                    ['id' => 'html', 'label' => 'کد HTML', 'type' => 'textarea'],
                                    ['id' => 'css', 'label' => 'کد CSS', 'type' => 'textarea'],
                                    ['id' => 'js', 'label' => 'کد JavaScript', 'type' => 'textarea'],
                                ],
                            ],
                        ],
                    ],
                    'process_ai' => [
                        'id'    => 'process_ai',
                        'label' => 'راهنمای هوش مصنوعی',
                        'fields' => [
                            ['id' => 'process_ai_style_guide', 'label' => 'راهنمای استایل (برای کپی در چت AI)', 'type' => 'textarea', 'default' => "برای ساخت سکشن جدید در این سایت، قوانین زیر را رعایت کن:
- پالت رنگی: سرمه‌ای (#0c4a6e)، زرد (#facc15)، آبی روشن (#e0f2fe)، سفید (#ffffff)، مشکی (#14110f).
- فونت‌ها: Sahel برای تیترها، Vazirmatn برای متن.
- تمام حاشیه‌ها: 2px solid #14110f.
- تمام سایه‌ها: box-shadow با افست سخت (بدون blur)، مثلاً 4px 4px 0 #14110f.
- تمام گوشه‌ها: border-radius 8px یا 10px.
- دکمه‌ها: حاشیه مشکی، سایه سخت، هاور با transform: translate(-2px, -2px) و افزایش سایه."],
                        ],
                    ],
                ],
            ],
        ],
    ]);

}, 5);