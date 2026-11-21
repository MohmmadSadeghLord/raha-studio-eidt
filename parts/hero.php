<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   خواندن محتوا از تنظیمات
   ============================================================ */
$eyebrow  = raha_opt('hero_eyebrow', 'لرد وب · طراح سایت');
$title    = raha_opt('hero_title', 'طراحی با سلیقه شما');
$lead     = raha_opt('hero_lead', 'هر چیزی که در ذهن شما باشد، متعهد به پیاده‌سازی آن خواهم بود — طراحی‌های خاص، متناسب با شخصیت و نیاز کسب‌وکار شما.');

$c1t = raha_opt('hero_cta_1_text', 'دیدن نمونه‌کارها');
$c1u = raha_opt('hero_cta_1_url',  '#portfolio');
$c2t = raha_opt('hero_cta_2_text', 'رزرو مشاوره');
$c2u = raha_opt('hero_cta_2_url',  '#contact');

/* برچسب‌ها + fallback دیتای نمونه */
$tags = raha_opt('hero_tags', []);
if (empty($tags) || !is_array($tags)) {
    $tags = [
        ['text' => 'لندینگ پیج'],
        ['text' => 'سایت شرکتی'],
        ['text' => 'فروشگاه آنلاین'],
        ['text' => 'پورتفولیو'],
        ['text' => 'ریدیزاین'],
    ];
}

$mockup_url    = raha_opt('hero_mockup_url', 'wanderlust.ir');
$term_filename = raha_opt('hero_terminal_filename', 'index.html');
$term_code     = raha_opt('hero_terminal_code', '');

/* ============================================================
   تنظیمات نمایش (چک‌باکس‌ها)
   ============================================================ */
$show_mockup   = raha_opt('hero_show_mockup', '1');
$custom_mockup = raha_opt('hero_custom_mockup');
$show_terminal = raha_opt('hero_show_terminal', '1');
$show_floating = raha_opt('hero_show_floating', '1');
$show_sparkles = raha_opt('hero_show_sparkles', '1');

/* ============================================================
   تنظیمات استایل (از زیرتب استایل)
   ============================================================ */
$bg_color     = raha_opt('hero_bg_color', '#e0f2fe');
$title_color  = raha_opt('hero_title_color', '#14110f');
$title_size   = raha_opt('hero_title_size', 'clamp(2.5rem, 6vw, 4.5rem)');
$lead_size    = raha_opt('hero_lead_size', '1.0625rem');
$typing_speed = raha_opt('hero_typing_speed', 'normal');
$custom_css   = raha_opt('hero_custom_css', '');

if (!$title && !$lead) return;

/* نقشه سرعت تایپ -> میلی‌ثانیه */
$speed_map    = ['slow' => 80, 'normal' => 40, 'fast' => 15];
$typing_delay = $speed_map[$typing_speed] ?? 40;

$section_style = 'background:' . esc_attr($bg_color) . ';';
?>

<?php if (!empty($custom_css)) : ?>
<style id="raha-hero-custom-css"><?php echo $custom_css; ?></style>
<?php endif; ?>

<section class="section raha-hero-section" id="hero" style="<?php echo esc_attr($section_style); ?>">
  <div class="container hero__grid">

    <!-- ستون ۱ (بالا): محتوا -->
    <div class="hero__content">
      <?php if ($eyebrow) : ?>
      <div style="position:relative; display:inline-block">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <?php if ($show_sparkles == '1') : ?>
        <span class="sparkle sparkle--eyebrow" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M 12 2 L 13.6 10.4 L 22 12 L 13.6 13.6 L 12 22 L 10.4 13.6 L 2 12 L 10.4 10.4 Z"/></svg>
        </span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <h1 class="h-display" style="color:<?php echo esc_attr($title_color); ?>; font-size:<?php echo esc_attr($title_size); ?>;"><?php echo esc_html($title); ?></h1>
      <p class="lead" style="font-size:<?php echo esc_attr($lead_size); ?>;"><?php echo esc_html($lead); ?></p>

      <?php if (($c1t && $c1u) || ($c2t && $c2u)) : ?>
      <div class="hero__cta-wrap">
        <div class="hero__cta">
          <?php if ($c1t && $c1u) : ?>
            <a href="<?php echo esc_url($c1u); ?>" class="btn btn--primary btn--lg"><?php echo esc_html($c1t); ?></a>
          <?php endif; ?>
          <?php if ($c2t && $c2u) : ?>
            <a href="<?php echo esc_url($c2u); ?>" class="btn btn--ghost btn--lg"><?php echo esc_html($c2t); ?></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- ستون ۱ (پایین): برچسب‌ها -->
    <div class="hero__tags-block">
      <span class="hero__tags-label">کارهایی که انجام می‌دهم</span>
      <div class="hero__tags">
        <?php foreach ($tags as $tag) : if (empty($tag['text'])) continue; ?>
          <span class="badge"><?php echo esc_html($tag['text']); ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ستون ۲: ویژوال -->
    <div class="hero__visual">

      <div class="code-tag code-tag--cta" aria-hidden="true"><span class="code-tag__inner">&lt;/&gt;</span></div>
      <div class="code-tag code-tag--mockup" aria-hidden="true"><span class="code-tag__inner">{ }</span></div>

      <?php if ($show_mockup == '1') : ?>
        <?php if ($custom_mockup) : ?>
          <!-- تصویر اختصاصی آپلود شده -->
          <div class="mockup" style="border-radius: var(--r); box-shadow: var(--sh-3); overflow: hidden; border: var(--bw) solid var(--ink);">
            <?php echo wp_get_attachment_image($custom_mockup, 'full', false, ['style' => 'width:100%; height:auto; display:block;']); ?>
          </div>
        <?php else : ?>
          <!-- ماکاپ CSS -->
          <div class="mockup" role="img" aria-label="پیش‌نمایش یک صفحه سایت">
            <div class="mockup__bar">
              <div class="mockup__badges"><span class="mockup__chip mockup__chip--fill"></span><span class="mockup__chip"></span><span class="mockup__chip"></span></div>
              <span class="mockup__url"><?php echo esc_html($mockup_url); ?></span>
            </div>
            <div class="mockup__body">
              <div class="mockup__nav"><div class="mockup__logo"></div><div class="mockup__links"><span></span><span></span><span></span></div><div class="mockup__nav-cta"></div></div>
              <div class="mockup__hero"><div class="mockup__headline"></div><div class="mockup__headline mockup__headline--2"></div><div class="mockup__line"></div><div class="mockup__line mockup__line--short"></div><div class="mockup__actions"><div class="mockup__btn mockup__btn--fill"></div><div class="mockup__btn"></div></div></div>
              <div class="mockup__cards"><div class="mockup__card"></div><div class="mockup__card"></div><div class="mockup__card"></div></div>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($show_floating == '1') : ?>
      <div class="float-bob float-bob--1"><div class="float-btn" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></div></div>
      <div class="float-bob float-bob--2"><div class="float-btn float-btn--dark" aria-hidden="true"><svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></div></div>
      <div class="float-bob float-bob--3"><div class="float-btn" aria-hidden="true"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div></div>
      <?php endif; ?>

      <?php if ($show_terminal == '1') : ?>
      <div class="terminal" aria-hidden="true" data-typing-delay="<?php echo esc_attr($typing_delay); ?>">
        <div class="terminal__bar"><span class="terminal__dot"></span><span class="terminal__dot"></span><span class="terminal__dot"></span><span class="terminal__label"><?php echo esc_html($term_filename); ?></span></div>
        <div class="terminal__body" id="terminal-body"><div class="terminal__code" id="terminal-code"></div></div>
      </div>
      <?php endif; ?>

      <?php if ($show_sparkles == '1') : ?>
      <span class="sparkle sparkle--visual" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M 12 2 L 13.6 10.4 L 22 12 L 13.6 13.6 L 12 22 L 10.4 13.6 L 2 12 L 10.4 10.4 Z"/></svg></span>
      <?php endif; ?>

    </div>

  </div>
</section>

<?php if ($show_terminal == '1') : ?>
<script type="text/plain" id="hero-terminal-source"><?php echo esc_html($term_code ?: "$ cat index.html\n<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n  <meta charset=\"UTF-8\" />\n  <title>لرد وب | طراحی با سلیقه شما</title>\n</head>\n<body>\n  <nav class=\"nav\">\n    <a href=\"/\">لرد وب</a>\n  </nav>\n  <section class=\"hero\">\n    <h1>طراحی با سلیقه شما</h1>\n    <p>هر چیزی که در ذهن شما باشد، پیاده‌سازی می‌کنم.</p>\n  </section>\n</body>\n</html>"); ?></script>
<?php endif; ?>