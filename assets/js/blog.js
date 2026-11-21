document.addEventListener('DOMContentLoaded', function() {
    const chips   = document.querySelectorAll('.blog__chip');
    const grid    = document.getElementById('blogGrid');
    const empty   = document.querySelector('.blog__empty');
    const status  = document.getElementById('blog-status');
    const loadBtn = document.getElementById('loadmore');
    const cfg     = window.RahaBlogConfig || {};

    if (!chips.length || !grid) return;

    let currentFilter = 'all';

    // فیلتر کردن کارت‌ها
    function applyFilter(filter) {
        currentFilter = filter;
        const cards = grid.querySelectorAll('[data-category]');
        let visible = 0;

        cards.forEach(card => {
            const matches = (filter === 'all' || card.dataset.category === filter);
            card.hidden = !matches;
            if (matches) visible++;
        });

        if (empty) {
            // بررسی می‌کنیم که آیا دکمه "بیشتر" وجود دارد و آیا مخفی شده است
            const isLoadBtnHidden = !loadBtn || loadBtn.hidden;
            
            if (visible === 0 && isLoadBtnHidden) {
                empty.textContent = (filter !== 'all') ? 'هیچ نوشته‌ای در این دسته یافت نشد.' : 'هیچ نوشته‌ای یافت نشد.';
                empty.hidden = false;
            } else {
                empty.hidden = true;
            }
        }
        
        if (status) {
            status.textContent = visible ? `${visible} نوشته نمایش داده می‌شود.` : 'در حال بارگذاری...';
        }
    }

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.setAttribute('aria-pressed', 'false'));
            chip.setAttribute('aria-pressed', 'true');
            applyFilter(chip.dataset.filter);
        });
    });

    // Load More با AJAX
    if (loadBtn) {
        loadBtn.addEventListener('click', async () => {
            let page = parseInt(loadBtn.dataset.page);
            
            // اگر قبلاً مخفی شده، کاری نکن
            if (loadBtn.hidden) return;

            loadBtn.disabled = true;
            const originalHtml = loadBtn.innerHTML;
            loadBtn.innerHTML = 'در حال بارگذاری...';

            try {
                const formData = new FormData();
                formData.append('action', 'raha_load_more_posts');
                formData.append('page', page + 1);
                formData.append('nonce', cfg.nonce);

                const res = await fetch(cfg.ajaxUrl, { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    // افزودن HTML جدید به گرید
                    if (data.data.html) {
                        grid.insertAdjacentHTML('beforeend', data.data.html);
                    }
                    
                    // آپدیت شماره صفحه
                    loadBtn.dataset.page = page + 1;
                    
                    // تصمیم‌گیری بر اساس پاسخ سرور (نه دیتای استاتیک)
                    if (!data.data.has_more) {
                        loadBtn.hidden = true;
                    }
                    
                    // اعمال مجدد فیلتر روی کارت‌های جدید
                    applyFilter(currentFilter);
                    
                    // راه‌اندازی Prefetch برای کارت‌های جدید
                    setupPrefetch();
                } else {
                    alert('خطا در بارگذاری.');
                }
            } catch (e) {
                console.error(e);
                alert('خطا در ارتباط با سرور.');
            } finally {
                loadBtn.disabled = false;
                loadBtn.innerHTML = originalHtml;
            }
        });
    }

    // Prefetch System
    const prefetched = new Set();
    function prefetch(url) {
        if (!url || prefetched.has(url)) return;
        prefetched.add(url);
        const l = document.createElement('link');
        l.rel = 'prefetch'; l.href = url; l.as = 'document';
        document.head.appendChild(l);
    }

    function setupPrefetch() {
        const cards = grid.querySelectorAll('.post');
        cards.forEach(card => {
            const link = card.querySelector('a[href]');
            if (!link) return;
            
            // جلوگیری از اجرای مجدد روی کارت‌هایی که قبلاً تنظیم شده‌اند
            if (card.dataset.prefetchSetup) return;
            card.dataset.prefetchSetup = 'true';

            const handler = () => prefetch(link.href);
            card.addEventListener('pointerenter', handler, { passive: true, once: true });
            card.addEventListener('focusin', handler, { once: true });
        });
    }

    setupPrefetch();
    applyFilter('all');
});