<?php
/* Template Name: بازیابی رمز عبور */
if (!defined('ABSPATH')) exit;

// اگر کاربر لاگین است، به پنل برود
if (is_user_logged_in()) {
    wp_safe_redirect(raha_page_url('account'));
    exit;
}

// redirect_to با اعتبارسنجی
$redirect_to = isset($_GET['redirect_to'])
    ? wp_validate_redirect(wp_unslash($_GET['redirect_to']), '')
    : '';

$login_url = raha_page_url('login');
if ($redirect_to) {
    $login_url = add_query_arg('redirect_to', urlencode($redirect_to), $login_url);
}

get_header();
?>

<div class="raha-forgot-page" data-redirect="<?php echo esc_attr($redirect_to); ?>">

  <!-- Toast (برای گزینه‌های غیرفعال) -->
  <div class="raha-toast" id="rahaToast" role="status" aria-live="assertive">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="10"/>
      <line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span id="rahaToastText"></span>
  </div>

  <div class="forgot-container">
    <div class="deco-tag" aria-hidden="true">Reset Password</div>

    <section class="forgot-card" id="forgotCard" aria-labelledby="forgotTitle">
      <header class="forgot-header">
        <div class="forgot-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="7.5" cy="15.5" r="5.5"/>
            <path d="m21 2-9.6 9.6"/>
            <path d="m15.5 7.5 3 3L22 7l-3-3"/>
          </svg>
        </div>
        <h1 class="forgot-title" id="forgotTitle">بازیابی رمز عبور</h1>
        <p class="forgot-subtitle" id="stepSubtitle">ایمیل خود را وارد کنید تا کد بازیابی ارسال شود.</p>
      </header>

      <!-- Step indicator -->
      <div class="steps" aria-hidden="true">
        <div class="step-node active" data-step="1">
          <span class="step-dot">1</span>
          <span class="step-label">ایمیل</span>
        </div>
        <div class="step-line" data-line="1"></div>
        <div class="step-node" data-step="2">
          <span class="step-dot">2</span>
          <span class="step-label">کد تأیید</span>
        </div>
        <div class="step-line" data-line="2"></div>
        <div class="step-node" data-step="3">
          <span class="step-dot">3</span>
          <span class="step-label">رمز جدید</span>
        </div>
      </div>

      <!-- Global alert -->
      <div class="alert-error" id="errorAlert" role="alert" aria-live="polite">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span id="errorAlertText"></span>
      </div>

      <!-- ==================== STEP 1 ==================== -->
      <div class="wizard-step active" data-step-content="1">
        <div class="method-tabs" role="tablist" aria-label="روش دریافت کد">
          <button type="button" class="method-tab active" data-method="email" role="tab" aria-selected="true">ایمیل</button>
          <button type="button" class="method-tab is-disabled" data-method="phone" role="tab" aria-selected="false" aria-disabled="true" tabindex="0">
            موبایل
            <span class="method-badge">به‌زودی</span>
          </button>
        </div>

        <div class="field" data-field="destination">
          <div class="field-control">
            <input type="email" id="destination" class="field-input" placeholder=" "
                   autocomplete="email" spellcheck="false" autocapitalize="off" dir="ltr"
                   aria-describedby="destination-error" required>
            <label class="field-label" for="destination" id="destinationLabel">
              آدرس ایمیل <span class="req-mark">*</span>
            </label>
            <span class="field-icon" aria-hidden="true">
              <svg id="destinationIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="2"/>
                <path d="m3 7 9 6 9-6"/>
              </svg>
            </span>
          </div>
          <div class="field-error" id="destination-error" role="alert"></div>
        </div>

        <button type="button" class="btn-primary" id="step1Btn">
          <span id="step1Text">ارسال کد بازیابی</span>
          <svg id="step1Icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
          </svg>
        </button>
      </div>

      <!-- ==================== STEP 2 — OTP ==================== -->
      <div class="wizard-step" data-step-content="2">
        <div class="otp-wrap" id="otpWrap">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="0" aria-label="رقم ۱" autocomplete="one-time-code">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="1" aria-label="رقم ۲">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="2" aria-label="رقم ۳">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="3" aria-label="رقم ۴">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="4" aria-label="رقم ۵">
          <input type="text" class="otp-digit" inputmode="numeric" maxlength="1" data-index="5" aria-label="رقم ۶">
        </div>

        <div class="otp-status" id="otpStatus" role="status" aria-live="polite"></div>

        <div class="otp-info">
          <span class="otp-destination">
            ارسال به: <strong id="otpDestination">—</strong>
          </span>
          <button type="button" class="resend-btn" id="resendBtn" disabled>
            ارسال مجدد تا ۶۰ ثانیه
          </button>
        </div>

        <button type="button" class="btn-primary" id="step2Btn">
          <span id="step2Text">تأیید کد</span>
          <svg id="step2Icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
          </svg>
        </button>

        <div class="actions-row">
          <button type="button" class="btn-secondary" id="backToStep1">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
            تغییر ایمیل
          </button>
        </div>
      </div>

      <!-- ==================== STEP 3 ==================== -->
      <div class="wizard-step" data-step-content="3">
        <div class="field" data-field="newPassword">
          <div class="field-control">
            <input type="password" id="newPassword" class="field-input has-toggle" placeholder=" "
                   autocomplete="new-password" aria-describedby="newPassword-error" required>
            <label class="field-label" for="newPassword">رمز عبور جدید</label>
            <button type="button" class="toggle-password" data-target="newPassword" aria-label="نمایش رمز عبور">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="password-strength" id="passwordStrength" data-strength="0">
            <div class="password-strength-bar"></div>
            <div class="password-strength-bar"></div>
            <div class="password-strength-bar"></div>
            <div class="password-strength-bar"></div>
          </div>
          <div class="password-strength-label" id="passwordStrengthLabel"></div>
          <div class="field-error" id="newPassword-error" role="alert"></div>
        </div>

        <div class="field" data-field="confirmPassword">
          <div class="field-control">
            <input type="password" id="confirmPassword" class="field-input has-toggle" placeholder=" "
                   autocomplete="new-password" aria-describedby="confirmPassword-error" required>
            <label class="field-label" for="confirmPassword">تکرار رمز عبور جدید</label>
            <button type="button" class="toggle-password" data-target="confirmPassword" aria-label="نمایش رمز عبور">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="field-error" id="confirmPassword-error" role="alert"></div>
        </div>

        <button type="button" class="btn-primary" id="step3Btn">
          <span id="step3Text">ذخیره رمز جدید</span>
          <svg id="step3Icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/>
            <path d="m12 19-7-7 7-7"/>
          </svg>
        </button>
      </div>

      <!-- ==================== SUCCESS ==================== -->
      <div class="success-screen" id="successScreen">
        <div class="success-check-wrap">
          <svg class="success-check" viewBox="0 0 52 52">
            <circle cx="26" cy="26" r="24"/>
            <path d="M14 27l8 8 16-16"/>
          </svg>
        </div>
        <h2 class="success-title">رمز عبور با موفقیت تغییر کرد!</h2>
        <p class="success-desc">حالا می‌توانید با رمز جدید وارد حساب کاربری خود شوید.</p>
        <a href="<?php echo esc_url($login_url); ?>" class="btn-login-now">
          ورود به حساب
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
        </a>
      </div>

      <footer class="forgot-footer" id="forgotFooter">
        <a href="<?php echo esc_url($login_url); ?>" class="back-login">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14"/>
            <path d="m12 5 7 7-7 7"/>
          </svg>
          بازگشت به صفحه ورود
        </a>
      </footer>
    </section>
  </div>
</div>

<noscript>
  <div style="position:fixed; inset:0; background:#fee2e2; color:#7f1d1d; padding:24px; text-align:center; font-family:system-ui, sans-serif; z-index:99999;">
    برای بازیابی رمز عبور نیاز به فعال‌سازی JavaScript دارید.
  </div>
</noscript>

<?php get_footer(); ?>