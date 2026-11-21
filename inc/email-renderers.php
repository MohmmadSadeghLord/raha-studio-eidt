<?php
if (!defined('ABSPATH')) exit;

/**
 * رندر تب لاگ ایمیل‌ها
 */
function raha_render_email_logs($page) {
    $type   = sanitize_key($_GET['log_type'] ?? '');
    $status = sanitize_key($_GET['log_status'] ?? '');
    $paged  = max(1, (int) ($_GET['paged'] ?? 1));
    $per    = 30;

    $logs  = raha_email_get_logs($per, $paged, $type, $status);
    $total = raha_email_count_logs($type, $status);
    $pages = ceil($total / $per);

    $types = [
        ''                 => 'همه انواع',
        'order_admin'      => 'سفارش (ادمین)',
        'order_customer'   => 'سفارش (مشتری)',
        'register_welcome' => 'خوش‌آمد ثبت‌نام',
        'reset_password'   => 'بازیابی رمز',
        'ticket_admin'     => 'تیکت (ادمین)',
        'ticket_reply'     => 'پاسخ تیکت',
        'cta_admin'        => 'CTA (ادمین)',
    ];
    $statuses = [
        ''       => 'همه وضعیت‌ها',
        'sent'   => 'ارسال شده',
        'failed' => 'ناموفق',
        'queued' => 'در صف',
    ];
    ?>
    <div class="raha-email-logs-wrap">
        <div class="raha-email-logs-filters">
            <form method="get">
                <input type="hidden" name="page" value="raha-options">
                <input type="hidden" name="tab" value="email">
                <select name="log_type">
                    <?php foreach ($types as $val => $lbl) : ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($type, $val); ?>><?php echo esc_html($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="log_status">
                    <?php foreach ($statuses as $val => $lbl) : ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($status, $val); ?>><?php echo esc_html($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button">فیلتر</button>
                <a href="<?php echo esc_url(admin_url('admin.php?page=raha-options&tab=email')); ?>" class="button">پاک کردن</a>
            </form>
            <span class="raha-email-logs-count">
                <?php echo number_format_i18n($total); ?> ایمیل
            </span>
        </div>

        <?php if (empty($logs)) : ?>
            <div style="padding: 40px; text-align: center; color: #999; background: #f8fafc; border-radius: 8px;">
                هیچ ایمیلی در لاگ وجود ندارد.
            </div>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped raha-email-logs-table">
                <thead>
                    <tr>
                        <th style="width:150px;">تاریخ</th>
                        <th style="width:140px;">نوع</th>
                        <th>گیرنده</th>
                        <th>موضوع</th>
                        <th style="width:90px;">وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : 
                        $status_color = ['sent' => '#16a34a', 'failed' => '#dc2626', 'queued' => '#f59e0b'][$log->status] ?? '#666';
                    ?>
                        <tr>
                            <td>
                                <span title="<?php echo esc_attr($log->created_at); ?>">
                                    <?php echo esc_html(mysql2date('Y/m/d H:i', $log->created_at)); ?>
                                </span>
                            </td>
                            <td><code style="font-size:11px;"><?php echo esc_html($log->type); ?></code></td>
                            <td style="font-size:12px;"><?php echo esc_html($log->recipient); ?></td>
                            <td>
                                <details>
                                    <summary style="cursor:pointer;"><?php echo esc_html($log->subject); ?></summary>
                                    <div style="margin-top:12px;padding:12px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;max-height:400px;overflow:auto;">
                                        <p><strong>Body:</strong></p>
                                        <iframe srcdoc="<?php echo esc_attr($log->body); ?>" style="width:100%;height:400px;border:1px solid #ccc;"></iframe>
                                        <?php if ($log->error_message) : ?>
                                            <p style="color:#dc2626;margin-top:12px;"><strong>خطا:</strong> <?php echo esc_html($log->error_message); ?></p>
                                        <?php endif; ?>
                                        <p style="margin-top:12px;color:#999;font-size:11px;">IP: <?php echo esc_html($log->ip); ?></p>
                                    </div>
                                </details>
                            </td>
                            <td>
                                <span style="display:inline-block;padding:3px 8px;border-radius:4px;background:<?php echo esc_attr($status_color); ?>;color:#fff;font-size:11px;font-weight:700;">
                                    <?php echo esc_html($log->status); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($pages > 1) : ?>
                <div class="tablenav"><div class="tablenav-pages">
                    <?php
                    echo paginate_links([
                        'base'      => add_query_arg('paged', '%#%'),
                        'format'    => '',
                        'current'   => $paged,
                        'total'     => $pages,
                        'prev_text' => 'قبلی',
                        'next_text' => 'بعدی',
                    ]);
                    ?>
                </div></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <style>
        .raha-email-logs-wrap { padding: 8px 0; }
        .raha-email-logs-filters { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px; }
        .raha-email-logs-filters form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
        .raha-email-logs-count { font-weight:700; color:#0c4a6e; }
        .raha-email-logs-table { font-family: inherit; }
        .raha-email-logs-table th { font-weight:800; color:#14110f; }
    </style>
    <?php
}

/**
 * رندر تب تست ایمیل
 */
function raha_render_email_test($page) {
    $result = null;
    if (isset($_GET['test_result'])) {
        $result = sanitize_key($_GET['test_result']);
    }
    ?>
    <div class="raha-email-test-wrap">
        <?php if ($result === 'success') : ?>
            <div class="notice notice-success"><p>✅ ایمیل تست با موفقیت ارسال شد. لاگ آن را در تب «لاگ ارسال» ببینید.</p></div>
        <?php elseif ($result === 'failed') : ?>
            <div class="notice notice-error"><p>❌ ارسال ایمیل ناموفق بود. جزئیات خطا در تب «لاگ ارسال» موجود است.</p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="raha-email-test-form">
            <input type="hidden" name="action" value="raha_send_test_email">
            <?php wp_nonce_field('raha_send_test_email', '_test_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="test_email">ایمیل گیرنده</label></th>
                    <td>
                        <input type="email" name="test_email" id="test_email"
                               value="<?php echo esc_attr(get_option('admin_email')); ?>"
                               class="regular-text" required>
                        <p class="description">ایمیلی که ایمیل تست به آن ارسال می‌شود.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="test_type">نوع ایمیل</label></th>
                    <td>
                        <select name="test_type" id="test_type" class="regular-text">
                            <option value="order_customer">سفارش (به مشتری)</option>
                            <option value="order_admin">سفارش (به ادمین)</option>
                            <option value="register_welcome">خوش‌آمد ثبت‌نام</option>
                            <option value="reset_password">بازیابی رمز</option>
                            <option value="ticket_admin">تیکت (ادمین)</option>
                            <option value="ticket_reply">پاسخ تیکت</option>
                            <option value="cta_admin">CTA (ادمین)</option>
                        </select>
                        <p class="description">قالب انتخابی با داده‌های آزمایشی ارسال می‌شود.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('ارسال ایمیل تست', 'primary', 'submit', false); ?>
        </form>

        <div style="margin-top:32px;padding:16px;background:#f0f9ff;border-right:4px solid #0c4a6e;border-radius:6px;">
            <strong>💡 نکته:</strong> اگر ایمیل تست ارسال نمی‌شود، ابتدا تب «تنظیمات SMTP» را بررسی کنید و از تنظیمات درست مطمئن شوید.
        </div>
    </div>
    <?php
}

/**
 * پردازش ارسال ایمیل تست
 */
add_action('admin_post_raha_send_test_email', function () {
    if (!current_user_can('manage_options')) wp_die('دسترسی ندارید');
    if (!wp_verify_nonce($_POST['_test_nonce'] ?? '', 'raha_send_test_email')) wp_die('Nonce failed');

    $to   = sanitize_email($_POST['test_email'] ?? '');
    $type = sanitize_key($_POST['test_type'] ?? 'order_customer');

    $sample_data = [
        'code'         => 'TEST-' . wp_date('ymd') . '-DEMO',
        'name'         => 'کاربر آزمایشی',
        'company'      => 'شرکت نمونه',
        'contact'      => '09123456789',
        'method'       => 'تلفن',
        'service'      => 'طراحی سایت جدید',
        'purpose'      => 'معرفی شرکت',
        'budget'       => '۱۰ تا ۳۰ میلیون',
        'subject'      => 'سوال درباره محصولات',
        'priority'     => 'normal',
        'message'      => 'این یک پیام آزمایشی برای تست قالب تیکت است.',
        'reply'        => 'این یک پاسخ آزمایشی است که توسط تیم پشتیبانی ارسال می‌شود.',
        'tracking_url' => home_url('/thank-you/?tracking=TEST-00000-DEMO'),
        'account_url'  => function_exists('raha_page_url') ? raha_page_url('account') : home_url('/'),
        'admin_url'    => admin_url('admin.php?page=raha-orders-panel'),
        'ticket_url'   => function_exists('raha_page_url') ? raha_page_url('account') . '?tab=tickets' : home_url('/'),
        'source'       => 'فرم CTA',
    ];

    $sent = Raha_Email::send($type, $to, $sample_data, ['skip_enabled_check' => true]);

    $status = $sent ? 'success' : 'failed';
    wp_safe_redirect(add_query_arg([
        'page'        => 'raha-options',
        'tab'         => 'email',
        'test_result' => $status,
    ], admin_url('admin.php')));
    exit;
});