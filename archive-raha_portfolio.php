<?php
if (!defined('ABSPATH')) exit;
get_header();
?>

<section class="raha-section raha-portfolio">
    <div class="raha-container">
        
        <header class="raha-section__head raha-section__head--center">
            <p class="raha-eyebrow">پورتفولیو</p>
            <h1 class="raha-section__title">همه نمونه‌کارها</h1>
            <p class="raha-section__lead">مجموعه‌ای از پروژه‌هایی که با افتخار روی آن‌ها کار کرده‌ام.</p>
        </header>

        <?php if (have_posts()) : ?>
            <div class="raha-portfolio__grid">
                <?php while (have_posts()) : the_post(); 
                    $tag  = get_post_meta(get_the_ID(), 'tag', true);
                    $year = get_post_meta(get_the_ID(), 'year', true);
                    $desc = get_post_meta(get_the_ID(), 'desc', true);
                    $cover_id = (int) get_post_meta(get_the_ID(), 'cover', true);
                ?>
                    <article class="raha-portfolio-card">
                        <div class="raha-portfolio-card__media">
                            <?php if ($cover_id) : ?>
                                <?php echo wp_get_attachment_image($cover_id, 'large', false, ['class' => 'raha-portfolio-card__img']); ?>
                            <?php endif; ?>
                            <?php if ($tag) : ?>
                                <span class="raha-portfolio-card__tag"><?php echo esc_html($tag); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="raha-portfolio-card__body">
                            <h3 class="raha-portfolio-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <?php if ($desc) : ?>
                                <p class="raha-portfolio-card__desc"><?php echo esc_html($desc); ?></p>
                            <?php endif; ?>
                            <?php if ($year) : ?>
                                <span class="raha-portfolio-card__year"><?php echo esc_html($year); ?></span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <div class="raha-pagination">
                <?php echo paginate_links(['prev_text' => 'قبلی', 'next_text' => 'بعدی']); ?>
            </div>
        <?php else : ?>
            <p style="text-align:center; padding:40px;">هنوز نمونه‌کاری ثبت نشده است.</p>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>