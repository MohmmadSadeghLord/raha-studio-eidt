document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('rahaCommApp');
    if (!app) return;

    const config = window.RahaCommData;

    // 1. مدیریت تب‌ها
    const tabs = app.querySelectorAll('.comm-tab');
    const panels = app.querySelectorAll('.comm-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('is-active'));
            panels.forEach(p => p.classList.remove('is-active'));
            
            tab.classList.add('is-active');
            document.getElementById('panel-' + tab.dataset.tab).classList.add('is-active');
            
            // پاک کردن جستجو هنگام تغییر تب
            document.getElementById('commSearchInput').value = '';
            filterRows('');
        });
    });

    // 2. جستجو
    const searchInput = document.getElementById('commSearchInput');
    searchInput.addEventListener('input', (e) => {
        filterRows(e.target.value.toLowerCase().trim());
    });

    function filterRows(query) {
        const activePanel = app.querySelector('.comm-panel.is-active');
        const rows = activePanel.querySelectorAll('.comm-row');
        
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 3. کپی کردن شماره تماس
    app.addEventListener('click', function(e) {
        if (e.target.classList.contains('copy-contact')) {
            const contact = e.target.dataset.contact;
            if (contact) {
                navigator.clipboard.writeText(contact).then(() => {
                    const originalText = e.target.innerHTML;
                    e.target.innerHTML = 'کپی شد! ✅';
                    setTimeout(() => { e.target.innerHTML = originalText; }, 1500);
                });
            }
        }
    });

    // 4. تغییر وضعیت
    app.addEventListener('change', function(e) {
        if (e.target.classList.contains('comm-status-select')) {
            const select = e.target;
            const postId = select.dataset.id;
            const metaKey = select.dataset.meta;
            const status = select.value;

            select.disabled = true;

            const fd = new FormData();
            fd.append('action', 'raha_update_comm_status');
            fd.append('nonce', config.nonce);
            fd.append('post_id', postId);
            fd.append('meta_key', metaKey);
            fd.append('status', status);

            fetch(config.ajaxUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        alert(res.data.message || 'خطا در ذخیره');
                        select.value = select.dataset.oldVal || 'new'; // بازگرداندن در صورت خطا
                    } else {
                        // اگر از new به چیز دیگری تغییر کرد، باید صفحه را رفرش کنیم تا بج بالای صفحه کم شود (یا با JS هندل کنیم)
                        // برای سادگی و اطمینان از صحت داده‌ها، صفحه را رفرش می‌کنیم
                        window.location.reload();
                    }
                })
                .catch(() => alert('خطای شبکه'))
                .finally(() => { select.disabled = false; });
        }
    });

    // 5. مودال (مشاهده جزئیات)
    const modal = document.getElementById('commModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const saveModalBtn = document.getElementById('saveModalBtn');

    app.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-view')) {
            const postId = e.target.dataset.id;
            const type = e.target.dataset.type;

            modal.classList.add('is-open');
            modalBody.innerHTML = '<div style="text-align:center; padding:20px;">در حال بارگذاری...</div>';

            const fd = new FormData();
            fd.append('action', 'raha_get_comm_details');
            fd.append('nonce', config.nonce);
            fd.append('post_id', postId);
            fd.append('type', type);

            fetch(config.ajaxUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        modalTitle.textContent = res.data.title;
                        modalBody.innerHTML = res.data.html;
                    } else {
                        modalBody.innerHTML = '<p style="color:red;">خطا در دریافت اطلاعات.</p>';
                    }
                })
                .catch(() => {
                    modalBody.innerHTML = '<p style="color:red;">خطای شبکه.</p>';
                });
        }
    });

    function closeModal() {
        modal.classList.remove('is-open');
        modalBody.innerHTML = '';
    }

    closeModalBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    
    saveModalBtn.addEventListener('click', () => {
        closeModal(); // در حال حاضر چون فرمی برای ویرایش در مودال نداریم، فقط می‌بندیم. (اگر بعداً فیلدهای ویرایشی اضافه شد، اینجا AJAX ذخیره می‌شود).
    });

});