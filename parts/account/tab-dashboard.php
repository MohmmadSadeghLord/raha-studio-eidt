<?php
if (!defined('ABSPATH')) exit;
$user   = wp_get_current_user();
$orders = raha_get_user_orders();
$total    = count($orders);
$progress = 0; $done = 0; $paid = 0;
foreach ($orders as $o) {
    $status = get_post_meta($o->ID, 'order_status', true) ?: 'new';
    if (in_array($status, ['review', 'progress'], true)) $progress++;
    if ($status === 'done') $done++;
    $paid += (int) get_post_meta($o->ID, 'amount_paid', true);
}
?>
<div class="raha-tab">
    <div class="raha-tab__head">
        <h1>سلام، <?php echo esc_html($user->display_name); ?>! 👋</h1>
        <p>خلاصه‌ای از فعالیت‌های شما</p>
    </div>

    <div class="raha-dash-stats">
        <div class="raha-stat">
            <span class="raha-stat__icon">📦</span>
            <div>
                <small>کل سفارش‌ها</small>
                <strong><?php echo esc_html(raha_fa_num($total)); ?></strong>
            </div>
        </div>
        <div class="raha-stat">
            <span class="raha-stat__icon">⏳</span>
            <div>
                <small>در حال انجام</small>
                <strong><?php echo esc_html(raha_fa_num($progress)); ?></strong>
            </div>
        </div>
        <div class="raha-stat">
            <span class="raha-stat__icon">✅</span>
            <div>
                <small>تکمیل‌شده</small>
                <strong><?php echo esc_html(raha_fa_num($done)); ?></strong>
            </div>
        </div>
        <div class="raha-stat">
            <span class="raha-stat__icon">💰</span>
            <div>
                <small>مجموع پرداختی (تومان)</small>
                <strong><?php echo esc_html(raha_money($paid)); ?></strong>
            </div>
        </div>
    </div>

    <div class="raha-dash-cta">
        <h2>پروژه جدیدی در ذهن دارید؟</h2>
        <p>درخواست خود را ثبت کنید تا با شما هماهنگ کنیم.</p>
        <a href="<?php echo esc_url(raha_page_url('order-form')); ?>" class="raha-btn raha-btn--primary">ثبت سفارش جدید</a>
    </div>

    <?php if (!empty($orders)) : ?>
        <h2 class="raha-tab__subtitle">آخرین سفارش‌ها</h2>
        <div class="raha-order-list">
            <?php foreach (array_slice($orders, 0, 3) as $o) :
                $status = get_post_meta($o->ID, 'order_status', true) ?: 'new';
                $service = get_post_meta($o->ID, 'service', true);
                $code = get_post_meta($o->ID, 'tracking_code', true);
            ?>
                <div class="raha-order-row">
                    <div>
                        <strong dir="ltr"><?php echo esc_html($code); ?></strong>
                        <small><?php echo esc_html($service); ?></small>
                    </div>
                    <span class="raha-badge raha-badge--<?php echo esc_attr($status); ?>"><?php echo esc_html(raha_status_label($status)); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>