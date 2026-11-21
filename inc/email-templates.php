<?php
if (!defined('ABSPATH')) exit;

function raha_email_base_wrap($title, $content) {
    $accent   = get_option('raha_email_accent_color', '#0c4a6e');
    $site     = get_bloginfo('name');
    $site_url = home_url('/');
    $logo_id  = (int) get_option('raha_email_logo');
    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
    $footer   = get_option('raha_email_footer_text', 'این ایمیل به‌صورت خودکار ارسال شده است.');

    ob_start();
    ?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head><meta charset="UTF-8"><title><?php echo esc_html($title); ?></title></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Tahoma,Arial,sans-serif;direction:rtl;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:24px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06);">
<tr><td style="background:<?php echo esc_attr($accent); ?>;padding:24px;text-align:center;">
<?php if ($logo_url) : ?><img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site); ?>" style="max-height:52px;display:inline-block;">
<?php else : ?><h1 style="margin:0;color:#fff;font-size:22px;"><?php echo esc_html($site); ?></h1><?php endif; ?>
</td></tr>
<tr><td style="padding:32px 28px;color:#333;font-size:14px;line-height:1.9;">
<h2 style="margin:0 0 20px;color:<?php echo esc_attr($accent); ?>;font-size:19px;font-weight:700;"><?php echo esc_html($title); ?></h2>
<?php echo $content; ?>
</td></tr>
<tr><td style="background:#f8f9fa;padding:20px 28px;text-align:center;font-size:12px;color:#888;border-top:1px solid #eaeaea;">
<p style="margin:0 0 8px;"><?php echo esc_html($footer); ?></p>
<a href="<?php echo esc_url($site_url); ?>" style="color:<?php echo esc_attr($accent); ?>;text-decoration:none;"><?php echo esc_html($site_url); ?></a>
</td></tr>
</table></td></tr></table></body></html>
    <?php
    return ob_get_clean();
}

function raha_email_btn($text, $url) {
    $accent = get_option('raha_email_accent_color', '#0c4a6e');
    return sprintf(
        '<table cellpadding="0" cellspacing="0" style="margin:24px auto;"><tr><td style="background:%s;border-radius:6px;"><a href="%s" style="display:inline-block;padding:12px 28px;color:#fff;text-decoration:none;font-weight:700;font-size:14px;">%s</a></td></tr></table>',
        esc_attr($accent), esc_url($url), esc_html($text)
    );
}

function raha_email_info_table($rows) {
    $html = '<table width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;background:#f8fafc;border-radius:6px;padding:4px 16px;">';
    foreach ($rows as $label => $value) {
        if ($value === '' || $value === null) continue;
        $html .= sprintf(
            '<tr><td style="padding:10px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:13px;width:40%%;">%s</td><td style="padding:10px 0;border-bottom:1px solid #e5e7eb;color:#111827;font-weight:700;font-size:13px;">%s</td></tr>',
            esc_html($label), esc_html($value)
        );
    }
    return $html . '</table>';
}

/* ---------- قالب‌ها ---------- */

function raha_email_template_order_admin($data) {
    $rows = [
        'کد پیگیری'    => $data['code']       ?? '',
        'نام مشتری'     => $data['name']       ?? '',
        'شرکت'          => $data['company']    ?? '',
        'شماره تماس'    => $data['contact']    ?? '',
        'روش تماس'      => $data['method']     ?? '',
        'نوع خدمت'      => $data['service']    ?? '',
        'هدف سایت'      => $data['purpose']    ?? '',
        'بودجه'         => $data['budget']     ?? '',
    ];
    $content  = '<p style="margin:0 0 16px;">سفارش جدید در سایت ثبت شد. جزئیات در ادامه:</p>';
    $content .= raha_email_info_table($rows);
    $content .= raha_email_btn('مشاهده در پیشخوان', $data['admin_url'] ?? admin_url('admin.php?page=raha-orders-panel'));
    return raha_email_base_wrap('سفارش جدید ثبت شد', $content);
}

function raha_email_template_order_customer($data) {
    $content  = sprintf('<p style="margin:0 0 16px;">سلام %s عزیز،</p>', esc_html($data['name'] ?? ''));
    $content .= '<p style="margin:0 0 16px;">درخواست شما با موفقیت ثبت شد. تیم ما به‌زودی برای هماهنگی با شما تماس خواهد گرفت.</p>';
    $content .= raha_email_info_table([
        'کد پیگیری' => $data['code']    ?? '',
        'نوع خدمت'  => $data['service'] ?? '',
        'وضعیت'     => 'در انتظار بررسی',
    ]);
    $content .= raha_email_btn('پیگیری سفارش', $data['tracking_url'] ?? home_url('/'));
    return raha_email_base_wrap('ثبت موفق درخواست شما', $content);
}

function raha_email_template_register_welcome($data) {
    $content  = sprintf('<p style="margin:0 0 16px;">سلام %s عزیز،</p>', esc_html($data['name'] ?? ''));
    $content .= '<p style="margin:0 0 16px;">به خانواده‌ی ما خوش آمدید! حساب کاربری شما با موفقیت ایجاد شد.</p>';
    $content .= '<p style="margin:0 0 16px;">از این پس می‌توانید سفارش‌های خود را پیگیری کنید، تیکت ارسال نمایید و به تمام امکانات دسترسی داشته باشید.</p>';
    $content .= raha_email_btn('ورود به پنل کاربری', $data['account_url'] ?? home_url('/'));
    return raha_email_base_wrap('خوش آمدید!', $content);
}

function raha_email_template_reset_password($data) {
    $content  = sprintf('<p style="margin:0 0 16px;">سلام %s عزیز،</p>', esc_html($data['name'] ?? ''));
    $content .= '<p style="margin:0 0 16px;">کد بازیابی رمز عبور شما:</p>';
    $content .= sprintf(
        '<div style="margin:24px 0;text-align:center;"><div style="display:inline-block;background:#fef08a;border:2px solid #ca8a04;border-radius:8px;padding:16px 32px;font-size:32px;font-weight:900;letter-spacing:8px;color:#14110f;direction:ltr;">%s</div></div>',
        esc_html($data['code'] ?? '')
    );
    $content .= '<p style="margin:0 0 16px;">این کد تا ۱۰ دقیقه معتبر است.</p>';
    $content .= '<p style="margin:0;color:#999;font-size:12px;">اگر شما درخواست بازیابی رمز نداده‌اید، این ایمیل را نادیده بگیرید.</p>';
    return raha_email_base_wrap('کد بازیابی رمز عبور', $content);
}

function raha_email_template_ticket_admin($data) {
    $content  = '<p style="margin:0 0 16px;">تیکت جدید در سایت ثبت شد:</p>';
    $content .= raha_email_info_table([
        'موضوع'    => $data['subject']  ?? '',
        'مشتری'    => $data['name']     ?? '',
        'اولویت'   => $data['priority'] ?? '',
    ]);
    $content .= sprintf('<div style="margin:16px 0;padding:16px;background:#f8fafc;border-right:3px solid #0c4a6e;border-radius:4px;">%s</div>', nl2br(esc_html($data['message'] ?? '')));
    $content .= raha_email_btn('مشاهده تیکت', $data['admin_url'] ?? admin_url('edit.php?post_type=raha_ticket'));
    return raha_email_base_wrap('تیکت پشتیبانی جدید', $content);
}

function raha_email_template_ticket_reply($data) {
    $content  = sprintf('<p style="margin:0 0 16px;">سلام %s عزیز،</p>', esc_html($data['name'] ?? ''));
    $content .= sprintf('<p style="margin:0 0 16px;">پاسخ تیم پشتیبانی به تیکت «%s»:</p>', esc_html($data['subject'] ?? ''));
    $content .= sprintf('<div style="margin:16px 0;padding:16px;background:#f0f9ff;border-right:3px solid #0c4a6e;border-radius:4px;">%s</div>', nl2br(esc_html($data['reply'] ?? '')));
    $content .= raha_email_btn('مشاهده و پاسخ', $data['ticket_url'] ?? home_url('/'));
    return raha_email_base_wrap('پاسخ تیم پشتیبانی', $content);
}

function raha_email_template_cta_admin($data) {
    $content  = '<p style="margin:0 0 16px;">یک درخواست تماس جدید از فرم صفحه اصلی ثبت شد:</p>';
    $content .= raha_email_info_table([
        'راه ارتباطی' => $data['contact'] ?? '',
        'منبع'         => $data['source']  ?? 'فرم CTA',
    ]);
    return raha_email_base_wrap('درخواست تماس جدید', $content);
}