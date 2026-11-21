<?php
if (!defined('ABSPATH')) exit;
$user = wp_get_current_user();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['_raha_profile_nonce'] ?? '', 'raha_profile')) {
    $name  = sanitize_text_field($_POST['display_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $pass  = $_POST['new_password'] ?? '';

    $update = ['ID' => $user->ID];
    if ($name) $update['display_name'] = $name;
    if ($email && is_email($email) && $email !== $user->user_email) {
        if (!email_exists($email)) $update['user_email'] = $email;
    }
    if ($pass && strlen($pass) >= 8) $update['user_pass'] = $pass;

    $result = wp_update_user($update);
    if (!is_wp_error($result)) $msg = 'تغییرات ذخیره شد.';
}
?>
<div class="raha-tab">
    <div class="raha-tab__head">
        <h1>پروفایل</h1>
        <p>اطلاعات شخصی خود را مدیریت کنید</p>
    </div>

    <?php if ($msg) : ?>
        <div class="raha-auth__alert" style="background:#dcfce7;border-color:#16a34a;color:#14532d;"><?php echo esc_html($msg); ?></div>
    <?php endif; ?>

    <form method="post" class="raha-profile-form">
        <?php wp_nonce_field('raha_profile', '_raha_profile_nonce'); ?>
        <label class="raha-field">
            <span class="raha-field__label">نام و نام خانوادگی</span>
            <input type="text" name="display_name" class="raha-field__input" value="<?php echo esc_attr($user->display_name); ?>">
        </label>
        <label class="raha-field">
            <span class="raha-field__label">ایمیل</span>
            <input type="email" name="email" class="raha-field__input" dir="ltr" value="<?php echo esc_attr($user->user_email); ?>">
        </label>
        <label class="raha-field">
            <span class="raha-field__label">رمز عبور جدید (اختیاری)</span>
            <input type="password" name="new_password" class="raha-field__input" autocomplete="new-password">
            <small>حداقل ۸ کاراکتر</small>
        </label>
        <button type="submit" class="raha-btn raha-btn--primary">ذخیره تغییرات</button>
    </form>
</div>