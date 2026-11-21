<?php
if (!defined('ABSPATH')) exit;
get_header();
?>

<main class="page raha-search-page">
  <div class="page__inner">

    <!-- Search Bar -->
    <section class="search" aria-label="نوار جست‌وجو">
      <form class="search__form" id="searchForm" role="search" autocomplete="off" novalidate>
        <label class="sr-only" for="q">جست‌وجو در نوشته‌ها</label>
        <div class="search__field" id="searchField">
          <span class="search__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          </span>
          <input id="q" class="search__input" type="search" name="q" placeholder="مثلاً: CSS، سئو، دیزاین سیستم…" role="combobox" aria-expanded="false" aria-controls="spPanel" aria-autocomplete="list" enterkeyhint="search" spellcheck="false" maxlength="80">
          <button type="button" class="search__clear" id="clearBtn" hidden aria-label="پاک کردن عبارت جست‌وجو">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
          <kbd class="search__kbd" id="kbdHint">Ctrl K</kbd>
        </div>
        <button type="submit" class="search__submit">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          <span>جست‌وجو</span>
        </button>
      </form>

      <div class="search__panel" id="spPanel" hidden>
        <div class="panel__group" id="suggGroup" hidden>
          <p class="panel__title">پیشنهادها</p>
          <div class="panel__list" id="suggList" role="listbox" aria-label="پیشنهادهای جست‌وجو"></div>
          <button type="button" class="panel__viewall" id="viewAllBtn" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <span id="viewAllLabel">مشاهده همه نتایج</span>
          </button>
        </div>
        <div class="panel__group" id="recentGroup" hidden>
          <p class="panel__title">جست‌وجوهای اخیر <button type="button" class="panel__link" id="recentClear">پاک کردن</button></p>
          <div class="panel__chips" id="recentChips"></div>
        </div>
        <div class="panel__group">
          <p class="panel__title">جست‌وجوهای پرطرفدار</p>
          <div class="panel__chips" id="popularChips"></div>
        </div>
        <p class="panel__keys" aria-hidden="true">
          <span><kbd>/</kbd> جست‌وجوی سریع</span>
          <span><kbd>↑</kbd><kbd>↓</kbd> جابه‌جایی</span>
          <span><kbd>Enter</kbd> انتخاب</span>
          <span><kbd>Esc</kbd> بستن</span>
        </p>
      </div>
    </section>

    <!-- Toolbar -->
    <div class="toolbar" role="region" aria-label="فیلتر و مرتب‌سازی نتایج">
      <div class="toolbar__cats" role="group" aria-label="فیلتر دسته‌بندی" id="catGroup">
        <!-- در جاوااسکریپت پر می‌شود -->
      </div>
      <div class="toolbar__right">
        <p class="toolbar__count" id="count" aria-live="polite">در حال بارگذاری…</p>
        <button type="button" class="toolbar__reset" id="resetBtn" hidden>حذف فیلترها <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
        <label class="toolbar__sort">
          <span>ترتیب:</span>
          <select class="toolbar__select" id="sortSelect">
            <option value="newest">جدیدترین</option>
            <option value="oldest">قدیمی‌ترین</option>
            <option value="relevant">مرتبط‌ترین</option>
          </select>
        </label>
      </div>
    </div>

    <!-- Results -->
    <div class="results" id="results" aria-live="polite" aria-label="نتایج جست‌وجو"></div>

    <!-- Empty -->
    <div class="empty" id="empty" hidden>
      <div class="empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/></svg></div>
      <h2 class="empty__title">نتیجه‌ای پیدا نشد</h2>
      <p class="empty__text" id="emptyText">برای عبارت وارد شده نوشته‌ای نداریم.</p>
      <div class="panel__chips" id="emptyChips"></div>
      <button type="button" class="chip is-accent" id="emptyReset">نمایش همه نوشته‌ها</button>
    </div>

    <!-- Pager -->
    <nav class="pager" id="pager" aria-label="صفحه‌بندی نتایج" hidden></nav>

  </div>
</main>

<?php get_footer(); ?>