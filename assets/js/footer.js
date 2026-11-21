document.addEventListener('DOMContentLoaded', function() {
    // فرم خبرنامه
    const form  = document.getElementById('rahaNewsletterForm');
    const input = document.getElementById('nb-email');
    const msg   = form ? form.querySelector('.nb-form__msg') : null;
    const cfg   = window.RahaFooterConfig || {}; // اگر بخواهید AJAX کنید

    if (form && input && msg) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!input.checkValidity()) {
                form.classList.add('is-error', 'is-sent');
                msg.textContent = 'ایمیل معتبر وارد کنید.';
                input.focus();
                return;
            }

            // ارسال با AJAX به وردپرس (اختیاری - الان فقط پیام موفقیت نشان می‌دهد)
            form.classList.remove('is-error');
            form.classList.add('is-sent');
            msg.textContent = 'ثبت شد — جمعه می‌رسد.';
            form.reset();
        });
        
        input.addEventListener('input', () => form.classList.remove('is-sent', 'is-error'));
    }

    // بازگشت به بالا
    const backToTop = document.getElementById('rahaBackToTop');
    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});