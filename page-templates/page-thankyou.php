<?php
/* Template Name: تشکر */
if (!defined('ABSPATH')) exit;

$tracking = sanitize_text_field($_GET['tracking'] ?? '');
$order    = null;
if ($tracking) {
    $found = get_posts([
        'post_type'      => 'raha_order',
        'posts_per_page' => 1,
        'meta_key'       => 'tracking_code',
        'meta_value'     => $tracking,
        'post_status'    => 'publish',
    ]);
    $order = $found[0] ?? null;
}

get_header();
?>
<div class="raha-thankyou">
    <div class="raha-container">

        <?php if (!$order) : ?>
            <div class="raha-thankyou__card">
                <div class="raha-thankyou__icon">🔍</div>
                <h1>سفارشی یافت نشد</h1>
                <p>کد پیگیری وارد شده معتبر نیست یا سفارش حذف شده است.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="raha-btn raha-btn--primary">بازگشت به خانه</a>
            </div>
        <?php else :
            $status  = get_post_meta($order->ID, 'order_status', true) ?: 'new';
            $service = get_post_meta($order->ID, 'service', true);
            $budget  = get_post_meta($order->ID, 'budget', true);
            $contact = get_post_meta($order->ID, 'contact_value', true);
            $method  = get_post_meta($order->ID, 'contact_method', true);
            $name    = get_post_meta($order->ID, 'full_name', true);
        ?>
            <div class="raha-thankyou__card">
                <div class="raha-thankyou__header">
                    <div class="raha-thankyou__icon">🎉</div>
                    <h1>درخواست شما با موفقیت ثبت شد!</h1>
                    <p>اطلاعات شما در سیستم ثبت شد و به‌زودی برای هماهنگی جلسه مشاوره با شما تماس می‌گیریم.</p>
                </div>

                <div class="raha-thankyou__tracking">
                    <span>کد پیگیری درخواست</span>
                    <strong id="trackingCode" dir="ltr"><?php echo esc_html($tracking); ?></strong>
                    <button type="button" class="raha-btn raha-btn--ghost" id="copyTracking">کپی</button>
                </div>

                <h2 class="raha-thankyou__section-title">خلاصه اطلاعات ثبت‌شده</h2>
                <div class="raha-thankyou__summary">
                    <div><span>نام</span><strong><?php echo esc_html($name); ?></strong></div>
                    <div><span>نوع خدمت</span><strong><?php echo esc_html($service); ?></strong></div>
                    <div><span>بودجه</span><strong><?php echo esc_html($budget); ?></strong></div>
                    <div><span>راه تماس</span><strong dir="ltr"><?php echo esc_html($contact); ?></strong></div>
                    <div><span>وضعیت</span><strong><?php echo esc_html(raha_status_label($status)); ?></strong></div>
                </div>

                <div class="raha-thankyou__actions">
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(raha_page_url('account')); ?>" class="raha-btn raha-btn--primary">مشاهده در پنل کاربری</a>
                    <?php endif; ?>
                    <button type="button" class="raha-btn raha-btn--ghost" onclick="window.print()">🖨️ چاپ</button>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="raha-btn raha-btn--ghost">بازگشت به خانه</a>
                </div>
            </div>

            <script>
            document.getElementById('copyTracking').addEventListener('click', function () {
                var code = document.getElementById('trackingCode').textContent.trim();
                navigator.clipboard.writeText(code).then(function () {
                    var b = document.getElementById('copyTracking');
                    var t = b.textContent;
                    b.textContent = 'کپی شد ✅';
                    setTimeout(function () { b.textContent = t; }, 1600);
                });
            });
            </script>
        <?php endif; ?>

    </div>
</div>
<?php get_footer();