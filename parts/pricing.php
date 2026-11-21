<?php
if (!defined('ABSPATH')) exit;

// دریافت پکیج‌های اصلی از پیشخوان
$plans = get_posts([
    'post_type'      => 'raha_pricing',
    'posts_per_page' => -1, // هر تعداد که خواستید
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
]);

// اگر هیچ پکیجی نبود، چیزی نمایش نده
if (empty($plans)) return;

// دریافت داده‌های تکمیلی
$title       = raha_opt('pricing_title', 'پکیج ها');
$monthly     = raha_opt('pricing_monthly_services', []);
$custom_url  = raha_opt('pricing_custom_btn_url', '#contact');
?>

<section class="pkgs raha-pricing-section" aria-labelledby="pkgs-title">
  <div class="pkgs__inner">

    <header class="pkgs__head">
      <h2 class="pkgs__title" id="pkgs-title"><?php echo esc_html($title); ?></h2>
      <a href="<?php echo esc_url(home_url('/packages')); ?>" class="pkgs__see-all">
        مشاهده همه پکیج ها
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
      </a>
    </header>

    <div class="pkgs__scroller">
      <div class="pkgs__grid">
        <?php foreach ($plans as $plan) : 
            $desc     = get_post_meta($plan->ID, 'desc', true);
            $value    = get_post_meta($plan->ID, 'price_value', true);
            $unit     = get_post_meta($plan->ID, 'price_unit', true);
            $popular  = get_post_meta($plan->ID, 'popular', true);
            $btn_t    = get_post_meta($plan->ID, 'btn_text', true) ?: 'درخواست مشاوره';
            $btn_u    = get_post_meta($plan->ID, 'btn_link', true) ?: '#contact';
            $features = get_post_meta($plan->ID, 'features', true) ?: [];
            $card_class = $popular ? ' pkg-card--popular' : '';
        ?>
          <article class="pkg-card<?php echo $card_class; ?>">
            <?php if ($popular) : ?><span class="pkg-badge">پیشنهاد ویژه</span><?php endif; ?>
            
            <h3 class="pkg-title"><?php echo esc_html($plan->post_title); ?></h3>
            <?php if ($desc) : ?><p class="pkg-desc"><?php echo esc_html($desc); ?></p><?php endif; ?>

            <div class="pkg-price">
              <span class="pkg-price__label">شروع از</span>
              <span class="pkg-price__value">
                  <?php echo esc_html($value); ?> 
                  <?php if ($unit) : ?><small><?php echo esc_html($unit); ?></small><?php endif; ?>
              </span>
            </div>

            <?php if (!empty($features)) : ?>
            <ul class="pkg-features">
              <?php foreach ($features as $f) : 
                  if (empty($f['label'])) continue;
                  $included = !empty($f['included']);
                  $li_class = $included ? '' : 'disabled';
              ?>
                <li class="<?php echo $li_class; ?>">
                  <?php if ($included) : ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  <?php else : ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  <?php endif; ?>
                  <?php echo esc_html($f['label']); ?>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <a href="<?php echo esc_url($btn_u); ?>" class="pkg-btn"><?php echo esc_html($btn_t); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Monthly Services -->
    <?php if (!empty($monthly)) : ?>
    <div class="pkgs__monthly" role="region" aria-label="خدمات ماهانه">
      <header class="pkgs__monthly-head">
        <h3 class="pkgs__monthly-title">خدمات ماهانه</h3>
        <span class="pkgs__monthly-subtitle">اشتراکی، بدون قرارداد بلندمدت</span>
      </header>

      <?php foreach ($monthly as $m) : 
          if (empty($m['title'])) continue;
          $m_class = !empty($m['highlight']) ? ' pkg-monthly--sun' : '';
          $icon_svg = $m['icon'] ?? '<circle cx="12" cy="12" r="10"/>';
      ?>
        <a href="#contact" class="pkg-monthly<?php echo $m_class; ?>">
          <div class="pkg-monthly__start">
            <span class="pkg-monthly__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <?php echo wp_kses($icon_svg, [
                    'path' => ['d' => true, 'fill' => true],
                    'polyline' => ['points' => true],
                    'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true],
                    'circle' => ['cx' => true, 'cy' => true, 'r' => true],
                    'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true]
                ]); ?>
              </svg>
            </span>
            <div class="pkg-monthly__info">
              <div class="pkg-monthly__title-row">
                <h4 class="pkg-monthly__name"><?php echo esc_html($m['title']); ?></h4>
                <span class="pkg-monthly__tag">ماهانه</span>
              </div>
              <p class="pkg-monthly__desc"><?php echo esc_html($m['desc'] ?? ''); ?></p>
            </div>
          </div>
          <div class="pkg-monthly__end">
            <div class="pkg-monthly__price">
              <small>ماهانه از</small>
              <strong><?php echo wp_kses_post($m['price'] ?? ''); ?></strong>
            </div>
            <span class="pkg-monthly__arrow" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            </span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Custom Quote -->
    <?php if (raha_opt('pricing_custom_title')) : ?>
    <div class="pkgs__quote">
      <div class="pkgs__quote__content">
        <h3 class="pkgs__quote__title"><?php echo esc_html(raha_opt('pricing_custom_title')); ?></h3>
        <p class="pkgs__quote__desc"><?php echo esc_html(raha_opt('pricing_custom_desc')); ?></p>
      </div>
      <a href="<?php echo esc_url($custom_url); ?>" class="pkgs__quote__btn">
        <?php echo esc_html(raha_opt('pricing_custom_btn_text', 'گفتگو درباره پروژه')); ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
      </a>
    </div>
    <?php endif; ?>

  </div>
</section>