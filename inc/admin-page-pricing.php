<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=raha_pricing',
        'داشبورد مدیریت پکیج‌ها',
        '⚙️ داشبورد و تنظیمات',
        'manage_options',
        'raha-pricing-dashboard',
        'raha_render_pricing_dashboard'
    );
});

function raha_render_pricing_dashboard() {
    if (!current_user_can('manage_options')) return;

    // دریافت پکیج‌ها
    $plans = get_posts([
        'post_type'      => 'raha_pricing',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ]);
    ?>
    <div class="wrap raha-pricing-dash">
        
        <div class="raha-pricing-dash__header">
            <h1>
                <span style="font-size:32px;">📦</span> 
                مدیریت پکیج‌های قیمت
                <span class="badge">نسخه جدید</span>
            </h1>
        </div>

        <div class="raha-pricing-dash__actions">
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=raha_pricing')); ?>" class="raha-pricing-btn raha-pricing-btn--primary">
                <span>➕</span> افزودن پکیج جدید
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=raha-options&tab=pricing')); ?>" class="raha-pricing-btn raha-pricing-btn--sun">
                <span>⚙️</span> ویرایش خدمات ماهانه و باکس سفارشی
            </a>
            <a href="<?php echo esc_url(home_url('/#pricing')); ?>" target="_blank" class="raha-pricing-btn">
                <span>👁️</span> مشاهده در سایت
            </a>
        </div>

        <h2 style="font-size:20px; font-weight:900; color:#0c4a6e; margin-bottom:16px;">لیست پکیج‌های اصلی</h2>
        
        <div class="raha-pricing-dash__grid">
            <?php if (empty($plans)) : ?>
                <div style="grid-column: 1/-1; padding: 40px; text-align:center; background:#fff; border:2px dashed #ccc; border-radius:10px;">
                    <h3 style="color:#666; font-weight:bold;">هنوز پکیجی ساخته نشده است.</h3>
                    <p>برای شروع روی دکمه «افزودن پکیج جدید» کلیک کنید.</p>
                </div>
            <?php else : 
                foreach ($plans as $plan) : 
                    $popular = get_post_meta($plan->ID, 'popular', true);
                    $price   = get_post_meta($plan->ID, 'price_value', true);
                    $unit    = get_post_meta($plan->ID, 'price_unit', true);
                    $card_class = $popular ? ' is-popular' : '';
            ?>
                <div class="raha-pricing-card<?php echo $card_class; ?>">
                    <?php if ($popular) : ?><span class="raha-pricing-card__badge">پیشنهاد ویژه</span><?php endif; ?>
                    
                    <h3 class="raha-pricing-card__title"><?php echo esc_html($plan->post_title); ?></h3>
                    <div class="raha-pricing-card__price">
                        <?php echo esc_html($price ?: 'تماس بگیرید'); ?> <?php echo esc_html($unit); ?>
                    </div>
                    
                    <a href="<?php echo esc_url(get_edit_post_link($plan->ID)); ?>" class="raha-pricing-card__edit">
                        ✏️ ویرایش اطلاعات
                    </a>
                </div>
            <?php endforeach; endif; ?>
        </div>

    </div>
    <?php
}