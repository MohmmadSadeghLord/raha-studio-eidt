<?php
if (!defined('ABSPATH')) exit;

// پردازش درخواست لغو
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    if (wp_verify_nonce($_POST['_cancel_nonce'] ?? '', 'raha_cancel_order')) {
        $order_id = absint($_POST['order_id'] ?? 0);
        $order = get_post($order_id);
        
        if ($order && $order->post_type === 'raha_order' && $order->post_author == get_current_user_id()) {
            $status = get_post_meta($order_id, 'order_status', true);
            if (in_array($status, ['new', 'review'])) {
                update_post_meta($order_id, 'order_status', 'canceled');
                echo '<div style="padding:10px; background:#dcfce7; color:#14532d; border-radius:6px; margin-bottom:15px; font-weight:bold;">سفارش شما با موفقیت لغو شد.</div>';
            } else {
                echo '<div style="padding:10px; background:#fee2e2; color:#7f1d1d; border-radius:6px; margin-bottom:15px; font-weight:bold;">امکان لغو این سفارش وجود ندارد (در حال انجام است).</div>';
            }
        }
    }
}

$orders = raha_get_user_orders();
?>
<div class="raha-tab">
    <div class="raha-tab__head">
        <h1>سفارش‌های من</h1>
        <p><?php echo esc_html(raha_fa_num(count($orders))); ?> سفارش</p>
    </div>

    <?php if (empty($orders)) : ?>
        <div class="raha-empty">
            <div class="raha-empty__icon">📦</div>
            <h2>هنوز سفارشی ثبت نکرده‌اید</h2>
            <p>اولین پروژه خود را ثبت کنید.</p>
            <a href="<?php echo esc_url(raha_page_url('order-form')); ?>" class="raha-btn raha-btn--primary">ثبت سفارش جدید</a>
        </div>
    <?php else : ?>
        <div class="raha-order-list">
            <?php foreach ($orders as $o) :
                $status = get_post_meta($o->ID, 'order_status', true) ?: 'new';
                $service = get_post_meta($o->ID, 'service', true);
                $code = get_post_meta($o->ID, 'tracking_code', true);
                $amount = (int) get_post_meta($o->ID, 'amount', true);
                $paid   = (int) get_post_meta($o->ID, 'amount_paid', true);
            ?>
                <div class="raha-order-card">
                    <div class="raha-order-card__head">
                        <div>
                            <strong dir="ltr"><?php echo esc_html($code); ?></strong>
                            <span class="raha-badge raha-badge--<?php echo esc_attr($status); ?>"><?php echo esc_html(raha_status_label($status)); ?></span>
                        </div>
                        <small><?php echo esc_html(get_the_date('', $o)); ?></small>
                    </div>
                    <div class="raha-order-card__body">
                        <div><span>نوع خدمت</span><strong><?php echo esc_html($service); ?></strong></div>
                        <div><span>مبلغ کل</span><strong><?php echo $amount ? esc_html(raha_money($amount)) . ' تومان' : 'در انتظار برآورد'; ?></strong></div>
                        <div><span>پرداخت‌شده</span><strong><?php echo esc_html(raha_money($paid)); ?> تومان</strong></div>
                    </div>
                    
                    <?php if (in_array($status, ['new', 'review'])) : ?>
                        <div class="raha-order-card__actions">
                            <form method="post" onsubmit="return confirm('آیا از لغو این سفارش مطمئن هستید؟');">
                                <?php wp_nonce_field('raha_cancel_order', '_cancel_nonce'); ?>
                                <input type="hidden" name="action" value="cancel_order">
                                <input type="hidden" name="order_id" value="<?php echo esc_attr($o->ID); ?>">
                                <button type="submit" class="raha-btn raha-btn--danger" style="font-size:11px; min-height:32px; padding:0 12px;">لغو سفارش</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>