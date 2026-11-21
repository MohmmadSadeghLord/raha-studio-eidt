<?php
if (!defined('ABSPATH')) exit;
get_header();

while (have_posts()) : the_post();
    $tag      = get_post_meta(get_the_ID(), 'tag', true);
    $year     = get_post_meta(get_the_ID(), 'year', true);
    $desc     = get_post_meta(get_the_ID(), 'desc', true);
    $demo     = get_post_meta(get_the_ID(), 'demo_url', true);
    $cover_id = (int) get_post_meta(get_the_ID(), 'cover', true);
    $gallery  = get_post_meta(get_the_ID(), 'gallery', true) ?: [];
?>

<div class="raha-container raha-template-page">
    
    <nav class="raha-breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
        <span>›</span>
        <a href="<?php echo esc_url(get_post_type_archive_link('raha_portfolio')); ?>">نمونه‌کارها</a>
        <span>›</span>
        <span><?php the_title(); ?></span>
    </nav>

    <div class="raha-single-portfolio">
        <div class="raha-single-portfolio__content">
            <?php if ($tag) : ?><span class="raha-chip"><?php echo esc_html($tag); ?></span><?php endif; ?>
            <h1 class="raha-single-portfolio__title"><?php the_title(); ?></h1>
            
            <div class="raha-single-portfolio__meta">
                <?php if ($year) : ?><span>🗓 <?php echo esc_html($year); ?></span><?php endif; ?>
            </div>

            <?php if ($desc) : ?>
                <p class="raha-single-portfolio__desc"><?php echo esc_html($desc); ?></p>
            <?php endif; ?>

            <?php if ($demo) : ?>
                <a href="<?php echo esc_url($demo); ?>" target="_blank" rel="noopener" class="raha-btn raha-btn--primary" style="margin-top: 16px;">
                    مشاهده سایت زنده
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
                </a>
            <?php endif; ?>
        </div>

        <?php if ($cover_id) : ?>
            <div class="raha-single-portfolio__cover">
                <?php echo wp_get_attachment_image($cover_id, 'full'); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (get_the_content()) : ?>
        <div class="raha-single-portfolio__body">
            <h2>درباره این پروژه</h2>
            <div class="raha-prose"><?php the_content(); ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($gallery)) : ?>
        <div class="raha-single-portfolio__gallery">
            <h2>گالری تصاویر</h2>
            <div class="raha-portfolio__grid">
                <?php foreach ($gallery as $img) : if (empty($img['image'])) continue; ?>
                    <div class="raha-portfolio-card" style="cursor: default;">
                        <div class="raha-portfolio-card__media" style="aspect-ratio: auto;">
                            <?php echo wp_get_attachment_image($img['image'], 'large', false, ['style' => 'width:100%; height:auto; display:block;']); ?>
                        </div>
                        <?php if (!empty($img['caption'])) : ?>
                            <div class="raha-portfolio-card__body" style="padding: 10px;">
                                <p style="margin:0; font-size:12px; color:var(--muted); text-align:center;"><?php echo esc_html($img['caption']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php endwhile; ?>
<?php get_footer(); ?>