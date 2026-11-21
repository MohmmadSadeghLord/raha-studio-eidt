(function () {
    'use strict';

    /* ============================================================
       1) FAQ (فقط یکی باز باشد)
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        var faqItems = document.querySelectorAll('.raha-faq__item');
        faqItems.forEach(function (item) {
            item.addEventListener('toggle', function () {
                if (item.open) {
                    faqItems.forEach(function (other) {
                        if (other !== item && other.open) other.open = false;
                    });
                }
            });
        });
    });

    /* ============================================================
       2) تبدیل اعداد انگلیسی به فارسی
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function() {
        function toPersianDigits(str) {
            const persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            return str.replace(/\d/g, d => persian[d]);
        }
        const elements = document.querySelectorAll('body *:not(script):not(style):not(code):not(pre):not(svg):not(path)');
        elements.forEach(el => {
            Array.from(el.childNodes).forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.nodeValue = toPersianDigits(node.nodeValue);
                }
            });
        });
    });
})();