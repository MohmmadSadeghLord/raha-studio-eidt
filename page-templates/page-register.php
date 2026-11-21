<?php
/* Template Name: ثبت‌نام */
if (!defined('ABSPATH')) exit;

// اگر کاربر لاگین است، به پنل برود
if (is_user_logged_in()) {
    wp_safe_redirect(raha_page_url('account'));
    exit;
}

// لینک صفحات قوانین و حریم خصوصی (اگر وجود داشته باشند)
$terms_page   = get_page_by_path('terms');
$privacy_page = get_page_by_path('privacy');
$terms_url    = $terms_page   ? get_permalink($terms_page)   : '#';
$privacy_url  = $privacy_page ? get_permalink($privacy_page) : '#';

get_header();
?>

<div class="raha-register-page">
  <div class="register-container">
    <div class="deco-tag" aria-hidden="true">New User</div>

    <div class="register-card" id="registerCard">

      <!-- Progress bar -->
      <div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="پیشرفت تکمیل فرم">
        <div class="progress-fill" id="progressFill"></div>
      </div>

      <!-- Header -->
      <div class="register-header" id="registerHeader">
        <div class="register-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="22" y1="11" x2="16" y2="11"/>
          </svg>
        </div>
        <h1 class="register-title">ایجاد حساب کاربری</h1>
        <p class="register-subtitle">برای پیگیری سفارش‌ها و دسترسی به امکانات ویژه، ثبت‌نام کنید.</p>
      </div>

      <!-- Global alert -->
      <div class="alert-error" id="errorAlert" role="alert">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="errorAlertText"></span>
      </div>

      <!-- ==================== FORM ==================== -->
      <form id="registerForm" novalidate>

        <!-- Honeypot (ضد اسپم - برای کاربر پنهان) -->
        <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
          <label>وب‌سایت
            <input type="text" name="website_url" value="" tabindex="-1" autocomplete="off">
          </label>
        </div>

        <div class="form-columns">

          <!-- ستون راست -->
          <div class="form-col">

            <div class="field" data-field="name">
              <div class="field-control">
                <input type="text" id="name" name="display_name" class="field-input" placeholder=" " autocomplete="name" required>
                <label class="field-label" for="name">نام و نام خانوادگی</label>
                <span class="field-icon field-icon-default" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <span class="field-icon field-icon-valid" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
              </div>
              <div class="field-error" id="name-error" role="alert"></div>
            </div>

            <div class="field" data-field="phone">
              <div class="field-control">
                <input type="tel" id="phone" name="phone" class="field-input" placeholder=" " dir="ltr" inputmode="numeric" autocomplete="tel" maxlength="11" required>
                <label class="field-label" for="phone">شماره تماس <span class="req-mark">*</span></label>
                <span class="field-icon field-icon-default" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 16.9z"/></svg>
                </span>
                <span class="field-icon field-icon-valid" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
              </div>
              <div class="field-error" id="phone-error" role="alert"></div>
            </div>

            <div class="field" data-field="email">
              <div class="field-control">
                <input type="email" id="email" name="email" class="field-input" placeholder=" " dir="ltr" autocomplete="email" required>
                <label class="field-label" for="email">آدرس ایمیل</label>
                <span class="field-icon field-icon-default" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                </span>
                <span class="field-icon field-icon-valid" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
              </div>
              <div class="field-error" id="email-error" role="alert"></div>
            </div>

          </div>

          <!-- ستون چپ -->
          <div class="form-col">

            <div class="field" data-field="password">
              <div class="field-control">
                <input type="password" id="password" name="password" class="field-input has-toggle" placeholder=" " autocomplete="new-password" required>
                <label class="field-label" for="password">رمز عبور</label>
                <span class="field-icon field-icon-default" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <span class="field-icon field-icon-valid" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <button type="button" class="toggle-password" data-target="password" aria-label="نمایش رمز عبور">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="password-strength" id="passwordStrength" data-strength="0">
                <div class="password-strength-bar"></div>
                <div class="password-strength-bar"></div>
                <div class="password-strength-bar"></div>
                <div class="password-strength-bar"></div>
              </div>
              <div class="password-strength-label" id="passwordStrengthLabel"></div>
              <div class="field-error" id="password-error" role="alert"></div>
            </div>

            <div class="field" data-field="confirmPassword">
              <div class="field-control">
                <input type="password" id="confirmPassword" class="field-input has-toggle" placeholder=" " autocomplete="new-password" required>
                <label class="field-label" for="confirmPassword">تکرار رمز عبور</label>
                <span class="field-icon field-icon-default" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="m9 16 2 2 4-4"/></svg>
                </span>
                <span class="field-icon field-icon-valid" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <button type="button" class="toggle-password" data-target="confirmPassword" aria-label="نمایش رمز عبور">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="field-error" id="confirmPassword-error" role="alert"></div>
            </div>

            <label class="terms-wrap" id="termsWrap">
              <input type="checkbox" id="terms" name="terms" required>
              <span class="terms-label">
                <a href="<?php echo esc_url($terms_url); ?>" target="_blank" rel="noopener">قوانین و مقررات</a> و <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">حریم خصوصی</a> سایت را خوانده‌ام و می‌پذیرم.
              </span>
            </label>

          </div>

        </div>

        <button type="submit" class="btn-register" id="submitBtn">
          <span id="submitText">ثبت‌نام</span>
          <svg id="submitIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
          </svg>
        </button>

      </form>

      <!-- Success -->
      <div class="success-screen" id="successScreen">
        <div class="success-check-wrap">
          <svg class="success-check" viewBox="0 0 52 52">
            <circle cx="26" cy="26" r="24"/>
            <path d="M14 27l8 8 16-16"/>
          </svg>
        </div>
        <h2 class="success-title">ثبت‌نام با موفقیت انجام شد!</h2>
        <p class="success-desc">حساب کاربری شما ساخته شد. حالا می‌توانید وارد شوید و سفارش‌های خود را پیگیری کنید.</p>
        <a href="<?php echo esc_url(raha_page_url('login')); ?>" class="btn-login-now">
          ورود به حساب کاربری
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        </a>
      </div>

      <!-- Footer -->
      <div class="register-footer" id="registerFooter">
        <span class="login-link">
          قبلاً حساب ساخته‌اید؟ <a href="<?php echo esc_url(raha_page_url('login')); ?>">وارد شوید</a>
        </span>
      </div>

    </div>
  </div>
</div>

<noscript>
  <div style="position:fixed; inset:0; background:#fee2e2; color:#7f1d1d; padding:24px; text-align:center; font-family:system-ui, sans-serif; z-index:99999;">
    برای ثبت‌نام نیاز به فعال‌سازی JavaScript دارید.
  </div>
</noscript>

<?php get_footer(); ?>