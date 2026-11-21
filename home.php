<?php
if (!defined('ABSPATH')) exit;
get_header();

// ۱. پیدا کردن پست شاخص (Sticky)
$sticky_posts = get_option('sticky_posts');
$featured_post = null;
$exclude_ids = [];

if (!empty($sticky_posts)) {
    $featured_post = get_post($sticky_posts[0]);
    $exclude_ids[] = $featured_post->ID;
}

// اگر پست چسبان نداشتیم، آخرین پست را به عنوان شاخص بگیریم
if (!$featured_post) {
    $latest = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
    if (!empty($latest)) {
        $featured_post = $latest[0];
        $exclude_ids[] = $featured_post->ID;
    }
}

// ۲. آماده‌سازی کوئری برای بقیه پست‌ها
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = [
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 6, // ۶ پست در هر صفحه
    'paged'          => $paged,
    'post__not_in'   => $exclude_ids,
];
$blog_query = new WP_Query($args);
?>

<main class="raha-blog-page">
  <section class="blog" id="blog" aria-labelledby="blog-title">
    <div class="blog__inner">

      <header class="blog__head">
        <h1 id="blog-title" class="blog__title">یادداشت های محمد صادق محمودی لرد</h1>
        <p class="blog__lead">یادداشت‌های کاری من؛ ابزار، ترفند و تصمیم‌هایی که پشت هر پروژه‌ی واقعی ایستاده‌اند. بدون حاشیه، از دل کار</p>
      </header>

      <!-- فیلترها -->
      <div class="blog__filters" role="group" aria-label="فیلتر دسته‌بندی نوشته‌ها" id="blogFilters">
        <button type="button" class="blog__chip" data-filter="all" aria-pressed="true">همه</button>
        <?php
        $categories = get_categories(['hide_empty' => true]);
        foreach ($categories as $cat) {
            echo '<button type="button" class="blog__chip" data-filter="' . esc_attr($cat->slug) . '" aria-pressed="false">' . esc_html($cat->name) . '</button>';
        }
        ?>
      </div>

      <p class="sr-only" role="status" aria-live="polite" id="blog-status"></p>

      <!-- پست شاخص -->
      <?php if ($featured_post) : 
          $f_cats = get_the_category($featured_post->ID);
          $f_cat_slug = !empty($f_cats) ? $f_cats[0]->slug : 'uncategorized';
          $f_cat_name = !empty($f_cats) ? $f_cats[0]->name : 'عمومی';
      ?>
      <article class="blog__featured" data-category="<?php echo esc_attr($f_cat_slug); ?>">
        <div class="blog__featured-media">
          <?php if (has_post_thumbnail($featured_post->ID)) : ?>
            <?php echo get_the_post_thumbnail($featured_post->ID, 'full', ['class' => 'post__img', 'loading' => 'eager', 'fetchpriority' => 'high']); ?>
          <?php else : ?>
            <!-- فال‌بک تصویر هماهنگ با AJAX -->
            <img class="post__img" src="https://picsum.photos/seed/<?php echo $featured_post->ID; ?>/1200/800" alt="" loading="eager">
          <?php endif; ?>
          <span class="blog__featured-tag">منتخب</span>
          <div class="blog__featured-icon" aria-hidden="true"><svg><use href="#i-book"/></svg></div>
        </div>
        <div class="blog__featured-body">
          <p class="blog__featured-eyebrow"><?php echo esc_html($f_cat_name); ?> · <?php echo esc_html(get_the_date('', $featured_post->ID)); ?></p>
          <h2 class="blog__featured-title"><?php echo esc_html($featured_post->post_title); ?></h2>
          <p class="blog__featured-desc"><?php echo wp_trim_words(wp_strip_all_tags($featured_post->post_content), 30); ?></p>
          <div class="blog__featured-meta">
            <span><svg aria-hidden="true"><use href="#i-calendar"/></svg><time datetime="<?php echo esc_attr(get_the_date('Y-m-d', $featured_post->ID)); ?>"><?php echo esc_html(get_the_date('', $featured_post->ID)); ?></time></span>
            <a href="<?php echo esc_url(get_permalink($featured_post->ID)); ?>" class="blog__featured-cta">ادامه مطلب <svg aria-hidden="true"><use href="#i-arrow-left"/></svg></a>
          </div>
        </div>
      </article>
      <?php endif; ?>

      <!-- گرید پست‌ها -->
      <div class="blog__grid" id="blogGrid">
        <?php if ($blog_query->have_posts()) : 
            $num_offset = ($paged - 1) * 6;
            $i = 1;
            while ($blog_query->have_posts()) : $blog_query->the_post(); 
                $cats = get_the_category();
                $cat_slug = !empty($cats) ? $cats[0]->slug : 'uncategorized';
                $cat_name = !empty($cats) ? $cats[0]->name : 'عمومی';
                
                // تعیین آیکون بر اساس دسته
                $icon_id = 'i-book';
                if ($cat_slug === 'performance') $icon_id = 'i-zap';
                elseif ($cat_slug === 'development') $icon_id = 'i-code';
                elseif ($cat_slug === 'ux') $icon_id = 'i-globe';
                elseif ($cat_slug === 'ui' || $cat_slug === 'design') $icon_id = 'i-grid';
                
                $num_fa = str_pad((string)($i + $num_offset), 2, '0', STR_PAD_LEFT);
                $num_fa = strtr($num_fa, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
        ?>
          <article class="post" data-category="<?php echo esc_attr($cat_slug); ?>">
            <div class="post__media">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', ['class' => 'post__img', 'loading' => 'lazy']); ?>
              <?php else : ?>
                <!-- فال‌بک تصویر هماهنگ با AJAX -->
                <img class="post__img" src="https://picsum.photos/seed/<?php echo get_the_ID(); ?>/800/500" alt="" loading="lazy" decoding="async">
              <?php endif; ?>
              <span class="post__tag"><?php echo esc_html($cat_name); ?></span>
              <span class="post__num" aria-hidden="true"><?php echo esc_html($num_fa); ?></span>
              <div class="post__icon" aria-hidden="true"><svg><use href="#<?php echo esc_attr($icon_id); ?>"/></svg></div>
            </div>
            <div class="post__body">
              <h2 class="post__title"><a class="post__title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
              <p class="post__desc"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
              <div class="post__foot">
                <span class="post__date"><svg aria-hidden="true"><use href="#i-calendar"/></svg><time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date()); ?></time></span>
                <span class="post__arrow" aria-hidden="true"><svg><use href="#i-arrow-left"/></svg></span>
              </div>
            </div>
          </article>
        <?php $i++; endwhile; ?>
        <?php endif; ?>
      </div>

      <!-- Load More با فال‌بک سئو -->
      <?php if ($blog_query->max_num_pages > 1) : ?>
      <div class="blog__loadmore-wrap">
        <!-- دکمه برای کاربران (AJAX) -->
        <button type="button" class="blog__loadmore" id="loadmore" data-page="<?php echo $paged; ?>" data-max="<?php echo $blog_query->max_num_pages; ?>">
          نمایش نوشته‌های بیشتر
          <svg aria-hidden="true"><use href="#i-chevron-down"/></svg>
        </button>
        
        <!-- لینک پنهان برای خزنده‌های گوگل (Fallback SEO) -->
        <noscript>
            <div style="margin-top: 20px; text-align: center;">
                <?php echo paginate_links(['prev_text' => 'قبلی', 'next_text' => 'بعدی']); ?>
            </div>
        </noscript>
        
        <!-- استفاده از کلاس sr-only به جای display:none برای خوانایی بهتر ربات‌های جستجو -->
        <div class="sr-only">
            <?php echo paginate_links(['prev_text' => 'قبلی', 'next_text' => 'بعدی']); ?>
        </div>
      </div>
      <?php endif; ?>

      <p class="blog__empty" hidden>هیچ نوشته‌ای در این دسته یافت نشد.</p>

    </div>
  </section>
</main>

<?php wp_reset_postdata(); ?>
<?php get_footer(); ?>