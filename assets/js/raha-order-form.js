(function () {
    'use strict';

    var cfg = window.RahaOrderConfig || {};
    var state = {
        service: null, currentSiteUrl: '', purpose: null,
        businessName: '', businessDescription: '',
        features: [], featuresUnsure: false, featuresOther: '',
        designHasSamples: false, designSamples: [''], hasLogoBrand: false,
        designSuggestByYou: false, designNotes: '', logoFile: null,
        contentStatus: null, domainHosting: null,
        timeline: null, timelineDate: '', budget: null,
        extraNotes: '', extraFile: null,
        fullName: '', companyName: '', contactMethod: null,
        contactValue: '', altContact: '', bestTime: '',
    };

    var currentStep = 0;
    var totalSteps = 10;

    var GENERAL_FEATURES = [
        { v: 'intro', icon: '📄', label: 'معرفی خدمات' },
        { v: 'gallery', icon: '🖼️', label: 'نمونه‌کار و گالری' },
        { v: 'blog', icon: '📝', label: 'وبلاگ' },
        { v: 'contact-form', icon: '✉️', label: 'فرم تماس' },
        { v: 'user-account', icon: '👤', label: 'حساب کاربری' },
        { v: 'multilang', icon: '🌐', label: 'چندزبانه' },
        { v: 'integration', icon: '🔗', label: 'اتصال به ابزار' },
    ];
    var ECOMMERCE_FEATURES = [
        { v: 'online-payment', icon: '💳', label: 'پرداخت آنلاین' },
        { v: 'product-management', icon: '📦', label: 'مدیریت محصولات' },
        { v: 'discount-code', icon: '🏷️', label: 'کد تخفیف' },
        { v: 'shipping', icon: '🚚', label: 'روش‌های ارسال' },
    ];
    var SERVICE_FEATURES = [
        { v: 'booking', icon: '📅', label: 'رزرو نوبت' },
        { v: 'service-order', icon: '🛎️', label: 'ثبت سفارش خدمات' },
        { v: 'file-upload-cust', icon: '📎', label: 'دریافت فایل' },
        { v: 'service-payment', icon: '💳', label: 'پرداخت هزینه خدمات' },
    ];

    var CONTACT_METHODS = {
        phone:    { label: 'شماره تلفن', placeholder: '۰۹۱۲۳۴۵۶۷۸۹', type: 'tel' },
        whatsapp: { label: 'شماره واتساپ', placeholder: '۰۹۱۲۳۴۵۶۷۸۹', type: 'tel' },
        telegram: { label: 'آیدی تلگرام', placeholder: '@username', type: 'text' },
        email:    { label: 'ایمیل', placeholder: 'you@example.com', type: 'email' },
    };

    /* ---------- Helpers ---------- */
    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function faNum(n) { return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }

    function optCard(group, value, icon, label) {
        var sel = state[group] === value;
        return '<div class="raha-opt' + (sel ? ' is-sel' : '') + '" data-group="' + group + '" data-value="' + value + '">' +
            '<span class="raha-opt__icon">' + icon + '</span>' +
            '<span class="raha-opt__label">' + label + '</span>' +
            '</div>';
    }
    function optMulti(group, value, icon, label) {
        var sel = state[group].indexOf(value) > -1;
        return '<div class="raha-opt' + (sel ? ' is-sel' : '') + '" data-group="' + group + '" data-value="' + value + '" data-multi="1">' +
            '<span class="raha-opt__icon">' + (sel ? '✅' : icon) + '</span>' +
            '<span class="raha-opt__label">' + label + '</span>' +
            '</div>';
    }
    function optToggle(group, icon, label) {
        var sel = !!state[group];
        return '<div class="raha-opt' + (sel ? ' is-sel' : '') + '" data-group="' + group + '" data-toggle="1">' +
            '<span class="raha-opt__icon">' + (sel ? '✅' : icon) + '</span>' +
            '<span class="raha-opt__label">' + label + '</span>' +
            '</div>';
    }

    /* ---------- Steps ---------- */
    function stepService() {
        var showUrl = state.service === 'redesign' || state.service === 'development';
        return '<div class="raha-opt-grid">' +
            optCard('service', 'new', '🆕', 'طراحی سایت جدید') +
            optCard('service', 'redesign', '🔄', 'بازطراحی سایت فعلی') +
            optCard('service', 'development', '🛠️', 'توسعه یا افزودن امکانات') +
            optCard('service', 'consult', '💬', 'مشاوره می‌خواهم') +
            '</div>' +
            (showUrl ? '<label class="raha-field" style="margin-top:16px"><span class="raha-field__label">آدرس سایت فعلی</span><input type="url" dir="ltr" class="raha-field__input" data-input="currentSiteUrl" value="' + esc(state.currentSiteUrl) + '"></label>' : '');
    }
    function stepPurpose() {
        return '<div class="raha-opt-grid">' +
            optCard('purpose', 'company', '🏢', 'معرفی شرکت') +
            optCard('purpose', 'sell', '🛒', 'فروش محصولات') +
            optCard('purpose', 'services', '🤝', 'ارائه خدمات') +
            optCard('purpose', 'education', '🎓', 'آموزش و دوره') +
            optCard('purpose', 'booking', '📅', 'رزرو نوبت') +
            optCard('purpose', 'other', '✨', 'کاربرد دیگر') +
            optCard('purpose', 'unsure', '❓', 'نمی‌دانم') +
            '</div>';
    }
    function stepBusiness() {
        return '<label class="raha-field"><span class="raha-field__label">نام کسب‌وکار</span>' +
            '<input type="text" class="raha-field__input" data-input="businessName" value="' + esc(state.businessName) + '"></label>' +
            '<label class="raha-field"><span class="raha-field__label">کسب‌وکار شما چه کاری انجام می‌دهد؟ *</span>' +
            '<textarea class="raha-field__input" rows="4" data-input="businessDescription">' + esc(state.businessDescription) + '</textarea></label>';
    }
    function stepFeatures() {
        var groups = [{ title: 'عمومی', items: GENERAL_FEATURES }];
        if (state.purpose === 'sell') groups.push({ title: 'فروشگاهی', items: ECOMMERCE_FEATURES });
        else if (state.purpose === 'services' || state.purpose === 'booking') groups.push({ title: 'خدماتی', items: SERVICE_FEATURES });

        var html = '';
        groups.forEach(function (g) {
            html += '<h3 class="raha-step__subtitle">' + g.title + '</h3><div class="raha-opt-grid">';
            g.items.forEach(function (it) { html += optMulti('features', it.v, it.icon, it.label); });
            html += '</div>';
        });
        html += '<div class="raha-opt-grid" style="margin-top:16px">' + optToggle('featuresUnsure', '🤔', 'هنوز نمی‌دانم') + '</div>';
        html += '<label class="raha-field" style="margin-top:16px"><span class="raha-field__label">سایر امکانات</span>' +
            '<textarea class="raha-field__input" rows="3" data-input="featuresOther">' + esc(state.featuresOther) + '</textarea></label>';
        return html;
    }
    function stepDesign() {
        var html = '<div class="raha-opt-grid">' +
            optToggle('designHasSamples', '🔗', 'نمونه سایت دارم') +
            optToggle('hasLogoBrand', '🎨', 'لوگو و رنگ برند دارم') +
            optToggle('designSuggestByYou', '✨', 'شما پیشنهاد بدهید') +
            '</div>';
        if (state.designHasSamples) {
            html += '<div style="margin-top:12px">';
            state.designSamples.forEach(function (link, i) {
                html += '<div class="raha-opt-link-row" style="display:flex;gap:6px;margin-bottom:6px">' +
                    '<input type="url" dir="ltr" class="raha-field__input" data-sample="' + i + '" value="' + esc(link) + '">' +
                    (state.designSamples.length > 1 ? '<button type="button" class="raha-btn raha-btn--ghost" data-remove-sample="' + i + '">✕</button>' : '') +
                    '</div>';
            });
            html += '<button type="button" class="raha-btn raha-btn--ghost" data-add-sample>+ افزودن</button></div>';
        }
        html += '<label class="raha-field" style="margin-top:16px"><span class="raha-field__label">توضیحات بصری</span>' +
            '<textarea class="raha-field__input" rows="3" data-input="designNotes">' + esc(state.designNotes) + '</textarea></label>';
        html += '<label class="raha-field"><span class="raha-field__label">فایل لوگو</span>' +
            '<input type="file" data-file="logoFile" accept="image/*,.pdf"><small>' + (state.logoFile ? '📎 ' + esc(state.logoFile.name) : 'انتخاب کنید') + '</small></label>';
        return html;
    }
    function stepContent() {
        return '<label class="raha-field"><span class="raha-field__label">وضعیت محتوا</span></label>' +
            '<div class="raha-opt-grid">' +
            optCard('contentStatus', 'ready', '✅', 'همه آماده') +
            optCard('contentStatus', 'partial', '⏳', 'بخشی آماده') +
            optCard('contentStatus', 'need-help', '🤝', 'کمک می‌خواهم') +
            optCard('contentStatus', 'unsure', '❓', 'مشخص نیست') +
            '</div>' +
            '<label class="raha-field" style="margin-top:16px"><span class="raha-field__label">دامنه و هاست</span></label>' +
            '<div class="raha-opt-grid">' +
            optCard('domainHosting', 'have-both', '🌐', 'هر دو را دارم') +
            optCard('domainHosting', 'have-domain', '🔗', 'فقط دامنه') +
            optCard('domainHosting', 'have-hosting', '🖥️', 'فقط هاست') +
            optCard('domainHosting', 'none', '❌', 'هیچ‌کدام') +
            optCard('domainHosting', 'need-help', '🤝', 'راهنمایی') +
            '</div>';
    }
    function stepTime() {
        var html = '<div class="raha-opt-grid">' +
            optCard('timeline', 'asap', '⚡', 'در اولین فرصت') +
            optCard('timeline', 'specific', '📅', 'تا تاریخ مشخص') +
            optCard('timeline', 'flexible', '🕐', 'زمان‌بندی منعطف') +
            '</div>';
        if (state.timeline === 'specific') {
            html += '<label class="raha-field" style="margin-top:12px"><span class="raha-field__label">تاریخ مورد نظر</span>' +
                '<input type="date" dir="ltr" class="raha-field__input" data-input="timelineDate" value="' + esc(state.timelineDate) + '"></label>';
        }
        html += '<label class="raha-field" style="margin-top:16px"><span class="raha-field__label">بودجه</span></label>' +
            '<div class="raha-opt-grid">' +
            optCard('budget', 'low', '💰', 'تا ۱۰ میلیون') +
            optCard('budget', 'mid', '💰💰', '۱۰ تا ۳۰ میلیون') +
            optCard('budget', 'high', '💰💰💰', '۳۰ تا ۶۰ میلیون') +
            optCard('budget', 'premium', '💎', 'بیش از ۶۰ میلیون') +
            optCard('budget', 'unknown', '❓', 'نامشخص') +
            '</div>';
        return html;
    }
    function stepExtra() {
        return '<label class="raha-field"><span class="raha-field__label">توضیحات تکمیلی</span>' +
            '<textarea class="raha-field__input" rows="4" data-input="extraNotes">' + esc(state.extraNotes) + '</textarea></label>' +
            '<label class="raha-field"><span class="raha-field__label">فایل شرح پروژه</span>' +
            '<input type="file" data-file="extraFile"><small>' + (state.extraFile ? '📎 ' + esc(state.extraFile.name) : 'انتخاب کنید') + '</small></label>';
    }
    function stepContact() {
        var cfgM = state.contactMethod ? CONTACT_METHODS[state.contactMethod] : null;
        var html = '<label class="raha-field"><span class="raha-field__label">نام و نام خانوادگی *</span>' +
            '<input type="text" class="raha-field__input" data-input="fullName" value="' + esc(state.fullName) + '"></label>' +
            '<label class="raha-field"><span class="raha-field__label">نام شرکت</span>' +
            '<input type="text" class="raha-field__input" data-input="companyName" value="' + esc(state.companyName) + '"></label>' +
            '<label class="raha-field"><span class="raha-field__label">روش تماس *</span></label>' +
            '<div class="raha-opt-grid">' +
            optCard('contactMethod', 'phone', '📞', 'تلفن') +
            optCard('contactMethod', 'whatsapp', '💚', 'واتساپ') +
            optCard('contactMethod', 'telegram', '✈️', 'تلگرام') +
            optCard('contactMethod', 'email', '📧', 'ایمیل') +
            '</div>';
        if (cfgM) {
            html += '<label class="raha-field" style="margin-top:12px"><span class="raha-field__label">' + cfgM.label + ' *</span>' +
                '<input type="' + cfgM.type + '" dir="ltr" class="raha-field__input" data-input="contactValue" placeholder="' + cfgM.placeholder + '" value="' + esc(state.contactValue) + '"></label>';
        }
        html += '<label class="raha-field"><span class="raha-field__label">تماس جایگزین</span>' +
            '<input type="text" class="raha-field__input" data-input="altContact" value="' + esc(state.altContact) + '"></label>' +
            '<label class="raha-field"><span class="raha-field__label">زمان مناسب تماس</span>' +
            '<input type="text" class="raha-field__input" data-input="bestTime" value="' + esc(state.bestTime) + '"></label>';
        return html;
    }
    function stepReview() {
        var items = [
            ['نوع خدمت', state.service],
            ['هدف', state.purpose],
            ['کسب‌وکار', state.businessDescription],
            ['تماس', state.fullName + ' — ' + state.contactValue],
        ];
        var html = '';
        items.forEach(function (it) {
            html += '<div class="raha-review"><strong>' + it[0] + '</strong><span>' + (esc(it[1]) || '—') + '</span></div>';
        });
        return html;
    }

    /* ---------- Title / Subtitle ---------- */
    var STEPS = [
        { fn: stepService,  title: 'به چه خدماتی نیاز دارید؟',      sub: 'یکی را انتخاب کنید.' },
        { fn: stepPurpose,  title: 'هدف سایت چیست؟',                sub: 'به ما در پیشنهاد ساختار کمک می‌کند.' },
        { fn: stepBusiness, title: 'کمی از کسب‌وکارتان بگویید',      sub: 'هرچه دقیق‌تر، بهتر.' },
        { fn: stepFeatures, title: 'چه امکاناتی نیاز دارید؟',        sub: 'چند گزینه مجاز است.' },
        { fn: stepDesign,   title: 'ظاهر سایت',                     sub: 'سلیقه بصری خود را بگویید.' },
        { fn: stepContent,  title: 'محتوا و زیرساخت',                sub: 'وضعیت محتوا را مشخص کنید.' },
        { fn: stepTime,     title: 'زمان و بودجه',                  sub: 'برای پیشنهاد راهکار.' },
        { fn: stepExtra,    title: 'نکات تکمیلی',                    sub: 'اختیاری.' },
        { fn: stepContact,  title: 'اطلاعات تماس',                  sub: 'برای پیگیری.' },
        { fn: stepReview,   title: 'مرور و ثبت',                    sub: 'در نهایت ثبت کنید.' },
    ];

    var stepContainer = document.getElementById('stepContainer');
    var progressFill = document.getElementById('progressFill');
    var stepLabel = document.getElementById('stepLabel');
    var stepPercent = document.getElementById('stepPercent');
    var backBtn = document.getElementById('backBtn');
    var nextBtn = document.getElementById('nextBtn');
    var errorBox = document.getElementById('orderError');

    function render() {
        var s = STEPS[currentStep];
        stepContainer.innerHTML =
            '<h2 class="raha-step__title">' + s.title + '</h2>' +
            '<p class="raha-step__subtitle">' + s.sub + '</p>' +
            s.fn();

        var pct = Math.round(((currentStep + 1) / totalSteps) * 100);
        progressFill.style.width = pct + '%';
        stepLabel.textContent = 'مرحله ' + faNum(currentStep + 1) + ' از ' + faNum(totalSteps);
        stepPercent.textContent = faNum(pct) + '٪';
        backBtn.style.visibility = currentStep === 0 ? 'hidden' : 'visible';
        nextBtn.textContent = currentStep === totalSteps - 1 ? 'ثبت درخواست' : 'بعدی ←';
        errorBox.style.display = 'none';
        bindEvents();
    }

    function bindEvents() {
        stepContainer.querySelectorAll('.raha-opt').forEach(function (el) {
            el.onclick = function () {
                var group = el.dataset.group;
                var value = el.dataset.value;
                if (el.dataset.multi) {
                    var i = state[group].indexOf(value);
                    if (i > -1) state[group].splice(i, 1);
                    else state[group].push(value);
                } else if (el.dataset.toggle) {
                    state[group] = !state[group];
                } else {
                    if (group === 'purpose' && state.purpose !== value) state.features = [];
                    state[group] = value;
                    if (group === 'contactMethod') state.contactValue = '';
                }
                render();
            };
        });
        stepContainer.querySelectorAll('[data-input]').forEach(function (el) {
            el.oninput = function () { state[el.dataset.input] = el.value; };
        });
        stepContainer.querySelectorAll('[data-sample]').forEach(function (el) {
            el.oninput = function () { state.designSamples[+el.dataset.sample] = el.value; };
        });
        stepContainer.querySelectorAll('[data-file]').forEach(function (el) {
            el.onchange = function () {
                state[el.dataset.file] = el.files[0] || null;
                render();
            };
        });
        stepContainer.querySelectorAll('[data-add-sample]').forEach(function (el) {
            el.onclick = function () { state.designSamples.push(''); render(); };
        });
        stepContainer.querySelectorAll('[data-remove-sample]').forEach(function (el) {
            el.onclick = function () { state.designSamples.splice(+el.dataset.removeSample, 1); if (!state.designSamples.length) state.designSamples = ['']; render(); };
        });
    }

    function showError(msg) {
        errorBox.textContent = '⚠️ ' + msg;
        errorBox.style.display = 'block';
        errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function validate() {
        if (currentStep === 0 && !state.service) return 'نوع خدمت را انتخاب کنید.';
        if (currentStep === 1 && !state.purpose) return 'هدف سایت را انتخاب کنید.';
        if (currentStep === 2 && !state.businessDescription.trim()) return 'توضیحات کسب‌وکار الزامی است.';
        if (currentStep === 8) {
            if (!state.fullName.trim()) return 'نام الزامی است.';
            if (!state.contactMethod || !state.contactValue.trim()) return 'راه تماس را کامل کنید.';
        }
        return '';
    }

    function submit() {
        nextBtn.disabled = true;
        nextBtn.textContent = 'در حال ارسال...';

        var fd = new FormData();
        fd.append('action', 'raha_submit_order');
        fd.append('nonce', cfg.nonce);

        /* همه فیلدهای ساده */
        Object.keys(state).forEach(function (k) {
            var v = state[k];
            if (v === null || v === undefined) return;
            if (Array.isArray(v)) {
                v.forEach(function (item) {
                    if (item && item !== '') fd.append(k + '[]', item);
                });
            } else if (typeof v === 'boolean') {
                if (v) fd.append(k, '1');
            } else if (v instanceof File) {
                fd.append(k, v);
            } else {
                fd.append(k, v);
            }
        });

        fetch(cfg.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    window.location.href = res.data.redirect;
                } else {
                    showError((res.data && res.data.message) || 'خطا در ثبت.');
                    nextBtn.disabled = false;
                    nextBtn.textContent = 'ثبت درخواست';
                }
            })
            .catch(function () {
                showError('خطا در ارتباط. دوباره تلاش کنید.');
                nextBtn.disabled = false;
                nextBtn.textContent = 'ثبت درخواست';
            });
    }

    nextBtn.onclick = function () {
        var err = validate();
        if (err) { showError(err); return; }
        if (currentStep === totalSteps - 1) { submit(); return; }
        currentStep++;
        render();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    backBtn.onclick = function () {
        if (currentStep > 0) { currentStep--; render(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
    };

    render();
})();