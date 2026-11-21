document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('rahaCtaForm');
    const successBox = document.getElementById('rahaCtaSuccess');
    const cfg = window.RahaCtaConfig || {};

    if (!form || !successBox) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const contactInput = document.getElementById('ctaContact');
        const contact = contactInput.value.trim();

        if (!contact) {
            alert('لطفاً راه ارتباطی خود را وارد کنید.');
            return;
        }

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'در حال ارسال...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'raha_submit_lead');
        formData.append('nonce', cfg.nonce);
        formData.append('contact', contact);

        fetch(cfg.ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                form.style.display = 'none';
                successBox.classList.add('is-visible');
            } else {
                alert(res.data.message || 'خطا در ارسال.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(() => {
            alert('خطا در ارتباط با سرور.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
});