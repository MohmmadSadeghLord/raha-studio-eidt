<?php
if (!defined('ABSPATH')) exit;
get_header();

while (have_posts()) : the_post();
    $id = get_the_ID();
    $tag       = get_post_meta($id, 'tag_line', true);
    $short     = get_post_meta($id, 'short_desc', true);

    $delivery  = get_post_meta($id, 'delivery_time', true);
    $mobile    = get_post_meta($id, 'mobile_status', true);
    $speed     = get_post_meta($id, 'speed_score', true);
    $tech      = get_post_meta($id, 'tech', true);

    $features  = get_post_meta($id, 'features', true) ?: [];
    $specs     = get_post_meta($id, 'specs', true) ?: [];
    $gallery   = get_post_meta($id, 'gallery', true) ?: [];
    $use_cases = get_post_meta($id, 'use_cases', true) ?: [];
    $faqs      = get_post_meta($id, 'faq', true) ?: [];

    $price_prefix = get_post_meta($id, 'price_prefix', true);
    $price        = get_post_meta($id, 'price', true);
    $purchase_features = get_post_meta($id, 'purchase_features', true) ?: [];
    $demo_url     = get_post_meta($id, 'demo_url', true);

    // ---- دمو: محاسبه خودکار مسیر از فایل ZIP آپلودشده ----
    $demo_entry  = get_post_meta($id, 'demo_entry_file', true);
    $demo_folder = get_post_meta($id, 'demo_folder', true);
    $demo_base   = '';
    $demo_src    = '';

    if ($demo_entry && $demo_folder) {
        $uploads   = wp_upload_dir();
        $demo_base = trailingslashit($uploads['baseurl']) . 'raha-templates/tpl-' . $id;
        $demo_src  = $demo_base . '/' . ltrim($demo_entry, '/');
    }

    // اگر demo_url دستی تنظیم نشده بود، از فایل ZIP پر می‌شود
    if (empty($demo_url) && $demo_src) {
        $demo_url = $demo_src;
    }

    $cta_title    = get_post_meta($id, 'cta_title', true);
    $cta_desc     = get_post_meta($id, 'cta_desc', true);
    $cta_btn_text = get_post_meta($id, 'cta_btn_text', true);
    $cta_btn_url  = get_post_meta($id, 'cta_btn_url', true);
?>
<main class="raha-template-page">
  <div class="container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="مسیر صفحه">
      <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
      <span class="sep">›</span>
      <a href="<?php echo esc_url(get_post_type_archive_link('raha_template')); ?>">قالب‌ها</a>
      <span class="sep">›</span>
      <span class="current"><?php the_title(); ?></span>
    </nav>

    <div class="template-layout">

      <!-- ستون اصلی -->
      <div class="main-column">
        <section class="hero">
          <?php if ($tag) : ?><span class="hero-badge">دسته‌بندی: <?php echo esc_html($tag); ?></span><?php endif; ?>
          <h1 class="hero-title"><?php the_title(); ?></h1>
          <?php if ($short) : ?><p class="hero-lead"><?php echo esc_html($short); ?></p><?php endif; ?>

          <div class="template-cover">
            <span class="code-tag code-tag--tr">&lt;/&gt;</span>
            <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('full', ['style' => 'width:100%; height:100%; object-fit:cover; position:relative; z-index:1;']); ?>
            <?php else : ?>
                <div class="template-cover-icon">🛒</div>
            <?php endif; ?>
            <span class="code-tag code-tag--bl">{ template }</span>
          </div>
        </section>

        <!-- پیش‌نمایش زنده دمو -->
        <?php if ($demo_src) : ?>
        <section class="content-section" id="live-demo">
          <h2 class="section-title">پیش‌نمایش زنده</h2>
          <div class="section-body" style="padding:0;overflow:hidden;">
            <div style="position:relative;padding-bottom:60%;height:0;overflow:hidden;background:#f1f5f9;">
              <iframe
                src="<?php echo esc_url($demo_src); ?>"
                title="پیش‌نمایش زنده <?php echo esc_attr(get_the_title()); ?>"
                loading="lazy"
                style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                sandbox="allow-scripts allow-same-origin allow-popups allow-forms"
              ></iframe>
            </div>
            <div style="padding:12px;text-align:center;background:#fff;border-top:2px dashed rgba(20,17,15,.15);">
              <a href="<?php echo esc_url($demo_src); ?>" target="_blank" rel="noopener"
                 class="btn btn-secondary" style="display:inline-flex;width:auto;min-width:200px;">
                🔗 باز کردن دمو در تب جدید
              </a>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <!-- Info Strip -->
        <div class="info-strip">
          <?php if ($delivery) : ?>
          <div class="info-strip-item">
            <span class="info-strip-icon">⏱️</span>
            <div class="info-strip-body">
              <span class="info-strip-label">زمان پیاده‌سازی</span>
              <span class="info-strip-value"><?php echo esc_html($delivery); ?></span>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($mobile) : ?>
          <div class="info-strip-item">
            <span class="info-strip-icon">📱</span>
            <div class="info-strip-body">
              <span class="info-strip-label">وضعیت موبایل</span>
              <span class="info-strip-value"><?php echo esc_html($mobile); ?></span>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($speed) : ?>
          <div class="info-strip-item">
            <span class="info-strip-icon">⚡</span>
            <div class="info-strip-body">
              <span class="info-strip-label">امتیاز سرعت</span>
              <span class="info-strip-value"><?php echo esc_html($speed); ?></span>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($tech) : ?>
          <div class="info-strip-item">
            <span class="info-strip-icon">🛠️</span>
            <div class="info-strip-body">
              <span class="info-strip-label">تکنولوژی</span>
              <span class="info-strip-value"><?php echo esc_html($tech); ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- محتوای اصلی -->
        <?php if (get_the_content()) : ?>
        <section class="content-section" id="overview">
          <h2 class="section-title">درباره این قالب</h2>
          <div class="section-body"><?php the_content(); ?></div>
        </section>
        <?php endif; ?>

        <!-- Features -->
        <?php if (!empty($features)) : ?>
        <section class="content-section" id="features">
          <h2 class="section-title">امکانات کلیدی</h2>
          <div class="section-body">
            <div class="features-list">
              <?php foreach ($features as $f) : if(empty($f['label'])) continue; ?>
                <div class="feature-item"><?php echo esc_html($f['label']); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <!-- Specs -->
        <?php if (!empty($specs)) : ?>
        <section class="content-section" id="specs">
          <h2 class="section-title">مشخصات فنی</h2>
          <div class="section-body">
            <ul class="spec-list">
              <?php foreach ($specs as $s) : if(empty($s['label'])) continue; ?>
                <li class="spec-item">
                  <span class="spec-label"><?php echo esc_html($s['label']); ?></span>
                  <span class="spec-value"><?php echo esc_html($s['value'] ?? ''); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </section>
        <?php endif; ?>

        <!-- Gallery -->
        <?php if (!empty($gallery)) : ?>
        <section class="content-section" id="gallery">
          <h2 class="section-title">پیش‌نمایش صفحات</h2>
          <div class="section-body">
            <div class="gallery">
              <?php foreach ($gallery as $img) : if(empty($img['image'])) continue; ?>
                <div class="gallery-item">
                  <?php echo wp_get_attachment_image($img['image'], 'large'); ?>
                  <?php if (!empty($img['label'])) : ?><span class="gallery-label"><?php echo esc_html($img['label']); ?></span><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <!-- Use Cases -->
        <?php if (!empty($use_cases)) : ?>
        <section class="content-section" id="usecases">
          <h2 class="section-title">مناسب برای چه کسب‌وکارهایی؟</h2>
          <div class="section-body">
            <div class="use-cases">
              <?php foreach ($use_cases as $u) : if(empty($u['label'])) continue; ?>
                <div class="use-case"><?php echo esc_html($u['label']); ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <!-- FAQ -->
        <?php if (!empty($faqs)) : ?>
        <section class="content-section" id="faq">
          <h2 class="section-title">سوالات متداول درباره این قالب</h2>
          <div class="faq-list">
            <?php foreach ($faqs as $faq) : if(empty($faq['question'])) continue; ?>
              <details class="faq-item">
                <summary class="faq-summary"><?php echo esc_html($faq['question']); ?></summary>
                <div class="faq-content"><?php echo wp_kses_post(wpautop($faq['answer'] ?? '')); ?></div>
              </details>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

      </div>

      <!-- سایدبار (Sticky) -->
      <aside class="sidebar">
        <div class="purchase-card">
          <div class="purchase-head">
            <h3>درخواست راه‌اندازی</h3>
          </div>
          <div class="purchase-body">
            <?php if ($price) : ?>
            <div class="price-row">
              <span class="price-label"><?php echo esc_html($price_prefix ?: 'شروع قیمت از'); ?></span>
              <span class="price-value"><?php echo esc_html($price); ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($purchase_features)) : ?>
            <ul class="purchase-features">
              <?php foreach ($purchase_features as $pf) : if(empty($pf['label'])) continue; ?>
                <li><?php echo esc_html($pf['label']); ?></li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <a href="<?php echo esc_url(home_url('/#contact')); ?>" class="btn btn-primary">درخواست این قالب</a>
            <?php if ($demo_url) : ?>
              <a href="<?php echo esc_url($demo_url); ?>" target="_blank" rel="noopener" class="btn btn-secondary">
                مشاهده دمو زنده
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M7 17 17 7"/><path d="M7 7h10v10"/>
                </svg>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <div class="toc-card">
          <div class="toc-head">فهرست مطالب</div>
          <nav class="toc-nav">
            <?php if ($demo_src) : ?><a href="#live-demo" class="toc-link">پیش‌نمایش زنده</a><?php endif; ?>
            <?php if (get_the_content()) : ?><a href="#overview" class="toc-link">درباره این قالب</a><?php endif; ?>
            <?php if (!empty($features)) : ?><a href="#features" class="toc-link">امکانات کلیدی</a><?php endif; ?>
            <?php if (!empty($specs)) : ?><a href="#specs" class="toc-link">مشخصات فنی</a><?php endif; ?>
            <?php if (!empty($gallery)) : ?><a href="#gallery" class="toc-link">پیش‌نمایش صفحات</a><?php endif; ?>
            <?php if (!empty($use_cases)) : ?><a href="#usecases" class="toc-link">مناسب برای</a><?php endif; ?>
            <?php if (!empty($faqs)) : ?><a href="#faq" class="toc-link">سوالات متداول</a><?php endif; ?>
          </nav>
        </div>
      </aside>

    </div>

    <!-- CTA پایانی -->
    <?php if ($cta_title) : ?>
    <section class="bottom-cta">
      <h2><?php echo esc_html($cta_title); ?></h2>
      <?php if ($cta_desc) : ?><p><?php echo esc_html($cta_desc); ?></p><?php endif; ?>
      <?php if ($cta_btn_text && $cta_btn_url) : ?>
        <a href="<?php echo esc_url($cta_btn_url); ?>" class="btn btn-primary"><?php echo esc_html($cta_btn_text); ?></a>
      <?php endif; ?>
    </section>
    <?php endif; ?>

  </div>
</main>
<?php endwhile; ?>
<?php get_footer(); ?>