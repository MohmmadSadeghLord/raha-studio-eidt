(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.RahaRegisterConfig || {};
    if (!document.getElementById('registerForm')) return;

    /* ============================================================
       1) REGEX & DIGIT MAPS
       ============================================================ */
    const RE = {
      name:        /^[\u0600-\u06FFa-zA-Z\s]+$/,
      email:       /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
      phone:       /^09\d{9}$/,
      hasLetter:   /[a-zA-Z]/,
      hasDigit:    /\d/,
      hasUpper:    /[A-Z]/,
      hasLower:    /[a-z]/,
      hasSpecial:  /[^a-zA-Z0-9]/,
      arabicDigit: /[٠-٩]/g,
      nonDigit:    /[^\u06F0-\u06F90-9]/g,
      persianOrAr: /[۰-۹٠-٩]/g,
    };

    const FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
    const AR_DIGITS = '٠١٢٣٤٥٦٧٨٩';
    const AR_TO_FA = Object.create(null);
    const DIGIT_TO_EN = Object.create(null);
    for (let i = 0; i < 10; i++) {
      AR_TO_FA[AR_DIGITS[i]] = FA_DIGITS[i];
      DIGIT_TO_EN[FA_DIGITS[i]] = String(i);
      DIGIT_TO_EN[AR_DIGITS[i]] = String(i);
    }

    /* ============================================================
       2) DOM CACHE
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const els = {
      card:                  $('registerCard'),
      form:                  $('registerForm'),
      name:                  $('name'),
      phone:                 $('phone'),
      email:                 $('email'),
      password:              $('password'),
      confirmPassword:       $('confirmPassword'),
      terms:                 $('terms'),
      termsWrap:             $('termsWrap'),
      errorAlert:            $('errorAlert'),
      errorAlertText:        $('errorAlertText'),
      submitBtn:             $('submitBtn'),
      submitText:            $('submitText'),
      submitIcon:            $('submitIcon'),
      progressFill:          $('progressFill'),
      progressTrack:         document.querySelector('.raha-register-page .progress-track'),
      passwordStrength:      $('passwordStrength'),
      passwordStrengthLabel: $('passwordStrengthLabel'),
      successScreen:         $('successScreen'),
      successCheck:          document.querySelector('.raha-register-page .success-check'),
    };

    const fieldMap = new WeakMap();
    const errMap = new WeakMap();
    [els.name, els.phone, els.email, els.password, els.confirmPassword].forEach(input => {
      const field = input.closest('.field');
      fieldMap.set(input, field);
      errMap.set(input, field.querySelector('.field-error'));
    });

    /* ============================================================
       3) STATE
       ============================================================ */
    const state = {
      name: false,
      phone: false,
      email: false,
      password: false,
      confirmPassword: false,
      terms: false,
    };

    /* ============================================================
       4) VALIDATORS
       ============================================================ */
    function validateName(v) {
      const t = v.trim();
      if (!t) return 'نام و نام خانوادگی را وارد کنید.';
      if (t.length < 3) return 'نام باید حداقل ۳ کاراکتر باشد.';
      if (!RE.name.test(t)) return 'فقط حروف و فاصله مجاز است.';
      return null;
    }
    function validateEmail(v) {
      const t = v.trim();
      if (!t) return 'آدرس ایمیل را وارد کنید.';
      if (!RE.email.test(t)) return 'فرمت ایمیل معتبر نیست.';
      return null;
    }
    function validatePhone(v) {
      if (!v) return 'شماره تماس را وارد کنید.';
      if (!RE.phone.test(v)) return 'شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.';
      return null;
    }
    function validatePassword(v) {
      if (!v) return 'رمز عبور را وارد کنید.';
      if (v.length < 8) return 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
      if (!RE.hasLetter.test(v) || !RE.hasDigit.test(v)) return 'رمز باید شامل حرف و عدد باشد.';
      return null;
    }
    function validateConfirm(v) {
      if (!v) return 'تکرار رمز عبور را وارد کنید.';
      if (v !== els.password.value) return 'رمزهای عبور یکسان نیستند.';
      return null;
    }

    /* ============================================================
       5) FIELD STATE UPDATER
       ============================================================ */
    function updateFieldState(input, key, validator, force) {
      const error = validator(input.value);
      const isValid = error === null;
      const errEl = errMap.get(input);

      if (!force && state[key] === isValid && errEl.textContent === (error || '')) {
        return isValid;
      }
      state[key] = isValid;
      const field = fieldMap.get(input);

      if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        errEl.textContent = '';
        input.setAttribute('aria-invalid', 'false');
      } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        errEl.textContent = error;
        input.setAttribute('aria-invalid', 'true');
      }
      return isValid;
    }

    function clearFieldVisual(input, key) {
      fieldMap.get(input).classList.remove('is-valid', 'is-invalid');
      errMap.get(input).textContent = '';
      input.removeAttribute('aria-invalid');
      state[key] = false;
    }

    /* ============================================================
       6) PROGRESS
       ============================================================ */
    let progressScheduled = false;
    function scheduleProgressUpdate() {
      if (progressScheduled) return;
      progressScheduled = true;
      requestAnimationFrame(() => {
        progressScheduled = false;
        const checks = [state.name, state.phone, state.email, state.password, state.confirmPassword, state.terms];
        let done = 0;
        for (let i = 0; i < checks.length; i++) if (checks[i]) done++;
        const pct = Math.round((done / checks.length) * 100);
        els.progressFill.style.width = pct + '%';
        els.progressTrack.setAttribute('aria-valuenow', String(pct));
      });
    }

    /* ============================================================
       7) PASSWORD STRENGTH
       ============================================================ */
    const STRENGTH_LABELS = ['', 'ضعیف', 'متوسط', 'خوب', 'عالی'];
    const STRENGTH_MAP = [1, 1, 2, 3, 4, 4];
    let lastStrength = 0;

    function calcStrength(pw) {
      if (!pw) return 0;
      let score = 0;
      if (pw.length >= 8) score++;
      if (pw.length >= 12) score++;
      if (RE.hasUpper.test(pw) && RE.hasLower.test(pw)) score++;
      if (RE.hasDigit.test(pw)) score++;
      if (RE.hasSpecial.test(pw)) score++;
      return STRENGTH_MAP[score];
    }

    function updateStrength() {
      const s = calcStrength(els.password.value);
      if (s === lastStrength) return;
      lastStrength = s;
      const sStr = String(s);
      els.passwordStrength.setAttribute('data-strength', sStr);
      els.passwordStrengthLabel.setAttribute('data-strength', sStr);
      els.passwordStrengthLabel.textContent = s ? STRENGTH_LABELS[s] : '';
    }

    /* ============================================================
       8) GLOBAL ERROR
       ============================================================ */
    function showGlobalError(msg) {
      if (els.errorAlertText.textContent === msg && els.errorAlert.classList.contains('visible')) return;
      els.errorAlertText.textContent = msg;
      els.errorAlert.classList.add('visible');
      els.errorAlert.style.animation = 'none';
      void els.errorAlert.offsetHeight;
      els.errorAlert.style.animation = '';
    }
    function clearGlobalError() {
      if (els.errorAlert.classList.contains('visible')) {
        els.errorAlert.classList.remove('visible');
      }
    }

    /* ============================================================
       9) PHONE NORMALIZER
       ============================================================ */
    function normalizePhone() {
      const val = els.phone.value;
      if (!val) return;
      if (!RE.nonDigit.test(val) && val.length <= 11) return;
      let v = val.replace(RE.arabicDigit, ch => AR_TO_FA[ch]);
      v = v.replace(RE.nonDigit, '').slice(0, 11);
      if (v !== val) els.phone.value = v;
    }
    function toEnglishDigits(str) {
      return str.replace(RE.persianOrAr, ch => DIGIT_TO_EN[ch]);
    }

    /* ============================================================
       10) INPUT CONFIG
       ============================================================ */
    const inputConfig = new Map([
      [els.name,            { key: 'name',            validator: validateName }],
      [els.phone,           { key: 'phone',           validator: v => validatePhone(toEnglishDigits(v)) }],
      [els.email,           { key: 'email',           validator: validateEmail }],
      [els.password,        { key: 'password',        validator: validatePassword }],
      [els.confirmPassword, { key: 'confirmPassword', validator: validateConfirm }],
    ]);

    /* ============================================================
       11) EVENT DELEGATION
       ============================================================ */
    function onFormInput(e) {
      const input = e.target;
      const config = inputConfig.get(input);
      if (!config) return;

      if (input === els.phone) normalizePhone();

      const field = fieldMap.get(input);
      const isTouched = field.classList.contains('is-valid') || field.classList.contains('is-invalid');
      const isEmpty = input.value.trim() === '';

      if (isEmpty) {
        clearFieldVisual(input, config.key);
      } else if (isTouched) {
        updateFieldState(input, config.key, config.validator);
      } else {
        state[config.key] = config.validator(input.value) === null;
      }

      if (input === els.password) {
        updateStrength();
        if (els.confirmPassword.value) {
          updateFieldState(els.confirmPassword, 'confirmPassword', validateConfirm);
        }
      }

      scheduleProgressUpdate();
      clearGlobalError();
    }

    function onFormFocusOut(e) {
      const input = e.target;
      const config = inputConfig.get(input);
      if (!config) return;
      if (input.value.trim() !== '') {
        updateFieldState(input, config.key, config.validator);
      }
      scheduleProgressUpdate();
    }

    els.form.addEventListener('input',    onFormInput,    { passive: true });
    els.form.addEventListener('focusout', onFormFocusOut, { passive: true });

    /* تغییر تیک قوانین */
    els.form.addEventListener('change', (e) => {
      if (e.target !== els.terms) return;
      state.terms = els.terms.checked;
      els.termsWrap.classList.toggle('is-invalid', !state.terms);
      scheduleProgressUpdate();
      clearGlobalError();
    });

    /* تاگل رمز عبور */
    const EYE_ON  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    const EYE_OFF = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    els.form.addEventListener('click', (e) => {
      const btn = e.target.closest('.toggle-password');
      if (!btn) return;
      const input = $(btn.dataset.target);
      if (!input) return;
      const isPw = input.type === 'password';
      input.type = isPw ? 'text' : 'password';
      btn.setAttribute('aria-label', isPw ? 'مخفی کردن رمز عبور' : 'نمایش رمز عبور');
      btn.firstElementChild.innerHTML = isPw ? EYE_OFF : EYE_ON;
    });

    /* ============================================================
       12) SUBMIT — با fetch واقعی به WordPress AJAX
       ============================================================ */
    function resetButton() {
      els.submitBtn.classList.remove('is-loading');
      els.submitBtn.disabled = false;
      els.submitText.textContent = 'ثبت‌نام';
      els.submitIcon.innerHTML = '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>';
    }

    function showSuccess() {
      els.card.classList.add('is-success');
      els.successScreen.classList.add('visible');

      const circle = els.successCheck.querySelector('circle');
      const path   = els.successCheck.querySelector('path');
      circle.style.animation = 'none';
      path.style.animation   = 'none';
      void els.successCheck.offsetWidth;
      circle.style.animation = '';
      path.style.animation   = '';

      els.successScreen.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    els.form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (els.submitBtn.classList.contains('is-loading')) return;
      clearGlobalError();

      updateFieldState(els.name,            'name',            validateName,           true);
      updateFieldState(els.phone,           'phone',           v => validatePhone(toEnglishDigits(v)), true);
      updateFieldState(els.email,           'email',           validateEmail,          true);
      updateFieldState(els.password,        'password',        validatePassword,       true);
      updateFieldState(els.confirmPassword, 'confirmPassword', validateConfirm,        true);

      state.terms = els.terms.checked;
      els.termsWrap.classList.toggle('is-invalid', !state.terms);

      const allValid = state.name && state.phone && state.email
                    && state.password && state.confirmPassword && state.terms;

      if (!allValid) {
        showGlobalError('لطفاً موارد مشخص‌شده را بررسی و اصلاح کنید.');
        const firstInvalid = els.form.querySelector('.field.is-invalid .field-input');
        if (firstInvalid) {
          firstInvalid.focus({ preventScroll: true });
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
      }

      els.submitBtn.classList.add('is-loading');
      els.submitBtn.disabled = true;
      els.submitText.textContent = 'در حال ایجاد حساب...';
      els.submitIcon.innerHTML = '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>';

      const formData = new FormData();
      formData.append('action',        cfg.action || 'raha_register');
      formData.append('nonce',         cfg.nonce  || '');
      formData.append('display_name',  els.name.value.trim());
      formData.append('phone',         toEnglishDigits(els.phone.value.trim()));
      formData.append('email',         els.email.value.trim().toLowerCase());
      formData.append('password',      els.password.value);
      formData.append('terms',         els.terms.checked ? '1' : '0');
      // honeypot (باید خالی باشد)
      const honeypot = els.form.querySelector('input[name="website_url"]');
      formData.append('website_url',   honeypot ? honeypot.value : '');

      try {
        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: formData });
        const data = await res.json();

        if (!data.ok) {
          if (data.field && els[data.field] && inputConfig.has(els[data.field])) {
            updateFieldState(els[data.field], data.field, () => data.message, true);
            els[data.field].focus({ preventScroll: true });
            els[data.field].scrollIntoView({ behavior: 'smooth', block: 'center' });
          } else {
            showGlobalError(data.message || 'خطا در ثبت‌نام.');
          }
          resetButton();
          return;
        }

        showSuccess();

      } catch (err) {
        showGlobalError('اتصال به سرور برقرار نشد. اتصال اینترنت خود را بررسی کنید.');
        resetButton();
      }
    });

    /* ============================================================
       13) INIT
       ============================================================ */
    scheduleProgressUpdate();
    updateStrength();
  });
})();