<?php
/* Template Name: تماس با من (مشاوره) */
if (!defined('ABSPATH')) exit;
get_header();
?>

<main id="main-content" class="raha-contact-page">

  <!-- ============ PROCESS INTRO ============ -->
  <section class="process-intro">
    <div class="container">
      <div class="process-card">
        <div class="process-card__icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            <path d="M8 7h8M8 11h6M8 15h4"/>
          </svg>
        </div>
        <div class="process-card__body">
          <h2 class="process-card__title">فرآیند همکاری را خوانده‌اید؟</h2>
          <p class="process-card__desc">
            قبل از ثبت وقت مشاوره، پیشنهاد می‌کنم صفحه <strong>«فرآیند همکاری»</strong> را یک بار بخوانید. روند کامل طراحی سایت، از اولین جلسه و شناخت موارد مورد نیاز، تا طراحی، توسعه، تحویل و پشتیبانی، در صفحه‌ی «فرآیند همکاری» قدم‌به‌قدم توضیح داده شده است.
          </p>
          <p class="process-card__desc">
            اگر قبلاً آن را نخوانده‌اید، پیشنهاد می‌کنم قبل از ثبت وقت مشاوره یک نگاه به آن بیندازید تا با دید بازتری وارد گفتگو شویم. و اگر بعد از خواندن هم سوالی داشتید، نگران نباشید، همین‌جا فرم را پر کنید، در خدمتم.
          </p>
          <a href="<?php echo esc_url(raha_page_url('process-pathway')); ?>" class="process-card__cta">
            مشاهده صفحه فرآیند همکاری
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M19 12H5"/>
              <path d="m12 19-7-7 7-7"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============ CONSULTATION FORM ============ -->
  <section class="consult-form" id="consult-form">
    <div class="container">
      <div class="form-wrapper">

        <div class="form-header" id="formHeader">
          <span class="form-header__badge">۱۵ دقیقه · رایگان</span>
          <h2 class="form-header__title">ثبت وقت مشاوره رایگان</h2>
          <p class="form-header__desc">فرم زیر را پر کنید تا در کمتر از ۲۴ ساعت با شما تماس بگیرم. مشاوره کاملاً رایگان است و هیچ تعهدی ایجاد نمی‌کند.</p>
        </div>

        <form class="cf" id="consultForm" novalidate>
          <?php wp_nonce_field('raha_consultation_nonce', '_raha_consult_nonce'); ?>

          <div class="cf-row">
            <div class="cf-field">
              <label for="cf-name">نام و نام خانوادگی <span class="req" aria-hidden="true">*</span></label>
              <input type="text" id="cf-name" name="name" placeholder="مثلاً: علی رضایی" autocomplete="name" required spellcheck="false">
            </div>
            <div class="cf-field">
              <label for="cf-phone">شماره تماس <span class="req" aria-hidden="true">*</span></label>
              <input type="tel" id="cf-phone" name="phone" placeholder="۰۹۱۲۳۴۵۶۷۸۹" inputmode="numeric" autocomplete="tel" maxlength="11" required>
            </div>
          </div>

          <div class="cf-field">
            <label for="cf-email">ایمیل (اختیاری)</label>
            <input type="email" id="cf-email" name="email" placeholder="you@example.com" dir="ltr" autocomplete="email">
          </div>

          <div class="cf-field">
            <label>موضوع مشاوره <span class="req" aria-hidden="true">*</span></label>
            <div class="cf-radios" role="radiogroup" aria-label="موضوع مشاوره">
              <label class="cf-radio"><input type="radio" name="service" value="design"><span>طراحی سایت</span></label>
              <label class="cf-radio"><input type="radio" name="service" value="seo"><span>سئو</span></label>
              <label class="cf-radio"><input type="radio" name="service" value="redesign"><span>بازطراحی</span></label>
              <label class="cf-radio"><input type="radio" name="service" value="consult"><span>مشاوره / استراتژی</span></label>
              <label class="cf-radio"><input type="radio" name="service" value="other"><span>سایر</span></label>
            </div>
          </div>

          <div class="cf-field">
            <label for="cf-desc">توضیحات پروژه</label>
            <textarea id="cf-desc" name="desc" placeholder="هر چه فکر می‌کنید لازم است بدانم را اینجا بنویسید — مثلاً هدف، نمونه‌های موردعلاقه، زمان‌بندی و..."></textarea>
          </div>

          <div class="cf-field">
            <label>روش ارتباط ترجیحی <span class="req" aria-hidden="true">*</span></label>
            <div class="cf-radios" role="radiogroup" aria-label="روش ارتباط">
              <label class="cf-radio"><input type="radio" name="method" value="phone"><span>تلفنی</span></label>
              <label class="cf-radio"><input type="radio" name="method" value="telegram"><span>تلگرام</span></label>
            </div>
            <div class="cf-telegram-warning" id="cfTelegramWarning" hidden>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
              <p>
                <strong>توجه:</strong> اگر تلگرام شما به شماره‌ای که وارد کرده‌اید متصل نیست، لطفاً آیدی تلگرام خود را در بخش <strong>«توضیحات پروژه»</strong> بنویسید تا بتوانم با شما در ارتباط باشم.
              </p>
            </div>
          </div>

          <div class="cf-error" id="cfError" role="alert"></div>

          <button type="submit" class="cf-submit" id="cfSubmit">
            <span>درخواست ثبت وقت مشاوره</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M19 12H5"/>
              <path d="m12 19-7-7 7-7"/>
            </svg>
          </button>

        </form>

        <div class="cf-success" id="cfSuccess" role="status">
          <div class="cf-success__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h3 class="cf-success__title">درخواست شما ثبت شد!</h3>
          <p class="cf-success__desc">در کمتر از ۲۴ ساعت کاری با شما تماس می‌گیرم تا زمان جلسه مشاوره را هماهنگ کنیم.</p>
          
          <!-- تغییر دکمه به لینک خانه -->
          <a href="<?php echo esc_url(home_url('/')); ?>" class="cf-success__btn">
            بازگشت به صفحه اصلی
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </a>
        </div>

      </div>
    </div>
  </section>

  <!-- ============ FAQ ============ -->
  <section class="contact-faq">
    <div class="container">
      <header class="faq-section-head">
        <p class="section-eyebrow">سوالات رایج</p>
        <h2 class="section-title">قبل از ثبت وقت بدانید</h2>
        <p class="section-lead">اگر پاسخ سوالتان اینجا نیست، از طریق فرم بالای صفحه بپرسید — در خدمتم.</p>
      </header>

      <div class="faq-list">
        <details class="faq-item" open>
          <summary class="faq-summary">آیا مشاوره تلفنی رایگان است؟</summary>
          <div class="faq-content">بله. جلسه‌ی ۱۵ تا ۳۰ دقیقه‌ای مشاوره‌ی اولیه کاملاً رایگان است و هیچ تعهدی برای ادامه‌ی همکاری ایجاد نمی‌کند. هدف این جلسه شناخت پروژه و بررسی راه‌های ممکن است.</div>
        </details>
        <details class="faq-item">
          <summary class="faq-summary">چقدر طول می‌کشد تا بعد از ثبت وقت با من تماس بگیرید؟</summary>
          <div class="faq-content">در کمتر از ۲۴ ساعت کاری. اگر درخواست شما آخر هفته یا تعطیلات ثبت شود، اولین روز کاری بعد با شما تماس می‌گیرم.</div>
        </details>
        <details class="faq-item">
          <summary class="faq-summary">اگر پروژه‌ی من خاص است، باز هم می‌توانم مشاوره بگیرم؟</summary>
          <div class="faq-content">قطعاً. پروژه‌های خاص و غیرمعمول جذاب‌ترین بخش کار من هستند. در فرم، «سایر» را انتخاب کنید و توضیح دهید — بعد با هم بررسی می‌کنیم که چطور می‌توانیم کنار بیاییم.</div>
        </details>
        <details class="faq-item">
          <summary class="faq-summary">مشاوره فقط برای پروژه‌های جدید است؟</summary>
          <div class="faq-content">نه. اگر سایت فعلی دارید و می‌خواهید عملکرد، سئو یا تجربه کاربری را بهبود دهید، این جلسه دقیقاً برای شماست. در حین مشاوره، وضعیت فعلی بررسی و نقشه راه ارائه می‌شود.</div>
        </details>
        <details class="faq-item">
          <summary class="faq-summary">چه اطلاعاتی برای جلسه مشاوره آماده کنم؟</summary>
          <div class="faq-content">لازم نیست چیز خاصی آماده کنید. اگر این موارد را در دسترس داشته باشید، گفتگو مفیدتر خواهد بود: هدف اصلی کسب‌وکار، مخاطب هدف، نمونه سایت‌هایی که می‌پسندید (۲ تا ۳ مورد)، و اگر سایت فعلی دارید، آدرس آن. اگر هیچ‌کدام را هم نداشته باشید، در جلسه با هم بررسی می‌کنیم.</div>
        </details>
        <details class="faq-item">
          <summary class="faq-summary">آیا بعد از مشاوره، الزامی به همکاری دارم؟</summary>
          <div class="faq-content">به هیچ وجه. مشاوره‌ی اولیه کاملاً بدون تعهد است. اگر بعد از جلسه احساس کردید همکاری با من مناسب نیازتان نیست، آزادانه می‌توانید تصمیم دیگری بگیرید.</div>
        </details>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>