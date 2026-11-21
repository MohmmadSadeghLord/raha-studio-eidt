<?php
/* Template Name: فرآیند همکاری */
if (!defined('ABSPATH')) exit;
get_header();

// تابع کمکی برای بررسی نمایش
function raha_show_process_section($key) {
    return raha_opt('process_show_' . $key, '1') === '1';
}
?>

<style>
/* استایل‌های اختصاصی صفحه فرآیند (کپی شده از پروتوتایپ) */
<?php echo raha_opt('process_custom_css', ''); ?>
</style>

<div class="process-page-wrapper">
    
    <?php if (raha_show_process_section('intro')) : 
        $eyebrow = raha_opt('process_eyebrow', 'فرآیند همکاری');
        $title = raha_opt('process_intro_title', 'مسیر تبدیل فکرهای شما به یک وب‌سایت واقعی و سودآور.');
        $lead = raha_opt('process_intro_lead');
        
        $c1t = raha_opt('process_btn_1_text');
        $c1u = raha_opt('process_btn_1_url');
        $c2t = raha_opt('process_btn_2_text');
        $c2u = raha_opt('process_btn_2_url');
        
        $sticker = raha_opt('process_sticker', '100% شفاف');
        $panel_title = raha_opt('process_panel_title', 'Project_Overview.exe');
        
        $stat1_n = raha_opt('process_stat_1_num', '۸');
        $stat1_l = raha_opt('process_stat_1_label', 'گام شفاف و مشخص');
        $stat2_n = raha_opt('process_stat_2_num', '۴–۶');
        $stat2_l = raha_opt('process_stat_2_label', 'میانگین زمان تحویل (هفته)');
        $stat3_n = raha_opt('process_stat_3_num', '۳۰ الی ۴۵');
        $stat3_l = raha_opt('process_stat_3_label', 'روز پشتیبانی رایگان');
    ?>
    <!-- ۰۱ · مقدمه (Hero) -->
    <section class="hero" id="intro" aria-labelledby="intro-title">
      <div class="hero__shape hero__shape--1"></div>
      <div class="hero__shape hero__shape--2"></div>
      <div class="hero__shape hero__shape--3"></div>

      <div class="wrap">
        <div class="hero__grid">

          <!-- ستون اول: محتوای متنی -->
          <div class="hero__content">
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h1 class="hero__title" id="intro-title"><?php echo esc_html($title); ?></h1>
            <p class="hero__lead"><?php echo esc_html($lead); ?></p>
            <div class="hero__actions">
              <?php if ($c1t && $c1u) : ?>
                <a href="<?php echo esc_url($c1u); ?>" class="btn btn--primary"><?php echo esc_html($c1t); ?></a>
              <?php endif; ?>
              <?php if ($c2t && $c2u) : ?>
                <a href="<?php echo esc_url($c2u); ?>" class="btn btn--ghost"><?php echo esc_html($c2t); ?></a>
              <?php endif; ?>
            </div>
          </div>

          <!-- ستون دوم: پنل آمار -->
          <div class="hero__panel-wrapper">
            <div class="hero__panel">
              <div class="hero__sticker"><?php echo esc_html($sticker); ?></div>
              <div class="hero__panel-header">
                <div class="hero__panel-dots"><span></span><span></span><span></span></div>
                <span class="hero__panel-title"><?php echo esc_html($panel_title); ?></span>
              </div>
              <div class="hero__panel-body">
                <div class="stat">
                  <div class="stat__info">
                    <div class="stat__icon" style="background: var(--bg);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <span class="stat__label"><?php echo esc_html($stat1_l); ?></span>
                  </div>
                  <span class="stat__num"><?php echo esc_html($stat1_n); ?></span>
                </div>
                <div class="stat">
                  <div class="stat__info">
                    <div class="stat__icon" style="background: var(--sun);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <span class="stat__label"><?php echo esc_html($stat2_l); ?></span>
                  </div>
                  <span class="stat__num"><?php echo esc_html($stat2_n); ?></span>
                </div>
                <div class="stat">
                  <div class="stat__info">
                    <div class="stat__icon" style="background: var(--bg);">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <span class="stat__label"><?php echo esc_html($stat3_l); ?></span>
                  </div>
                  <span class="stat__num"><?php echo esc_html($stat3_n); ?></span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('philosophy')) : 
        $phil_title = raha_opt('process_philosophy_title');
        $phil_items = raha_opt('process_philosophy_items', []);
    ?>
    <!-- ۰۲ · فلسفه -->
    <section class="section section--alt" id="philosophy" aria-labelledby="philosophy-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۲ — فلسفه‌ی کار</p>
          <h2 class="sec-title" id="philosophy-title"><?php echo esc_html($phil_title); ?></h2>
        </header>
        <div class="pillars">
          <?php foreach ($phil_items as $item) : ?>
            <div class="pillar">
              <div class="pillar__icon" aria-hidden="true"><?php echo esc_html($item['icon']); ?></div>
              <h3><?php echo esc_html($item['title']); ?></h3>
              <p><?php echo esc_html($item['desc']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('process')) : 
        $timeline_lead = raha_opt('process_timeline_lead');
        $timeline_items = raha_opt('process_timeline_items', []);
    ?>
    <!-- ۰۴ · Timeline -->
    <section class="section" id="process" aria-labelledby="process-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۴ — مسیر پروژه</p>
          <h2 class="sec-title" id="process-title">هشت گام از فکر تا لانچ.</h2>
          <p class="sec-lead"><?php echo esc_html($timeline_lead); ?></p>
        </header>

        <ol class="tl__list">
          <?php foreach ($timeline_items as $index => $item) : ?>
            <li class="tl__step" data-phase="<?php echo esc_attr($item['phase_num']); ?>">
              <span class="tl__node" aria-hidden="true"><?php echo esc_html(strtr($index + 1, '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?></span>
              <article class="tl__card">
                <div class="tl__head"><span class="phase"><?php echo esc_html($item['phase']); ?></span><span class="dur"><?php echo esc_html($item['duration']); ?></span></div>
                <h3><?php echo esc_html($item['title']); ?></h3>
                <p><?php echo esc_html($item['desc']); ?></p>
                <div class="out"><b>خروجی:</b> <?php echo esc_html($item['output']); ?></div>
              </article>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('gantt')) : 
        $gantt_items = raha_opt('process_gantt_items', []);
    ?>
    <!-- ۰۵ · Gantt -->
    <section class="section section--alt" id="gantt" aria-labelledby="gantt-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۵ — زمان‌بندی</p>
          <h2 class="sec-title" id="gantt-title">هر فاز چقدر طول می‌کشد؟</h2>
        </header>

        <div class="gantt">
          <?php foreach ($gantt_items as $g) : ?>
          <div class="gantt__row">
            <div class="gantt__label" style="--c: <?php echo esc_attr($g['color']); ?>"><?php echo esc_html($g['label']); ?></div>
            <div class="gantt__bar-wrap"><div class="gantt__bar" style="--c: <?php echo esc_attr($g['color']); ?>; width: <?php echo esc_attr($g['width']); ?>;"><?php echo esc_html($g['time_text']); ?></div></div>
          </div>
          <?php endforeach; ?>
          <div class="gantt__total"><span>مجموع برآوردی:</span><b>۴–۶ هفته کاری</b></div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('who')) : 
        $who_me = raha_opt('process_who_me', []);
        $who_you = raha_opt('process_who_you', []);
    ?>
    <!-- ۰۶ · Who -->
    <section class="section" id="who" aria-labelledby="who-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۶ — تعهدات طرفین</p>
          <h2 class="sec-title" id="who-title">چه کاری با من است، چه کاری با شما.</h2>
        </header>
        <div class="whowhat">
          <div class="ww-col ww-col--me">
            <div class="ww-col__head"><h3><span aria-hidden="true">من</span>تعهدات من</h3><span class="ww-count"><?php echo esc_html(strtr(count($who_me), '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?> مورد</span></div>
            <ul class="ww-list">
              <?php foreach ($who_me as $item) : ?><li><?php echo esc_html($item['text']); ?></li><?php endforeach; ?>
            </ul>
          </div>
          <div class="ww-col ww-col--you">
            <div class="ww-col__head"><h3><span aria-hidden="true">شما</span>تعهدات شما</h3><span class="ww-count"><?php echo esc_html(strtr(count($who_you), '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?> مورد</span></div>
            <ul class="ww-list">
              <?php foreach ($who_you as $item) : ?><li><?php echo esc_html($item['text']); ?></li><?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('req')) : 
        $req_items = raha_opt('process_req_items', []);
    ?>
    <!-- ۰۷ · Requirements -->
    <section class="section section--alt" id="req" aria-labelledby="req-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۷ — چه چیزی از شما می‌خواهم</p>
          <h2 class="sec-title" id="req-title">پیش‌نیازهای شروع پروژه.</h2>
        </header>
        <div class="req-grid">
          <?php foreach ($req_items as $index => $item) : ?>
            <div class="req-item">
                <span class="req-num" aria-hidden="true"><?php echo esc_html(strtr(str_pad($index + 1, 2, '0', STR_PAD_LEFT), '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?></span>
                <div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['desc']); ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('comm')) : 
        $comm_title = raha_opt('process_comm_title');
        $comm_items = raha_opt('process_comm_items', []);
    ?>
    <!-- ۰۸ · Communication -->
    <section class="section" id="comm" aria-labelledby="comm-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۸ — کانال‌های ارتباطی</p>
          <h2 class="sec-title" id="comm-title"><?php echo esc_html($comm_title); ?></h2>
        </header>
        <div class="comm">
          <?php foreach ($comm_items as $item) : ?>
            <div class="comm-card"><div class="comm-card__icon" aria-hidden="true"><?php echo esc_html($item['icon']); ?></div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['desc']); ?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('deliv')) : 
        $deliv_items = raha_opt('process_deliv_items', []);
    ?>
    <!-- ۰۹ · Deliverables -->
    <section class="section section--alt" id="deliv" aria-labelledby="deliv-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۰۹ — تحویل‌دادنی‌ها</p>
          <h2 class="sec-title" id="deliv-title">در پایان، دقیقاً چه چیزی تحویل می‌گیرید.</h2>
        </header>
        <ul class="deliv">
          <?php foreach ($deliv_items as $item) : ?><li class="deliv__item"><?php echo esc_html($item['text']); ?></li><?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('revision')) : 
        $rev_title = raha_opt('process_rev_title');
        $rev_desc = raha_opt('process_rev_desc');
    ?>
    <!-- ۱۰ · Revision -->
    <section class="section" id="revision" aria-labelledby="revision-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۰ — قوانین بازخورد</p>
          <h2 class="sec-title" id="revision-title"><?php echo esc_html($rev_title); ?></h2>
        </header>
        <div class="rev">
          <div class="rev__num" aria-hidden="true">۲</div>
          <div class="rev__body">
            <h3>دو دور بازخورد رایگان در هر مرحله.</h3>
            <p><?php echo esc_html($rev_desc); ?></p>
            <div class="rev__tags"><span>۲ دور در وایرفریم</span><span>۲ دور در طراحی</span><span>۲ دور در توسعه</span></div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('payment')) : 
        $payment_items = raha_opt('process_payment_items', []);
    ?>
    <!-- ۱۱ · Payment -->
    <section class="section section--alt" id="payment" aria-labelledby="payment-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۱ — مدل پرداخت</p>
          <h2 class="sec-title" id="payment-title">پرداخت شفاف، مرحله‌به‌مرحله.</h2>
        </header>
        <div class="pay-grid">
          <?php foreach ($payment_items as $item) : ?>
            <div class="pay-card"><span class="pay-pct"><?php echo esc_html($item['percent']); ?></span><span class="pay-label"><?php echo esc_html($item['label']); ?></span><span class="pay-note"><?php echo esc_html($item['note']); ?></span></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('guarantee')) : 
        $guar_items = raha_opt('process_guar_items', []);
    ?>
    <!-- ۱۲ · Guarantees -->
    <section class="section" id="guarantee" aria-labelledby="guarantee-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۲ — ضمانت کیفیت</p>
          <h2 class="sec-title" id="guarantee-title">سه ضمانت کتبی.</h2>
        </header>
        <div class="guar">
          <?php foreach ($guar_items as $item) : ?>
            <div class="guar-card"><div class="guar-ico" aria-hidden="true"><?php echo esc_html($item['icon']); ?></div><div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['desc']); ?></p></div></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('scope')) : 
        $scope_items = raha_opt('process_scope_items', []);
    ?>
    <!-- ۱۳ · Out of scope -->
    <section class="section section--alt" id="scope" aria-labelledby="scope-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۳ — خارج از محدوده</p>
          <h2 class="sec-title" id="scope-title">چه چیزهایی شامل این فرآیند نیست.</h2>
        </header>
        <ul class="scope">
          <?php foreach ($scope_items as $item) : ?><li class="scope-item"><span><?php echo esc_html($item['text']); ?></span></li><?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('risks')) : 
        $risk_items = raha_opt('process_risk_items', []);
    ?>
    <!-- ۱۴ · Risks -->
    <section class="section" id="risks" aria-labelledby="risks-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۴ — مدیریت ریسک</p>
          <h2 class="sec-title" id="risks-title">اگر چیزی اشتباه پیش برود، چه می‌کنیم؟</h2>
        </header>
        <ul class="risks">
          <?php foreach ($risk_items as $item) : ?>
            <li class="risk"><div class="risk__head"><?php echo esc_html($item['title']); ?></div><div class="risk__solve"><?php echo esc_html($item['solve']); ?></div></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('support')) : 
        $support_items = raha_opt('process_support_items', []);
    ?>
    <!-- ۱۵ · Support -->
    <section class="section section--alt" id="support" aria-labelledby="support-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۵ — پشتیبانی پس از لانچ</p>
          <h2 class="sec-title" id="support-title">بعد از تحویل چه اتفاقی می‌افتد؟</h2>
        </header>
        <div class="support">
          <?php foreach ($support_items as $item) : ?>
            <div class="support-card"><span class="big"><?php echo esc_html($item['big']); ?></span><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['desc']); ?></p></div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

<?php if (raha_show_process_section('compare')) : 
    $compare_items = raha_opt('process_compare_items', []);
?>
<!-- ۱۶ · Comparison -->
<section class="section" id="compare" aria-labelledby="compare-title">
  <div class="wrap">
    <header class="sec-head">
      <p class="sec-label">۱۶ — مقایسه</p>
      <h2 class="sec-title" id="compare-title">این فرآیند چه تفاوتی با آژانس‌های معمول دارد.</h2>
    </header>

    <div class="compare" role="table" aria-label="مقایسه فرآیند همکاری با آژانس‌های معمول">
      <div class="compare__row compare__row--head" role="row">
        <div class="compare__cell" role="columnheader">ویژگی</div>
        <div class="compare__cell" role="columnheader">این فرآیند</div>
        <div class="compare__cell" role="columnheader">آژانس‌های معمول</div>
      </div>

      <?php foreach ($compare_items as $item) : ?>
        <div class="compare__row" role="row">
          <div class="compare__cell" role="rowheader">
            <?php echo esc_html($item['feature']); ?>
          </div>
          <div class="compare__cell compare__cell--good" role="cell" data-label="این فرآیند">
            <span class="compare__icon" aria-hidden="true">✓</span>
            <span class="compare__text"><?php echo esc_html($item['ours']); ?></span>
          </div>
          <div class="compare__cell compare__cell--bad" role="cell" data-label="آژانس‌های معمول">
            <span class="compare__icon" aria-hidden="true">✕</span>
            <span class="compare__text"><?php echo esc_html($item['theirs']); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

    <?php if (raha_show_process_section('faq')) : 
        $faq_items = raha_opt('process_faq_items', []);
    ?>
    <!-- ۱۸ · FAQ -->
    <section class="section" id="faq" aria-labelledby="faq-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۸ — سوالات متداول</p>
          <h2 class="sec-title" id="faq-title">چیزهایی که بیشتر پرسیده می‌شود.</h2>
        </header>
        <div class="faq-list">
          <?php foreach ($faq_items as $index => $item) : ?>
          <details class="faq" <?php echo $index === 0 ? 'open' : ''; ?>>
            <summary><span><?php echo esc_html($item['q']); ?></span><span class="faq__icon" aria-hidden="true">+</span></summary>
            <div class="faq__body"><p><?php echo esc_html($item['a']); ?></p></div>
          </details>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('steps')) : 
        $step_items = raha_opt('process_step_items', []);
    ?>
    <!-- ۱۹ · Next steps -->
    <section class="section section--alt" id="steps" aria-labelledby="steps-title">
      <div class="wrap">
        <header class="sec-head">
          <p class="sec-label">۱۹ — قدم بعدی</p>
          <h2 class="sec-title" id="steps-title">سه قدم تا شروع پروژه.</h2>
        </header>
        <ol class="steps" style="list-style:none;padding:0;margin:0;">
          <?php foreach ($step_items as $item) : ?>
            <li class="step-card"><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['desc']); ?></p><span class="time"><?php echo esc_html($item['time']); ?></span></li>
          <?php endforeach; ?>
        </ol>
      </div>
    </section>
    <?php endif; ?>

    <?php if (raha_show_process_section('cta')) : ?>
    <!-- ۲۰ · Final CTA -->
    <section class="section" id="cta" aria-labelledby="cta-title">
      <div class="wrap">
        <div class="cta">
          <h2 id="cta-title">آماده‌اید پروژه‌تان را با یک فرآیند شفاف و حرفه‌ای شروع کنیم؟</h2>
          <p>یک جلسه‌ی ۳۰ دقیقه‌ای رایگان رزرو کنید. بدون تعهد، بدون فروش.</p>
          <div class="cta__actions">
            <a href="mailto:hi@neobrutal.studio?subject=درخواست جلسه مشاوره" class="btn btn--primary">رزرو جلسه مشاوره رایگان</a>
            <a href="mailto:hi@neobrutal.studio?subject=سفارش پروژه" class="btn btn--ghost">ارسال فرم سفارش پروژه</a>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <!-- بخش‌های سفارشی (HTML/CSS/JS) -->
    <?php 
    $custom_sections = raha_opt('process_custom_sections', []);
    if (!empty($custom_sections)) {
        foreach ($custom_sections as $index => $section) {
            $uid = 'custom-section-' . $index;
            
            // تزریق CSS
            if (!empty($section['css'])) {
                echo '<style id="' . esc_attr($uid) . '-css">' . wp_strip_all_tags($section['css']) . '</style>';
            }
            
            // رندر HTML
            if (!empty($section['html'])) {
                echo '<div id="' . esc_attr($uid) . '" class="custom-section-wrapper">' . wp_kses_post($section['html']) . '</div>';
            }
            
            // تزریق JS (به صورت IIFE برای جلوگیری از تداخل)
            if (!empty($section['js'])) {
                echo '<script>(function() {' . $section['js'] . '})();</script>';
            }
        }
    }
    ?>

</div>

<?php get_footer(); ?>