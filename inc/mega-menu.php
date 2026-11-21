<?php
/**
 * Raha Studio — Mega Menu Admin Fields
 */
if (!defined('ABSPATH')) exit;

/* ============================================================
   ۱. نمایش فیلدهای اختصاصی در صفحه ویرایش منو
   ============================================================ */
add_action('wp_nav_menu_item_custom_fields', function ($item_id, $item, $depth, $args, $id) {
    
    // گرفتن مقادیر ذخیره‌شده
    $mega      = get_post_meta($item_id, '_raha_mega', true);
    $featured  = get_post_meta($item_id, '_raha_featured', true);
    $feat_desc = get_post_meta($item_id, '_raha_feat_desc', true);
    $feat_img  = (int) get_post_meta($item_id, '_raha_feat_image', true);
    $desc      = get_post_meta($item_id, '_raha_desc', true);
    $icon      = get_post_meta($item_id, '_raha_icon', true);
    $count     = get_post_meta($item_id, '_raha_count', true);

    // لیست آیکون‌های پرکاربرد Dashicons
    $dashicons = [
        '' => '— بدون آیکون —',
        'dashicons-admin-site' => 'سایت و وب (Site)',
        'dashicons-layout' => 'طرح بندی (Layout)',
        'dashicons-store' => 'فروشگاه (Store)',
        'dashicons-cart' => 'سبد خرید (Cart)',
        'dashicons-chart-line' => 'نمودار و رشد (Growth)',
        'dashicons-smartphone' => 'موبایل (Mobile)',
        'dashicons-desktop' => 'دسکتاپ (Desktop)',
        'dashicons-edit' => 'ویرایش و نوشتن (Edit)',
        'dashicons-admin-appearance' => 'ظاهر (Appearance)',
        'dashicons-admin-customizer' => 'شخصی‌سازی (Customizer)',
        'dashicons-format-gallery' => 'گالری (Gallery)',
        'dashicons-welcome-write-blog' => 'وبلاگ (Blog)',
        'dashicons-search' => 'جستجو (Search)',
        'dashicons-megaphone' => 'تبلیغات (Marketing)',
        'dashicons-networking' => 'شبکه و ارتباطات (Network)',
        'dashicons-portfolio' => 'نمونه‌کار (Portfolio)',
        'dashicons-analytics' => 'آنالیز (Analytics)',
        'dashicons-lock' => 'امنیت (Security)',
        'dashicons-lightbulb' => 'ایده و ایده‌پردازی (Idea)',
        'dashicons-hammer' => 'ابزار و ساخت (Tools)',
        'dashicons-admin-tools' => 'تنظیمات (Settings)',
        'dashicons-email' => 'ایمیل (Email)',
        'dashicons-phone' => 'تلفن (Phone)',
        'dashicons-location' => 'موقعیت (Location)',
        'dashicons-calendar-alt' => 'تقویم (Calendar)',
        'dashicons-video-alt3' => 'ویدیو (Video)',
        'dashicons-heading' => 'تیتر (Heading)',
        'dashicons-images-alt2' => 'تصاویر (Images)',
        'dashicons-cloud' => 'ابر (Cloud)',
        'dashicons-database' => 'دیتابیس (Database)',
    ];
    ?>

    <div class="raha-menu-fields" style="padding: 10px 0; border-top: 1px dashed #ccc; margin-top: 10px;">
        
        <!-- ========== سطح ۰: تنظیمات اصلی ========== -->
        <?php if ($depth === 0) : ?>
            <p class="description description-wide" style="margin-bottom: 10px;">
                <label>
                    <input type="checkbox" name="raha_mega[<?php echo esc_attr($item_id); ?>]" value="1" <?php checked($mega, '1'); ?>>
                    <strong style="color: #0c4a6e;">این آیتم را به صورت مگا منو نمایش بده</strong>
                </label>
            </p>
        <?php endif; ?>

        <!-- ========== سطح ۱: تنظیمات دراپ‌داون یا کارت ویژه ========== -->
        <?php if ($depth === 1) : ?>
            
            <?php if ($mega) : // اگر منوی مادر مگا منو باشد، این آیتم یک ستون یا کارت ویژه است ?>
                <p class="description description-wide" style="margin-bottom: 10px;">
                    <label>
                        <input type="checkbox" name="raha_featured[<?php echo esc_attr($item_id); ?>]" value="1" <?php checked($featured, '1'); ?>>
                        <strong style="color: #d97706;">این آیتم کارت ویژه باشد (داخل مگا منو)</strong>
                    </label>
                </p>
                
                <?php if ($featured) : ?>
                    <p class="description description-wide">
                        <label>توضیح کوتاه (برای کارت ویژه):<br>
                            <textarea name="raha_feat_desc[<?php echo esc_attr($item_id); ?>]" rows="2" class="widefat"><?php echo esc_textarea($feat_desc); ?></textarea>
                        </label>
                    </p>
                    <p class="description description-wide">
                        <label>تصویر کارت ویژه:</label><br>
                        <input type="hidden" name="raha_feat_image[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($feat_img); ?>" class="raha-menu-image-id">
                        <button type="button" class="button raha-menu-image-select">انتخاب تصویر</button>
                        <?php if ($feat_img) : ?>
                            <button type="button" class="button raha-menu-image-remove" style="color:#dc2626;">حذف</button>
                        <?php endif; ?>
                        <div class="raha-menu-image-preview" style="margin-top: 8px;">
                            <?php if ($feat_img) echo wp_get_attachment_image($feat_img, 'thumbnail', false, ['style' => 'max-width:80px; border-radius:4px; border: 1px solid #ccc;']); ?>
                        </div>
                    </p>
                <?php endif; ?>

            <?php else : // اگر منوی مادر دراپ‌داون عادی است (مثل خدمات) ?>
                <p class="description description-wide">
                    <label>آیکون (از کتابخانه Dashicons):<br>
                        <select name="raha_icon[<?php echo esc_attr($item_id); ?>]" class="widefat">
                            <?php foreach ($dashicons as $val => $label) : ?>
                                <option value="<?php echo esc_attr($val); ?>" <?php selected($icon, $val); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </p>
                <p class="description description-wide">
                    <label>توضیح کوتاه (زیر عنوان):<br>
                        <input type="text" name="raha_desc[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($desc); ?>" class="widefat">
                    </label>
                </p>
            <?php endif; ?>

        <?php endif; ?>

        <!-- ========== سطح ۲: لینک‌های داخل ستون‌های مگا منو ========== -->
        <?php if ($depth === 2 && $mega) : ?>
            <p class="description description-wide">
                <label>شمارنده (اختیاری):<br>
                    <input type="text" name="raha_count[<?php echo esc_attr($item_id); ?>]" value="<?php echo esc_attr($count); ?>" class="widefat" placeholder="مثلاً ۱۲">
                </label>
            </p>
        <?php endif; ?>

    </div>
    <?php
}, 10, 5);


/* ============================================================
   ۲. ذخیره‌سازی فیلدها هنگام ذخیره منو
   ============================================================ */
add_action('wp_update_nav_menu_item', function ($menu_id, $menu_item_db_id, $args) {
    
    // چک‌باکس‌ها
    $checkboxes = [
        '_raha_mega'     => 'raha_mega',
        '_raha_featured' => 'raha_featured',
    ];
    foreach ($checkboxes as $meta_key => $post_key) {
        if (!empty($_POST[$post_key][$menu_item_db_id])) {
            update_post_meta($menu_item_db_id, $meta_key, 1);
        } else {
            delete_post_meta($menu_item_db_id, $meta_key);
        }
    }

    // فیلدهای متنی
    $texts = [
        '_raha_feat_desc' => 'raha_feat_desc',
        '_raha_icon'      => 'raha_icon', // حالا فقط نام کلاس Dashicon است
        '_raha_desc'      => 'raha_desc',
        '_raha_count'     => 'raha_count',
    ];
    foreach ($texts as $meta_key => $post_key) {
        $val = $_POST[$post_key][$menu_item_db_id] ?? '';
        
        if ($val !== '') {
            update_post_meta($menu_item_db_id, $meta_key, sanitize_text_field($val));
        } else {
            delete_post_meta($menu_item_db_id, $meta_key);
        }
    }

    // تصویر
    $img = $_POST['raha_feat_image'][$menu_item_db_id] ?? '';
    if ($img && is_numeric($img)) {
        update_post_meta($menu_item_db_id, '_raha_feat_image', absint($img));
    } else {
        delete_post_meta($menu_item_db_id, '_raha_feat_image');
    }

}, 10, 3);

/* ============================================================
   ۳. اضافه کردن کلاس‌های CSS به آیتم‌های منو
   ============================================================ */
add_filter('nav_menu_css_class', function ($classes, $item, $args, $depth) {
    if (get_post_meta($item->ID, '_raha_mega', true)) {
        $classes[] = 'is-mega';
    }
    if (get_post_meta($item->ID, '_raha_featured', true)) {
        $classes[] = 'is-featured';
    }
    return $classes;
}, 10, 4);