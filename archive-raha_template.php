<?php
if (!defined('ABSPATH')) exit;
get_header();
?>

<section class="raha-section raha-portfolio">
    <div class="raha-container">
        
        <header class="raha-section__head raha-section__head--center">
            <p class="raha-eyebrow">قالب‌های آماده</p>
            <h1 class="raha-section__title">قالب‌های اختصاصی استودیو</h1>
            <p class="raha-section__lead">قالب‌های طراحی شده با تمرکز بر سرعت، سئو و تجربه کاربری.</p>
        </header>

        <?php if (have_posts()) : ?>
            <div class="raha-portfolio__grid">
                <?php while (have_posts()) : the_post(); 
                    $tag   = get_post_meta(get_the_ID(), 'tag_line', true);
                    $price = get_post_meta(get_the_ID(), 'price', true);
                    $tech  = get_post_meta(get_the_ID(), 'tech', true);
                ?>
                    <article class="raha-portfolio-card">
                        <div class="raha-portfolio-card__media">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large', ['class' => 'raha-portfolio-card__img']); ?>
                            <?php endif; ?>
                            <?php if ($tag) : ?>
                                <span class="raha-portfolio-card__tag"><?php echo esc_html($tag); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="raha-portfolio-card__body">
                            <h3 class="raha-portfolio-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; font-size:11px; font-weight:800;">
                                <span>🛠 <?php echo esc_html($tech ?: '—'); ?></span>
                                <span style="color:var(--accent);">💰 <?php echo esc_html($price ?: 'استعلام قیمت'); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="raha-pagination">
                <?php echo paginate_links(['prev_text' => 'قبلی', 'next_text' => 'بعدی']); ?>
            </div>
        <?php else : ?>
            <p style="text-align:center; padding:40px;">هنوز قالبی ثبت نشده است.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>