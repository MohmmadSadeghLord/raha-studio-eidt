document.addEventListener('DOMContentLoaded', function() {
    
    // ---------- 1. هدر شناور (Pill Scroll) ----------
    const header = document.getElementById('siteHeader');
    let ticking = false;
    const THRESHOLD = 32;

    function updateHeader() {
        if (header && window.scrollY > THRESHOLD) {
            header.classList.add('is-scrolled');
        } else if (header) {
            header.classList.remove('is-scrolled');
        }
        ticking = false;
    }

    if (header) {
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });
        updateHeader(); 
    }

    // ---------- 2. مگا منو و دراپ‌داون ----------
    const triggers = document.querySelectorAll('[data-target]');
    let activeTrigger = null;
    let activePanel   = null;
    let closeTimer    = null;

    // تابع کمکی برای پیدا کردن پنل بر اساس ID
    function getPanel(id) { return document.getElementById(id); }
    
    function clearClose() {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
    }
    function startClose() {
        clearClose();
        closeTimer = setTimeout(doClose, 240); // تاخیر ۲۴۰ میلی‌ثانیه برای هاور
    }
    function doClose() {
        if (activeTrigger) activeTrigger.setAttribute('aria-expanded', 'false');
        if (activePanel)   activePanel.classList.remove('is-open');
        activeTrigger = null;
        activePanel   = null;
    }
    function openNav(trigger, panel) {
        clearClose();
        if (activeTrigger && activeTrigger !== trigger) {
            activeTrigger.setAttribute('aria-expanded', 'false');
        }
        if (activePanel && activePanel !== panel) {
            activePanel.classList.remove('is-open');
        }
        trigger.setAttribute('aria-expanded', 'true');
        panel.classList.add('is-open');
        activeTrigger = trigger;
        activePanel   = panel;
    }

    if (triggers.length) {
        triggers.forEach(function (trigger) {
            const panel = getPanel(trigger.dataset.target);
            if (!panel) return;

            // رویدادهای هاور ماوس
            trigger.addEventListener('mouseenter', function () { openNav(trigger, panel); });
            trigger.addEventListener('mouseleave', function () {
                if (activeTrigger === trigger) startClose();
            });

            panel.addEventListener('mouseenter', clearClose);
            panel.addEventListener('mouseleave', function () {
                if (activeTrigger !== trigger) return;
                if (panel.contains(document.activeElement)) return; // اگر کاربر داخل پنل کلیک کرده، نبند
                startClose();
            });

            // رویدادهای کیبورد و فوکوس
            trigger.addEventListener('focus', function () { openNav(trigger, panel); });

            trigger.addEventListener('focusout', function (e) {
                if (activeTrigger !== trigger) return;
                const next = e.relatedTarget;
                if (next && (trigger.contains(next) || panel.contains(next))) return;
                startClose();
            });
            panel.addEventListener('focusout', function (e) {
                if (activeTrigger !== trigger) return;
                const next = e.relatedTarget;
                if (next && (trigger.contains(next) || panel.contains(next))) return;
                startClose();
            });

            trigger.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    openNav(trigger, panel);
                    const first = panel.querySelector('a, button');
                    if (first) first.focus();
                }
            });
            panel.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { doClose(); trigger.focus(); }
            });

            // کلیک روی دکمه
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                if (activeTrigger === trigger) doClose();
                else openNav(trigger, panel);
            });
        });

        // بستن با کلیک بیرون
        document.addEventListener('click', function (e) {
            if (!activeTrigger) return;
            if (!activeTrigger.contains(e.target) && !activePanel.contains(e.target)) doClose();
        });
        
        // بستن با کلید Esc
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && activeTrigger) doClose();
        });
    }

    // ---------- 3. منوی موبایل (Drawer) ----------
    const drawer = document.getElementById('mobileDrawer');
    const overlay = document.getElementById('drawerOverlay');
    const openBtn = document.getElementById('mobileMenuOpen');
    const closeBtn = document.getElementById('mobileMenuClose');
    let originalOverflow = '';

    function openDrawer() {
        if(!drawer || !overlay) return;
        drawer.classList.add('is-open');
        overlay.classList.add('is-open');
        if(openBtn) openBtn.setAttribute('aria-expanded', 'true');
        originalOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
    }
    
    function closeDrawer() {
        if(!drawer || !overlay) return;
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        if(openBtn) {
            openBtn.setAttribute('aria-expanded', 'false');
            openBtn.focus(); // بازگرداندن فوکوس به دکمه
        }
        document.body.style.overflow = originalOverflow;
    }

    if (openBtn) openBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);
    
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });

    // ---------- 4. منوی کاربر (آواتار در هدر دسکتاپ) ----------
    const userMenu = document.querySelector('[data-user-menu]');
    const userToggle = userMenu ? userMenu.querySelector('.raha-user-toggle') : null;

    if (userMenu && userToggle) {
        userToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = userMenu.classList.toggle('is-open');
            userToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('is-open');
                userToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                userMenu.classList.remove('is-open');
                userToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
});