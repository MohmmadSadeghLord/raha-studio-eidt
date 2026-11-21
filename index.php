<?php
/**
 * Raha Studio — Index (fallback)
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<div class="raha-container">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class(); ?> style="margin-block: 48px;">
                <h2 style="font-size: 28px; color: var(--accent);">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div style="color: var(--ink-soft);"><?php the_excerpt(); ?></div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <div class="raha-placeholder">
            <h1>هیچ محتوایی برای نمایش نیست</h1>
            <p>از پیشخوان شروع کنید به اضافه کردن محتوا.</p>
            <?php if (current_user_can('edit_posts')) : ?>
                <p>از منوی راست: <code>خدمات</code>، <code>نمونه‌کارها</code>، <code>پکیج‌های قیمت</code>، <code>سوالات متداول</code></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>