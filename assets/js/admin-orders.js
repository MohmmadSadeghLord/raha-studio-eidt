document.addEventListener('DOMContentLoaded', function() {
    const usersData = window.RahaAdminOrdersData || [];
    
    const mainGrid = document.getElementById('mainGrid');
    const searchInput = document.getElementById('searchInput');
    const searchBoxWrapper = document.getElementById('searchBoxWrapper');
    const breadcrumb = document.getElementById('adminBreadcrumb');
    const breadcrumbCurrent = document.getElementById('breadcrumbCurrent');
    
    const modal = document.getElementById('orderModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const saveBtn = document.getElementById('saveOrderBtn');
    
    const ajaxMessage = document.getElementById('ajaxMessage');

    let currentView = 'users'; // 'users' یا 'orders'
    let currentUser = null;
    let currentOrder = null;

    // توابع کمکی
    function getServiceLabel(code) {
        const map = { new: 'طراحی جدید', redesign: 'بازطراحی', development: 'توسعه', consult: 'مشاوره' };
        return map[code] || code;
    }
    function getStatusLabel(code) {
        const map = { new: 'جدید', review: 'در حال بررسی', progress: 'در حال انجام', done: 'تکمیل شده', canceled: 'لغو شده' };
        return map[code] || code;
    }
    function getStatusClass(code) {
        const map = { new: 'status-new', review: 'status-review', progress: 'status-review', done: 'status-done', canceled: 'status-canceled' };
        return map[code] || '';
    }
    function toFa(num) { return String(num).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]); }

    // ---------- رندر سطح مشتریان ----------
    function renderUsers(filter = '') {
        currentView = 'users';
        currentUser = null;
        breadcrumb.style.display = 'none';
        searchBoxWrapper.style.display = 'block';
        searchInput.placeholder = 'جستجوی نام مشتری یا شماره تماس...';
        mainGrid.innerHTML = '';

        // اگر کلاً داده‌ای وجود نداشت
        if (usersData.length === 0) {
            mainGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1/-1; text-align:center; padding: 40px;">
                    <h3 style="font-size:20px; color:var(--muted);">هنوز هیچ سفارشی ثبت نشده است.</h3>
                    <p>وقتی مشتریان از سایت سفارش ثبت کنند، در اینجا نمایش داده می‌شوند.</p>
                </div>`;
            return;
        }
        
        const filtered = usersData.filter(u => {
            const str = `${u.name} ${u.contact}`.toLowerCase();
            return str.includes(filter.toLowerCase());
        });

        if (filtered.length === 0) {
            mainGrid.innerHTML = `<div class="empty-state"><h3>هیچ مشتری‌ای یافت نشد.</h3></div>`;
            return;
        }

        filtered.forEach(user => {
            const card = document.createElement('div');
            card.className = 'order-card'; // استفاده از همان کلاس کارت
            card.style.cursor = 'pointer';
            card.onclick = () => renderUserOrders(user);
            
            card.innerHTML = `
                <div class="card-header">
                    <div class="client-info">
                        <h3>👤 ${user.name}</h3>
                        <p dir="ltr">${user.contact}</p>
                    </div>
                </div>
                <div class="card-details" style="justify-content: space-between; border-top: 2px dashed rgba(20,17,15,.1); padding-top: 12px;">
                    <span class="detail-tag">📦 ${toFa(user.orders.length)} سفارش</span>
                    <span class="detail-tag">💰 ${toFa(user.total_spent)} تومان پرداختی</span>
                </div>
            `;
            mainGrid.appendChild(card);
        });
    }

    // ---------- رندر سطح سفارشات یک مشتری ----------
    function renderUserOrders(user) {
        currentView = 'orders';
        currentUser = user;
        
        // فعال کردن Breadcrumb
        breadcrumb.style.display = 'flex';
        breadcrumbCurrent.textContent = user.name;
        searchBoxWrapper.style.display = 'none';
        
        mainGrid.innerHTML = '';
        
        if (user.orders.length === 0) {
            mainGrid.innerHTML = `<div class="empty-state"><h3>این کاربر سفارشی ندارد.</h3></div>`;
            return;
        }

        user.orders.forEach(order => {
            const card = document.createElement('div');
            card.className = 'order-card';
            card.onclick = () => openOrderModal(order);
            
            card.innerHTML = `
                <div class="card-header">
                    <div class="client-info">
                        <h3 style="font-family:monospace; font-size:1rem; color:var(--ink);" dir="ltr">${order.tracking}</h3>
                        <p>${order.companyName || order.businessName || 'بدون نام برند'}</p>
                    </div>
                    <span class="status-badge ${getStatusClass(order.status)}">${getStatusLabel(order.status)}</span>
                </div>
                <div class="card-details">
                    <span class="detail-tag">🛠️ ${getServiceLabel(order.service)}</span>
                    <span class="detail-tag">💰 ${order.budget}</span>
                    <span class="detail-tag">📅 ${order.date}</span>
                </div>
            `;
            mainGrid.appendChild(card);
        });
    }

    // ---------- باز کردن مودال سفارش ----------
    function openOrderModal(order) {
        currentOrder = order;
        modalTitle.textContent = `سفارش ${order.tracking}`;
        
        let featuresText = order.features.length ? order.features.join('، ') : 'ثبت نشده';
        
        // فرم تعاملی (Status, Amount, Paid, Notes)
        modalBody.innerHTML = `
            <div class="detail-section">
                <div class="detail-section-title">📞 اطلاعات تماس</div>
                <div class="detail-section-content">نام: ${currentUser.name}
شرکت: ${order.companyName || '—'}
روش تماس: ${order.contactMethod}
اطلاعات تماس: ${order.contactValue || '—'}</div>
            </div>
            
            <div class="detail-section">
                <div class="detail-section-title">🛠️ اطلاعات پروژه</div>
                <div class="detail-section-content">خدمت: ${getServiceLabel(order.service)}
هدف: ${order.purpose || '—'}
برند: ${order.businessName || '—'}
امکانات: ${featuresText}
سایر: ${order.featuresOther || '—'}</div>
            </div>

            <!-- بخش مدیریت (Interactive) -->
            <div class="detail-section" style="background: #fffbe8; border-color: var(--sun);">
                <div class="detail-section-title" style="color: var(--ink);">⚙️ مدیریت سفارش (ادمین)</div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                    <label style="font-size:12px; font-weight:800;">وضعیت سفارش:
                        <select id="adminStatus" class="admin-input">
                            <option value="new" ${order.status === 'new' ? 'selected' : ''}>جدید</option>
                            <option value="review" ${order.status === 'review' ? 'selected' : ''}>در حال بررسی</option>
                            <option value="progress" ${order.status === 'progress' ? 'selected' : ''}>در حال انجام</option>
                            <option value="done" ${order.status === 'done' ? 'selected' : ''}>تکمیل شده</option>
                            <option value="canceled" ${order.status === 'canceled' ? 'selected' : ''}>لغو شده</option>
                        </select>
                    </label>
                    
                    <div></div> <!-- فضای خالی -->

                    <label style="font-size:12px; font-weight:800;">مبلغ کل (تومان):
                        <input type="number" id="adminAmount" class="admin-input" value="${order.amount}" placeholder="0">
                    </label>
                    
                    <label style="font-size:12px; font-weight:800;">مبلغ پرداخت‌شده (تومان):
                        <input type="number" id="adminAmountPaid" class="admin-input" value="${order.amount_paid}" placeholder="0">
                    </label>
                </div>

                <label style="display:block; font-size:12px; font-weight:800; margin-top:12px;">یادداشت داخلی (فقط برای شما):
                    <textarea id="adminNotes" class="admin-input" rows="2">${order.admin_notes || ''}</textarea>
                </label>
            </div>
        `;
        
        modal.classList.add('active');
    }

    // ---------- ذخیره تغییرات با AJAX ----------
    saveBtn.onclick = function() {
        if (!currentOrder) return;
        
        saveBtn.textContent = 'در حال ذخیره...';
        saveBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'raha_update_order_admin');
        // استفاده از کد امنیتی که از وردپرس گرفتیم
        formData.append('nonce', RahaAdminConfig.nonce); 
        formData.append('order_id', currentOrder.id);
        formData.append('status', document.getElementById('adminStatus').value);
        formData.append('amount', document.getElementById('adminAmount').value);
        formData.append('amount_paid', document.getElementById('adminAmountPaid').value);
        formData.append('admin_notes', document.getElementById('adminNotes').value);

        // استفاده از آدرس AJAX وردپرس
        fetch(RahaAdminConfig.ajaxurl, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // نمایش پیام موفقیت
                    ajaxMessage.textContent = res.data.message;
                    ajaxMessage.className = 'ajax-message is-success';
                    ajaxMessage.style.display = 'block';
                    
                    // آپدیت داده‌ها در حافظه
                    currentOrder.status = document.getElementById('adminStatus').value;
                    currentOrder.amount = document.getElementById('adminAmount').value;
                    currentOrder.amount_paid = document.getElementById('adminAmountPaid').value;
                    currentOrder.admin_notes = document.getElementById('adminNotes').value;
                    
                    setTimeout(() => {
                        ajaxMessage.style.display = 'none';
                        closeModal();
                        renderUserOrders(currentUser); // رفرش لیست سفارشات این کاربر
                    }, 1500);
                } else {
                    alert(res.data.message || 'خطا در ذخیره.');
                }
            })
            .catch(() => alert('خطا در ارتباط با سرور.'))
            .finally(() => {
                saveBtn.textContent = '💾 ذخیره تغییرات';
                saveBtn.disabled = false;
            });
    };
    // ---------- رویدادها (Events) ----------
    document.getElementById('closeModalBtn').onclick = closeModal;
    document.getElementById('cancelModalBtn').onclick = closeModal;
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

    function closeModal() {
        modal.classList.remove('active');
        currentOrder = null;
    }

    // بازگشت به لیست کاربران با کلیک روی Breadcrumb
    breadcrumb.addEventListener('click', (e) => {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            renderUsers();
        }
    });

    searchInput.addEventListener('input', (e) => {
        renderUsers(e.target.value);
    });

    // شروع
    renderUsers();
});