<?php
if (!defined('ABSPATH')) exit;

$services = get_posts([
    'post_type'      => 'raha_service',
    'posts_per_page' => -1,
    'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
]);
if (empty($services)) return;
?>
<section class="raha-section raha-services">
    <div class="raha-container">
        <header class="raha-section__head">
            <p class="raha-eyebrow">خدمات</p>
            <h2 class="raha-section__title">چه کاری برایتان انجام می‌دهم</h2>
        </header>
        <div class="raha-services__grid">
            <?php foreach ($services as $s):
                $icon = get_post_meta($s->ID, 'icon', true);
                $desc = get_post_meta($s->ID, 'short_desc', true);
                $link = get_post_meta($s->ID, 'link', true);
                $tag  = $link ? 'a' : 'div';
            ?>
                <<?php echo $tag; ?><?php if ($link): ?> href="<?php echo esc_url($link); ?>"<?php endif; ?> class="raha-service-card">
                    <?php if ($icon): ?>
                        <span class="raha-service-card__icon"><?php echo raha_svg($icon); ?></span>
                    <?php endif; ?>
                    <h3 class="raha-service-card__title"><?php echo esc_html($s->post_title); ?></h3>
                    <?php if ($desc): ?>
                        <p class="raha-service-card__desc"><?php echo esc_html($desc); ?></p>
                    <?php endif; ?>
                </<?php echo $tag; ?>>
            <?php endforeach; ?>
        </div>
    </div>
</section>