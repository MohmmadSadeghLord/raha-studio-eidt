<?php
if (!defined('ABSPATH')) exit;
$orders = raha_get_user_orders();
$rows = [];
foreach ($orders as $o) {
    $amount = (int) get_post_meta($o->ID, 'amount', true);
    if (!$amount) continue;
    $rows[] = [
        'code'   => get_post_meta($o->ID, 'tracking_code', true),
        'title'  => get_post_meta($o->ID, 'service', true),
        'amount' => $amount,
        'paid'   => (int) get_post_meta($o->ID, 'amount_paid', true),
        'date'   => get_the_date('', $o),
        'status' => get_post_meta($o->ID, 'order_status', true) ?: 'new',
    ];
}
?>
<div class="raha-tab">
    <div class="raha-tab__head">
        <h1>فاکتورها و پرداخت‌ها</h1>
        <p>سابقه مالی سفارش‌ها</p>
    </div>

    <?php if (empty($rows)) : ?>
        <div class="raha-empty">
            <div class="raha-empty__icon">💳</div>
            <h2>هنوز فاکتوری صادر نشده</h2>
            <p>پس از برآورد پروژه توسط تیم، فاکتورها اینجا نمایش داده می‌شوند.</p>
        </div>
    <?php else : ?>
        <div class="raha-table-wrap">
            <table class="raha-table">
                <thead>
                    <tr>
                        <th>کد سفارش</th>
                        <th>بابت</th>
                        <th>مبلغ</th>
                        <th>پرداخت‌شده</th>
                        <th>باقی‌مانده</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r) :
                        $remaining = $r['amount'] - $r['paid'];
                        $payment_status = $remaining <= 0 ? 'پرداخت کامل' : ($r['paid'] > 0 ? 'پرداخت جزئی' : 'پرداخت‌نشده');
                    ?>
                        <tr>
                            <td><strong dir="ltr"><?php echo esc_html($r['code']); ?></strong></td>
                            <td><?php echo esc_html($r['title']); ?></td>
                            <td><?php echo esc_html(raha_money($r['amount'])); ?> تومان</td>
                            <td><?php echo esc_html(raha_money($r['paid'])); ?> تومان</td>
                            <td><?php echo esc_html(raha_money($remaining)); ?> تومان</td>
                            <td><span class="raha-badge raha-badge--<?php echo esc_attr($r['status']); ?>"><?php echo esc_html($payment_status); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>