<?php
/**
 * Raha Studio — Header
 */
if (!defined('ABSPATH')) exit;

$is_logged_in = is_user_logged_in();
$login_url    = function_exists('raha_page_url') ? raha_page_url('login') : wp_login_url();
$account_url  = function_exists('raha_page_url') ? raha_page_url('account') : home_url('/');
$order_form_url = function_exists('raha_page_url') ? raha_page_url('order-form') : home_url('/');
$current_user = wp_get_current_user();

// دریافت آیتم‌های منو
$locations = get_nav_menu_locations();
$menu_id = isset($locations['primary']) ? $locations['primary'] : 0;
$menu_items = $menu_id ? wp_get_nav_menu_items($menu_id) : [];

$top_level_items = [];
if ($menu_items) {
    foreach ($menu_items as $item) {
        if ($item->menu_item_parent == 0) $top_level_items[] = $item;
    }
}

function raha_get_children($parent_id, $items) {
    $children = [];
    foreach ($items as $item) {
        if ($item->menu_item_parent == $parent_id) $children[] = $item;
    }
    return $children;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- SVG Sprite for Icons -->
<svg class="svg-sprite" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" style="position:absolute; width:0; height:0; overflow:hidden;">
  <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></symbol>
  <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></symbol>
  <symbol id="i-book" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M8 7h8M8 11h6"/></symbol>
  <symbol id="i-zap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></symbol>
  <symbol id="i-code" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m16 18 6-6-6-6"/><path d="m8 6-6 6 6 6"/></symbol>
  <symbol id="i-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></symbol>
  <symbol id="i-grid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></symbol>
  <symbol id="i-refresh" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.5 0 4.8 1 6.4 2.6"/><path d="M21 3v6h-6"/></symbol>
  <symbol id="i-layers" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></symbol>
  <symbol id="i-chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></symbol>
</svg>

<header class="site-header" id="siteHeader">
  <div class="header-shell">
    
    <!-- نوار اصلی هدر -->
    <div class="header-card">
      <div class="header-inner">
        
        <!-- لوگو -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
            <?php bloginfo('name'); ?>
        </a>

        <!-- ناوبری دسکتاپ -->
        <nav class="main-nav">
            <?php foreach ($top_level_items as $item) : 
                $children = raha_get_children($item->ID, $menu_items);
                $is_mega = get_post_meta($item->ID, '_raha_mega', true);
                $panel_id = 'panel-' . $item->ID;
            ?>
                <?php if (!empty($children)) : ?>
                    <div class="nav-item">
                        <button class="nav-trigger" type="button" data-target="<?php echo esc_attr($panel_id); ?>" aria-expanded="false" aria-haspopup="true" aria-controls="<?php echo esc_attr($panel_id); ?>">
                            <?php echo esc_html($item->title); ?>
                            <svg class="chev" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        
                        <?php if (!$is_mega) : ?>
                            <div class="nav-panel nav-panel--dropdown" id="<?php echo esc_attr($panel_id); ?>">
                                <div class="dropdown">
                                    <?php foreach ($children as $link_item) : 
                                        $desc = get_post_meta($link_item->ID, '_raha_desc', true);
                                        $icon = get_post_meta($link_item->ID, '_raha_icon', true);
                                    ?>
                                        <a href="<?php echo esc_url($link_item->url); ?>" class="dropdown__link">
                                            <?php if ($icon) : ?>
                                                <span class="dropdown__icon"><span class="dashicons <?php echo esc_attr($icon); ?>"></span></span>
                                            <?php endif; ?>
                                            <span class="dropdown__text">
                                                <span class="dropdown__title"><?php echo esc_html($link_item->title); ?></span>
                                                <?php if ($desc) : ?><small class="dropdown__desc"><?php echo esc_html($desc); ?></small><?php endif; ?>
                                            </span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url($item->url); ?>" class="nav-link"><?php echo esc_html($item->title); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <!-- اکشن‌های هدر (دسکتاپ) -->
        <div class="header-actions">
            
            <!-- 1. دکمه ثبت پروژه (یا ورود) -->
            <?php if ($is_logged_in) : ?>
                <a href="<?php echo esc_url($order_form_url); ?>" class="header-cta">ثبت پروژه</a>
            <?php else : ?>
                <a href="<?php echo esc_url($login_url); ?>" class="header-cta">ورود / ثبت‌نام</a>
            <?php endif; ?>

            <!-- 2. پنل کاربری (فقط در صورت لاگین) -->
            <?php if ($is_logged_in) : ?>
                <div class="raha-user-menu" data-user-menu>
                    <button class="raha-user-toggle" aria-label="پنل کاربری" aria-expanded="false">
                        <span class="raha-user-avatar"><?php echo esc_html(mb_substr($current_user->display_name, 0, 1)); ?></span>
                    </button>
                    <div class="raha-user-dropdown">
                        <div class="raha-user-dropdown__head">
                            <strong><?php echo esc_html($current_user->display_name); ?></strong>
                            <small dir="ltr"><?php echo esc_html($current_user->user_email); ?></small>
                        </div>
                        <a href="<?php echo esc_url($account_url); ?>"><span aria-hidden="true">🏠</span> داشبورد من</a>
                        <a href="<?php echo esc_url(add_query_arg('tab', 'orders', $account_url)); ?>"><span aria-hidden="true">📦</span> سفارش‌های من</a>
                        <hr>
                        <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="is-danger"><span aria-hidden="true">🚪</span> خروج از حساب</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- آیکون جستجو (دسکتاپ) -->
            <a href="<?php echo esc_url(home_url('/?s=')); ?>" class="header-icon-btn" aria-label="جستجو در سایت">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </a>



            <!-- دکمه موبایل -->
            <button class="burger" type="button" id="mobileMenuOpen" aria-label="باز کردن منو" aria-expanded="false" aria-controls="mobileDrawer">
                <span></span><span></span><span></span>
            </button>
        </div>
      </div>
    </div>

    <!-- پنل‌های مگا منو (بیرون از نوار اصلی، داخل هدر) -->
    <?php foreach ($top_level_items as $item) : 
        $children = raha_get_children($item->ID, $menu_items);
        $is_mega = get_post_meta($item->ID, '_raha_mega', true);
        if (empty($children) || !$is_mega) continue;
        $panel_id = 'panel-' . $item->ID;
    ?>
        <div class="nav-panel nav-panel--mega" id="<?php echo esc_attr($panel_id); ?>">
            <div class="mega">
                <div class="mega__grid">
                    <?php 
                    $has_featured = false;
                    foreach ($children as $child) {
                        if (get_post_meta($child->ID, '_raha_featured', true)) {
                            $has_featured = true; break;
                        }
                    }
                    
                    foreach ($children as $col_item) : 
                        $is_featured = get_post_meta($col_item->ID, '_raha_featured', true);
                        if ($is_featured) continue;
                        $sub_children = raha_get_children($col_item->ID, $menu_items);
                    ?>
                        <div class="mega__col">
                            <p class="mega__title"><?php echo esc_html($col_item->title); ?></p>
                            <?php foreach ($sub_children as $link_item) : 
                                $count = get_post_meta($link_item->ID, '_raha_count', true);
                            ?>
                                <a href="<?php echo esc_url($link_item->url); ?>" class="mega__link">
                                    <span class="mega__link-dot"></span>
                                    <span><?php echo esc_html($link_item->title); ?></span>
                                    <?php if ($count) : ?><span class="mega__link-count"><?php echo esc_html($count); ?></span><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($has_featured) : 
                        foreach ($children as $col_item) : 
                            if (!get_post_meta($col_item->ID, '_raha_featured', true)) continue;
                            $feat_img = get_post_meta($col_item->ID, '_raha_feat_image', true);
                            $feat_desc = get_post_meta($col_item->ID, '_raha_feat_desc', true);
                            $img_url = $feat_img ? wp_get_attachment_image_url($feat_img, 'medium') : 'https://via.placeholder.com/300x200?text=Image';
                    ?>
                        <a href="<?php echo esc_url($col_item->url); ?>" class="mega__feature">
                            <div class="mega__feature-media"><img src="<?php echo esc_url($img_url); ?>" alt=""></div>
                            <div class="mega__feature-body">
                                <span class="mega__feature-eyebrow">پروژه منتخب</span>
                                <h5 class="mega__feature-title"><?php echo esc_html($col_item->title); ?></h5>
                                <?php if ($feat_desc) : ?><p class="mega__feature-desc"><?php echo esc_html($feat_desc); ?></p><?php endif; ?>
                                <span class="mega__feature-cta">مشاهده ←</span>
                            </div>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

  </div>
</header>

<!-- منوی موبایل -->
<div class="drawer-overlay" id="drawerOverlay"></div>
<aside class="drawer" id="mobileDrawer" role="dialog" aria-label="منوی موبایل">
  <div class="drawer__header">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="drawer__logo"><?php bloginfo('name'); ?></a>
    <button class="drawer__close" type="button" id="mobileMenuClose" aria-label="بستن منو">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
    </button>
  </div>
  
  <div class="drawer__search">
      <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
          <!-- مقدار value حذف شد تا در صفحه نتایج، متن قبلی در منو نماند -->
          <input type="search" name="s" placeholder="جستجو در سایت...">
          <button type="submit" aria-label="جستجو">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
          </button>
      </form>
  </div>

  <nav class="drawer__nav">
    <?php foreach ($top_level_items as $item) : 
        $children = raha_get_children($item->ID, $menu_items);
    ?>
        <?php if (!empty($children)) : ?>
            <details class="drawer__group">
                <summary><?php echo esc_html($item->title); ?></summary>
                <div class="drawer__sub">
                    <?php foreach ($children as $child) : ?>
                        <a href="<?php echo esc_url($child->url); ?>"><?php echo esc_html($child->title); ?></a>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php else : ?>
            <a href="<?php echo esc_url($item->url); ?>" class="drawer__link"><?php echo esc_html($item->title); ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
  </nav>
  
  <div class="drawer__footer">
      <?php if ($is_logged_in) : ?>
          <a href="<?php echo esc_url($order_form_url); ?>" class="drawer__cta">ثبت پروژه</a>
          <a href="<?php echo esc_url($account_url); ?>" class="drawer__cta drawer__cta--ghost">پنل کاربری</a>
      <?php else : ?>
          <a href="<?php echo esc_url($login_url); ?>" class="drawer__cta">ورود / ثبت‌نام</a>
      <?php endif; ?>
  </div>
</aside>

<main class="site-main">