<?php
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- Meta tag for SEO (Noindex) -->
<meta name="robots" content="noindex, follow">

<div class="error-page-wrapper">
    <main class="error-page">
      
      <div class="deco deco--1" aria-hidden="true">&lt;/&gt;</div>
      <div class="deco deco--2" aria-hidden="true">{ }</div>
      <div class="deco deco--3" aria-hidden="true"></div>
      <div class="deco deco--4" aria-hidden="true"></div>

      <div class="error-container">
        
        <div class="error-top">
          <div class="error-code" aria-hidden="true">۴۰۴</div>
          
          <div class="error-card-wrapper">
            <div class="error-card">
              <svg class="robot-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <line x1="50" y1="15" x2="50" y2="25" stroke="#14110f" stroke-width="4" stroke-linecap="round"/>
                <circle cx="50" cy="10" r="5" fill="#facc15" stroke="#14110f" stroke-width="3"/>
                <rect x="20" y="25" width="60" height="45" rx="10" fill="#ffffff" stroke="#14110f" stroke-width="4"/>
                <rect x="30" y="40" width="12" height="12" rx="3" fill="#0c4a6e" stroke="#14110f" stroke-width="2"/>
                <rect class="eye" x="58" y="40" width="12" height="12" rx="3" fill="#0c4a6e" stroke="#14110f" stroke-width="2"/>
                <path d="M40 58 Q50 62 60 58" stroke="#14110f" stroke-width="3" stroke-linecap="round"/>
                <rect x="12" y="38" width="8" height="16" rx="3" fill="#facc15" stroke="#14110f" stroke-width="3"/>
                <rect x="80" y="38" width="8" height="16" rx="3" fill="#facc15" stroke="#14110f" stroke-width="3"/>
                <rect x="35" y="75" width="30" height="15" rx="4" fill="#e0f2fe" stroke="#14110f" stroke-width="4"/>
                <line x1="30" y1="82" x2="70" y2="82" stroke="#14110f" stroke-width="4" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="error-content">
          <span class="error-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            خطای ۴۰۴
          </span>
          
          <h1 class="error-title">اوه! این صفحه گم شد.</h1>
          
          <p class="error-desc">
            به نظر می‌رسد آدرسی که وارد کرده‌اید وجود ندارد، حذف شده یا جابه‌جا شده است. نگران نباشید، می‌توانید از فرم زیر برای پیدا کردن مسیر درست استفاده کنید.
          </p>

          <!-- فرم جستجوی استاندارد وردپرس -->
          <form class="error-search" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
            <label for="error-search-input" class="visually-hidden">جستجو در سایت</label>
            <input
              type="search"
              id="error-search-input"
              name="s"
              class="error-search-input"
              placeholder="جستجو در سایت…"
              autocomplete="off"
              enterkeyhint="search"
              required
            >
            <button type="submit" class="btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              جستجو
            </button>
          </form>

          <div class="error-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
              <svg class="icon-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              بازگشت به خانه
            </a>
          </div>
        </div>

      </div>
    </main>
</div>

<?php get_footer(); ?>