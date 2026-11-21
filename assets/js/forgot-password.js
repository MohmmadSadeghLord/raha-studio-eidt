(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const cfg = window.RahaForgotConfig || {};
    if (!document.getElementById('forgotCard')) return;

    /* ============================================================
       1) DOM CACHE
       ============================================================ */
    const $  = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

    const els = {
      card:           $('#forgotCard'),
      subtitle:       $('#stepSubtitle'),
      errorAlert:     $('#errorAlert'),
      errorAlertText: $('#errorAlertText'),

      stepNodes:      $$('.step-node'),
      stepLines:      $$('.step-line'),
      stepContents:   $$('.wizard-step'),

      // Toast
      toast:          $('#rahaToast'),
      toastText:      $('#rahaToastText'),

      // Step 1
      methodTabs:     $$('.method-tab'),
      destination:    $('#destination'),
      destinationLbl: $('#destinationLabel'),
      step1Btn:       $('#step1Btn'),
      step1Text:      $('#step1Text'),
      step1Icon:      $('#step1Icon'),

      // Step 2 (OTP)
      otpWrap:        $('#otpWrap'),
      otpDigits:      $$('.otp-digit'),
      otpStatus:      $('#otpStatus'),
      otpDestination: $('#otpDestination'),
      resendBtn:      $('#resendBtn'),
      step2Btn:       $('#step2Btn'),
      step2Text:      $('#step2Text'),
      step2Icon:      $('#step2Icon'),
      backToStep1:    $('#backToStep1'),

      // Step 3
      newPassword:    $('#newPassword'),
      confirmPassword:$('#confirmPassword'),
      passwordStrength: $('#passwordStrength'),
      passwordStrengthLabel: $('#passwordStrengthLabel'),
      step3Btn:       $('#step3Btn'),
      step3Text:      $('#step3Text'),
      step3Icon:      $('#step3Icon'),

      // Success
      successScreen:  $('#successScreen'),
      successCheck:   $('.success-check'),
      forgotHeader:   $('.forgot-header'),
      stepsBar:       $('.steps'),
    };

    /* ============================================================
       2) STATE
       ============================================================ */
    const state = {
      step: 1,
      destination: '',
      isSubmitting: false,
      otpAttempts: 0,
      otpValue: '',
      resendUntil: 0,
      resendTimer: null,
      toastTimer: null,
    };

    const RESEND_COOLDOWN_MS    = 60000;
    const MAX_OTP_ATTEMPTS      = 5;
    const REDIRECT_AFTER_MS     = 4000;
    const OTP_SUCCESS_DELAY_MS  = 900;

    /* ============================================================
       3) HELPERS
       ============================================================ */
    const FA = '۰۱۲۳۴۵۶۷۸۹', AR = '٠١٢٣٤٥٦٧٨٩';
    const MAP = Object.create(null);
    for (let i = 0; i < 10; i++) { MAP[FA[i]] = String(i); MAP[AR[i]] = String(i); }
    const toEn = s => String(s).replace(/[۰-۹٠-٩]/g, ch => MAP[ch]);

    const RE = {
      email:      /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
      hasLetter:  /[a-zA-Z]/,
      hasDigit:   /\d/,
      hasUpper:   /[A-Z]/,
      hasLower:   /[a-z]/,
      hasSpecial: /[^a-zA-Z0-9]/,
    };

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function showToast(msg) {
      clearTimeout(state.toastTimer);
      els.toastText.textContent = msg;
      els.toast.classList.add('visible');
      state.toastTimer = setTimeout(() => {
        els.toast.classList.remove('visible');
      }, 3800);
    }

    function showError(msg) {
      els.errorAlertText.textContent = msg;
      els.errorAlert.classList.add('visible');
      if (!prefersReducedMotion) {
        els.errorAlert.style.animation = 'none';
        void els.errorAlert.offsetHeight;
        els.errorAlert.style.animation = '';
      }
    }
    function clearError() { els.errorAlert.classList.remove('visible'); }

    function setFieldError(input, message) {
      const field = input.closest('.field');
      const errEl = field.querySelector('.field-error');
      if (message) {
        field.classList.add('is-invalid');
        errEl.textContent = message;
        input.setAttribute('aria-invalid', 'true');
      } else {
        field.classList.remove('is-invalid');
        errEl.textContent = '';
        input.setAttribute('aria-invalid', 'false');
      }
    }

    function clearAllFields() {
      $$('.field').forEach(f => {
        f.classList.remove('is-invalid');
        const errEl = f.querySelector('.field-error');
        if (errEl) errEl.textContent = '';
      });
      clearOtpStates();
    }

    function setBtnLoading(btn, textEl, iconEl, loading, defaultText, loadingText) {
      btn.classList.toggle('is-loading', loading);
      btn.disabled = loading;
      if (loading) {
        textEl.textContent = loadingText || 'در حال ارسال...';
        iconEl.innerHTML = '<path d="M21 12a9 9 0 1 1-6.219-8.56"/>';
      } else {
        textEl.textContent = defaultText;
        iconEl.innerHTML = '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>';
      }
    }

    function getOtpValue() { return els.otpDigits.map(d => d.value).join(''); }
    function clearOtpDigits() {
      els.otpDigits.forEach(d => { d.value = ''; d.classList.remove('filled'); });
      state.otpValue = '';
    }

    const ICON_ERR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    const ICON_OK  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

    function setOtpStatus(kind, html) {
      if (!kind) {
        els.otpStatus.className = 'otp-status';
        els.otpStatus.innerHTML = '';
        return;
      }
      els.otpStatus.className = 'otp-status ' + kind;
      els.otpStatus.innerHTML = (kind === 'error' ? ICON_ERR : ICON_OK) + '<span>' + html + '</span>';
    }
    function clearOtpStates() {
      els.otpWrap.classList.remove('is-error', 'is-success');
      setOtpStatus(null);
    }

    function maskEmail(email) {
      const parts = String(email).split('@');
      if (parts.length !== 2) return email;
      const [user, domain] = parts;
      const visible = user.slice(0, Math.min(2, user.length));
      return visible + '*'.repeat(Math.max(0, user.length - 2)) + '@' + domain;
    }

    /* ============================================================
       4) STEP NAVIGATION
       ============================================================ */
    const SUBTITLES = {
      1: 'ایمیل خود را وارد کنید تا کد بازیابی ارسال شود.',
      2: 'کد ۶ رقمی ارسال‌شده به ایمیل خود را وارد کنید.',
      3: 'رمز عبور جدید خود را تعیین کنید.',
    };

    function goToStep(step) {
      state.step = step;
      clearError();
      clearAllFields();

      els.stepContents.forEach(el => {
        el.classList.toggle('active', Number(el.dataset.stepContent) === step);
      });

      els.stepNodes.forEach(node => {
        const n = Number(node.dataset.step);
        node.classList.toggle('active', n === step);
        node.classList.toggle('done', n < step);
      });

      els.stepLines.forEach(line => {
        const n = Number(line.dataset.line);
        line.classList.toggle('done', n < step);
      });

      els.subtitle.textContent = SUBTITLES[step];

      requestAnimationFrame(() => {
        if (step === 1) els.destination.focus({ preventScroll: true });
        else if (step === 2) els.otpDigits[0].focus({ preventScroll: true });
        else if (step === 3) els.newPassword.focus({ preventScroll: true });
      });
    }

    /* ============================================================
       5) PHONE TAB — غیرفعال با پیام toast
       ============================================================ */
    els.methodTabs.forEach(tab => {
      tab.addEventListener('click', (e) => {
        if (tab.classList.contains('is-disabled') || tab.dataset.method === 'phone') {
          e.preventDefault();
          showToast('این گزینه غیرفعال است. لطفاً از ایمیل استفاده کنید.');
          return;
        }
        els.methodTabs.forEach(t => {
          const on = t === tab;
          t.classList.toggle('active', on);
          t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
      });
    });

    /* ============================================================
       6) STEP 1 — ارسال کد
       ============================================================ */
    function validateEmail(v) {
      const t = toEn(v).trim();
      if (!t) return 'وارد کردن ایمیل الزامی است.';
      if (!RE.email.test(t)) return 'فرمت ایمیل معتبر نیست.';
      return null;
    }

    els.destination.addEventListener('input', () => {
      setFieldError(els.destination, null);
      clearError();
    });
    els.destination.addEventListener('blur', () => {
      if (els.destination.value.trim()) {
        setFieldError(els.destination, validateEmail(els.destination.value));
      }
    });

    els.step1Btn.addEventListener('click', async () => {
      if (state.isSubmitting) return;

      const err = validateEmail(els.destination.value);
      if (err) {
        setFieldError(els.destination, err);
        els.destination.focus();
        return;
      }

      const email = toEn(els.destination.value).trim().toLowerCase();

      state.isSubmitting = true;
      setBtnLoading(els.step1Btn, els.step1Text, els.step1Icon, true, 'ارسال کد بازیابی', 'در حال ارسال...');
      clearError();

      try {
        const fd = new FormData();
        fd.append('action', 'raha_forgot_send');
        fd.append('nonce',  cfg.sendNonce || '');
        fd.append('email',  email);

        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.ok) {
          showError(data.message || 'ارسال کد با خطا مواجه شد.');
          setBtnLoading(els.step1Btn, els.step1Text, els.step1Icon, false, 'ارسال کد بازیابی');
          state.isSubmitting = false;
          return;
        }

        state.destination = email;
        state.otpAttempts = 0;
        state.otpValue = '';

        els.otpDestination.textContent = maskEmail(email);
        clearOtpDigits();

        goToStep(2);
        startResendTimer();

      } catch (e) {
        showError('خطای شبکه. اتصال خود را بررسی کنید.');
        setBtnLoading(els.step1Btn, els.step1Text, els.step1Icon, false, 'ارسال کد بازیابی');
      } finally {
        state.isSubmitting = false;
      }
    });

    /* ============================================================
       7) STEP 2 — OTP
       ============================================================ */
    function playPress(el) {
      el.classList.remove('filled');
      void el.offsetWidth;
      if (el.value) el.classList.add('filled');
    }

    els.otpDigits.forEach((d, i) => {
      d.addEventListener('input', (e) => {
        const v = toEn(e.target.value).replace(/\D/g, '');
        e.target.value = v.slice(-1);

        playPress(d);
        clearOtpStates();

        if (d.value && i < els.otpDigits.length - 1) {
          els.otpDigits[i + 1].focus();
        }

        if (getOtpValue().length === 6) {
          setTimeout(() => {
            if (!state.isSubmitting) els.step2Btn.click();
          }, 450);
        }
      });

      d.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
          if (d.value) {
            d.value = '';
            d.classList.remove('filled');
            clearOtpStates();
          } else if (i > 0) {
            const prev = els.otpDigits[i - 1];
            prev.focus();
            prev.value = '';
            prev.classList.remove('filled');
            clearOtpStates();
          }
          e.preventDefault();
        } else if (e.key === 'ArrowLeft' && i < els.otpDigits.length - 1) {
          els.otpDigits[i + 1].focus();
        } else if (e.key === 'ArrowRight' && i > 0) {
          els.otpDigits[i - 1].focus();
        } else if (e.key === 'Enter') {
          els.step2Btn.click();
        }
      });

      d.addEventListener('paste', (e) => {
        e.preventDefault();
        const txt = toEn((e.clipboardData || window.clipboardData).getData('text') || '')
          .replace(/\D/g, '').slice(0, 6);
        if (!txt) return;

        clearOtpDigits();
        txt.split('').forEach((ch, idx) => {
          const el = els.otpDigits[idx];
          if (!el) return;
          el.value = ch;
          setTimeout(() => {
            el.classList.add('filled');
            void el.offsetWidth;
          }, idx * 70);
        });

        const nextIdx = Math.min(txt.length, els.otpDigits.length - 1);
        els.otpDigits[nextIdx].focus();
        clearOtpStates();

        if (txt.length === 6) {
          setTimeout(() => {
            if (!state.isSubmitting) els.step2Btn.click();
          }, 500);
        }
      });

      d.addEventListener('focus', () => {
        if (els.otpWrap.classList.contains('is-error')) {
          els.otpWrap.classList.remove('is-error');
          setOtpStatus(null);
        }
      });
    });

    /* ============================================================
       8) RESEND TIMER
       ============================================================ */
    function startResendTimer() {
      state.resendUntil = Date.now() + RESEND_COOLDOWN_MS;
      updateResendButton();
      clearInterval(state.resendTimer);
      state.resendTimer = setInterval(updateResendButton, 1000);
    }

    function updateResendButton() {
      const remaining = Math.max(0, Math.ceil((state.resendUntil - Date.now()) / 1000));
      if (remaining > 0) {
        els.resendBtn.disabled = true;
        els.resendBtn.textContent = `ارسال مجدد تا ${remaining} ثانیه`;
      } else {
        els.resendBtn.disabled = false;
        els.resendBtn.textContent = 'ارسال مجدد کد';
        clearInterval(state.resendTimer);
      }
    }

    els.resendBtn.addEventListener('click', async () => {
      if (els.resendBtn.disabled) return;
      els.resendBtn.disabled = true;
      els.resendBtn.textContent = 'در حال ارسال...';

      try {
        const fd = new FormData();
        fd.append('action', 'raha_forgot_send');
        fd.append('nonce',  cfg.sendNonce || '');
        fd.append('email',  state.destination);

        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.ok) {
          els.resendBtn.disabled = false;
          els.resendBtn.textContent = 'ارسال مجدد کد';
          setOtpStatus('error', data.message || 'ارسال مجدد ناموفق بود');
          return;
        }

        clearOtpDigits();
        els.otpDigits[0].focus();
        clearOtpStates();
        startResendTimer();

      } catch (e) {
        els.resendBtn.disabled = false;
        els.resendBtn.textContent = 'ارسال مجدد کد';
        setOtpStatus('error', 'ارسال مجدد ناموفق بود');
      }
    });

    /* ============================================================
       9) VERIFY OTP
       ============================================================ */
    els.step2Btn.addEventListener('click', async () => {
      if (state.isSubmitting) return;

      const otp = getOtpValue();
      if (otp.length !== 6) {
        els.otpWrap.classList.add('is-error');
        setOtpStatus('error', 'لطفاً کد ۶ رقمی را کامل وارد کنید');
        return;
      }

      state.isSubmitting = true;
      setBtnLoading(els.step2Btn, els.step2Text, els.step2Icon, true, 'تأیید کد', 'در حال بررسی...');
      clearOtpStates();

      try {
        const fd = new FormData();
        fd.append('action', 'raha_forgot_verify');
        fd.append('nonce',  cfg.verifyNonce || '');
        fd.append('email',  state.destination);
        fd.append('code',   otp);

        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.ok) {
          state.otpAttempts++;
          els.otpWrap.classList.add('is-error');

          if (state.otpAttempts >= MAX_OTP_ATTEMPTS) {
            setOtpStatus('error', 'تعداد تلاش‌های ناموفق بیش از حد مجاز. کد جدید دریافت کنید.');
            els.step2Btn.disabled = true;
            setTimeout(() => { els.step2Btn.disabled = false; }, 30000);
          } else {
            setOtpStatus('error', data.message || `کد نامعتبر (${state.otpAttempts} از ${MAX_OTP_ATTEMPTS})`);
          }

          setTimeout(() => {
            clearOtpDigits();
            els.otpDigits[0].focus();
          }, 550);

          setBtnLoading(els.step2Btn, els.step2Text, els.step2Icon, false, 'تأیید کد');
          state.isSubmitting = false;
          return;
        }

        state.otpAttempts = 0;
        state.otpValue = otp;

        els.otpWrap.classList.add('is-success');
        setOtpStatus('success', 'کد تأیید شد، در حال انتقال…');
        els.otpDigits.forEach(d => d.blur());

        setTimeout(() => {
          els.otpWrap.classList.remove('is-success');
          setOtpStatus(null);
          goToStep(3);
        }, OTP_SUCCESS_DELAY_MS);

      } catch (e) {
        setOtpStatus('error', 'خطای شبکه. دوباره تلاش کنید.');
      } finally {
        setBtnLoading(els.step2Btn, els.step2Text, els.step2Icon, false, 'تأیید کد');
        state.isSubmitting = false;
      }
    });

    els.backToStep1.addEventListener('click', () => {
      clearInterval(state.resendTimer);
      clearOtpDigits();
      clearOtpStates();
      goToStep(1);
    });

    /* ============================================================
       10) STEP 3 — رمز جدید
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
      const s = calcStrength(els.newPassword.value);
      if (s === lastStrength) return;
      lastStrength = s;
      const sStr = String(s);
      els.passwordStrength.setAttribute('data-strength', sStr);
      els.passwordStrengthLabel.setAttribute('data-strength', sStr);
      els.passwordStrengthLabel.textContent = s ? STRENGTH_LABELS[s] : '';
    }

    function validatePassword(v) {
      if (!v) return 'رمز عبور جدید را وارد کنید.';
      if (v.length < 8) return 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
      if (!RE.hasLetter.test(v) || !RE.hasDigit.test(v)) return 'رمز باید شامل حرف و عدد باشد.';
      return null;
    }
    function validateConfirm(v) {
      if (!v) return 'تکرار رمز عبور را وارد کنید.';
      if (v !== els.newPassword.value) return 'رمزهای عبور یکسان نیستند.';
      return null;
    }

    els.newPassword.addEventListener('input', () => {
      updateStrength();
      if (els.newPassword.value) setFieldError(els.newPassword, null);
      if (els.confirmPassword.value) {
        setFieldError(els.confirmPassword, validateConfirm(els.confirmPassword.value));
      }
    });
    els.confirmPassword.addEventListener('input', () => {
      if (els.confirmPassword.value) {
        setFieldError(els.confirmPassword, validateConfirm(els.confirmPassword.value));
      } else {
        setFieldError(els.confirmPassword, null);
      }
    });

    // Toggle password
    $$('.toggle-password').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const isPw = input.type === 'password';
        input.type = isPw ? 'text' : 'password';
        btn.setAttribute('aria-label', isPw ? 'مخفی کردن رمز عبور' : 'نمایش رمز عبور');
        btn.firstElementChild.innerHTML = isPw
          ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
          : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
      });
    });

    /* ============================================================
       11) SUBMIT — ذخیره رمز جدید
       ============================================================ */
    els.step3Btn.addEventListener('click', async () => {
      if (state.isSubmitting) return;

      const pErr = validatePassword(els.newPassword.value);
      const cErr = validateConfirm(els.confirmPassword.value);
      setFieldError(els.newPassword, pErr);
      setFieldError(els.confirmPassword, cErr);

      if (pErr || cErr) {
        if (pErr) els.newPassword.focus();
        else els.confirmPassword.focus();
        return;
      }

      state.isSubmitting = true;
      setBtnLoading(els.step3Btn, els.step3Text, els.step3Icon, true, 'ذخیره رمز جدید', 'در حال ذخیره...');
      clearError();

      try {
        const fd = new FormData();
        fd.append('action',   'raha_forgot_reset');
        fd.append('nonce',    cfg.resetNonce || '');
        fd.append('email',    state.destination);
        fd.append('code',     state.otpValue);
        fd.append('password', els.newPassword.value);

        const res  = await fetch(cfg.ajaxUrl, { method: 'POST', body: fd });
        const data = await res.json();

        if (!data.ok) {
          showError(data.message || 'تغییر رمز ناموفق بود.');
          setBtnLoading(els.step3Btn, els.step3Text, els.step3Icon, false, 'ذخیره رمز جدید');
          state.isSubmitting = false;
          return;
        }

        showSuccess(data.loginUrl || cfg.loginUrl);

      } catch (e) {
        showError('خطای شبکه. اتصال خود را بررسی کنید.');
        setBtnLoading(els.step3Btn, els.step3Text, els.step3Icon, false, 'ذخیره رمز جدید');
        state.isSubmitting = false;
      }
    });

    /* ============================================================
       12) SUCCESS
       ============================================================ */
    function showSuccess(loginUrl) {
      clearInterval(state.resendTimer);

      els.stepContents.forEach(el => el.classList.remove('active'));
      els.stepNodes.forEach(n => { n.classList.remove('active'); n.classList.add('done'); });
      els.stepLines.forEach(l => l.classList.add('done'));
      els.forgotHeader.style.display = 'none';
      els.stepsBar.style.display = 'none';
      clearError();

      els.successScreen.classList.add('visible');

      const circle = els.successCheck.querySelector('circle');
      const path   = els.successCheck.querySelector('path');
      circle.style.animation = 'none';
      path.style.animation   = 'none';
      void els.successCheck.offsetWidth;
      circle.style.animation = '';
      path.style.animation   = '';

      const target = loginUrl || cfg.loginUrl || '/';
      setTimeout(() => {
        window.location.replace(target);
      }, REDIRECT_AFTER_MS);
    }

    /* ============================================================
       13) INIT
       ============================================================ */
    requestAnimationFrame(() => {
      els.destination.focus({ preventScroll: true });
    });

    window.addEventListener('beforeunload', () => {
      if (state.resendTimer) clearInterval(state.resendTimer);
      if (state.toastTimer) clearTimeout(state.toastTimer);
    }, { once: true });
  });
})();