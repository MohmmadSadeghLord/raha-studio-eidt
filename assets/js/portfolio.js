document.addEventListener('DOMContentLoaded', function() {
    const chips = Array.from(document.querySelectorAll('.raha-portfolio-section .chip'));
    const cardEls = Array.from(document.querySelectorAll('.raha-portfolio-section .project-card'));

    if (!chips.length || !cardEls.length) return;

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const filter = chip.dataset.filter;

            // آپدیت وضعیت دکمه‌ها
            chips.forEach(c => {
                const isOn = c.dataset.filter === filter;
                c.classList.toggle('is-active', isOn);
                c.setAttribute('aria-pressed', isOn ? 'true' : 'false');
            });

            // نمایش/مخفی کردن کارت‌ها
            cardEls.forEach(card => {
                const show = filter === 'all' || card.dataset.category === filter;
                card.classList.toggle('is-hidden', !show);
            });
        });
    });
});