<?php
/* Template Name: ورود */
if (!defined('ABSPATH')) exit;

if (is_user_logged_in()) {
    wp_safe_redirect(raha_page_url('account'));
    exit;
}

// redirect_to با اعتبارسنجی (جلوگیری از open redirect)
$redirect_to = isset($_GET['redirect_to'])
    ? wp_validate_redirect(wp_unslash($_GET['redirect_to']), raha_page_url('account'))
    : raha_page_url('account');

$register_url     = raha_page_url('register');
$lost_password    = wp_lostpassword_url();

get_header();
?>

<div class="raha-login-page">
  <div class="login-container">
    <div class="deco-tag" aria-hidden="true">Welcome Back</div>

    <section class="login-card" id="loginCard" aria-labelledby="loginTitle">
      <header class="login-header">
        <div class="login-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
          </svg>
        </div>
        <h1 class="login-title" id="loginTitle">ورود به حساب کاربری</h1>
        <p class="login-subtitle">برای پیگیری سفارشات و مشاهده تاریخچه خرید، وارد شوید.</p>
      </header>

      <!-- Global error -->
      <div class="alert-error" id="errorAlert" role="alert" aria-live="polite">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="errorAlertText"></span>
      </div>

      <form id="loginForm" novalidate autocomplete="on">

        <!-- نام کاربری / ایمیل -->
        <div class="field" data-field="username">
          <div class="field-control">
            <input type="text" id="username" class="field-input" placeholder=" "
                   autocomplete="username" spellcheck="false" autocapitalize="off"
                   aria-describedby="username-error" required>
            <label class="field-label" for="username">
              نام کاربری یا ایمیل <span class="req-mark">*</span>
            </label>
            <span class="field-icon field-icon-default" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <span class="field-icon field-icon-valid" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
          </div>
          <div class="field-error" id="username-error" role="alert"></div>
        </div>

        <!-- رمز عبور (فقط toggle، بدون آیکون قفل) -->
        <div class="field" data-field="password">
          <div class="field-control">
            <input type="password" id="password" class="field-input has-toggle" placeholder=" "
                   autocomplete="current-password" aria-describedby="password-error" required>
            <label class="field-label" for="password">
              رمز عبور <span class="req-mark">*</span>
            </label>
            <span class="caps-badge" id="capsBadge" aria-live="polite">Caps Lock</span>
            <button type="button" class="toggle-password" id="togglePassword" aria-label="نمایش رمز عبور">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="field-error" id="password-error" role="alert"></div>
        </div>

        <!-- گزینه‌ها -->
        <div class="form-options">
          <label class="checkbox-wrap">
            <input type="checkbox" id="rememberMe">
            <span class="checkbox-label">مرا به خاطر بسپار</span>
          </label>
          <a href="<?php echo esc_url($lost_password); ?>" class="forgot-link" rel="nofollow">فراموشی رمز عبور؟</a>
        </div>

        <!-- دکمه ورود -->
        <button type="submit" class="btn-login" id="submitBtn" aria-busy="false">
          <span id="submitText">ورود به حساب کاربری</span>
          <svg id="submitIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
          </svg>
        </button>

        <div class="rate-notice" id="rateNotice" role="status">
          <span id="rateNoticeText"></span>
        </div>

      </form>

      <footer class="login-footer">
        <span class="register-prompt">
          هنوز حساب کاربری ندارید؟
          <a href="<?php echo esc_url($register_url); ?>" class="register-link">ثبت‌نام کنید</a>
        </span>
      </footer>
    </section>
  </div>
</div>

<noscript>
  <div style="position:fixed; inset:0; background:#fee2e2; color:#7f1d1d; padding:24px; text-align:center; font-family:system-ui, sans-serif; z-index:99999;">
    برای ورود به حساب کاربری نیاز به فعال‌سازی JavaScript دارید.
  </div>
</noscript>

<?php get_footer(); ?>