document.addEventListener('DOMContentLoaded', () => {
    const app = document.getElementById('raha-admin-app');
    if (!app) return;

    const config = window.RahaAdmin;

    /* ============================================================
       1. Helper Functions
       ============================================================ */
    const toFa = (num) => String(num).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    const formatMoney = (num) => toFa(Number(num).toLocaleString('en-US')) + ' تومان';

    /* ============================================================
       1.5. Toast System — Global API (NEW)
       ============================================================ */
    (function initToastSystem() {
        function ensureContainer() {
            let c = document.getElementById('raha-toasts');
            if (!c) {
                c = document.createElement('div');
                c.id = 'raha-toasts';
                c.className = 'raha-toasts';
                c.setAttribute('role', 'status');
                c.setAttribute('aria-live', 'polite');
                c.setAttribute('aria-atomic', 'false');
                document.body.appendChild(c);
            }
            return c;
        }

        const ICONS = {
            success: '<svg class="raha-icon" aria-hidden="true"><use href="#icon-check"></use></svg>',
            error:   '<svg class="raha-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>',
            warn:    '<svg class="raha-icon" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg>',
            info:    '<svg class="raha-icon" aria-hidden="true"><use href="#icon-info"></use></svg>'
        };

        const DURATIONS = { success: 3200, info: 3200, warn: 4500, error: 6000 };

        function showToast(message, type, options) {
            type = type || 'info';
            options = options || {};
            if (!ICONS[type]) type = 'info';

            const container = ensureContainer();
            const toast = document.createElement('div');
            toast.className = `raha-toast ${type}`;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const msgEl = document.createElement('span');
            msgEl.className = 'raha-toast__msg';
            msgEl.textContent = String(message);

            const iconWrapper = document.createElement('span');
            iconWrapper.className = 'raha-toast__icon';
            iconWrapper.innerHTML = ICONS[type];

            toast.appendChild(iconWrapper);
            toast.appendChild(msgEl);

            if (options.dismissible !== false) {
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'raha-toast__close';
                closeBtn.setAttribute('aria-label', 'بستن اعلان');
                closeBtn.innerHTML = '<svg class="raha-icon" aria-hidden="true"><use href="#icon-x"></use></svg>';
                closeBtn.addEventListener('click', () => close());
                toast.appendChild(closeBtn);
            }

            container.appendChild(toast);

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!reducedMotion) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-16px)';
                requestAnimationFrame(() => {
                    toast.style.transition = 'opacity .3s ease, transform .3s ease';
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateX(0)';
                });
            }

            let closed = false;
            let autoTimer = null;

            function close() {
                if (closed) return;
                closed = true;
                if (autoTimer) clearTimeout(autoTimer);
                if (reducedMotion) {
                    toast.remove();
                    if (typeof options.onClose === 'function') options.onClose();
                    return;
                }
                toast.classList.add('hiding');
                toast.addEventListener('animationend', () => {
                    toast.remove();
                    if (typeof options.onClose === 'function') options.onClose();
                }, { once: true });
            }

            const duration = options.duration != null ? options.duration : DURATIONS[type];
            if (duration > 0) autoTimer = setTimeout(close, duration);

            if (!reducedMotion && duration > 0) {
                toast.addEventListener('mouseenter', () => {
                    if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; }
                });
                toast.addEventListener('mouseleave', () => {
                    if (!closed && duration > 0) autoTimer = setTimeout(close, 1200);
                });
            }

            return { close, el: toast };
        }

        window.showRahaToast = showToast;
    })();

    /* ============================================================
       1.6. Theme Init (NEW)
       ============================================================ */
    function initTheme() {
        const saved = localStorage.getItem('raha-theme') || 'light';
        const target = document.getElementById('raha-admin-app');
        if (target) target.setAttribute('data-theme', saved);
        document.body.setAttribute('data-theme', saved);
    }
    initTheme();

    const themeToggle = document.getElementById('raha-theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = document.getElementById('raha-admin-app').getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.getElementById('raha-admin-app').setAttribute('data-theme', next);
            document.body.setAttribute('data-theme', next);
            localStorage.setItem('raha-theme', next);
            const use = themeToggle.querySelector('use');
            if (use) use.setAttribute('href', next === 'dark' ? '#icon-sun' : '#icon-moon');
        });
        // set initial icon
        const saved = localStorage.getItem('raha-theme') || 'light';
        const use = themeToggle.querySelector('use');
        if (use) use.setAttribute('href', saved === 'dark' ? '#icon-sun' : '#icon-moon');
    }

    /* ============================================================
       2. API Helper
       ============================================================ */
    async function apiFetch(endpoint, params = {}) {
        const url = new URL(config.restUrl + endpoint);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': config.nonce,
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            window.showRahaToast('خطا در ارتباط با سرور', 'error');
            return null;
        }
    }

    /* ============================================================
       3. SPA Routing Engine
       ============================================================ */
    const navItems = document.querySelectorAll('.raha-nav-item');
    const viewContainer = document.getElementById('raha-view-container');
    const breadcrumb = document.getElementById('raha-breadcrumb');

    async function navigateTo(route, pushState = true) {
        navItems.forEach(item => {
            item.classList.toggle('active', item.dataset.route === route);
        });

        const activeMenu = document.querySelector(`.raha-nav-item[data-route="${route}"]`);
        if (activeMenu) breadcrumb.textContent = activeMenu.textContent.trim();

        // نمایش لودینگ
        viewContainer.innerHTML = `
            <div class="raha-skeleton-wrapper">
                <div class="raha-skeleton-card" style="height:100px;"></div>
                <div class="raha-skeleton-card" style="height:400px;"></div>
            </div>
        `;

        // رندر صفحات
        if (route === 'dashboard') await loadDashboardView();
        else if (route === 'orders') await loadOrdersView();
        else if (route === 'invoices') await loadInvoicesView();
        else if (route === 'tickets') await loadTicketsView();
        else if (route === 'audit-logs') await loadAuditLogsView();
        else if (route === 'settings') await loadSettingsView();
        else if (route === 'tools') await loadToolsView();
        else viewContainer.innerHTML = `<div class="raha-card"><h2>صفحه ${route}</h2><p>در حال ساخت...</p></div>`;

        if (pushState) history.pushState({ route }, '', `#${route}`);
    }

    /* ============================================================
       4. View: Dashboard
       ============================================================ */
    async function loadDashboardView() {
        const data = await apiFetch('dashboard');
        if (!data) return;

        const statsHTML = `
            <div class="raha-stats-grid">
                <div class="raha-stat-card">
                    <div class="raha-stat-icon" style="background:#e0f2fe;">📦</div>
                    <div class="raha-stat-info"><span>کل سفارشات</span><strong>${toFa(data.stats.total_orders)}</strong></div>
                </div>
                <div class="raha-stat-card">
                    <div class="raha-stat-icon" style="background:#dcfce7;">💰</div>
                    <div class="raha-stat-info"><span>درآمد کل</span><strong>${formatMoney(data.stats.total_revenue)}</strong></div>
                </div>
                <div class="raha-stat-card">
                    <div class="raha-stat-icon" style="background:#fef9c3;">💬</div>
                    <div class="raha-stat-info"><span>تیکت‌های فعال</span><strong>${toFa(data.stats.active_tickets)}</strong></div>
                </div>
            </div>
        `;

        let recentHTML = '';
        if (data.recent_orders.length > 0) {
            data.recent_orders.forEach(o => {
                recentHTML += `
                    <tr>
                        <td><strong dir="ltr">${o.code}</strong></td>
                        <td>${o.customer}</td>
                        <td>${o.service}</td>
                        <td>${o.amount ? formatMoney(o.amount) : 'در انتظار'}</td>
                        <td><span class="raha-badge raha-badge-${o.status}">${o.status}</span></td>
                        <td>${toFa(o.date)}</td>
                    </tr>
                `;
            });
        } else {
            recentHTML = '<tr><td colspan="6" style="text-align:center;">سفارشی یافت نشد.</td></tr>';
        }

        viewContainer.innerHTML = `
            <div class="raha-page-header"><h1>داشبورد مدیریت</h1><p>خلاصه‌ای از عملکرد استودیو</p></div>
            ${statsHTML}
            <div class="raha-card" style="margin-top: 24px;">
                <div class="raha-card-header">
                    <h3>آخرین سفارشات</h3>
                    <a href="#orders" class="raha-nav-item" style="padding:4px 8px; font-size:12px; color:var(--accent);">مشاهده همه</a>
                </div>
                <table class="raha-table">
                    <thead><tr><th>کد پیگیری</th><th>مشتری</th><th>خدمت</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                    <tbody>${recentHTML}</tbody>
                </table>
            </div>
        `;
    }

    /* ============================================================
       5. View: Orders (with Filter + Bulk Actions)
       ============================================================ */
    let currentOrderSearch = '';
    let currentOrderFilter = 'all';

    async function loadOrdersView(page = 1, search = '', filter = 'all') {
        currentOrderSearch = search;
        currentOrderFilter = filter;

        const data = await apiFetch('orders', { page, search });
        if (!data) return;

        let filteredOrders = data.orders;
        if (filter !== 'all') {
            filteredOrders = data.orders.filter(o => o.status === filter);
        }

        let rowsHTML = '';
        if (filteredOrders.length > 0) {
            filteredOrders.forEach(o => {
                rowsHTML += `
                    <tr>
                        <td><input type="checkbox" class="order-checkbox" value="${o.id}"></td>
                        <td><strong dir="ltr">${o.code}</strong></td>
                        <td>${o.customer}</td>
                        <td>${o.service}</td>
                        <td>${o.amount ? formatMoney(o.amount) : '—'}</td>
                        <td><span class="raha-badge raha-badge-${o.status}">${o.status}</span></td>
                        <td>${toFa(o.date)}</td>
                        <td>
                            <button class="raha-btn raha-btn-sm raha-btn-primary view-order-btn" data-id="${o.id}">جزئیات</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            rowsHTML = '<tr><td colspan="8" style="text-align:center; padding: 40px;">سفارشی یافت نشد.</td></tr>';
        }

        let pagerHTML = '';
        if (data.pages > 1 && filter === 'all') {
            pagerHTML = `<div class="raha-pagination">`;
            for (let i = 1; i <= data.pages; i++) {
                pagerHTML += `<button class="raha-page-btn ${i === page ? 'active' : ''}" data-page="${i}">${toFa(i)}</button>`;
            }
            pagerHTML += `</div>`;
        }

        viewContainer.innerHTML = `
            <div class="raha-page-header">
                <h1>مدیریت سفارشات</h1>
                <div class="raha-topbar-actions">
                    <input type="text" id="order-search-input" class="raha-input" placeholder="جستجوی کد یا نام مشتری..." value="${search}">
                </div>
            </div>

            <div class="raha-toolbar">
                <div class="raha-filters">
                    <button class="raha-filter-btn ${filter === 'all' ? 'active' : ''}" data-filter="all">همه</button>
                    <button class="raha-filter-btn ${filter === 'new' ? 'active' : ''}" data-filter="new">جدید</button>
                    <button class="raha-filter-btn ${filter === 'progress' ? 'active' : ''}" data-filter="progress">در حال انجام</button>
                    <button class="raha-filter-btn ${filter === 'done' ? 'active' : ''}" data-filter="done">تکمیل شده</button>
                </div>
                <div class="raha-bulk-actions" id="bulk-actions-bar" style="display:none;">
                    <span id="selected-count">۰</span> مورد انتخاب شده
                    <select id="bulk-status-select" class="raha-input" style="padding:4px 8px; height:32px; margin-inline-start:12px;">
                        <option value="">تغییر وضعیت به...</option>
                        <option value="progress">در حال انجام</option>
                        <option value="done">تکمیل شده</option>
                    </select>
                    <button class="raha-btn raha-btn-sm raha-btn-primary" id="apply-bulk-btn" style="margin-inline-start:8px;">اعمال</button>
                </div>
            </div>

            <div class="raha-card">
                <table class="raha-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="select-all-orders"></th>
                            <th>کد پیگیری</th>
                            <th>مشتری</th>
                            <th>خدمت</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
                ${pagerHTML}
            </div>
        `;

        // رویدادهای جستجو
        const searchInput = document.getElementById('order-search-input');
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadOrdersView(1, e.target.value, currentOrderFilter), 500);
        });

        // رویدادهای فیلتر
        document.querySelectorAll('.raha-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                loadOrdersView(1, currentOrderSearch, btn.dataset.filter);
            });
        });

        // رویدادهای دکمه جزئیات
        document.querySelectorAll('.view-order-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const order = data.orders.find(o => o.id == btn.dataset.id);
                if (order) openOrderDrawer(order);
            });
        });

        // منطق Checkboxها و Bulk Actions
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const selectAll = document.getElementById('select-all-orders');
        const bulkBar = document.getElementById('bulk-actions-bar');
        const countDisplay = document.getElementById('selected-count');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.order-checkbox:checked');
            countDisplay.textContent = toFa(checked.length);
            bulkBar.style.display = checked.length > 0 ? 'flex' : 'none';
        }

        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                checkboxes.forEach(cb => cb.checked = e.target.checked);
                updateBulkBar();
            });
        }
        checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

        // اجرای Bulk Action
        const applyBtn = document.getElementById('apply-bulk-btn');
        if (applyBtn) {
            applyBtn.addEventListener('click', async () => {
                const newStatus = document.getElementById('bulk-status-select').value;
                if (!newStatus) return window.showRahaToast('لطفا وضعیت جدید را انتخاب کنید', 'error');

                const selectedIds = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
                if (selectedIds.length === 0) return;

                applyBtn.disabled = true;
                applyBtn.textContent = 'در حال اعمال...';

                try {
                    const res = await fetch(config.restUrl + 'orders/update-status', {
                        method: 'POST',
                        headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ ids: selectedIds, status: newStatus })
                    });
                    const result = await res.json();
                    if (res.ok) {
                        window.showRahaToast(result.message, 'success');
                        loadOrdersView(1, currentOrderSearch, currentOrderFilter);
                    } else {
                        window.showRahaToast(result.message || 'خطا', 'error');
                    }
                } catch (e) {
                    window.showRahaToast('خطای شبکه', 'error');
                } finally {
                    applyBtn.disabled = false;
                    applyBtn.textContent = 'اعمال';
                }
            });
        }
    }

    /* ============================================================
       6. View: Invoices
       ============================================================ */
    async function loadInvoicesView(page = 1) {
        const data = await apiFetch('invoices', { page });
        if (!data) return;

        let rowsHTML = '';
        if (data.invoices.length > 0) {
            data.invoices.forEach(inv => {
                const statusBadge = inv.status === 'paid'
                    ? '<span class="raha-badge" style="background:#dcfce7;color:#14532d;">پرداخت شده</span>'
                    : '<span class="raha-badge" style="background:#fee2e2;color:#7f1d1d;">پرداخت نشده</span>';

                rowsHTML += `
                    <tr>
                        <td><strong dir="ltr">${inv.number}</strong></td>
                        <td>${inv.customer}</td>
                        <td><strong>${formatMoney(inv.total)}</strong></td>
                        <td>${statusBadge}</td>
                        <td>${toFa(inv.date)}</td>
                        <td>${toFa(inv.due_date)}</td>
                        <td><button class="raha-btn raha-btn-sm raha-btn-primary" data-action="view-invoice" data-id="${inv.id}">مشاهده</button></td>
                    </tr>
                `;
            });
        } else {
            rowsHTML = '<tr><td colspan="7" style="text-align:center; padding: 40px; color:#64748b;">هیچ فاکتوری صادر نشده است.</td></tr>';
        }

        let pagerHTML = '';
        if (data.pages > 1) {
            pagerHTML = `<div class="raha-pagination">`;
            for (let i = 1; i <= data.pages; i++) {
                pagerHTML += `<button class="raha-page-btn ${i === page ? 'active' : ''}" data-page="${i}">${toFa(i)}</button>`;
            }
            pagerHTML += `</div>`;
        }

        viewContainer.innerHTML = `
            <div class="raha-page-header"><h1>مدیریت مالی و فاکتورها</h1><p>کنترل جریان نقدینگی و پرداخت‌ها</p></div>
            <div class="raha-card">
                <table class="raha-table">
                    <thead><tr><th>شماره فاکتور</th><th>مشتری</th><th>مبلغ کل</th><th>وضعیت</th><th>تاریخ صدور</th><th>سررسید</th><th>عملیات</th></tr></thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
                ${pagerHTML}
            </div>
        `;
    }

    /* ============================================================
       7. View: Tickets
       ============================================================ */
    async function loadTicketsView(page = 1) {
        const data = await apiFetch('tickets', { page });
        if (!data) return;

        let rowsHTML = '';
        if (data.tickets.length > 0) {
            data.tickets.forEach(t => {
                const statusMap = {
                    'open': { label: 'باز', bg: '#fef9c3', color: '#854d0e' },
                    'progress': { label: 'در حال بررسی', bg: '#e0f2fe', color: '#0369a1' },
                    'waiting': { label: 'در انتظار مشتری', bg: '#ffedd5', color: '#9a3412' },
                    'closed': { label: 'بسته شده', bg: '#dcfce7', color: '#14532d' }
                };
                const st = statusMap[t.status] || statusMap['open'];
                const statusBadge = `<span class="raha-badge" style="background:${st.bg};color:${st.color};">${st.label}</span>`;

                const priorityMap = {
                    'high': '<span style="color:#dc2626; font-weight:900;">بالا</span>',
                    'normal': '<span style="color:#d97706; font-weight:900;">متوسط</span>',
                    'low': '<span style="color:#16a34a; font-weight:900;">کم</span>'
                };
                const priorityBadge = priorityMap[t.priority] || t.priority;

                rowsHTML += `
                    <tr>
                        <td><strong>#${toFa(t.id)}</strong></td>
                        <td><strong>${t.subject}</strong></td>
                        <td>${t.customer}</td>
                        <td>${priorityBadge}</td>
                        <td>${statusBadge}</td>
                        <td>${toFa(t.date)}</td>
                        <td><button class="raha-btn raha-btn-sm" data-action="reply-ticket" data-id="${t.id}" style="background:var(--accent); color:#fff;">پاسخ</button></td>
                    </tr>
                `;
            });
        } else {
            rowsHTML = '<tr><td colspan="7" style="text-align:center; padding: 40px; color:#64748b;">تیکتی یافت نشد.</td></tr>';
        }

        let pagerHTML = '';
        if (data.pages > 1) {
            pagerHTML = `<div class="raha-pagination">`;
            for (let i = 1; i <= data.pages; i++) {
                pagerHTML += `<button class="raha-page-btn ${i === page ? 'active' : ''}" data-page="${i}">${toFa(i)}</button>`;
            }
            pagerHTML += `</div>`;
        }

        viewContainer.innerHTML = `
            <div class="raha-page-header"><h1>تیکت‌های پشتیبانی</h1><p>مدیریت درخواست‌ها و ارتباط با مشتریان</p></div>
            <div class="raha-card">
                <table class="raha-table">
                    <thead><tr><th>شناسه</th><th>موضوع</th><th>مشتری</th><th>اولویت</th><th>وضعیت</th><th>تاریخ ثبت</th><th>عملیات</th></tr></thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
                ${pagerHTML}
            </div>
        `;
    }

    /* ============================================================
       8. View: Audit Logs
       ============================================================ */
    async function loadAuditLogsView(page = 1) {
        const data = await apiFetch('audit-logs', { page });
        if (!data) return;

        let rowsHTML = '';
        if (data.logs.length > 0) {
            data.logs.forEach(log => {
                let diffHTML = '—';
                if (log.before || log.after) {
                    diffHTML = `<button class="raha-btn raha-btn-sm" onclick="window.showAuditDiff(${log.id}, '${btoa(unescape(encodeURIComponent(log.before || '{}')))}', '${btoa(unescape(encodeURIComponent(log.after || '{}')))}')">تغییرات</button>`;
                }

                let actionBadge = `<span class="raha-badge">${log.action}</span>`;
                if (log.action === 'create') actionBadge = `<span class="raha-badge" style="background:#dcfce7;color:#14532d;">ایجاد</span>`;
                if (log.action === 'update') actionBadge = `<span class="raha-badge" style="background:#e0f2fe;color:#0369a1;">ویرایش</span>`;
                if (log.action === 'delete') actionBadge = `<span class="raha-badge" style="background:#fee2e2;color:#7f1d1d;">حذف</span>`;

                rowsHTML += `
                    <tr>
                        <td>${toFa(log.date)}</td>
                        <td><strong>${log.actor}</strong></td>
                        <td>${actionBadge}</td>
                        <td><strong dir="ltr">${log.entity_type} #${log.entity_id}</strong></td>
                        <td dir="ltr" style="font-family:monospace; font-size:12px;">${log.ip}</td>
                        <td>${diffHTML}</td>
                    </tr>
                `;
            });
        } else {
            rowsHTML = '<tr><td colspan="6" style="text-align:center; padding: 40px; color:#64748b;">هیچ لاگی ثبت نشده است.</td></tr>';
        }

        let pagerHTML = '';
        if (data.pages > 1) {
            pagerHTML = `<div class="raha-pagination">`;
            for (let i = 1; i <= data.pages; i++) {
                pagerHTML += `<button class="raha-page-btn ${i === page ? 'active' : ''}" data-page="${i}">${toFa(i)}</button>`;
            }
            pagerHTML += `</div>`;
        }

        viewContainer.innerHTML = `
            <div class="raha-page-header"><h1>گزارش فعالیت‌ها</h1><p>ردیابی امنیتی تمام تغییرات سیستم</p></div>
            <div class="raha-card">
                <table class="raha-table">
                    <thead><tr><th>تاریخ</th><th>کاربر</th><th>عملیات</th><th>موجودیت</th><th>IP آدرس</th><th>جزئیات</th></tr></thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
                ${pagerHTML}
            </div>
        `;
    }

    /* ============================================================
       9. View: Settings
       ============================================================ */
    async function loadSettingsView() {
        const data = await apiFetch('settings');
        if (!data) return;

        viewContainer.innerHTML = `
            <div class="raha-page-header"><h1>تنظیمات سیستم</h1><p>مدیریت اطلاعات پایه و اتصالات</p></div>
            <div class="raha-card" style="padding: 32px; max-width: 800px;">
                <form id="raha-settings-form">
                    <div class="raha-form-grid">
                        <div class="raha-field"><label>نام استودیو</label><input type="text" name="studio_name" class="raha-input" value="${data.studio_name}"></div>
                        <div class="raha-field"><label>ایمیل پشتیبانی</label><input type="email" name="support_email" class="raha-input" dir="ltr" value="${data.support_email}"></div>
                        <div class="raha-field"><label>شماره تماس پشتیبانی</label><input type="text" name="support_phone" class="raha-input" dir="ltr" value="${data.support_phone}"></div>
                        <div class="raha-field"><label>مرچنت کد درگاه پرداخت</label><input type="text" name="gateway_key" class="raha-input" dir="ltr" value="${data.gateway_key}"></div>
                    </div>
                    <button type="submit" class="raha-btn raha-btn-primary" style="margin-top:24px; width: 200px;">ذخیره تنظیمات</button>
                </form>
            </div>
        `;

        document.getElementById('raha-settings-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button');
            btn.disabled = true;
            btn.textContent = 'در حال ذخیره...';

            const payload = {
                studio_name: form.studio_name.value,
                support_email: form.support_email.value,
                support_phone: form.support_phone.value,
                gateway_key: form.gateway_key.value,
            };

            try {
                const res = await fetch(config.restUrl + 'settings', {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (res.ok) window.showRahaToast('تنظیمات با موفقیت ذخیره شد', 'success');
                else window.showRahaToast('خطا در ذخیره تنظیمات', 'error');
            } catch (err) {
                window.showRahaToast('خطای شبکه', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'ذخیره تنظیمات';
            }
        });
    }

    /* ============================================================
       10. View: Tools — Demo of all 32 components (Phase 1 Item N)
       ============================================================ */
    async function loadToolsView() {
        viewContainer.innerHTML = `
            <div class="raha-page-header">
                <h1>آزمایشگاه کامپوننت‌ها</h1>
                <p>نمونه‌ی زنده‌ی هر ۳۲ کامپوننت پایه در همه حالت‌ها — Item N / O / P</p>
            </div>

            <!-- ============ 1. BUTTON ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱ — Button</h3>
                    <span class="raha-tag">.raha-btn</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                        <button class="raha-btn">Default</button>
                        <button class="raha-btn raha-btn-primary">Primary</button>
                        <button class="raha-btn raha-btn-sun">Sun</button>
                        <button class="raha-btn raha-btn-danger">Danger</button>
                        <button class="raha-btn raha-btn-ghost">Ghost</button>
                        <button class="raha-btn raha-btn-sm">Small</button>
                        <button class="raha-btn raha-btn-lg">Large</button>
                        <button class="raha-btn is-loading">Loading</button>
                        <button class="raha-btn" disabled>Disabled</button>
                    </div>
                </div>
            </div>

            <!-- ============ 2. ICON BUTTON ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲ — Icon Button</h3>
                    <span class="raha-tag">.raha-icon-btn</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;gap:12px;align-items:center;">
                        <button class="raha-icon-btn" aria-label="افزودن"><svg class="raha-icon" aria-hidden="true"><use href="#icon-plus"></use></svg></button>
                        <button class="raha-icon-btn raha-icon-btn-sm" aria-label="بستن"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-x"></use></svg></button>
                        <button class="raha-icon-btn raha-icon-btn-ghost" aria-label="تنظیمات"><svg class="raha-icon" aria-hidden="true"><use href="#icon-settings"></use></svg></button>
                        <button class="raha-icon-btn" aria-label="اعلان" style="position:relative;">
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-bell"></use></svg>
                            <span class="raha-dot"></span>
                        </button>
                        <button class="raha-icon-btn" disabled aria-label="غیرفعال"><svg class="raha-icon" aria-hidden="true"><use href="#icon-lock"></use></svg></button>
                    </div>
                </div>
            </div>

            <!-- ============ 3. STATUS BADGE ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۳ — Status Badge — همه ۷ variant</h3>
                    <span class="raha-tag">.raha-status-badge</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <span class="raha-status-badge raha-status-badge--pending">در انتظار</span>
                        <span class="raha-status-badge raha-status-badge--progress">در حال انجام</span>
                        <span class="raha-status-badge raha-status-badge--review">در بازبینی</span>
                        <span class="raha-status-badge raha-status-badge--done">تکمیل شده</span>
                        <span class="raha-status-badge raha-status-badge--canceled">لغو شده</span>
                        <span class="raha-status-badge raha-status-badge--open">باز</span>
                        <span class="raha-status-badge raha-status-badge--closed">بسته</span>
                    </div>
                </div>
            </div>

            <!-- ============ 4. TAG ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۴ — Tag</h3>
                    <span class="raha-tag">.raha-tag</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <span class="raha-tag">طراحی</span>
                        <span class="raha-tag">توسعه</span>
                        <span class="raha-tag">سئو</span>
                        <span class="raha-tag">اولویت بالا</span>
                    </div>
                </div>
            </div>

            <!-- ============ 5. CARD ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۵ — Card</h3>
                    <span class="raha-tag">.raha-card</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-card" style="margin:0;">
                        <div class="raha-card-head">
                            <h3 class="raha-card-title">نمونه کارت</h3>
                            <button class="raha-icon-btn raha-icon-btn-sm raha-icon-btn-ghost" aria-label="گزینه‌ها"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-more-vertical"></use></svg></button>
                        </div>
                        <div class="raha-card-body">محتوای کارت — اینجا می‌تواند هر چیزی باشد.</div>
                        <div class="raha-card-foot"><button class="raha-btn raha-btn-sm raha-btn-primary">اقدام</button></div>
                    </div>
                </div>
            </div>

            <!-- ============ 6. STAT CARD ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۶ — Stat Card — ۴ رنگ</h3>
                    <span class="raha-tag">.raha-stat-card</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-stats-grid" style="margin:0;">
                        <div class="raha-stat-card">
                            <div class="raha-stat-icon blue">📦</div>
                            <div class="raha-stat-info"><span>کل سفارشات</span><strong>۱٬۲۳۴</strong><small class="raha-stat-card__trend up">↑ ۱۲٪</small></div>
                        </div>
                        <div class="raha-stat-card">
                            <div class="raha-stat-icon yellow">💰</div>
                            <div class="raha-stat-info"><span>درآمد</span><strong>۸۵ م</strong><small class="raha-stat-card__trend up">↑ ۸٪</small></div>
                        </div>
                        <div class="raha-stat-card">
                            <div class="raha-stat-icon green">✅</div>
                            <div class="raha-stat-info"><span>تکمیل</span><strong>۹۸۰</strong><small class="raha-stat-card__trend flat">—</small></div>
                        </div>
                        <div class="raha-stat-card">
                            <div class="raha-stat-icon red">⚠️</div>
                            <div class="raha-stat-info"><span>بازگشت</span><strong>۴۲</strong><small class="raha-stat-card__trend down">↓ ۳٪</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ 7. PROGRESS ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۷ — Progress</h3>
                    <span class="raha-tag">.raha-progress</span>
                </div>
                <div class="raha-card-body" style="display:flex;flex-direction:column;gap:12px;">
                    <div class="raha-progress"><span style="width: 45%;"></span></div>
                    <div class="raha-progress"><span style="width: 80%;"></span></div>
                    <div class="raha-progress raha-progress--striped"><span style="width: 65%;"></span></div>
                </div>
            </div>

            <!-- ============ 8. STEPS ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۸ — Steps</h3>
                    <span class="raha-tag">.raha-steps</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-steps">
                        <span class="raha-step done">ثبت سفارش</span>
                        <span class="raha-step done">تایید پرداخت</span>
                        <span class="raha-step now">در حال انجام</span>
                        <span class="raha-step">تحویل</span>
                    </div>
                </div>
            </div>

            <!-- ============ 9. TIMELINE ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۹ — Timeline</h3>
                    <span class="raha-tag">.raha-timeline</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-timeline">
                        <div class="raha-tl-item ok"><span class="raha-tl-item__time">۱۴۰۳/۰۸/۱۵ — ۱۰:۳۰</span><p class="raha-tl-item__title">پرداخت موفق</p><p class="raha-tl-item__desc">مبلغ ۲٬۵۰۰٬۰۰۰ تومان تایید شد.</p></div>
                        <div class="raha-tl-item info"><span class="raha-tl-item__time">۱۴۰۳/۰۸/۱۵ — ۱۰:۲۵</span><p class="raha-tl-item__title">درخواست ثبت شد</p><p class="raha-tl-item__desc">سفارش توسط مشتری ثبت شد.</p></div>
                        <div class="raha-tl-item warn"><span class="raha-tl-item__time">۱۴۰۳/۰۸/۱۵ — ۱۰:۲۰</span><p class="raha-tl-item__title">هشدار</p><p class="raha-tl-item__desc">موجودی محدود بود.</p></div>
                    </div>
                </div>
            </div>

            <!-- ============ 10. FILE ITEM ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۰ — File Item — ۵ نوع</h3>
                    <span class="raha-tag">.raha-file-item</span>
                </div>
                <div class="raha-card-body" style="display:flex;flex-direction:column;gap:10px;">
                    <div class="raha-file-item"><div class="raha-file-ext pdf">PDF</div><div><strong>قرارداد.pdf</strong><br><small>۲.۳ MB</small></div></div>
                    <div class="raha-file-item"><div class="raha-file-ext img">IMG</div><div><strong>لوگو.png</strong><br><small>۴۵۰ KB</small></div></div>
                    <div class="raha-file-item"><div class="raha-file-ext zip">ZIP</div><div><strong>پروژه.zip</strong><br><small>۱۲ MB</small></div></div>
                    <div class="raha-file-item"><div class="raha-file-ext doc">DOC</div><div><strong>پیشنهاد.docx</strong><br><small>۸۰۰ KB</small></div></div>
                    <div class="raha-file-item"><div class="raha-file-ext other">BIN</div><div><strong>فایل.bin</strong><br><small>۲ MB</small></div></div>
                </div>
            </div>

            <!-- ============ 11. QUICK BTN ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۱ — Quick Btn</h3>
                    <span class="raha-tag">.raha-quick-btn</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;">
                        <button class="raha-quick-btn"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-plus"></use></svg> سفارش جدید</button>
                        <button class="raha-quick-btn"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-users"></use></svg> مشتری جدید</button>
                        <button class="raha-quick-btn"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-upload-cloud"></use></svg> آپلود فایل</button>
                        <button class="raha-quick-btn"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-refresh"></use></svg> بازخوانی</button>
                    </div>
                </div>
            </div>

            <!-- ============ 12. BANNER ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۲ — Banner — ۴ variant</h3>
                    <span class="raha-tag">.raha-banner</span>
                </div>
                <div class="raha-card-body" style="display:flex;flex-direction:column;gap:10px;">
                    <div class="raha-banner raha-banner--success"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-check"></use></svg> عملیات با موفقیت انجام شد.</div>
                    <div class="raha-banner raha-banner--info"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-info"></use></svg> اطلاعات جدید در دسترس است.</div>
                    <div class="raha-banner raha-banner--warn"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg> هشدار: موجودی کم است.</div>
                    <div class="raha-banner raha-banner--danger"><svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg> خطا در ارتباط با سرور.</div>
                </div>
            </div>

            <!-- ============ 13. TOOLBAR + CHIPS ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۳ — Toolbar + Chips</h3>
                    <span class="raha-tag">.raha-toolbar, .raha-chip</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-toolbar" style="margin:0;">
                        <div class="raha-filters">
                            <button class="raha-chip active">همه</button>
                            <button class="raha-chip">جدید</button>
                            <button class="raha-chip">در حال انجام</button>
                            <button class="raha-chip">تکمیل</button>
                        </div>
                        <div class="raha-bulk-actions">۲ مورد انتخاب شده</div>
                    </div>
                </div>
            </div>

            <!-- ============ 14. TABLE ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۴ — Table (ستونی + Mobile Stack)</h3>
                    <span class="raha-tag">.raha-table</span>
                </div>
                <div class="raha-card-body tight">
                    <table class="raha-table">
                        <thead><tr><th>کد</th><th>مشتری</th><th>وضعیت</th></tr></thead>
                        <tbody>
                            <tr><td>R-1001</td><td>سارا احمدی</td><td><span class="raha-status-badge raha-status-badge--done">تکمیل</span></td></tr>
                            <tr><td>R-1002</td><td>علی رضایی</td><td><span class="raha-status-badge raha-status-badge--progress">در حال انجام</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============ 15. PAGER ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۵ — Pager</h3>
                    <span class="raha-tag">.raha-pager</span>
                </div>
                <div class="raha-card-body tight">
                    <div class="raha-pager" style="border-top:0;">
                        <button class="raha-pg" disabled aria-label="قبلی"><svg class="raha-icon raha-icon-sm"><use href="#icon-chevron-right"></use></svg></button>
                        <button class="raha-pg" aria-current="page">۱</button>
                        <button class="raha-pg">۲</button>
                        <button class="raha-pg">۳</button>
                        <span class="raha-pg raha-pg--dots">…</span>
                        <button class="raha-pg">۱۰</button>
                        <button class="raha-pg" aria-label="بعدی"><svg class="raha-icon raha-icon-sm"><use href="#icon-chevron-left"></use></svg></button>
                    </div>
                </div>
            </div>

            <!-- ============ 16. EMPTY STATE ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۶ — Empty State</h3>
                    <span class="raha-tag">.raha-empty-state</span>
                </div>
                <div class="raha-card-body" style="padding:0;">
                    <div class="raha-empty-state">
                        <div class="raha-empty-ico"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-shopping-bag"></use></svg></div>
                        <h3>هنوز سفارشی ندارید</h3>
                        <p>برای شروع، اولین سفارش خود را از دکمه زیر ثبت کنید.</p>
                        <button class="raha-btn raha-btn-primary">ثبت سفارش</button>
                    </div>
                </div>
            </div>

            <!-- ============ 17. ERROR STATE ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۷ — Error State</h3>
                    <span class="raha-tag">.raha-error-state</span>
                </div>
                <div class="raha-card-body" style="padding:0;">
                    <div class="raha-error-state">
                        <div class="raha-error-ico"><svg class="raha-icon raha-icon-lg" aria-hidden="true"><use href="#icon-alert-triangle"></use></svg></div>
                        <h3>خطا در بارگذاری</h3>
                        <p>اتصال به سرور برقرار نشد. لطفاً مجدداً تلاش کنید.</p>
                        <button class="raha-btn raha-btn-danger">تلاش مجدد</button>
                    </div>
                </div>
            </div>

            <!-- ============ 18. SKELETON ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۸ — Skeleton — ۴ شکل</h3>
                    <span class="raha-tag">.raha-skel</span>
                </div>
                <div class="raha-card-body" style="display:flex;flex-direction:column;gap:12px;">
                    <div class="raha-skel raha-skel--line" style="width:60%;"></div>
                    <div class="raha-skel raha-skel--pill"></div>
                    <div class="raha-skel raha-skel--dot"></div>
                    <div class="raha-skel raha-skel--card"></div>
                </div>
            </div>

            <!-- ============ 19. FORM FIELD ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۱۹ — Form Field + Input/Textarea/Select</h3>
                    <span class="raha-tag">.raha-field</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-form-grid">
                        <div class="raha-field">
                            <label for="demo-input-1">نام</label>
                            <input id="demo-input-1" type="text" class="raha-input" placeholder="مثلاً سارا">
                            <small class="raha-field__hint">نام کامل خود را وارد کنید.</small>
                        </div>
                        <div class="raha-field has-error">
                            <label for="demo-input-2">ایمیل</label>
                            <input id="demo-input-2" type="email" class="raha-input" value="invalid@" dir="ltr">
                            <small class="raha-field__error">ایمیل معتبر نیست.</small>
                        </div>
                        <div class="raha-field">
                            <label for="demo-input-3">غیرفعال</label>
                            <input id="demo-input-3" type="text" class="raha-input" value="قابل ویرایش نیست" disabled>
                        </div>
                        <div class="raha-field">
                            <label for="demo-input-4">نوع خدمت</label>
                            <select id="demo-input-4" class="raha-select">
                                <option>طراحی سایت</option>
                                <option>توسعه اپلیکیشن</option>
                                <option>سئو</option>
                            </select>
                        </div>
                        <div class="raha-field" style="grid-column:1/-1;">
                            <label for="demo-input-5">توضیحات</label>
                            <textarea id="demo-input-5" class="raha-textarea" placeholder="توضیحات خود را بنویسید..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ 20. SWITCH ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۰ — Switch</h3>
                    <span class="raha-tag">.raha-switch</span>
                </div>
                <div class="raha-card-body" style="display:flex;flex-direction:column;gap:16px;">
                    <label class="raha-switch"><input type="checkbox"><span class="raha-switch__track"></span><span>خاموش</span></label>
                    <label class="raha-switch"><input type="checkbox" checked><span class="raha-switch__track"></span><span>روشن</span></label>
                    <label class="raha-switch"><input type="checkbox" disabled><span class="raha-switch__track"></span><span>غیرفعال</span></label>
                </div>
            </div>

            <!-- ============ 21. TABS ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۱ — Tabs</h3>
                    <span class="raha-tag">.raha-tabs</span>
                </div>
                <div class="raha-card-body" style="padding:0;">
                    <div class="raha-tabs" role="tablist" id="demo-tabs">
                        <button class="raha-tab active" role="tab" aria-selected="true" data-tab="t1">تب اول</button>
                        <button class="raha-tab" role="tab" aria-selected="false" data-tab="t2">تب دوم</button>
                        <button class="raha-tab" role="tab" aria-selected="false" data-tab="t3">تب سوم</button>
                    </div>
                    <div style="padding:16px;">
                        <div data-panel="t1">محتوای تب اول</div>
                        <div data-panel="t2" hidden>محتوای تب دوم</div>
                        <div data-panel="t3" hidden>محتوای تب سوم</div>
                    </div>
                </div>
            </div>

            <!-- ============ 22. ACCORDION ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۲ — Accordion</h3>
                    <span class="raha-tag">.raha-acc-item</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-acc-item">
                        <button class="raha-acc-btn" aria-expanded="false">
                            <span>سؤال اول</span>
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-chevron-down"></use></svg>
                        </button>
                        <div class="raha-acc-panel">پاسخ سؤال اول — این متن پس از باز شدن نمایش داده می‌شود.</div>
                    </div>
                    <div class="raha-acc-item open">
                        <button class="raha-acc-btn" aria-expanded="true">
                            <span>سؤال دوم (باز)</span>
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-chevron-down"></use></svg>
                        </button>
                        <div class="raha-acc-panel">پاسخ سؤال دوم — باز است.</div>
                    </div>
                </div>
            </div>

            <!-- ============ 23. MODAL ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۳ — Modal</h3>
                    <span class="raha-tag">.raha-modal</span>
                </div>
                <div class="raha-card-body">
                    <button class="raha-btn raha-btn-primary" id="demo-modal-open">باز کردن مودال</button>
                </div>
            </div>

            <!-- ============ 24. DETAIL LIST ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۴ — Detail List</h3>
                    <span class="raha-tag">.raha-detail-list</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-detail-list">
                        <div class="raha-detail-item"><span>نام مشتری</span><strong>سارا احمدی</strong></div>
                        <div class="raha-detail-item"><span>شماره تماس</span><strong dir="ltr">0912-1234567</strong></div>
                        <div class="raha-detail-item"><span>خدمت</span><strong>طراحی سایت</strong></div>
                        <div class="raha-detail-item"><span>مبلغ</span><strong>۲٬۵۰۰٬۰۰۰ تومان</strong></div>
                    </div>
                </div>
            </div>

            <!-- ============ 25. TOAST — DEMO (Item O) ============ -->
            <div class="raha-card" id="demo-toast">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۵ — Toast (Item O — تست دستی)</h3>
                    <span class="raha-tag">.raha-toast</span>
                </div>
                <div class="raha-card-body">
                    <p style="margin:0 0 12px;font-size:13px;font-weight:700;">روی هر دکمه کلیک کنید — تست چهار variant و auto-dismiss:</p>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        <button class="raha-btn raha-btn-sm" onclick="window.showRahaToast('عملیات با موفقیت انجام شد', 'success')">success</button>
                        <button class="raha-btn raha-btn-sm" onclick="window.showRahaToast('خطا رخ داد — دوباره تلاش کنید', 'error')">error</button>
                        <button class="raha-btn raha-btn-sm" onclick="window.showRahaToast('هشدار — موجودی کم است', 'warn')">warn</button>
                        <button class="raha-btn raha-btn-sm" onclick="window.showRahaToast('اطلاع‌رسانی جدید', 'info')">info</button>
                        <button class="raha-btn raha-btn-sm raha-btn-primary" onclick="window.showRahaToast('این پیام دستی بسته می‌شود', 'info', { duration: 0 })">Manual close (duration: 0)</button>
                    </div>
                </div>
            </div>

            <!-- ============ 26. DROPDOWN ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۶ — Dropdown</h3>
                    <span class="raha-tag">.raha-dropdown</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-dropdown">
                        <button class="raha-btn" onclick="this.closest('.raha-dropdown').classList.toggle('open')">
                            باز کردن منو <svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-chevron-down"></use></svg>
                        </button>
                        <div class="raha-dropdown-panel">
                            <button class="raha-dropdown-item">مشاهده</button>
                            <button class="raha-dropdown-item">ویرایش</button>
                            <button class="raha-dropdown-item">دانلود</button>
                            <button class="raha-dropdown-item raha-dropdown-item--danger">حذف</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ 27. AVATAR ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۷ — Avatar</h3>
                    <span class="raha-tag">.raha-avatar</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;gap:16px;align-items:center;">
                        <div class="raha-avatar raha-avatar--sm">س</div>
                        <div class="raha-avatar">س</div>
                        <div class="raha-avatar raha-avatar--lg">س</div>
                        <div class="raha-avatar online">س</div>
                        <div class="raha-avatar raha-avatar--lg online">س</div>
                    </div>
                </div>
            </div>

            <!-- ============ 28. CHART BAR ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۸ — Chart Bar</h3>
                    <span class="raha-tag">.raha-chart</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-chart">
                        <div class="raha-bar" style="height: 40%;"></div>
                        <div class="raha-bar" style="height: 70%;"></div>
                        <div class="raha-bar peak" style="height: 90%;"></div>
                        <div class="raha-bar" style="height: 55%;"></div>
                        <div class="raha-bar" style="height: 30%;"></div>
                        <div class="raha-bar" style="height: 60%;"></div>
                        <div class="raha-bar" style="height: 45%;"></div>
                    </div>
                </div>
            </div>

            <!-- ============ 29. DONUT ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۲۹ — Donut + Legend</h3>
                    <span class="raha-tag">.raha-donut</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
                        <div class="raha-donut"><div class="raha-donut-hole">۱۰۰٪</div></div>
                        <ul class="raha-legend">
                            <li><span class="swatch" style="background:var(--accent);"></span> طراحی (۴۰٪)</li>
                            <li><span class="swatch" style="background:var(--sun);"></span> توسعه (۳۰٪)</li>
                            <li><span class="swatch" style="background:var(--success);"></span> پشتیبانی (۳۰٪)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ============ 30. TOOLTIP ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۳۰ — Tooltip</h3>
                    <span class="raha-tag">.raha-tooltip</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;gap:24px;">
                        <span class="raha-tooltip" data-tip="این یک tooltip است"><button class="raha-btn">هاور کنید</button></span>
                        <span class="raha-tooltip" data-tip="حذف مورد"><button class="raha-icon-btn" aria-label="حذف"><svg class="raha-icon" aria-hidden="true"><use href="#icon-x"></use></svg></button></span>
                    </div>
                </div>
            </div>

            <!-- ============ 31. BREADCRUMB ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۳۱ — Breadcrumb</h3>
                    <span class="raha-tag">.raha-breadcrumb</span>
                </div>
                <div class="raha-card-body">
                    <div class="raha-breadcrumb">
                        <a href="#dashboard" class="raha-breadcrumb__item">داشبورد</a>
                        <span class="raha-breadcrumb__sep">‹</span>
                        <a href="#orders" class="raha-breadcrumb__item">سفارش‌ها</a>
                        <span class="raha-breadcrumb__sep">‹</span>
                        <span class="raha-breadcrumb__item is-current">R-1001</span>
                    </div>
                </div>
            </div>

            <!-- ============ 32. SPINNER ============ -->
            <div class="raha-card">
                <div class="raha-card-head">
                    <h3 class="raha-card-title">۳۲ — Spinner — ۳ اندازه</h3>
                    <span class="raha-tag">.raha-spinner</span>
                </div>
                <div class="raha-card-body">
                    <div style="display:flex;gap:16px;align-items:center;">
                        <span class="raha-spinner raha-spinner--sm"></span>
                        <span class="raha-spinner"></span>
                        <span class="raha-spinner raha-spinner--lg"></span>
                    </div>
                </div>
            </div>

            <!-- ============ MODAL MARKUP ============ -->
            <div class="raha-modal" id="demo-modal" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
                <div class="raha-modal-card">
                    <div class="raha-modal-head">
                        <h3 class="raha-modal-title" id="demo-modal-title">مودال نمونه</h3>
                        <button class="raha-icon-btn raha-icon-btn-sm" id="demo-modal-close" aria-label="بستن">
                            <svg class="raha-icon raha-icon-sm" aria-hidden="true"><use href="#icon-x"></use></svg>
                        </button>
                    </div>
                    <div class="raha-modal-body">این یک مودال نمونه با role="dialog" و aria-modal="true" است.</div>
                    <div class="raha-modal-foot">
                        <button class="raha-btn raha-btn-ghost" id="demo-modal-cancel">انصراف</button>
                        <button class="raha-btn raha-btn-primary" id="demo-modal-confirm">تایید</button>
                    </div>
                </div>
            </div>
        `;

        /* --- Wire up modal --- */
        const modal = document.getElementById('demo-modal');
        const openModal = () => modal && modal.classList.add('open');
        const closeModal = () => modal && modal.classList.remove('open');
        document.getElementById('demo-modal-open')?.addEventListener('click', openModal);
        document.getElementById('demo-modal-close')?.addEventListener('click', closeModal);
        document.getElementById('demo-modal-cancel')?.addEventListener('click', closeModal);
        document.getElementById('demo-modal-confirm')?.addEventListener('click', closeModal);
        modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        /* --- Wire up tabs --- */
        document.querySelectorAll('#demo-tabs .raha-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('#demo-tabs .raha-tab').forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
                const key = tab.dataset.tab;
                document.querySelectorAll('[data-panel]').forEach(p => {
                    p.hidden = p.dataset.panel !== key;
                });
            });
        });

        /* --- Wire up accordion --- */
        document.querySelectorAll('.raha-acc-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.raha-acc-item');
                const isOpen = item.classList.toggle('open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    }

    /* ============================================================
       11. Drawer Logic & Global Event Delegation
       ============================================================ */
    const drawer = document.getElementById('raha-drawer');
    const drawerOverlay = document.getElementById('raha-drawer-overlay');
    const drawerClose = document.getElementById('raha-drawer-close');
    const drawerTitle = document.getElementById('raha-drawer-title');
    const drawerContent = document.getElementById('raha-drawer-content');

    function openRahaDrawer(title, contentHTML) {
        drawerTitle.textContent = title;
        drawerContent.innerHTML = contentHTML;
        drawer.classList.add('active');
        drawerOverlay.classList.add('active');
    }

    function closeDrawer() {
        drawer.classList.remove('active');
        drawerOverlay.classList.remove('active');
    }

    drawerClose.addEventListener('click', closeDrawer);
    drawerOverlay.addEventListener('click', closeDrawer);

    function openOrderDrawer(order) {
        openRahaDrawer('سفارش ' + order.code, `
            <div class="raha-detail-list">
                <div class="raha-detail-item"><span>نام مشتری</span><strong>${order.customer}</strong></div>
                <div class="raha-detail-item"><span>شماره تماس</span><strong dir="ltr">${order.contact}</strong></div>
                <div class="raha-detail-item"><span>نوع خدمت</span><strong>${order.service}</strong></div>
                <div class="raha-detail-item"><span>بودجه اعلامی</span><strong>${order.budget}</strong></div>
                <div class="raha-detail-item"><span>مبلغ نهایی</span><strong>${order.amount ? formatMoney(order.amount) : 'تعیین نشده'}</strong></div>
                <div class="raha-detail-item"><span>تاریخ ثبت</span><strong>${toFa(order.date)}</strong></div>
            </div>
            <div style="margin-top:24px;">
                <label style="font-size:13px; font-weight:800; display:block; margin-bottom:8px;">تغییر وضعیت سفارش</label>
                <select class="raha-input" id="drawer-order-status">
                    <option value="new" ${order.status === 'new' ? 'selected' : ''}>جدید</option>
                    <option value="progress" ${order.status === 'progress' ? 'selected' : ''}>در حال انجام</option>
                    <option value="done" ${order.status === 'done' ? 'selected' : ''}>تکمیل شده</option>
                </select>
            </div>
            <button class="raha-btn raha-btn-primary" id="save-order-status-btn" style="margin-top:20px; width:100%;">
                ذخیره تغییرات
            </button>
            <button class="raha-btn" id="impersonate-btn" style="margin-top:10px; width:100%; background:#f59e0b; color:#fff; border-color:#b45309;">
                ورود به پنل این مشتری (Impersonate)
            </button>
        `);

        // اتصال دکمه ذخیره به REST API
        const saveBtn = document.getElementById('save-order-status-btn');
        saveBtn.addEventListener('click', async () => {
            const newStatus = document.getElementById('drawer-order-status').value;
            saveBtn.disabled = true;
            saveBtn.textContent = 'در حال ذخیره...';

            try {
                const res = await fetch(config.restUrl + 'orders/update-status', {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: [order.id], status: newStatus })
                });

                if (res.ok) {
                    window.showRahaToast('وضعیت با موفقیت به‌روزرسانی شد', 'success');
                    document.getElementById('raha-drawer-close').click();
                    loadOrdersView(1, currentOrderSearch, currentOrderFilter);
                } else {
                    window.showRahaToast('خطا در ذخیره‌سازی', 'error');
                }
            } catch (e) {
                window.showRahaToast('خطای شبکه', 'error');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'ذخیره تغییرات';
            }
        });

        // دکمه Impersonate
        document.getElementById('impersonate-btn').addEventListener('click', async () => {
            const customerId = 1;
            const btn = document.getElementById('impersonate-btn');
            btn.disabled = true;
            btn.textContent = 'در حال تولید لینک...';

            try {
                const res = await fetch(config.restUrl + 'impersonate', {
                    method: 'POST',
                    headers: { 'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ customer_id: customerId })
                });
                const data = await res.json();
                if (data.success && data.url) {
                    window.open(data.url, '_blank');
                    btn.textContent = 'تب جدید باز شد';
                } else {
                    window.showRahaToast('خطا در ورود', 'error');
                    btn.disabled = false;
                }
            } catch(e) {
                window.showRahaToast('خطای شبکه', 'error');
                btn.disabled = false;
            }
        });
    }

    function openInvoiceDrawer(id) {
        openRahaDrawer('جزئیات فاکتور #' + id, `
            <div class="raha-detail-list">
                <div class="raha-detail-item"><span>شناسه دیتابیس</span><strong>${id}</strong></div>
                <div class="raha-detail-item"><span>وضعیت</span><strong>در انتظار بررسی (دمو)</strong></div>
            </div>
        `);
    }

    function openTicketDrawer(id) {
        openRahaDrawer('پاسخ به تیکت #' + id, `
            <div style="margin-bottom:20px;">
                <label style="font-size:13px; font-weight:800; display:block; margin-bottom:8px;">متن پاسخ</label>
                <textarea class="raha-input" rows="5" placeholder="پاسخ خود را بنویسید..." style="width:100%;"></textarea>
            </div>
            <button class="raha-btn raha-btn-primary" style="width:100%;" onclick="window.showRahaToast('پاسخ ارسال شد (دمو)', 'success'); document.getElementById('raha-drawer-close').click();">
                ارسال پاسخ
            </button>
        `);
    }

    window.showAuditDiff = function(id, beforeB64, afterB64) {
        const before = JSON.parse(decodeURIComponent(escape(atob(beforeB64))));
        const after = JSON.parse(decodeURIComponent(escape(atob(afterB64))));
        const formatJson = (obj) => `<pre style="background:#1e293b; color:#e2e8f0; padding:16px; border-radius:8px; font-size:12px; overflow-x:auto; direction:ltr; text-align:left;">${JSON.stringify(obj, null, 2)}</pre>`;

        openRahaDrawer('تغییرات لاگ #' + id, `
            <div style="display:grid; gap:16px;">
                <div><h4 style="margin:0 0 8px; color:#dc2626;">قبل از تغییر:</h4>${formatJson(before)}</div>
                <div><h4 style="margin:0 0 8px; color:#16a34a;">بعد از تغییر:</h4>${formatJson(after)}</div>
            </div>
        `);
    };

    // مدیریت مرکزی کلیک‌ها
    viewContainer.addEventListener('click', (e) => {
        // صفحه‌بندی
        if (e.target.matches('.raha-page-btn')) {
            const page = parseInt(e.target.dataset.page);
            const route = window.location.hash.replace('#', '') || 'dashboard';
            if (route === 'orders') loadOrdersView(page, currentOrderSearch, currentOrderFilter);
            if (route === 'invoices') loadInvoicesView(page);
            if (route === 'tickets') loadTicketsView(page);
            if (route === 'audit-logs') loadAuditLogsView(page);
        }
        // دکمه فاکتور
        if (e.target.matches('[data-action="view-invoice"]')) {
            openInvoiceDrawer(e.target.dataset.id);
        }
        // دکمه تیکت
        if (e.target.matches('[data-action="reply-ticket"]')) {
            openTicketDrawer(e.target.dataset.id);
        }
    });

    /* ============================================================
       12. Live Notifications (Polling)
       ============================================================ */
    async function checkLiveNotifications() {
        try {
            const res = await fetch(config.restUrl + 'notifications', {
                headers: { 'X-WP-Nonce': config.nonce }
            });
            const data = await res.json();

            const dot = document.querySelector('.raha-dot');
            if (dot) {
                if (data.count > 0) {
                    dot.style.display = 'block';
                } else {
                    dot.style.display = 'none';
                }
            }
        } catch (e) {
            console.warn('Polling failed');
        }
    }

    // اجرای اولیه و سپس هر ۳۰ ثانیه
    checkLiveNotifications();
    setInterval(checkLiveNotifications, 30000);

    /* ============================================================
       13. Init (راه‌اندازی اولیه)
       ============================================================ */
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            navigateTo(item.dataset.route);
        });
    });

    window.addEventListener('popstate', (e) => {
        const route = (e.state && e.state.route) ? e.state.route : 'dashboard';
        navigateTo(route, false);
    });

    const initialRoute = window.location.hash.replace('#', '') || 'dashboard';
    navigateTo(initialRoute, false);
});