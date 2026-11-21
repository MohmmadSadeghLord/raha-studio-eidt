<?php
if (!defined('ABSPATH')) exit;

$title = raha_opt('cta_title', 'فقط یک تماس تا راه‌اندازی سایت‌تان فاصله است.');
$desc  = raha_opt('cta_desc', 'شماره‌تان را بگذارید؛ در کمتر از ۲۴ ساعت خودم تماس می‌گیرم.');

$trust_1 = raha_opt('cta_trust_1', 'راهنمایی کامل');
$trust_2 = raha_opt('cta_trust_2', 'ارتباط در کمتر از ۲۴ ساعت');
$trust_3 = raha_opt('cta_trust_3', 'تعهد کامل');

$form_label = raha_opt('cta_form_label', 'شماره تماس یا ایمیل');
$form_ph    = raha_opt('cta_form_placeholder', '۰۹۱۲۳۴۵۶۷۸۹ یا you@email.com');
$btn_text   = raha_opt('cta_btn_text', 'تماس رایگان ۳۰ دقیقه‌ای');

$success_title = raha_opt('cta_success_title', 'ثبت شد!');
$success_desc  = raha_opt('cta_success_desc', 'در کمتر از ۲۴ ساعت با شما تماس می‌گیرم.');

$channels = raha_opt('cta_channels', []);

if (!$title && !$desc) return;
?>

<section class="final-cta raha-cta-section" id="contact" aria-labelledby="cta-title">
  <div class="final-cta__inner">

    <div class="cta-card">

      <div class="cta-card__grid">
        <div class="cta-content">
          <h2 id="cta-title" class="cta-title"><?php echo esc_html($title); ?></h2>
          <?php if ($desc) : ?><p class="cta-desc"><?php echo esc_html($desc); ?></p><?php endif; ?>
          
          <div class="trust-row">
            <?php if ($trust_1) : ?><span><?php echo esc_html($trust_1); ?></span><?php endif; ?>
            <?php if ($trust_2) : ?><span><?php echo esc_html($trust_2); ?></span><?php endif; ?>
            <?php if ($trust_3) : ?><span><?php echo esc_html($trust_3); ?></span><?php endif; ?>
          </div>
        </div>

        <div class="cta-form-col">
          <form class="cta-form" id="rahaCtaForm" novalidate>
            <div class="form-group">
              <label for="ctaContact"><?php echo esc_html($form_label); ?></label>
              <input type="text" id="ctaContact" name="contact" class="form-control" placeholder="<?php echo esc_attr($form_ph); ?>" required>
            </div>
            <button type="submit" class="btn-primary">
              <?php echo esc_html($btn_text); ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            </button>
          </form>

          <div class="form-success" id="rahaCtaSuccess" aria-live="polite">
            <span class="form-success__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <h3 class="form-success__title"><?php echo esc_html($success_title); ?></h3>
            <p class="form-success__desc"><?php echo esc_html($success_desc); ?></p>
          </div>
        </div>
      </div>

      <?php if (!empty($channels)) : ?>
      <div class="cta-channels">
        <?php foreach ($channels as $ch) : 
            if (empty($ch['name'])) continue;
            $bg    = $ch['icon_bg'] ?? '#f1f5f9';
            $color = $ch['icon_color'] ?? '#14110f';
            $icon  = trim($ch['icon'] ?? '');
            $url   = $ch['url'] ?? '#';

            // اگر لینک ایمیل بود، mailto اضافه کن
            if (strpos($url, '@') !== false && strpos($url, 'mailto:') === false) {
                $url = 'mailto:' . $url;
            }

            // تمیزکاری SVG: اگر تگ <svg> داشت، آن را برمی‌داریم تا دوبار رندر نشود
            if (stripos($icon, '<svg') !== false) {
                // حذف تگ باز و بسته <svg>
                $icon_inner = preg_replace('/^\s*<svg[^>]*>|<\/svg>\s*$/i', '', $icon);
            } else {
                $icon_inner = $icon;
            }
        ?>
          <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="channel-item" style="--ch-bg: <?php echo esc_attr($bg); ?>; --ch-color: <?php echo esc_attr($color); ?>;">
            <span class="channel-item__icon" style="background: <?php echo esc_attr($bg); ?>; color: <?php echo esc_attr($color); ?>;">
              <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <?php echo wp_kses($icon_inner, [
                    'path' => ['d' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true],
                    'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true]
                ]); ?>
              </svg>
            </span>
            <span class="channel-item__name"><?php echo esc_html($ch['name']); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>