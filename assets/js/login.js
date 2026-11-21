(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.RahaLoginConfig || {};
    if (!document.getElementById('loginForm')) return;

    /* ============================================================
       1) DOM CACHE
       ============================================================ */
    const $ = (id) => document.getElementById(id);
    const els = {
      card:           $('loginCard'),
      form:           $('loginForm'),
      username:       $('username'),
      password:       $('password'),
      rememberMe:     $('rememberMe'),
      togglePassword: $('togglePassword'),
      capsBadge:      $('capsBadge'),
      errorAlert:     $('errorAlert'),
      errorAlertText: $('errorAlertText'),
      submitBtn:      $('submitBtn'),
      submitText:     $('submitText'),
      submitIcon:     $('submitIcon'),
      rateNotice:     $('rateNotice'),
      rateNoticeText: $('rateNoticeText'),
    };

    const fieldMap = new WeakMap();
    const errMap   = new WeakMap();
    [els.username, els.password].forEach(input => {
      const field = input.closest('.field');
      fieldMap.set(input, field);
      errMap.set(input, field.querySelector('.field-error'));
    });

    /* ============================================================
       2) STATE
       ============================================================ */
    const state = {
      isSubmitting: false,
      lastAttemptAt: 0,
      failedAttempts: 0,
      lockedUntil: 0,
      abortController: null,
      passwordVisible: false,
    };

    const MIN_INTERVAL_MS = 1500;
    const MAX_ATTEMPTS    = 5;
    const LOCK_MS         = 30000;

    /* ============================================================
       3) VALIDATORS
       ============================================================ */
    const RE = {
      email:    /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
      username: /^[a-zA-Z0-9._-]{3,}$/,
    };

    function validateUsername(v) {
      const t = v.trim();
      if (!t) return 'نام کاربری یا ایمیل را وارد کنید.';
      if (t.length < 3) return 'حداقل ۳ کاراکتر وارد کنید.';
      if (t.includes('@')) {
        if (!RE.email.test(t)) return 'فرمت ایمیل معتبر نیست.';
      } else if (!RE.username.test(t)) {
        return 'فقط حروف انگلیسی، عدد، نقطه، خط تیره و آندرلاین مجاز است.';
      }
      return null;
    }

    function validatePassword(v) {
      if (!v) return 'رمز عبور را وارد کنید.';
      return null;
    }

    /* ============================================================
       4) FIELD STATE
       ============================================================ */
    const inputConfig = new Map([
      [els.username, { validator: validateUsername }],
      [els.password, { validator: validatePassword }],
    ]);

    function setFieldState(input, error, force) {
      const field = fieldMap.get(input);
      const errEl = errMap.get(input);
      const isValid = error === null;

      if (!force
          && field.classList.contains(isValid ? 'is-valid' : 'is-invalid')
          && errEl.textContent === (error || '')) {
        return;
      }

      field.classList.toggle('is-invalid', !isValid);
      field.classList.toggle('is-valid', isValid && input.value.trim() !== '');
      errEl.textContent = error || '';
      input.setAttribute('aria-invalid', isValid ? 'false' : 'true');
    }

    function clearFieldState(input) {
      fieldMap.get(input).classList.remove('is-valid', 'is-invalid');
      errMap.get(input).textContent = '';
      input.removeAttribute('aria-invalid');
    }

    function runValidator(input, force) {
      const config = inputConfig.get(input);
      if (!config) return true;
      const error = config.validator(input.value);

      if (input.value.trim() === '' && !force) {
        clearFieldState(input);
        return false;
      }
      setFieldState(input, error, force);
      return error === null;
    }

    /* ============================================================
       5) GLOBAL ERROR
       ============================================================ */
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function showError(msg) {
      if (els.errorAlertText.textContent === msg && els.errorAlert.classList.contains('visible')) return;
      els.errorAlertText.textContent = msg;
      els.errorAlert.classList.add('visible');
      if (!prefersReducedMotion) {
        els.errorAlert.style.animation = 'none';
        void els.errorAlert.offsetHeight;
        els.errorAlert.style.animation = '';
      }
    }

    function clearError() {
      if (els.errorAlert.classList.contains('visible')) {
        els.errorAlert.classList.remove('visible');
      }
    }

    function showRateNotice(msg) {
      els.rateNoticeText.textContent = msg;
      els.rateNotice.classList.add('visible');
    }

    function clearRateNotice() {
      if (els.rateNotice.classList.contains('visible')) {
        els.rateNotice.classList.remove('visible');
      }
    }

    /* ============================================================
       6) RATE LIMIT (client-side)
       ============================================================ */
    function checkRateLimit() {
      const now = Date.now();

      if (state.lockedUntil > now) {
        const remaining = Math.ceil((state.lockedUntil - now) / 1000);
        showRateNotice(`به دلیل تلاش‌های ناموفق، ${remaining} ثانیه صبر کنید.`);
        return false;
      }

      const elapsed = now - state.lastAttemptAt;
      if (elapsed < MIN_INTERVAL_MS) {
        const remaining = Math.ceil((MIN_INTERVAL_MS - elapsed) / 1000);
        showRateNotice(`لطفاً ${remaining} ثانیه قبل از تلاش مجدد صبر کنید.`);
        return false;
      }

      clearRateNotice();
      return true;
    }

    function recordAttempt(success) {
      state.lastAttemptAt = Date.now();
      if (success) {
        state.failedAttempts = 0;
      } else {
        state.failedAttempts++;
        if (state.failedAttempts >= MAX_ATTEMPTS) {
          state.lockedUntil = Date.now() + LOCK_MS;
          state.failedAttempts = 0;
        }
      }
    }

    /* ============================================================
       7) SUBMIT BUTTON STATE
       ============================================================ */
    function setSubmitting(on) {
      state.isSubmitting = on;
      els.submitBtn.classList.toggle('is-loading', on);
      els.submitBtn.disabled = on;
      els.submitBtn.setAttribute('aria-busy', on ? 'true' : 'false');
      if (on) {
        els.submitText.textContent = 'در حال ورود...';
        els.submitIcon.innerHTML = '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>';
      } else {
        els.submitText.textContent = 'ورود به حساب کاربری';
        els.submitIcon.innerHTML = '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>';
      }
    }

    /* ============================================================
       8) INPUT & FOCUSOUT
       ============================================================ */
    function onInput(e) {
      const input = e.target;
      if (!inputConfig.has(input)) return;
      const field = fieldMap.get(input);
      const touched = field.classList.contains('is-valid') || field.classList.contains('is-invalid');
      if (touched) runValidator(input);
      clearError();
    }

    function onFocusOut(e) {
      const input = e.target;
      if (!inputConfig.has(input)) return;
      runValidator(input);
    }

    els.form.addEventListener('input',    onInput,    { passive: true });
    els.form.addEventListener('focusout', onFocusOut, { passive: true });

    /* CapsLock */
    function checkCapsLock(e) {
      if (!e.getModifierState) return;
      els.capsBadge.classList.toggle('visible', e.getModifierState('CapsLock'));
    }
    els.password.addEventListener('keydown', checkCapsLock, { passive: true });
    els.password.addEventListener('keyup',   checkCapsLock, { passive: true });
    els.password.addEventListener('blur',
      () => els.capsBadge.classList.remove('visible'), { passive: true });

    /* Toggle password */
    const EYE_ON  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    const EYE_OFF = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';

    els.togglePassword.addEventListener('click', () => {
      state.passwordVisible = !state.passwordVisible;
      els.password.type = state.passwordVisible ? 'text' : 'password';
      els.togglePassword.setAttribute(
        'aria-label',
        state.passwordVisible ? 'مخفی کردن رمز عبور' : 'نمایش رمز عبور'
      );
      els.togglePassword.firstElementChild.innerHTML =
        state.passwordVisible ? EYE_OFF : EYE_ON;
    });

    /* ============================================================
       9) SUBMIT — fetch واقعی به WordPress AJAX
       ============================================================ */
    els.form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (state.isSubmitting) return;

      clearError();
      clearRateNotice();

      if (!checkRateLimit()) return;

      const userOk = runValidator(els.username, true);
      const passOk = runValidator(els.password, true);

      if (!userOk || !passOk) {
        const firstInvalid = els.form.querySelector('.field.is-invalid .field-input');
        if (firstInvalid) {
          firstInvalid.focus({ preventScroll: true });
          firstInvalid.scrollIntoView({
            behavior: prefersReducedMotion ? 'auto' : 'smooth',
            block: 'center',
          });
        }
        return;
      }

      setSubmitting(true);

      // Abort previous
      if (state.abortController) state.abortController.abort();
      state.abortController = new AbortController();
      const { signal } = state.abortController;

      const formData = new FormData();
      formData.append('action',   cfg.action || 'raha_login');
      formData.append('nonce',    cfg.nonce  || '');
      formData.append('username', els.username.value.trim());
      formData.append('password', els.password.value);
      formData.append('remember', els.rememberMe.checked ? '1' : '0');
      formData.append('redirect', cfg.redirect || '');

      try {
        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: formData, signal });
        const data = await res.json();

        if (!data.ok) {
          recordAttempt(false);

          if (data.field && els[data.field] && inputConfig.has(els[data.field])) {
            setFieldState(els[data.field], data.message, true);
            els[data.field].focus({ preventScroll: true });
          } else {
            showError(data.message || 'خطا در ورود.');
          }

          if (data.field === 'password') {
            els.password.value = '';
            els.password.focus({ preventScroll: true });
          }

          setSubmitting(false);
          return;
        }

        // موفقیت
        recordAttempt(true);
        window.location.replace(data.redirect || cfg.redirect || '/');

      } catch (err) {
        if (err && err.name === 'AbortError') {
          // abort شده — نادیده بگیر
        } else {
          recordAttempt(false);
          showError('خطای شبکه. اتصال خود را بررسی کنید.');
        }
        setSubmitting(false);
      }
    });

    /* ============================================================
       10) INIT
       ============================================================ */
    function focusInitial() {
      if (els.username.value) {
        els.password.focus({ preventScroll: true });
      } else {
        els.username.focus({ preventScroll: true });
      }
    }

    if ('requestIdleCallback' in window) {
      requestIdleCallback(focusInitial, { timeout: 800 });
    } else {
      setTimeout(focusInitial, 100);
    }

    window.addEventListener('beforeunload', () => {
      if (state.abortController) state.abortController.abort();
    }, { once: true });

  });
})();