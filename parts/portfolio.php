<?php
if (!defined('ABSPATH')) exit;

$args = [
    'post_type'      => 'raha_portfolio',
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
$portfolio = get_posts($args);

if (empty($portfolio)) return;

// استخراج تگ‌های یکتا برای فیلترها
$tags_list = [];
$counts = ['all' => count($portfolio)];
foreach ($portfolio as $p) {
    $tag = get_post_meta($p->ID, 'tag', true);
    if ($tag) {
        if (!isset($counts[$tag])) $counts[$tag] = 0;
        $counts[$tag]++;
        $tags_list[$tag] = $tag;
    }
}

$archive_url = get_post_type_archive_link('raha_portfolio');
?>

<section class="projects raha-portfolio-section" aria-labelledby="projects-title">
  <div class="container">

    <header class="projects__head">
      <div class="projects__intro">
        <h2 id="projects-title" class="projects__title">نمونه‌کارهای منتخب</h2>
        <p class="projects__lead">چند پروژه که بیشترین افتخار را در کارنامه من دارند.</p>
      </div>

      <?php if ($archive_url): ?>
      <a href="<?php echo esc_url($archive_url); ?>" class="btn">
        همه پروژه‌ها
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
      </a>
      <?php endif; ?>
    </header>

    <div class="projects__filters" role="group" aria-label="فیلتر دسته‌بندی">
      <button class="chip is-active" data-filter="all" aria-pressed="true">
        همه <span class="chip__count"><?php echo esc_html(strtr(count($portfolio), '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?></span>
      </button>
      <?php foreach ($tags_list as $tag) : ?>
        <button class="chip" data-filter="<?php echo esc_attr(sanitize_title($tag)); ?>" aria-pressed="false">
          <?php echo esc_html($tag); ?> <span class="chip__count"><?php echo esc_html(strtr($counts[$tag], '0123456789', '۰۱۲۳۴۵۶۷۸۹')); ?></span>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="projects__grid">

      <?php foreach ($portfolio as $i => $p) : 
          $tag      = get_post_meta($p->ID, 'tag', true);
          $year     = get_post_meta($p->ID, 'year', true);
          $desc     = get_post_meta($p->ID, 'desc', true);
          $demo_url = get_post_meta($p->ID, 'demo_url', true);
          $cover_id = (int) get_post_meta($p->ID, 'cover', true);
          if (!$cover_id) $cover_id = get_post_thumbnail_id($p->ID);
          
          $cat_slug = $tag ? sanitize_title($tag) : 'other';
          $num_fa = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
          $num_fa = strtr($num_fa, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
      ?>
        <article class="project-card" data-category="<?php echo esc_attr($cat_slug); ?>">
          <div class="project-card__media">
            <?php if ($tag) : ?><span class="project-card__tag"><?php echo esc_html($tag); ?></span><?php endif; ?>
            
            <?php if ($cover_id) : ?>
              <?php echo wp_get_attachment_image($cover_id, 'large', false, ['class' => 'project-card__img']); ?>
            <?php else : ?>
              <span class="project-card__num" aria-hidden="true"><?php echo esc_html($num_fa); ?></span>
            <?php endif; ?>

            <?php if ($demo_url) : ?>
              <a class="project-card__demo" href="<?php echo esc_url($demo_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="نمایش دمو <?php echo esc_attr($p->post_title); ?>">
                نمایش دمو
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M7 17 17 7"/><path d="M7 7h10v10"/>
                </svg>
              </a>
            <?php endif; ?>
          </div>

          <div class="project-card__body">
            <h3 class="project-card__title">
              <a class="project-card__title-link" href="<?php echo esc_url(get_permalink($p->ID)); ?>"><?php echo esc_html($p->post_title); ?></a>
            </h3>
            <?php if ($desc) : ?>
              <p class="project-card__desc"><?php echo esc_html($desc); ?></p>
            <?php endif; ?>

            <div class="project-card__foot">
              <p class="project-card__meta">
                <span><?php echo esc_html($tag ?: 'پروژه'); ?></span>
                <?php if ($year) : ?>
                  <span class="dot" aria-hidden="true"></span>
                  <span><?php echo esc_html($year); ?></span>
                <?php endif; ?>
              </p>
              <div class="project-card__actions">
                <span class="project-card__arrow" aria-hidden="true">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
                  </svg>
                </span>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

    </div>
  </div>
</section>