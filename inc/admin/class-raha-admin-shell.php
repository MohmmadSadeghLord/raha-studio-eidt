<?php
if (!defined('ABSPATH')) exit;

class Raha_Admin_Shell {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function register_menu() {
        add_menu_page(
            'پنل مدیریت استودیو',
            'استودیو رها',
            'manage_options',
            'raha-dashboard',
            [__CLASS__, 'render_app'],
            'dashicons-chart-area',
            2
        );
    }

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_raha-dashboard') return;

        /* Fonts from CDN */
        wp_enqueue_style(
            'raha-vazirmatn',
            'https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css',
            [],
            null
        );
        wp_enqueue_style(
            'raha-sahel',
            'https://cdn.jsdelivr.net/gh/rastikerdar/sahel-font@v3.4.0/dist/font-face.css',
            [],
            null
        );

        /* Admin Shell CSS — after fonts */
        wp_enqueue_style(
            'raha-admin-shell',
            RAHA_URI . '/assets/css/admin-shell.css',
            ['raha-vazirmatn', 'raha-sahel'],
            RAHA_VERSION
        );

        /* JS */
        wp_enqueue_script(
            'raha-admin-shell',
            RAHA_URI . '/assets/js/admin-shell.js',
            [],
            RAHA_VERSION,
            true
        );

        wp_localize_script('raha-admin-shell', 'RahaAdmin', [
            'restUrl' => esc_url_raw(rest_url('raha/v1/')),
            'nonce'   => wp_create_nonce('wp_rest'),
            'homeUrl' => home_url('/'),
            'adminUrl'=> admin_url(),
            'roles'   => wp_get_current_user()->roles,
            'userName'=> wp_get_current_user()->display_name
        ]);
    }

    public static function render_app() {
        if (!current_user_can('manage_options')) {
            wp_die('شما دسترسی به این بخش ندارید.');
        }
        ?>
        <a href="#raha-view-container" class="raha-skip-link">پرش به محتوا</a>
        <div id="raha-admin-app" dir="rtl" data-theme="light">
            <aside class="raha-sidebar">
                <div class="raha-sidebar-header">
                    <div class="raha-logo">رها</div>
                    <span class="raha-badge-pro">پنل مدیریت</span>
                </div>
                <nav class="raha-nav">
                    <a href="#dashboard" class="raha-nav-item active" data-route="dashboard">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-home"></use></svg> داشبورد
                    </a>
                    <a href="#orders" class="raha-nav-item" data-route="orders">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-shopping-bag"></use></svg> سفارش‌ها
                    </a>
                    <a href="#invoices" class="raha-nav-item" data-route="invoices">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-credit-card"></use></svg> مالی و فاکتورها
                    </a>
                    <a href="#tickets" class="raha-nav-item" data-route="tickets">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-message-circle"></use></svg> پشتیبانی
                    </a>
                    <a href="#audit-logs" class="raha-nav-item" data-route="audit-logs">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-shield-check"></use></svg> لاگ امنیتی
                    </a>
                    <a href="#settings" class="raha-nav-item" data-route="settings">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-settings"></use></svg> تنظیمات
                    </a>
                    <!-- لینک جدید آزمایشگاه کامپوننت‌ها -->
                    <a href="#tools" class="raha-nav-item" data-route="tools">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-columns"></use></svg> آزمایشگاه کامپوننت‌ها
                    </a>
                </nav>
            </aside>

            <main class="raha-main">
                <header class="raha-topbar">
                    <button class="raha-mobile-toggle" aria-label="منو">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-menu"></use></svg>
                    </button>
                    <div class="raha-breadcrumb" id="raha-breadcrumb">داشبورد</div>
                    <div class="raha-topbar-actions">
                        <button class="raha-icon-btn" id="raha-search-trigger" aria-label="جستجو (Ctrl+K)">
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                            <span class="raha-shortcut">Ctrl+K</span>
                        </button>
                        <button class="raha-icon-btn" aria-label="اعلان‌ها">
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-bell"></use></svg>
                            <span class="raha-dot"></span>
                        </button>
                        <button class="raha-icon-btn" id="raha-theme-toggle" aria-label="حالت تاریک">
                            <svg class="raha-icon" aria-hidden="true"><use href="#icon-moon"></use></svg>
                        </button>
                    </div>
                </header>

                <div class="raha-content" id="raha-view-container" tabindex="-1">
                    <div class="raha-skeleton-wrapper" id="raha-loader">
                        <div class="raha-skeleton-card"></div>
                        <div class="raha-skeleton-card"></div>
                    </div>
                </div>
            </main>

            <div class="raha-drawer-overlay" id="raha-drawer-overlay"></div>
            <aside class="raha-drawer" id="raha-drawer">
                <div class="raha-drawer-header">
                    <h3 id="raha-drawer-title">جزئیات</h3>
                    <button class="raha-icon-btn" id="raha-drawer-close" aria-label="بستن">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-x"></use></svg>
                    </button>
                </div>
                <div class="raha-drawer-body" id="raha-drawer-content"></div>
            </aside>

            <div class="raha-palette-overlay" id="raha-palette-overlay">
                <div class="raha-palette">
                    <div class="raha-palette-input-wrapper">
                        <svg class="raha-icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                        <input type="text" id="raha-palette-input" placeholder="جستجوی سریع سفارش، مشتری یا صفحه..." autocomplete="off">
                    </div>
                    <ul class="raha-palette-results" id="raha-palette-results"></ul>
                </div>
            </div>

            <div class="raha-toasts" id="raha-toasts" role="status" aria-live="polite" aria-atomic="false"></div>

            <!-- ==================================================
                 SVG SPRITE — 40 icons (Feather/Lucide style)
                 ================================================== -->
            <svg width="0" height="0" aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden">
                <!-- 1-8: Existing -->
                <symbol id="icon-home" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></symbol>
                <symbol id="icon-shopping-bag" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></symbol>
                <symbol id="icon-credit-card" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></symbol>
                <symbol id="icon-message-circle" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></symbol>
                <symbol id="icon-menu" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></symbol>
                <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></symbol>
                <symbol id="icon-bell" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></symbol>
                <symbol id="icon-x" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></symbol>
                <!-- 9-32: Newly added -->
                <symbol id="icon-filter" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></symbol>
                <symbol id="icon-sort" viewBox="0 0 24 24"><path d="m21 16-4 4-4-4"></path><path d="M17 20V4"></path><path d="m3 8 4-4 4 4"></path><path d="M7 4v16"></path></symbol>
                <symbol id="icon-columns" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M9 3v18"></path><path d="M15 3v18"></path></symbol>
                <symbol id="icon-more-vertical" viewBox="0 0 24 24"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></symbol>
                <symbol id="icon-drag-handle" viewBox="0 0 24 24"><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="19" r="1"></circle></symbol>
                <symbol id="icon-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></symbol>
                <symbol id="icon-refresh" viewBox="0 0 24 24"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path></symbol>
                <symbol id="icon-upload-cloud" viewBox="0 0 24 24"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path><path d="M12 12v9"></path><path d="m16 16-4-4-4 4"></path></symbol>
                <symbol id="icon-keyboard" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"></rect><path d="M6 8h.001"></path><path d="M10 8h.001"></path><path d="M14 8h.001"></path><path d="M18 8h.001"></path><path d="M8 12h.001"></path><path d="M12 12h.001"></path><path d="M16 12h.001"></path><path d="M7 16h10"></path></symbol>
                <symbol id="icon-lock" viewBox="0 0 24 24"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></symbol>
                <symbol id="icon-shield-check" viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></symbol>
                <symbol id="icon-database" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M3 5V19A9 3 0 0 0 21 19V5"></path><path d="M3 12A9 3 0 0 0 21 12"></path></symbol>
                <symbol id="icon-server" viewBox="0 0 24 24"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"></rect><rect width="20" height="8" x="2" y="14" rx="2" ry="2"></rect><line x1="6" x2="6.01" y1="6" y2="6"></line><line x1="6" x2="6.01" y1="18" y2="18"></line></symbol>
                <symbol id="icon-activity" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></symbol>
                <symbol id="icon-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></symbol>
                <symbol id="icon-user-cog" viewBox="0 0 24 24"><circle cx="18" cy="15" r="3"></circle><circle cx="9" cy="7" r="4"></circle><path d="M10 15H6a4 4 0 0 0-4 4v2"></path><path d="m21.7 16.4-.9-.3"></path><path d="m15.2 13.9-.9-.3"></path><path d="m16.6 18.7.3-.9"></path><path d="m19.1 12.2.3-.9"></path><path d="m19.6 18.7-.4-1"></path><path d="m16.8 12.3-.4-1"></path><path d="m14.3 16.6 1-.4"></path><path d="m20.7 13.8 1-.4"></path></symbol>
                <symbol id="icon-list-checks" viewBox="0 0 24 24"><path d="m3 17 2 2 4-4"></path><path d="m3 7 2 2 4-4"></path><path d="M13 6h8"></path><path d="M13 12h8"></path><path d="M13 18h8"></path></symbol>
                <symbol id="icon-git-branch" viewBox="0 0 24 24"><line x1="6" x2="6" y1="3" y2="15"></line><circle cx="18" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><path d="M18 9a9 9 0 0 1-9 9"></path></symbol>
                <symbol id="icon-flag" viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" x2="4" y1="22" y2="15"></line></symbol>
                <symbol id="icon-eye-off" viewBox="0 0 24 24"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path><line x1="2" x2="22" y1="2" y2="22"></line></symbol>
                <symbol id="icon-user-x" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="17" x2="22" y1="8" y2="13"></line><line x1="22" x2="17" y1="8" y2="13"></line></symbol>
                <symbol id="icon-settings" viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></symbol>
                <symbol id="icon-log-out" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></symbol>
                <symbol id="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></symbol>
                <symbol id="icon-moon" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></symbol>
                <symbol id="icon-chevron-down" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"></path></symbol>
                <symbol id="icon-chevron-left" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"></path></symbol>
                <symbol id="icon-chevron-right" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"></path></symbol>
                <symbol id="icon-plus" viewBox="0 0 24 24"><path d="M5 12h14"></path><path d="M12 5v14"></path></symbol>
                <symbol id="icon-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"></path></symbol>
                <symbol id="icon-alert-triangle" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></symbol>
                <symbol id="icon-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></symbol>
            </svg>
        </div>
        <?php
    }
}