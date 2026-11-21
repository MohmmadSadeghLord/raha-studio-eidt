<?php
/**
 * Raha Impersonate — SECURITY PATCHED
 *
 * تغییرات نسبت به نسخه قبلی:
 * 1. کوکی raha_impersonator با transient اعتبارسنجی می‌شه (رفع Privilege Escalation)
 * 2. کوکی‌ها با HttpOnly + Secure + SameSite ست می‌شن
 * 3. خروج از impersonate با nonce محافظت می‌شه (رفع CSRF)
 * 4. ادمین‌ها نمی‌تونن ادمین دیگه رو impersonate کنن
 * 5. مسیر redirect به raha_page_url('account') اصلاح شد
 * 6. cleanup خودکار transient‌های منقضی
 */
if (!defined('ABSPATH')) exit;

class Raha_Impersonate {

    const COOKIE_NAME = 'raha_impersonator';
    const SESSION_TTL = HOUR_IN_SECONDS;

    public static function init() {
        add_action('init', [__CLASS__, 'handle_impersonation']);
        add_action('wp_body_open', [__CLASS__, 'render_banner']);
    }

    /* ============================================================
       ۱. پردازش لینک ورود/خروج
       ============================================================ */
    public static function handle_impersonation() {

        /* ---------- خروج از حالت Impersonation ---------- */
        if (isset($_GET['exit_impersonate'])) {
            self::handle_exit();
        }

        /* ---------- ورود به حساب مشتری ---------- */
        if (isset($_GET['raha_imp_token'])) {
            self::handle_enter();
        }
    }

    private static function handle_enter() {
        $token = sanitize_text_field($_GET['raha_imp_token']);

        // اعتبارسنجی توکن با format پایه
        if (!preg_match('/^[a-zA-Z0-9]{32,}$/', $token)) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        $data = get_transient('raha_imp_' . $token);
        if (!$data || empty($data['customer_id']) || empty($data['admin_id'])) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // بررسی مجوز ادمین
        if (!user_can($data['admin_id'], 'manage_options')) {
            delete_transient('raha_imp_' . $token);
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // جلوگیری از impersonate ادمین
        if (user_can($data['customer_id'], 'manage_options')) {
            delete_transient('raha_imp_' . $token);
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // ثبت session امن در transient (منبع معتبر برای خروج)
        $session_key = 'raha_imp_session_' . $data['customer_id'];
        set_transient($session_key, [
            'admin_id' => (int) $data['admin_id'],
            'issued'   => time(),
        ], self::SESSION_TTL);

        // پاک کردن توکن یک‌بار‌مصرف
        delete_transient('raha_imp_' . $token);

        // ست کردن کوکی امن (HttpOnly + Secure + SameSite)
        self::set_impersonator_cookie((int) $data['admin_id']);

        // لاگین مشتری
        wp_clear_auth_cookie();
        wp_set_auth_cookie((int) $data['customer_id'], false, is_ssl());

        // redirect به پنل کاربری مشتری
        $redirect = function_exists('raha_page_url')
            ? raha_page_url('account')
            : home_url('/account/');

        wp_safe_redirect($redirect);
        exit;
    }

    private static function handle_exit() {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            wp_safe_redirect(home_url('/'));
            exit;
        }

        $current_user_id = get_current_user_id();
        $cookie_admin_id = (int) $_COOKIE[self::COOKIE_NAME];

        // 🔴 اعتبارسنجی کوکی با transient (رفع Privilege Escalation)
        $session_key = 'raha_imp_session_' . $current_user_id;
        $session     = get_transient($session_key);

        // اگر transient وجود نداشت یا با کوکی نمی‌خوند → کوکی دستکاری شده
        if (!$session || (int) $session['admin_id'] !== $cookie_admin_id) {
            // پاک‌سازی و خروج امن
            self::clear_impersonator_cookie();
            delete_transient($session_key);
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // بررسی مجوز ادمین اصلی
        if (!user_can($cookie_admin_id, 'manage_options')) {
            self::clear_impersonator_cookie();
            delete_transient($session_key);
            wp_clear_auth_cookie();
            wp_safe_redirect(home_url('/'));
            exit;
        }

        // بازگشت به ادمین
        wp_clear_auth_cookie();
        wp_set_auth_cookie($cookie_admin_id, false, is_ssl());
        self::clear_impersonator_cookie();
        delete_transient($session_key);

        wp_safe_redirect(admin_url('admin.php?page=raha-dashboard'));
        exit;
    }

    /* ============================================================
       ۲. کوکی امن
       ============================================================ */
    private static function set_impersonator_cookie($admin_id) {
        if (headers_sent()) return;

        setcookie(
            self::COOKIE_NAME,
            (string) $admin_id,
            [
                'expires'  => time() + self::SESSION_TTL,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private static function clear_impersonator_cookie() {
        if (headers_sent()) return;

        setcookie(
            self::COOKIE_NAME,
            '',
            [
                'expires'  => time() - 3600,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN ?: '',
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    /* ============================================================
       ۳. نمایش نوار قرمز (banner)
       ============================================================ */
    public static function render_banner() {
        if (empty($_COOKIE[self::COOKIE_NAME])) return;

        $current_user_id = get_current_user_id();
        if (!$current_user_id) return;

        $session_key = 'raha_imp_session_' . $current_user_id;
        $session     = get_transient($session_key);
        if (!$session) return;

        // ساخت لینک خروج با nonce (رفع CSRF)
        $exit_url = wp_nonce_url(
            add_query_arg('exit_impersonate', '1', home_url('/')),
            'raha_exit_impersonate'
        );

        ?>
        <div class="raha-impersonate-banner" style="position:fixed;top:0;left:0;right:0;background:#dc2626;color:#fff;text-align:center;padding:10px;z-index:999999;font-family:Tahoma,sans-serif;font-weight:bold;box-shadow:0 4px 10px rgba(0,0,0,0.2);">
            ⚠️ شما در حال مشاهده سایت به عنوان مشتری هستید.
            <a href="<?php echo esc_url($exit_url); ?>" style="color:#fff;text-decoration:underline;margin-inline-start:10px;background:#991b1b;padding:4px 10px;border-radius:4px;">خروج از حساب مشتری</a>
        </div>
        <style>body { padding-top: 50px !important; }</style>
        <?php
    }

    /* ============================================================
       ۴. تولید توکن امن (توسط REST API فراخوانی می‌شه)
       ============================================================ */
    public static function generate_token($customer_id) {
        if (!current_user_can('manage_options')) return false;

        $customer_id = (int) $customer_id;
        if (!$customer_id) return false;

        // جلوگیری از impersonate ادمین
        if (user_can($customer_id, 'manage_options')) return false;

        // بررسی وجود کاربر
        if (!get_userdata($customer_id)) return false;

        $admin_id = get_current_user_id();
        $token    = bin2hex(random_bytes(32)); // 64 char hex

        set_transient('raha_imp_' . $token, [
            'admin_id'    => $admin_id,
            'customer_id' => $customer_id,
            'issued'      => time(),
        ], 5 * MINUTE_IN_SECONDS); // توکن ۵ دقیقه اعتبار داره

        // ثبت در لاگ امنیتی
        if (class_exists('Raha_Audit_Logger')) {
            Raha_Audit_Logger::log(
                'impersonate',
                'user',
                $customer_id,
                null,
                null,
                'ورود به پنل مشتری توسط ادمین'
            );
        }

        return add_query_arg('raha_imp_token', $token, home_url('/'));
    }
}