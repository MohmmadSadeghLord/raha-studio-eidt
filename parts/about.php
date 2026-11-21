<?php
if (!defined('ABSPATH')) exit;

$eyebrow  = raha_opt('about_eyebrow', 'درباره من');
$name     = raha_opt('about_name', 'محمد صادق محمودی لرد');
$portrait = raha_opt('about_portrait');
$slogan   = raha_opt('about_slogan', 'تعهد شعار من است.');

$c1t = raha_opt('about_cta_text', 'تماس با من');
$c1u = raha_opt('about_cta_url', '#contact');

// دریافت ۳ ستون
$columns = [
    ['title' => raha_opt('about_col_1_title', 'خدمات'), 'items' => raha_opt('about_col_1_items', [])],
    ['title' => raha_opt('about_col_2_title', 'ابزارها'), 'items' => raha_opt('about_col_2_items', [])],
    ['title' => raha_opt('about_col_3_title', 'سبک کار'), 'items' => raha_opt('about_col_3_items', [])],
];

$has_content = $name || $slogan || $portrait;
foreach ($columns as $col) {
    if ($col['title'] || !empty($col['items'])) { $has_content = true; break; }
}

if (!$has_content) return;
?>

<section class="about" id="about" aria-labelledby="about-name">
  <div class="about__inner">

    <header class="about__head">
      <p class="about__eyebrow"><?php echo esc_html($eyebrow); ?></p>
      <h2 id="about-name" class="about__name"><?php echo esc_html($name); ?></h2>
      <div class="about__rule" aria-hidden="true"></div>
    </header>

    <div class="about__grid">

      <?php if ($portrait) : ?>
      <figure class="about__media">
        <?php echo wp_get_attachment_image($portrait, 'large', false, ['alt' => $name, 'loading' => 'lazy', 'decoding' => 'async']); ?>
      </figure>
      <?php endif; ?>

      <div class="about__body">

        <?php if ($slogan) : ?>
          <p class="about__slogan"><?php echo esc_html($slogan); ?></p>
        <?php endif; ?>

        <div class="about__cells">
          <?php foreach ($columns as $i => $col) : 
              if (!$col['title'] && empty($col['items'])) continue;
              $num_fa = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
              $num_fa = strtr($num_fa, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
          ?>
            <div class="about__cell">
              <span class="about__cell-num" aria-hidden="true"><?php echo esc_html($num_fa); ?></span>
              <?php if ($col['title']) : ?><h3 class="about__cell-label"><?php echo esc_html($col['title']); ?></h3><?php endif; ?>
              
              <?php if (!empty($col['items'])) : ?>
              <ul class="about__cell-list">
                <?php foreach ($col['items'] as $item) : if (empty($item['text'])) continue; ?>
                  <li><?php echo esc_html($item['text']); ?></li>
                <?php endforeach; ?>
              </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($c1t && $c1u) : ?>
        <div class="about__actions">
          <a class="about__cta" href="<?php echo esc_url($c1u); ?>">
            <span class="about__cta-text"><?php echo esc_html($c1t); ?></span>
            <span class="about__cta-arrow" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5"/>
                <path d="m12 19-7-7 7-7"/>
              </svg>
            </span>
          </a>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</section>