<?php
/* Template Name: پنل کاربر */
if (!defined('ABSPATH')) exit;

raha_require_login();

$user    = wp_get_current_user();
$orders  = raha_get_user_orders();
$tab     = sanitize_key($_GET['tab'] ?? 'dashboard');
$allowed = ['dashboard', 'orders', 'invoices', 'tickets', 'profile'];
if (!in_array($tab, $allowed, true)) $tab = 'dashboard';

get_header();
?>
<div class="raha-panel">
    <div class="raha-container raha-panel__layout">

        <aside class="raha-panel__side">
            <div class="raha-panel__user">
                <div class="raha-panel__avatar"><?php echo esc_html(mb_substr($user->display_name, 0, 1)); ?></div>
                <div>
                    <strong><?php echo esc_html($user->display_name); ?></strong>
                    <small dir="ltr"><?php echo esc_html($user->user_email); ?></small>
                </div>
            </div>

            <nav class="raha-panel__nav">
                <?php
                $items = [
                    'dashboard' => ['داشبورد', '🏠'],
                    'orders'    => ['سفارش‌ها', '📦'],
                    'invoices'  => ['فاکتورها', '💳'],
                    'tickets'   => ['تیکت‌ها', '💬'],
                    'profile'   => ['پروفایل', '👤'],
                ];
                foreach ($items as $key => $item) : ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', $key, raha_page_url('account'))); ?>"
                       class="<?php echo $tab === $key ? 'is-active' : ''; ?>">
                        <span><?php echo $item[1]; ?></span><?php echo esc_html($item[0]); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="raha-panel__logout">خروج از حساب</a>
        </aside>

        <main class="raha-panel__main">
            <?php
            switch ($tab) {
                case 'orders':    get_template_part('parts/account/tab', 'orders');    break;
                case 'invoices':  get_template_part('parts/account/tab', 'invoices');  break;
                case 'tickets':   get_template_part('parts/account/tab', 'tickets');   break;
                case 'profile':   get_template_part('parts/account/tab', 'profile');   break;
                default:          get_template_part('parts/account/tab', 'dashboard');
            }
            ?>
        </main>

    </div>
</div>
<?php get_footer();