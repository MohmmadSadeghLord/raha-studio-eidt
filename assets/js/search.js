(function () {
  'use strict';

  var data = window.RahaSearchData || { posts: [], cats: {} };
  var POSTS = data.posts;
  var CATS = data.cats;

  var $  = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  var FA_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
  var AR_DIGITS = '٠١٢٣٤٥٦٧٨٩';
  function fa(n) { return String(n).replace(/\d/g, function (d) { return FA_DIGITS[+d]; }); }
  function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function escRe(s) { return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  function normalizeText(str) {
    return String(str == null ? '' : str)
      .replace(/[۰-۹]/g, function (d) { return FA_DIGITS.indexOf(d); })
      .replace(/[٠-٩]/g, function (d) { return AR_DIGITS.indexOf(d); })
      .replace(/[\u064A\u0649]/g, '\u06CC')
      .replace(/\u0643/g, '\u06A9')
      .replace(/\u0629/g, '\u0647')
      .replace(/\u06C0/g, '\u0647')
      .replace(/[أإآٱ]/g, '\u0627')
      .replace(/\u0624/g, '\u0648')
      .replace(/\u0626/g, '\u06CC')
      .replace(/[\u064B-\u0652\u0670\u0640]/g, '')
      .replace(/[\u200B-\u200F\uFEFF]/g, '')
      .replace(/\u200C/g, ' ')
      .replace(/[\u00A0\u2000-\u200A\u2010-\u2015]/g, ' ')
      .replace(/\s+/g, ' ')
      .toLowerCase()
      .trim();
  }

  function compactText(str) { return normalizeText(str).replace(/\s+/g, ''); }

  function highlightRe(word) {
    return word.split('').map(function (ch) {
      if (ch === '\u06CC') return '[\u06CC\u064A\u0649]';
      if (ch === '\u06A9') return '[\u06A9\u0643]';
      if (ch === '\u0647') return '[\u0647\u0629\u06C0]';
      if (ch === '\u0627') return '[\u0627\u0623\u0625\u0622\u0671]';
      if (ch === '\u0648') return '[\u0648\u0624]';
      if (/[0-9]/.test(ch)) return '[' + ch + FA_DIGITS[+ch] + AR_DIGITS[+ch] + ']';
      if (ch === ' ') return '\\s+';
      return escRe(ch);
    }).join('');
  }

  function highlight(text, q) {
    var safe = esc(text);
    var words = String(q || '').trim().split(/\s+/).filter(Boolean);
    if (!words.length) return safe;
    try {
      var re = new RegExp('(' + words.map(highlightRe).join('|') + ')', 'gi');
      return safe.replace(re, '<mark>$1</mark>');
    } catch (e) { return safe; }
  }

  var ICONS = {
    bolt:    '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
    code:    '<path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/>',
    globe:   '<circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/>',
    book:    '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>'
  };
  function svg(name) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' + (ICONS[name] || ICONS.book) + '</svg>';
  }

  var POPULAR = ['CSS', 'Core Web Vitals', 'دیزاین سیستم', 'Next.js', 'سئو', 'موبایل‌فرست', 'وردپرس', 'تایپوگرافی'];
  var PER_PAGE = 6;
  var RECENT_KEY = 'raha.recentSearches';

  // Elements
  var form        = $('#searchForm');
  var input       = $('#q');
  var field       = $('#searchField');
  var clearBtn    = $('#clearBtn');
  var panel       = $('#spPanel');
  var suggGroup   = $('#suggGroup');
  var suggList    = $('#suggList');
  var viewAllBtn  = $('#viewAllBtn');
  var viewAllLbl  = $('#viewAllLabel');
  var recentGroup = $('#recentGroup');
  var recentChips = $('#recentChips');
  var recentClear = $('#recentClear');
  var popularChips= $('#popularChips');
  var countEl     = $('#count');
  var resetBtn    = $('#resetBtn');
  var sortSelect  = $('#sortSelect');
  var results     = $('#results');
  var emptyEl     = $('#empty');
  var emptyText   = $('#emptyText');
  var emptyChips  = $('#emptyChips');
  var emptyReset  = $('#emptyReset');
  var pager       = $('#pager');
  var catGroup    = $('#catGroup');

  // Build categories dynamically
  if (catGroup) {
    var catHtml = '';
    var cIdx = 0;
    for (var catName in CATS) {
      if (CATS.hasOwnProperty(catName)) {
        var isAll = catName === 'all';
        catHtml += '<button type="button" class="cat" data-cat="' + esc(catName) + '" aria-pressed="' + (isAll ? 'true' : 'false') + '">';
        catHtml += '<span class="cat__label">' + esc(CATS[catName]) + '</span>';
        catHtml += '<span class="cat__count" data-count-for="' + esc(catName) + '">۰</span>';
        catHtml += '</button>';
        cIdx++;
      }
    }
    catGroup.innerHTML = catHtml;
  }

  var catBtns = $$('.cat');

  var state = { q: '', cat: 'all', sort: 'newest', page: 1 };
  var activeIndex = -1;
  var suggestions = [];

  var recent = (function () {
    try { var raw = localStorage.getItem(RECENT_KEY); var arr = raw ? JSON.parse(raw) : []; return Array.isArray(arr) ? arr.slice(0, 6) : []; } catch (e) { return []; }
  })();
  function saveRecent() { try { localStorage.setItem(RECENT_KEY, JSON.stringify(recent)); } catch (e) {} }

  function prepareQuery(q) {
    var normalized = normalizeText(q);
    var words = normalized ? normalized.split(' ').filter(Boolean) : [];
    var compact = words.length ? compactText(q) : '';
    return { normalized: normalized, words: words, compact: compact };
  }

  function postHaystack(post) { return normalizeText(post.title + ' ' + post.tag + ' ' + post.desc); }
  function postHaystackCompact(post) { return compactText(post.title + ' ' + post.tag + ' ' + post.desc); }

  function matchesQuery(post, q) {
    if (!q.words.length) return true;
    var hay = postHaystack(post);
    var allWords = q.words.every(function (w) { return hay.indexOf(w) > -1; });
    if (allWords) return true;
    if (q.compact) { if (postHaystackCompact(post).indexOf(q.compact) > -1) return true; }
    return false;
  }

  function scorePost(post, q) {
    if (!q.words.length) return 0;
    var title = normalizeText(post.title);
    var tag   = normalizeText(post.tag);
    var desc  = normalizeText(post.desc);
    var s = 0;
    q.words.forEach(function (w) {
      if (title.indexOf(w) > -1) s += 40;
      if (tag.indexOf(w)   > -1) s += 25;
      if (desc.indexOf(w)  > -1) s += 10;
    });
    var hay = title + ' ' + tag + ' ' + desc;
    if (hay.indexOf(q.normalized) > -1) s += 60;
    if (q.compact && postHaystackCompact(post).indexOf(q.compact) > -1) s += 40;
    if (title.indexOf(q.words[0]) === 0) s += 30;
    return s;
  }

  function matchTypeOf(post, q) {
    if (!q.words.length) return '';
    var title = normalizeText(post.title);
    var tag   = normalizeText(post.tag);
    var desc  = normalizeText(post.desc);
    var inTitle = q.words.every(function (w) { return title.indexOf(w) > -1; });
    var inTag   = q.words.every(function (w) { return tag.indexOf(w)   > -1; });
    var inDesc  = q.words.every(function (w) { return desc.indexOf(w)  > -1; });
    if (inTitle) return 'عنوان';
    if (inTag)   return 'دسته';
    if (inDesc)  return 'متن';
    return 'همه';
  }

  function buildList(cat, q) {
    return POSTS.filter(function (p) {
      if (cat !== 'all' && p.tag !== cat) return false;
      return matchesQuery(p, q);
    });
  }

  function sortList(list, sort, q) {
    if (sort === 'oldest') return list.slice().sort(function (a, b) { return a.ts - b.ts; });
    if (sort === 'relevant' && q.words.length) { return list.slice().sort(function (a, b) { return scorePost(b, q) - scorePost(a, q); }); }
    return list.slice().sort(function (a, b) { return b.ts - a.ts; });
  }

  function currentList() {
    var q = prepareQuery(state.q);
    var list = buildList(state.cat, q);
    return sortList(list, state.sort, q);
  }

  function cardHTML(p, q) {
    var mediaHTML = p.thumb ?
      '<img src="' + p.thumb + '" alt="" class="post__thumb">' :
      '<div class="post__icon" aria-hidden="true">' + svg(p.icon) + '</div>';

    return '' +
      '<article class="post" data-id="' + p.id + '">' +
        '<div class="post__media">' +
          '<span class="post__tag">' + esc(p.tag) + '</span>' +
          mediaHTML +
        '</div>' +
        '<div class="post__body">' +
          '<h3 class="post__title">' +
            '<a class="post__title-link" href="' + p.url + '">' + highlight(p.title, q) + '</a>' +
          '</h3>' +
          '<p class="post__desc">' + highlight(p.desc, q) + '</p>' +
          '<div class="post__foot">' +
            '<span class="post__date">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>' +
              esc(p.date) +
            '</span>' +
            '<span class="post__arrow" aria-hidden="true">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>' +
            '</span>' +
          '</div>' +
        '</div>' +
      '</article>';
  }

  function countParts(total, q) {
    var qTrim = state.q.trim();
    if (qTrim && state.cat !== 'all') return { num: total, rest: 'نتیجه برای «' + qTrim + '» در دسته «' + state.cat + '»' };
    if (qTrim) return { num: total, rest: 'نتیجه برای «' + qTrim + '»' };
    if (state.cat !== 'all') return { num: total, rest: 'نوشته در دسته «' + state.cat + '»' };
    return { num: total, rest: 'نوشته در آرشیو' };
  }

  function pageNumbers(current, total) {
    if (total <= 7) { var all = []; for (var i = 1; i <= total; i++) all.push(i); return all; }
    var pages = {}; pages[1] = true; pages[total] = true; pages[current] = true;
    if (current > 1) pages[current - 1] = true;
    if (current < total) pages[current + 1] = true;
    if (current <= 3) { pages[2] = true; pages[3] = true; pages[4] = true; }
    if (current >= total - 2) { pages[total - 1] = true; pages[total - 2] = true; pages[total - 3] = true; }
    var out = []; for (var k = 1; k <= total; k++) if (pages[k]) out.push(k);
    return out;
  }

  function renderPager(totalPages) {
    if (totalPages <= 1) { pager.hidden = true; pager.innerHTML = ''; return; }
    pager.hidden = false;
    var html = '';
    html += '<button type="button" data-page="' + (state.page - 1) + '" aria-label="صفحه قبل"' + (state.page === 1 ? ' disabled' : '') + '><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>قبل</button>';
    var nums = pageNumbers(state.page, totalPages);
    for (var i = 0; i < nums.length; i++) {
      var n = nums[i];
      if (i > 0 && n - nums[i - 1] > 1) html += '<span class="pager-gap" aria-hidden="true">…</span>';
      html += '<button type="button" data-page="' + n + '"' + (n === state.page ? ' aria-current="page"' : '') + ' aria-label="صفحه ' + fa(n) + '">' + fa(n) + '</button>';
    }
    html += '<button type="button" data-page="' + (state.page + 1) + '" aria-label="صفحه بعد"' + (state.page === totalPages ? ' disabled' : '') + '>بعد<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>';
    pager.innerHTML = html;
  }

  pager.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('button[data-page]') : null;
    if (!btn || btn.disabled) return;
    var n = parseInt(btn.dataset.page, 10);
    if (!n || n === state.page) return;
    state.page = n; render();
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  function updateCategoryCounts(q) {
    var counts = { all: 0 };
    POSTS.forEach(function (p) {
      if (matchesQuery(p, q)) { counts.all++; counts[p.tag] = (counts[p.tag] || 0) + 1; }
    });
    $$('.cat__count', catGroup).forEach(function (el) {
      var cat = el.dataset.countFor; var v = counts[cat] || 0;
      el.textContent = fa(v);
      el.parentElement.style.opacity = v === 0 ? '0.5' : '';
    });
  }

  function render() {
    var q = prepareQuery(state.q);
    var list = currentList();
    var total = list.length;
    var totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
    if (state.page > totalPages) state.page = totalPages;
    if (state.page < 1) state.page = 1;
    var start = (state.page - 1) * PER_PAGE;
    var pageItems = list.slice(start, start + PER_PAGE);

    if (total === 0) {
      results.innerHTML = ''; results.hidden = true; emptyEl.hidden = false;
      if (q.normalized && state.cat !== 'all') emptyText.innerHTML = 'برای «<b>' + esc(state.q.trim()) + '</b>» در دسته «<b>' + esc(state.cat) + '</b>» نوشته‌ای نداریم.';
      else if (q.normalized) emptyText.innerHTML = 'برای «<b>' + esc(state.q.trim()) + '</b>» نوشته‌ای نداریم. عبارت کوتاه‌تری بنویسید.';
      else emptyText.innerHTML = 'در دسته «<b>' + esc(state.cat) + '</b>» نوشته‌ای نیست.';
    } else {
      results.hidden = false; emptyEl.hidden = true;
      results.innerHTML = pageItems.map(function (p) { return cardHTML(p, state.q); }).join('');
    }

    var parts = countParts(total, state.q);
    countEl.innerHTML = '<b>' + fa(parts.num) + '</b> ' + esc(parts.rest);
    var hasFilter = !!state.q.trim() || state.cat !== 'all';
    resetBtn.hidden = !hasFilter;
    sortSelect.querySelector('option[value="relevant"]').disabled = !q.words.length;
    updateCategoryCounts(q);
    renderPager(totalPages);
    updateURL();
  }

  function updateURL() {
    try {
      var params = new URLSearchParams();
      // از پارامتر s وردپرس استفاده می‌کنیم
      if (state.q.trim()) params.set('s', state.q.trim());
      if (state.cat !== 'all') params.set('cat', state.cat);
      if (state.sort !== 'newest') params.set('sort', state.sort);
      if (state.page > 1) params.set('page', String(state.page));
      var qs = params.toString();
      var url = window.location.pathname + (qs ? '?' + qs : '');
      history.replaceState(null, '', url);
    } catch (e) {}
  }

  function readURL() {
    try {
      var params = new URLSearchParams(window.location.search);
      // خواندن s (وردپرس) و در غیر این صورت q (برای اطمینان)
      var q = params.get('s') || params.get('q') || '';
      state.q = q;
      state.cat = params.get('cat') || 'all';
      state.sort = params.get('sort') || 'newest';
      state.page = parseInt(params.get('page') || '1', 10) || 1;

      // مقداردهی به اینپوت
      input.value = state.q;
      clearBtn.hidden = !state.q;

      // ست کردن دسته و مرتب‌سازی
      sortSelect.value = ['newest','oldest','relevant'].indexOf(state.sort) > -1 ? state.sort : 'newest';
      catBtns.forEach(function (b) {
        b.setAttribute('aria-pressed', b.dataset.cat === state.cat ? 'true' : 'false');
      });
    } catch (e) {}
  }

  function buildSuggestions() {
    var q = prepareQuery(state.q);
    if (!q.words.length) return [];
    return POSTS.map(function (p) { return { p: p, s: scorePost(p, q), m: matchTypeOf(p, q) }; })
      .filter(function (x) { return x.s > 0; })
      .sort(function (a, b) { return b.s - a.s; })
      .slice(0, 5);
  }

  function renderSuggestions() {
    var q = state.q.trim();
    suggestions = buildSuggestions(); activeIndex = -1;
    input.removeAttribute('aria-activedescendant'); suggList.innerHTML = '';
    if (!q || !suggestions.length) { suggGroup.hidden = true; return; }
    suggGroup.hidden = false;
    suggestions.forEach(function (item, i) {
      var p = item.p; var b = document.createElement('button');
      b.type = 'button'; b.className = 'panel__option'; b.id = 'sug-' + i;
      b.setAttribute('role', 'option'); b.setAttribute('aria-selected', 'false'); b.dataset.index = i;
      b.innerHTML = '<span class="panel__option-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg></span><span class="panel__option-title">' + highlight(p.title, q) + '</span><span class="panel__option-meta"><span class="panel__option-meta-tag">' + esc(item.m) + '</span></span>';
      suggList.appendChild(b);
    });
    var total = buildList(state.cat, prepareQuery(state.q)).length;
    if (total > 0) { viewAllBtn.hidden = false; viewAllLbl.textContent = 'مشاهده همه نتایج (' + fa(total) + ')'; }
    else viewAllBtn.hidden = true;
  }

  function setActive(i) {
    var opts = $$('.panel__option', suggList);
    if (!opts.length) { activeIndex = -1; return; }
    if (i < 0) i = opts.length - 1;
    if (i >= opts.length) i = 0;
    opts.forEach(function (o, idx) { var on = idx === i; o.classList.toggle('is-active', on); o.setAttribute('aria-selected', on ? 'true' : 'false'); });
    activeIndex = i; input.setAttribute('aria-activedescendant', opts[i].id);
    if (opts[i].scrollIntoView) opts[i].scrollIntoView({ block: 'nearest' });
  }

  function openPanel() { if (!panel.hidden) return; panel.hidden = false; field.classList.add('is-open'); input.setAttribute('aria-expanded', 'true'); }
  function closePanel() { if (panel.hidden) return; panel.hidden = true; field.classList.remove('is-open'); input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); activeIndex = -1; }

  function makeChip(term, accent) {
    var b = document.createElement('button'); b.type = 'button'; b.className = 'chip' + (accent ? ' is-accent' : ''); b.textContent = term;
    b.addEventListener('click', function () { addRecent(term); setQuery(term, { silent: true }); closePanel(); results.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
    return b;
  }
  function renderRecent() { recentChips.innerHTML = ''; recentGroup.hidden = recent.length === 0; recent.forEach(function (t) { recentChips.appendChild(makeChip(t)); }); }
  function renderPopular(target) { if (!target) return; target.innerHTML = ''; POPULAR.forEach(function (t) { target.appendChild(makeChip(t)); }); }

  function setQuery(term, opts) {
    opts = opts || {}; input.value = term; state.q = term; state.page = 1; clearBtn.hidden = !term;
    renderSuggestions(); render(); if (!opts.silent) openPanel();
  }

  function addRecent(term) {
    term = String(term || '').trim(); if (!term) return;
    recent = [term].concat(recent.filter(function (x) { return normalizeText(x) !== normalizeText(term); })).slice(0, 6);
    saveRecent(); renderRecent();
  }

  function goToPost(p) {
    if (!p) return;
    window.location.href = p.url; // هدایت مستقیم به پست
  }

  var inputTimer = null;
  input.addEventListener('input', function () {
    state.q = input.value; state.page = 1; clearBtn.hidden = !state.q; openPanel();
    if (inputTimer) window.clearTimeout(inputTimer);
    inputTimer = window.setTimeout(function () { renderSuggestions(); render(); }, 60);
  });

  input.addEventListener('focus', function () { renderRecent(); renderPopular(popularChips); openPanel(); });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); openPanel(); if (suggestions.length) setActive(activeIndex + 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); openPanel(); if (suggestions.length) setActive(activeIndex - 1); }
    else if (e.key === 'Enter') { if (activeIndex > -1 && suggestions[activeIndex]) { e.preventDefault(); goToPost(suggestions[activeIndex].p); } }
    else if (e.key === 'Escape') { if (!panel.hidden) { e.preventDefault(); closePanel(); } else if (state.q) { e.preventDefault(); setQuery('', { silent: true }); } }
  });

  suggList.addEventListener('click', function (e) {
    var btn = e.target.closest ? e.target.closest('.panel__option') : null; if (!btn) return;
    var item = suggestions[+btn.dataset.index]; if (item) goToPost(item.p);
  });
  suggList.addEventListener('mousemove', function (e) {
    var btn = e.target.closest ? e.target.closest('.panel__option') : null; if (!btn) return;
    var i = +btn.dataset.index; if (i !== activeIndex) setActive(i);
  });

  form.addEventListener('submit', function (e) { e.preventDefault(); addRecent(state.q); closePanel(); state.page = 1; render(); results.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
  viewAllBtn.addEventListener('click', function () { addRecent(state.q); closePanel(); state.page = 1; render(); results.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
  clearBtn.addEventListener('click', function () { setQuery('', { silent: true }); input.focus(); openPanel(); });
  recentClear.addEventListener('click', function () { recent = []; saveRecent(); renderRecent(); input.focus(); });
  sortSelect.addEventListener('change', function () { state.sort = sortSelect.value; state.page = 1; render(); });
  resetBtn.addEventListener('click', function () { state.cat = 'all'; state.sort = 'newest'; state.page = 1; sortSelect.value = 'newest'; catBtns.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.cat === 'all' ? 'true' : 'false'); }); setQuery('', { silent: true }); closePanel(); });
  emptyReset.addEventListener('click', function () { state.cat = 'all'; state.page = 1; catBtns.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.cat === 'all' ? 'true' : 'false'); }); setQuery('', { silent: true }); input.focus(); });
  catBtns.forEach(function (b) { b.addEventListener('click', function () { state.cat = b.dataset.cat; state.page = 1; catBtns.forEach(function (o) { o.setAttribute('aria-pressed', o === b ? 'true' : 'false'); }); render(); closePanel(); }); });

  document.addEventListener('click', function (e) { if (!panel.hidden && !panel.contains(e.target) && !field.contains(e.target)) { closePanel(); } });
  document.addEventListener('keydown', function (e) {
    var tag = (e.target.tagName || '').toLowerCase();
    var typing = tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable;
    if ((e.key === 'k' || e.key === 'K') && (e.ctrlKey || e.metaKey)) { e.preventDefault(); input.focus(); input.select(); openPanel(); }
    else if (e.key === '/' && !typing) { e.preventDefault(); input.focus(); openPanel(); }
    else if (e.key === 'Escape') { closePanel(); }
  });

  window.addEventListener('popstate', function () { readURL(); renderSuggestions(); render(); });

  renderRecent(); renderPopular(popularChips); renderPopular(emptyChips); clearBtn.hidden = true;
  readURL(); renderSuggestions(); render();

  // فوکوس خودکار روی اینپوت جستجو هنگام ورود از آیکون هدر (?s=)
  if (window.location.search.indexOf('s=') !== -1) {
    setTimeout(function () { input.focus(); }, 100);
  }
})();