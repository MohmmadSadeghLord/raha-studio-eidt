(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const cfg = window.RahaContactConfig || {};
        const form = document.getElementById('consultForm');
        if (!form) return;

        const nameInput = document.getElementById('cf-name');
        const phoneInput = document.getElementById('cf-phone');
        const emailInput = document.getElementById('cf-email');
        const errorBox = document.getElementById('cfError');
        const submitBtn = document.getElementById('cfSubmit');
        const successBox = document.getElementById('cfSuccess');
        const formHeader = document.getElementById('formHeader');
        const telegramWarning = document.getElementById('cfTelegramWarning');
        const descInput = document.getElementById('cf-desc');
        const nonceInput = form.querySelector('input[name="_raha_consult_nonce"]');

        const FA = '۰۱۲۳۴۵۶۷۸۹';
        const AR = '٠١٢٣٤٥٦٧٨٩';

        function toEnglishDigits(str) {
            return String(str || '').replace(/[۰-۹٠-٩]/g, function (ch) {
                const fa = FA.indexOf(ch);
                if (fa > -1) return fa;
                const ar = AR.indexOf(ch);
                if (ar > -1) return ar;
                return ch;
            });
        }

        phoneInput.addEventListener('input', function () {
            let v = phoneInput.value.replace(/[٠-٩]/g, (ch) => FA[AR.indexOf(ch)]);
            v = v.replace(/[^\u06F0-\u06F90-9]/g, '').slice(0, 11);
            if (v !== phoneInput.value) phoneInput.value = v;
            phoneInput.classList.remove('is-invalid');
            clearError();
        });

        nameInput.addEventListener('input', () => { nameInput.classList.remove('is-invalid'); clearError(); });
        emailInput.addEventListener('input', () => { emailInput.classList.remove('is-invalid'); clearError(); });

        form.querySelectorAll('input[name="service"]').forEach((r) => r.addEventListener('change', clearError));

        form.querySelectorAll('input[name="method"]').forEach(function (r) {
            r.addEventListener('change', function () {
                const checked = form.querySelector('input[name="method"]:checked');
                const isTelegram = checked && checked.value === 'telegram';
                telegramWarning.hidden = !isTelegram;
                clearError();
            });
        });

        function showError(msg) {
            errorBox.textContent = '⚠️ ' + msg;
            errorBox.classList.add('is-visible');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function clearError() {
            errorBox.classList.remove('is-visible');
            errorBox.textContent = '';
        }

        function validate() {
            let valid = true;
            const name = nameInput.value.trim();
            if (!name) { nameInput.classList.add('is-invalid'); showError('لطفاً نام و نام خانوادگی خود را وارد کنید.'); valid = false; }
            else if (name.length < 3) { nameInput.classList.add('is-invalid'); showError('نام باید حداقل ۳ کاراکتر باشد.'); valid = false; }

            const phone = toEnglishDigits(phoneInput.value.trim());
            if (!phone) { phoneInput.classList.add('is-invalid'); showError('لطفاً شماره تماس خود را وارد کنید.'); valid = false; }
            else if (!/^09\d{9}$/.test(phone)) { phoneInput.classList.add('is-invalid'); showError('شماره موبایل باید با ۰۹ شروع شده و ۱۱ رقم باشد.'); valid = false; }

            const email = emailInput.value.trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) { emailInput.classList.add('is-invalid'); showError('آدرس ایمیل وارد شده معتبر نیست.'); valid = false; }

            if (!form.querySelector('input[name="service"]:checked')) { showError('لطفاً موضوع مشاوره را انتخاب کنید.'); valid = false; }
            if (!form.querySelector('input[name="method"]:checked')) { showError('لطفاً روش ارتباط ترجیحی را انتخاب کنید.'); valid = false; }
            return valid;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearError();
            if (!validate()) return;

            submitBtn.disabled = true;
            submitBtn.querySelector('span').textContent = 'در حال ثبت...';

            const formData = new FormData();
            formData.append('action', 'raha_submit_consultation');
            formData.append('nonce', nonceInput.value);
            formData.append('name', nameInput.value.trim());
            formData.append('phone', toEnglishDigits(phoneInput.value.trim()));
            formData.append('email', emailInput.value.trim());
            formData.append('service', form.querySelector('input[name="service"]:checked').value);
            formData.append('method', form.querySelector('input[name="method"]:checked').value);
            formData.append('desc', descInput.value.trim());

            fetch(cfg.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    form.style.display = 'none';
                    formHeader.style.display = 'none';
                    successBox.classList.add('is-visible');
                    successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    showError(data.message || 'خطا در ثبت درخواست.');
                }
            })
            .catch(() => {
                showError('خطای شبکه. لطفاً دوباره تلاش کنید.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.querySelector('span').textContent = 'درخواست ثبت وقت مشاوره';
            });
        });

        resetBtn.addEventListener('click', function () {
            form.reset();
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
            clearError();
            telegramWarning.hidden = true;
            form.style.display = '';
            formHeader.style.display = '';
            successBox.classList.remove('is-visible');
            nameInput.focus();
            document.getElementById('consult-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
})();