<?php
if (!defined('ABSPATH')) exit;

define('RAHA_VERSION', '1.0.0');
define('RAHA_DIR', get_template_directory());
define('RAHA_URI', get_template_directory_uri());

require_once RAHA_DIR . '/inc/setup.php';
require_once RAHA_DIR . '/inc/enqueue.php';
require_once RAHA_DIR . '/inc/class-raha-fields.php';
require_once RAHA_DIR . '/inc/class-raha-options-page.php';
require_once RAHA_DIR . '/inc/post-types.php';
require_once RAHA_DIR . '/inc/meta-boxes.php';
require_once RAHA_DIR . '/inc/template-demo-handler.php';
require_once RAHA_DIR . '/inc/options-page.php';
require_once RAHA_DIR . '/inc/mega-menu.php';
require_once RAHA_DIR . '/inc/order-handler.php';
require_once RAHA_DIR . '/inc/order-meta-boxes.php';
require_once RAHA_DIR . '/inc/admin-columns.php';
require_once RAHA_DIR . '/inc/user-panel.php';
require_once RAHA_DIR . '/inc/contact-handler.php';
/* ============================================================
   سیستم ایمیل
   ============================================================ */
require_once RAHA_DIR . '/inc/email-helpers.php';   // ← اول
require_once RAHA_DIR . '/inc/smtp.php';             // ← بعد
require_once RAHA_DIR . '/inc/email-log.php';
require_once RAHA_DIR . '/inc/class-raha-email.php';
require_once RAHA_DIR . '/inc/email-templates.php';
require_once RAHA_DIR . '/inc/email-renderers.php';

/* ============================================================
   سایر ماژول‌ها
   ============================================================ */
require_once RAHA_DIR . '/inc/cta-handler.php';
require_once RAHA_DIR . '/inc/admin-page-orders.php';
require_once RAHA_DIR . '/inc/admin-page-pricing.php';
require_once RAHA_DIR . '/inc/register-handler.php';
require_once RAHA_DIR . '/inc/login-handler.php';
require_once RAHA_DIR . '/inc/forgot-password-handler.php';
require_once RAHA_DIR . '/inc/restrict-admin.php';
require_once RAHA_DIR . '/inc/admin-page-communications.php';

/* ============================================================
   CORE INFRASTRUCTURE (Phase 1)
   ============================================================ */
require_once RAHA_DIR . '/inc/core/class-raha-db-installer.php';
require_once RAHA_DIR . '/inc/core/class-raha-rbac.php';
require_once RAHA_DIR . '/inc/core/class-raha-audit-logger.php';
require_once RAHA_DIR . '/inc/core/class-raha-security.php';

// راه‌اندازی
Raha_DB_Installer::init();
Raha_RBAC::init();

/* ============================================================
   ADMIN SHELL (Phase 2)
   ============================================================ */
require_once RAHA_DIR . '/inc/admin/class-raha-admin-shell.php';
Raha_Admin_Shell::init();

require_once RAHA_DIR . '/inc/admin/class-raha-admin-api.php';
Raha_Admin_API::init();


require_once RAHA_DIR . '/inc/admin/class-raha-impersonate.php';
Raha_Impersonate::init();








/* Helper عمومی: sanitize SVG */
function raha_svg($svg) {
    $allowed = [
        'svg' => ['viewbox'=>true,'width'=>true,'height'=>true,'fill'=>true,'stroke'=>true,'class'=>true,'xmlns'=>true,'aria-hidden'=>true],
        'path' => ['d'=>true,'fill'=>true,'stroke'=>true,'stroke-width'=>true,'stroke-linecap'=>true,'stroke-linejoin'=>true],
        'circle' => ['cx'=>true,'cy'=>true,'r'=>true,'fill'=>true],
        'rect' => ['x'=>true,'y'=>true,'width'=>true,'height'=>true,'rx'=>true,'fill'=>true,'stroke'=>true,'stroke-width'=>true],
        'line' => ['x1'=>true,'y1'=>true,'x2'=>true,'y2'=>true,'stroke'=>true,'stroke-width'=>true],
        'polyline' => ['points'=>true,'fill'=>true,'stroke'=>true,'stroke-width'=>true],
        'polygon' => ['points'=>true,'fill'=>true,'stroke'=>true,'stroke-width'=>true],
        'g' => ['fill'=>true,'stroke'=>true],
    ];
    return wp_kses($svg, $allowed);
}

/* ============================================================
   AJAX Load More برای وبلاگ
   ============================================================ */
add_action('wp_ajax_raha_load_more_posts', 'raha_load_more_posts_handler');
add_action('wp_ajax_nopriv_raha_load_more_posts', 'raha_load_more_posts_handler');

function raha_load_more_posts_handler() {
    // بررسی امنیتی nonce
    check_ajax_referer('raha_blog_nonce', 'nonce');

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    // دریافت پست چسبان برای حذف کردن از لیست
    $sticky_posts = get_option('sticky_posts');
    $exclude_ids = [];
    if (!empty($sticky_posts)) {
        $exclude_ids[] = $sticky_posts[0];
    } else {
        $latest = get_posts(['numberposts' => 1, 'post_status' => 'publish']);
        if (!empty($latest)) $exclude_ids[] = $latest[0]->ID;
    }

    $args = [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'paged'          => $page,
        'post__not_in'   => $exclude_ids,
    ];

    $query = new WP_Query($args);
    $html = '';

    if ($query->have_posts()) {
        $num_offset = ($page - 1) * 6;
        $i = 1;
        while ($query->have_posts()) {
            $query->the_post();
            $cats = get_the_category();
            $cat_slug = !empty($cats) ? $cats[0]->slug : 'uncategorized';
            $cat_name = !empty($cats) ? $cats[0]->name : 'عمومی';
            
            $icon_id = 'i-book';
            if ($cat_slug === 'performance') $icon_id = 'i-zap';
            elseif ($cat_slug === 'development') $icon_id = 'i-code';
            elseif ($cat_slug === 'ux') $icon_id = 'i-globe';
            elseif ($cat_slug === 'ui' || $cat_slug === 'design') $icon_id = 'i-grid';
            
            $num_fa = str_pad((string)($i + $num_offset), 2, '0', STR_PAD_LEFT);
            $num_fa = strtr($num_fa, '0123456789', '۰۱۲۳۴۵۶۷۸۹');
            
            $thumb = has_post_thumbnail() ? get_the_post_thumbnail(get_the_ID(), 'large', ['class' => 'post__img', 'loading' => 'lazy']) : '<div style="width:100%; height:100%; background:var(--accent);"></div>';

            ob_start();
            ?>
            <article class="post" data-category="<?php echo esc_attr($cat_slug); ?>">
                <div class="post__media">
                    <?php echo $thumb; ?>
                    <span class="post__tag"><?php echo esc_html($cat_name); ?></span>
                    <span class="post__num" aria-hidden="true"><?php echo esc_html($num_fa); ?></span>
                    <div class="post__icon" aria-hidden="true"><svg><use href="#<?php echo $icon_id; ?>"/></svg></div>
                </div>
                <div class="post__body">
                    <h2 class="post__title"><a class="post__title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p class="post__desc"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                    <div class="post__foot">
                        <span class="post__date"><svg><use href="#i-calendar"/></svg><time datetime="<?php echo esc_attr(get_the_date('Y-m-d')); ?>"><?php echo esc_html(get_the_date()); ?></time></span>
                        <span class="post__arrow" aria-hidden="true"><svg><use href="#i-arrow-left"/></svg></span>
                    </div>
                </div>
            </article>
            <?php
            $html .= ob_get_clean();
            $i++;
        }
    }
    wp_reset_postdata();

    // آیا صفحه بعدی وجود دارد؟
    $max_pages = $query->max_num_pages;
    $has_more = ($page < $max_pages);

    wp_send_json_success([
        'html' => $html,
        'has_more' => $has_more
    ]);
}