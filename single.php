<?php
if (!defined('ABSPATH')) exit;
get_header();

while (have_posts()) : the_post();
    $post_id = get_the_ID();
    $author_id = get_the_author_meta('ID');
    $author_name = get_the_author();
    $author_bio = get_the_author_meta('description');
    $categories = get_the_category();
    $primary_cat = !empty($categories) ? $categories[0] : null;
    
    // محاسبه زمان مطالعه
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $read_time = ceil($word_count / 200);
    if ($read_time < 1) $read_time = 1;
?>

<!-- نوار پیشرفت مطالعه -->
<div class="read-progress" aria-hidden="true"><span id="readProgress"></span></div>

<main id="main" class="single-post-wrapper">
  <div class="article__inner">
    <article class="article" aria-labelledby="article-title">
      
      <header class="article__head">
        <nav class="crumb" aria-label="مسیر صفحه">
          <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
          <span aria-hidden="true">›</span>
          <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>">وبلاگ</a>
          <?php if ($primary_cat) : ?>
            <span aria-hidden="true">›</span>
            <a href="<?php echo esc_url(get_category_link($primary_cat->term_id)); ?>"><?php echo esc_html($primary_cat->name); ?></a>
          <?php endif; ?>
        </nav>

        <?php if ($primary_cat) : ?>
          <span class="article__chip"><?php echo esc_html($primary_cat->name); ?></span>
        <?php endif; ?>

        <h1 id="article-title" class="article__title"><?php the_title(); ?></h1>

        <p class="article__lead"><?php echo esc_html(wp_strip_all_tags(get_the_excerpt())); ?></p>

        <div class="article__meta">
          <span class="author-mini">
            <span class="author-mini__avatar" aria-hidden="true"><?php echo esc_html(mb_substr($author_name, 0, 1)); ?></span>
            <?php echo esc_html($author_name); ?>
          </span>
          <span class="meta-dot" aria-hidden="true"></span>
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time>
          </span>
          <span class="meta-dot" aria-hidden="true"></span>
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <?php echo esc_html($read_time); ?> دقیقه مطالعه
          </span>
        </div>
      </header>

      <?php if (has_post_thumbnail()) : 
          $thumb_url = get_the_post_thumbnail_url($post_id, 'full');
          $thumb_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) ?: get_the_title();
      ?>
        <figure class="article__cover">
          <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($thumb_alt); ?>" fetchpriority="high" decoding="async">
        </figure>
      <?php endif; ?>

      <div class="article__layout">
        
        <div class="article__body" id="articleContent">
          <?php the_content(); ?>
        </div>

        <aside class="article__aside" aria-label="فهرست مطالب و اشتراک‌گذاری">
          <details class="toc" id="toc" open>
            <summary class="toc__summary">فهرست مطالب</summary>
            <nav class="toc__nav" id="tocNav"></nav>
          </details>

          <div class="share-box">
            <p class="share-box__title">اشتراک‌گذاری</p>
            <div class="share-row">
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="share-btn" aria-label="اشتراک در ایکس">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 3h3.1l-6.8 7.8L22 21h-6.3l-4.9-6.4L5.2 21H2.1l7.3-8.3L2 3h6.4l4.4 5.9L17.6 3z"/></svg>
              </a>
              <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" rel="noopener" class="share-btn" aria-label="اشتراک در لینکدین">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="2.5" y="9" width="3.9" height="12.5" rx="0.6"/><circle cx="4.5" cy="4.6" r="2.3"/><path d="M9.2 9h3.7v1.8c.6-1.1 1.9-2.1 4-2.1 3 0 4.4 1.9 4.4 5.5v7.3h-3.9v-6.8c0-1.7-.6-2.8-2-2.8-1.2 0-1.9.8-2.2 1.6-.1.3-.1.7-.1 1.1v6.9H9.2V9z"/></svg>
              </a>
              <a href="https://t.me/share/url?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" rel="noopener" class="share-btn" aria-label="اشتراک در تلگرام">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.8 3.4 2.6 10.8c-1.2.5-1.2 1.2-.2 1.5l4.9 1.5 1.9 5.7c.2.6.1.8.7.8.5 0 .7-.2 1-.5l2.4-2.3 5 3.7c.9.5 1.6.2 1.8-.9l3.3-15.4c.3-1.3-.5-1.9-1.6-1.5z"/></svg>
              </a>
              <button class="share-btn" type="button" data-copy aria-label="کپی لینک مقاله">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
              </button>
            </div>
          </div>
        </aside>

      </div>

      <footer class="article__foot">
        
        <!-- بخش بالای فوتر: برچسب‌ها و دکمه بازگشت -->
        <div class="article__foot-top">
            <?php if (has_tag()) : ?>
              <div class="tags">
                <span class="tags__label">برچسب‌ها</span>
                <?php 
                $post_tags = get_the_tags();
                if ($post_tags) {
                    foreach ($post_tags as $tag) {
                        echo '<a class="tag" href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>';
                    }
                }
                ?>
              </div>
            <?php endif; ?>

            <a class="back-link" href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              بازگشت به وبلاگ
            </a>
        </div>

        <!-- کارت نویسنده -->
        <div class="author-card">
          <span class="author-card__avatar" aria-hidden="true"><?php echo esc_html(mb_substr($author_name, 0, 1)); ?></span>
          <div class="author-card__body">
            <p class="author-card__name"><strong><?php echo esc_html($author_name); ?></strong></p>
            <p class="author-card__bio"><?php echo esc_html($author_bio); ?></p>
            <a class="author-card__link" href="<?php echo esc_url(get_author_posts_url($author_id)); ?>">
              مقالات من
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            </a>
          </div>
        </div>

      </footer>
    </article>

    <!-- مقالات مرتبط -->
    <?php
    $related_args = [
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [$post_id],
        'category__in' => wp_get_post_categories($post_id),
        'orderby' => 'rand'
    ];
    $related_posts = new WP_Query($related_args);
    if ($related_posts->have_posts()) :
    ?>
    <section class="related" aria-labelledby="related-title">
      <div class="related__head">
        <h2 class="related__title" id="related-title">مقالات مرتبط</h2>
      </div>
      <div class="related__grid">
        <?php while ($related_posts->have_posts()) : $related_posts->the_post(); ?>
          <a class="rel" href="<?php the_permalink(); ?>">
            <div class="rel__media">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('medium', ['class' => 'rel__img', 'loading' => 'lazy']); ?>
              <?php else : ?>
                <span class="rel__icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </span>
              <?php endif; ?>
            </div>
            <div class="rel__body">
              <span class="rel__cat"><?php echo esc_html(get_the_category()[0]->name ?? 'عمومی'); ?></span>
              <h3 class="rel__title"><?php the_title(); ?></h3>
              <span class="rel__date"><?php echo esc_html(get_the_date()); ?></span>
            </div>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- CTA پایانی -->
    <section class="cta" aria-labelledby="cta-title">
      <div class="cta__body">
        <h2 class="cta__title" id="cta-title">پروژه‌ای در ذهن دارید؟</h2>
        <p class="cta__desc">یک گفتگوی کوتاه کافی است تا مسیر طراحی سایت‌تان روشن شود.</p>
      </div>
      <a class="cta__btn" href="<?php echo esc_url(home_url('/#contact')); ?>">
        تماس با من
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
      </a>
    </section>

  </div>
</main>

<?php endwhile; ?>
<?php get_footer(); ?>