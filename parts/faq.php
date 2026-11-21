<?php
if (!defined('ABSPATH')) exit;

$eyebrow = raha_opt('faq_eyebrow', 'سوالات متداول');
$title   = raha_opt('faq_title', 'پرسش‌هایی که زیاد می‌شنوم');
$lead    = raha_opt('faq_lead', 'اگر پاسخ سوالت را اینجا پیدا نکردی، از دکمه‌ی «تماس با من» استفاده کن.');
$faqs    = raha_opt('faq_items', []);

if (empty($faqs)) return;
?>

<section class="faq-section" aria-labelledby="faq-title">
  <div class="faq-section__inner">

    <header class="faq-section__head">
      <?php if ($eyebrow) : ?><p class="faq-section__eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
      <?php if ($title) : ?><h2 id="faq-title" class="faq-section__title"><?php echo esc_html($title); ?></h2><?php endif; ?>
      <?php if ($lead) : ?><p class="faq-section__lead"><?php echo esc_html($lead); ?></p><?php endif; ?>
    </header>

    <div class="faq">
      <?php foreach ($faqs as $index => $item) : 
          if (empty($item['question'])) continue;
          $is_open = ($index === 0) ? ' open' : '';
      ?>
        <details class="faq__item"<?php echo $is_open; ?>>
          <summary class="faq__q">
            <span class="faq__text"><?php echo esc_html($item['question']); ?></span>
            <span class="faq__icon" aria-hidden="true">
              <!-- آیکون پلاس (هاردکد شده در قالب) -->
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14"/><path d="M5 12h14"/>
              </svg>
            </span>
          </summary>
          <div class="faq__a"><?php echo wp_kses_post(wpautop($item['answer'] ?? '')); ?></div>
        </details>
      <?php endforeach; ?>
    </div>

  </div>
</section>