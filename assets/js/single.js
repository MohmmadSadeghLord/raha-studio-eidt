document.addEventListener('DOMContentLoaded', function() {
    /* ---------- نوار پیشرفت مطالعه ---------- */
    const bar  = document.getElementById('readProgress');
    const body = document.getElementById('articleContent');

    function updateProgress() {
        if (!bar || !body) return;
        const rect   = body.getBoundingClientRect();
        const total  = rect.height - window.innerHeight;
        const passed = Math.min(Math.max(-rect.top, 0), Math.max(total, 0));
        const pct    = total > 0 ? (passed / total) * 100 : 0;
        bar.style.width = pct + '%';
    }

    let ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(function () { updateProgress(); ticking = false; });
            ticking = true;
        }
    }, { passive: true });
    window.addEventListener('resize', updateProgress);
    updateProgress();

    /* ---------- ساخت خودکار فهرست مطالب (TOC) ---------- */
    const toc = document.getElementById('toc');
    const tocNav = document.getElementById('tocNav');
    const headings = body.querySelectorAll('h2, h3');
    
    if (toc && tocNav && headings.length > 0) {
        headings.forEach(function(h, index) {
            if (!h.id) h.id = 'heading-' + index; // تزریق ID برای سئو
            
            const a = document.createElement('a');
            a.href = '#' + h.id;
            a.className = 'toc__link' + (h.tagName === 'H3' ? ' toc__link--sub' : '');
            a.textContent = h.textContent;
            
            a.addEventListener('click', function(e) {
                e.preventDefault();
                h.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (window.matchMedia('(max-width: 959px)').matches) toc.open = false;
                history.pushState(null, null, '#' + h.id);
            });
            tocNav.appendChild(a);
        });
    } else if (toc) {
        toc.style.display = 'none'; // پنهان کردن TOC اگر هدینگی نبود
    }

    /* ---------- آیتم فعال TOC ---------- */
    const tocLinks = document.querySelectorAll('.toc__link');
    const linkMap  = new Map();
    tocLinks.forEach(function(a) {
        const id = a.getAttribute('href').slice(1);
        if (id) linkMap.set(id, a);
    });

    if ('IntersectionObserver' in window && headings.length) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) return;
                tocLinks.forEach(function(l) { l.classList.remove('is-active'); });
                const active = linkMap.get(entry.target.id);
                if (active) active.classList.add('is-active');
            });
        }, { rootMargin: '-90px 0px -70% 0px', threshold: 0 });
        headings.forEach(function(h) { observer.observe(h); });
    }

    /* ---------- کپی کد ---------- */
    document.querySelectorAll('.code__copy').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const block = btn.closest('.code');
            const code  = block && block.querySelector('code');
            if (!code) return;
            const text = code.innerText;
            const done = function() {
                const original = btn.textContent;
                btn.textContent = 'کپی شد';
                setTimeout(function() { btn.textContent = original; }, 1600);
            };
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(done).catch(done);
            } else {
                done();
            }
        });
    });

    /* ---------- کپی لینک ---------- */
    document.querySelectorAll('[data-copy]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const url = window.location.href;
            const done = function() {
                btn.classList.add('is-copied');
                setTimeout(function() { btn.classList.remove('is-copied'); }, 1400);
            };
            if (navigator.clipboard) navigator.clipboard.writeText(url).then(done).catch(done);
            else done();
        });
    });
});