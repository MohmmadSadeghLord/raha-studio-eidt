<?php
if (!defined('ABSPATH')) exit;

$news_title = raha_opt('footer_news_title', 'هر جمعه، یک ایده‌ی خام طراحی در اینباکس شما.');
$news_sub   = raha_opt('footer_news_sub', 'بدون اسپم، بدون تبلیغ. فقط چیزهایی که واقعاً کار می‌کنند.');
$brand_desc = raha_opt('footer_brand_desc', 'استودیوی طراحی و توسعه با خط‌های ضخیم، تصمیم‌های شجاعانه و کدِ تمیز — برای برندهایی که نمی‌خواهند دیده نشوند.');
$socials    = raha_opt('footer_socials', []);
$columns    = raha_opt('footer_columns', []);

$email   = raha_opt('footer_email', 'hi@neobrutal.studio');
$phone   = raha_opt('footer_phone', '+98 21 0000 0000');
$address = raha_opt('footer_address', 'تهران، ولیعصر، پلاک ۱۲');
$status  = raha_opt('footer_status', 'آماده‌ی پروژه');

$copyright = raha_opt('footer_copyright', '© ۲۰۲۵ نئوبروتال استودیو');
$legal_raw = raha_opt('footer_legal', "حریم خصوصی | #\nقوانین | #\nکوکی‌ها | #");
?>
</main>

<footer class="raha-footer-wrapper">
  <div class="nb" role="contentinfo">

    <!-- ۱. خبرنامه -->
    <section class="nb-news" aria-labelledby="nb-news-title">
      <div class="nb__wrap">
        <div class="nb-news__inner">
          <div>
            <p class="nb-label">خبرنامه</p>
            <h2 class="nb-news__title" id="nb-news-title"><?php echo esc_html($news_title); ?></h2>
            <p class="nb-news__sub"><?php echo esc_html($news_sub); ?></p>
          </div>
          <form class="nb-form" id="rahaNewsletterForm" novalidate>
            <label class="screen-reader-text" for="nb-email">ایمیل شما</label>
            <input id="nb-email" type="email" name="email" placeholder="you@studio.com" autocomplete="email" required>
            <button type="submit">عضویت</button>
            <p class="nb-form__msg" role="status" aria-live="polite"></p>
          </form>
        </div>
      </div>
    </section>

    <!-- ۲. شبکه اصلی -->
    <div class="nb-main">
      <div class="nb__wrap">
        <div class="nb-main__grid">
          <div class="nb-brand">
            <a class="nb-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?> — خانه">
              <span class="nb-logo__mark" aria-hidden="true"><?php echo esc_html(mb_substr(get_bloginfo('name'), 0, 2)); ?></span>
              <span class="nb-logo__text"><?php bloginfo('name'); ?></span>
            </a>
            <p class="nb-brand__desc"><?php echo esc_html($brand_desc); ?></p>
            
            <?php if (!empty($socials)) : ?>
            <div class="nb-socials">
              <?php foreach ($socials as $s) : if(empty($s['url'])) continue; ?>
                <a class="nb-social" href="<?php echo esc_url($s['url']); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr($s['label'] ?? 'شبکه اجتماعی'); ?>">
                  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <?php echo wp_kses($s['icon'] ?? '', [
                        'path' => ['d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                        'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                        'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true]
                    ]); ?>
                  </svg>
                </a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <?php 
          $col_num = 1;
          foreach ($columns as $col) : 
              if (empty($col['title'])) continue;
              $links = explode("\n", $col['links'] ?? '');
          ?>
            <nav class="nb-col" aria-labelledby="c<?php echo $col_num; ?>">
              <h3 id="c<?php echo $col_num; ?>"><span>0<?php echo $col_num; ?></span><?php echo esc_html($col['title']); ?></h3>
              <ul>
                <?php foreach ($links as $line) : 
                    $line = trim($line);
                    if (empty($line)) continue;
                    $parts = explode('|', $line);
                    $label = trim($parts[0]);
                    $url = isset($parts[1]) ? trim($parts[1]) : '#';
                ?>
                  <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </nav>
          <?php $col_num++; endforeach; ?>

        </div>
      </div>
    </div>

    <!-- ۳. نوار تماس -->
    <div class="nb-contact">
      <div class="nb__wrap">
        <div class="nb-contact__inner">
          <a class="nb-contact__item" href="mailto:<?php echo esc_attr($email); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <span dir="ltr"><?php echo esc_html($email); ?></span>
          </a>
          <a class="nb-contact__item" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7A2 2 0 0 1 22 16.9z"/></svg>
            <span dir="ltr"><?php echo esc_html($phone); ?></span>
          </a>
          <span class="nb-contact__item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span><?php echo esc_html($address); ?></span>
          </span>
          <span class="nb-contact__item">
            <span class="nb-status-dot" aria-hidden="true"></span>
            <span><?php echo esc_html($status); ?></span>
          </span>
        </div>
      </div>
    </div>

    <!-- ۴. نوار پایانی -->
    <div class="nb-bottom">
      <div class="nb__wrap">
        <div class="nb-bottom__inner">
          <p class="nb-copyright"><?php echo esc_html($copyright); ?></p>
          
          <?php if ($legal_raw) : 
              $legal_items = explode("\n", $legal_raw);
          ?>
          <ul class="nb-legal">
            <?php foreach ($legal_items as $item) : 
                $item = trim($item);
                if (empty($item)) continue;
                $parts = explode('|', $item);
                $label = trim($parts[0]);
                $url = isset($parts[1]) ? trim($parts[1]) : '#';
            ?>
              <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>

          <button class="nb-top" type="button" id="rahaBackToTop">
            بالا
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>