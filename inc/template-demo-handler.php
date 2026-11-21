<?php
/**
 * Raha Template Demo Handler
 * آپلود HTML/ZIP، استخراج، تصویر شاخص خودکار، iframe نمایش
 */
if (!defined('ABSPATH')) exit;

class Raha_Template_Demo {

    const UPLOAD_DIR  = 'raha-templates';
    const META_ZIP    = 'demo_zip_file';       // ID فایل آپلودشده
    const META_ENTRY  = 'demo_entry_file';     // مسیر نسبی index.html
    const META_FOLDER = 'demo_folder';         // مسیر پوشه استخراج‌شده

    public static function init() {
        add_action('add_meta_boxes',               [__CLASS__, 'add_meta_box']);
        add_action('save_post_raha_template',      [__CLASS__, 'handle_upload'], 10, 2);
        add_action('before_delete_post',           [__CLASS__, 'cleanup']);
        add_action('wp_ajax_raha_gen_screenshot',  [__CLASS__, 'ajax_screenshot']);
        add_action('admin_enqueue_scripts',        [__CLASS__, 'admin_assets']);
    }

    /* ============================================================
       ۱. متاباکس آپلود
       ============================================================ */
    public static function add_meta_box() {
        add_meta_box(
            'raha_demo_upload',
            '🎨 فایل دموی قالب (HTML یا ZIP)',
            [__CLASS__, 'render_box'],
            'raha_template',
            'normal',
            'high'
        );
    }

    public static function render_box($post) {
        wp_nonce_field('raha_demo_upload', 'raha_demo_nonce');

        $zip_id   = (int) get_post_meta($post->ID, self::META_ZIP, true);
        $zip_url  = $zip_id ? wp_get_attachment_url($zip_id) : '';
        $entry    = get_post_meta($post->ID, self::META_ENTRY, true);
        $demo_url = get_post_meta($post->ID, 'demo_url', true);
        $thumb_id = get_post_thumbnail_id($post->ID);
        ?>

        <div class="raha-demo-upload">
            <p style="font-size:13px;color:#666;">
                فایل <strong>ZIP</strong> کامل قالب (شامل index.html + CSS + JS + تصاویر) یا یک فایل <strong>HTML</strong> مستقل آپلود کن.
                فایل به‌صورت خودکار استخراج می‌شه و index.html به‌عنوان صفحه اصلی دمو انتخاب می‌شه.
            </p>

            <p>
                <label for="raha_demo_file" style="display:block;font-weight:700;margin-bottom:6px;">
                    فایل ZIP یا HTML
                </label>
                <input type="file" name="raha_demo_file" id="raha_demo_file" accept=".zip,.html">
                <?php if ($zip_url) : ?>
                    <span style="display:inline-block;margin-inline-start:8px;font-size:12px;color:#16a34a;">
                        ✅ فایل فعلی: <a href="<?php echo esc_url($zip_url); ?>" target="_blank"><?php echo esc_html(basename($zip_url)); ?></a>
                    </span>
                    <br>
                    <label style="font-size:12px;margin-top:6px;display:inline-block;">
                        <input type="checkbox" name="raha_demo_remove" value="1"> حذف فایل فعلی
                    </label>
                <?php endif; ?>
            </p>

            <?php if ($entry) : ?>
                <div style="padding:10px 12px;background:#f0f9ff;border:1px solid #0c4a6e;border-radius:6px;margin:12px 0;">
                    <p style="margin:0;font-size:12px;">
                        📄 فایل ورودی: <code><?php echo esc_html($entry); ?></code>
                    </p>
                    <?php if ($demo_url) : ?>
                        <p style="margin:4px 0 0;font-size:12px;">
                            🔗 آدرس دمو: <a href="<?php echo esc_url($demo_url); ?>" target="_blank" dir="ltr"><?php echo esc_html($demo_url); ?></a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #ddd;">
                <p style="margin:0 0 8px;font-weight:700;">🖼️ تصویر شاخص خودکار</p>
                <p style="font-size:12px;color:#666;margin:0 0 10px;">
                    بعد از آپلود، روی دکمه بزن تا از آدرس دمو اسکرین‌شات گرفته بشه و به‌عنوان تصویر شاخص تنظیم بشه.
                </p>
                <button type="button" class="button" id="raha-gen-screenshot"
                        data-post-id="<?php echo esc_attr($post->ID); ?>"
                        <?php echo ($entry ? '' : 'disabled'); ?>>
                    📸 دریافت اسکرین‌شات خودکار
                </button>
                <span id="raha-screenshot-msg" style="margin-inline-start:10px;font-size:12px;"></span>

                <?php if ($thumb_id) : ?>
                    <div style="margin-top:10px;">
                        <strong style="font-size:12px;">تصویر شاخص فعلی:</strong>
                        <div style="margin-top:6px;">
                            <?php echo wp_get_attachment_image($thumb_id, 'medium', false, ['style' => 'max-width:240px;border:1px solid #ddd;border-radius:4px;']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        jQuery(function($){
            $('#raha-gen-screenshot').on('click', function(){
                var btn = $(this);
                var msg = $('#raha-screenshot-msg');
                if (!confirm('از آدرس دمو اسکرین‌شات گرفته بشه؟ (ممکنه ۱۰-۳۰ ثانیه طول بکشه)')) return;

                btn.prop('disabled', true);
                msg.css('color', '#0c4a6e').text('⏳ در حال دریافت...');

                $.post(ajaxurl, {
                    action: 'raha_gen_screenshot',
                    post_id: btn.data('post-id'),
                    nonce: '<?php echo esc_js(wp_create_nonce('raha_gen_screenshot')); ?>'
                }, function(res){
                    if (res.success) {
                        msg.css('color','#16a34a').text('✅ انجام شد! صفحه را رفرش کن.');
                    } else {
                        msg.css('color','#dc2626').text('❌ ' + (res.data && res.data.message || 'خطا'));
                        btn.prop('disabled', false);
                    }
                }).fail(function(){
                    msg.css('color','#dc2626').text('❌ خطای شبکه');
                    btn.prop('disabled', false);
                });
            });
        });
        </script>

        <style>
            .raha-demo-upload input[type="file"] {
                background: #fff;
                border: 2px solid #14110f;
                border-radius: 6px;
                padding: 6px 10px;
                font-size: 13px;
            }
        </style>
        <?php
    }

    /* ============================================================
       ۲. پردازش آپلود
       ============================================================ */
    public static function handle_upload($post_id, $post) {
        if (!isset($_POST['raha_demo_nonce'])) return;
        if (!wp_verify_nonce($_POST['raha_demo_nonce'], 'raha_demo_upload')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type !== 'raha_template') return;

        /* حذف فایل فعلی */
        if (!empty($_POST['raha_demo_remove'])) {
            self::delete_demo_files($post_id);
            delete_post_meta($post_id, self::META_ZIP);
            delete_post_meta($post_id, self::META_ENTRY);
            delete_post_meta($post_id, self::META_FOLDER);
            delete_post_meta($post_id, 'demo_url');
        }

        /* آپلود فایل جدید */
        if (empty($_FILES['raha_demo_file']['name'])) return;

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $file = $_FILES['raha_demo_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['zip', 'html', 'htm'], true)) {
            self::admin_notice('فرمت فایل باید ZIP یا HTML باشد.', 'error');
            return;
        }

        // آپلود فایل به رسانه
        $attachment_id = media_handle_upload('raha_demo_file', $post_id);
        if (is_wp_error($attachment_id)) {
            self::admin_notice('خطا در آپلود: ' . $attachment_id->get_error_message(), 'error');
            return;
        }

        // حذف فایل قبلی
        self::delete_demo_files($post_id);

        update_post_meta($post_id, self::META_ZIP, $attachment_id);

        // استخراج یا کپی
        if ($ext === 'zip') {
            $entry = self::extract_zip($post_id, get_attached_file($attachment_id));
        } else {
            $entry = self::copy_html($post_id, get_attached_file($attachment_id));
        }

        if ($entry === false) {
            self::admin_notice('خطا در استخراج فایل.', 'error');
            return;
        }

        // ذخیره فایل ورودی
        update_post_meta($post_id, self::META_ENTRY, $entry['path']);
        update_post_meta($post_id, self::META_FOLDER, $entry['folder']);

        // تنظیم demo_url اگر خالی بود
        $current_demo = get_post_meta($post_id, 'demo_url', true);
        if (!$current_demo) {
            update_post_meta($post_id, 'demo_url', $entry['url']);
        }

        self::admin_notice('فایل با موفقیت آپلود و استخراج شد.', 'success');
    }

    /* ============================================================
       ۳. استخراج ZIP
       ============================================================ */
    private static function extract_zip($post_id, $zip_path) {
        if (!class_exists('ZipArchive')) {
            self::admin_notice('ZipArchive در PHP فعال نیست.', 'error');
            return false;
        }

        $uploads = wp_upload_dir();
        $folder  = trailingslashit($uploads['basedir']) . self::UPLOAD_DIR . '/tpl-' . $post_id;
        $folder_url = trailingslashit($uploads['baseurl']) . self::UPLOAD_DIR . '/tpl-' . $post_id;

        // ساخت پوشه
        if (!wp_mkdir_p($folder)) {
            self::admin_notice('امکان ساخت پوشه نیست.', 'error');
            return false;
        }

        // محافظت PHP
        file_put_contents($folder . '/.htaccess', "php_flag engine off\n<FilesMatch \"\\.php$\">\n  Deny from all\n</FilesMatch>");

        // استخراج
        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) return false;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            // Zip Slip prevention
            if (strpos($name, '..') !== false || strpos($name, "\0") !== false) continue;
            if (substr($name, -1) === '/') continue; // پوشه
            // فایل‌های مخفی و php رو رد کن
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['php', 'phtml', 'phar', 'exe', 'sh', 'bat'], true)) continue;

            $target = $folder . '/' . $name;
            wp_mkdir_p(dirname($target));
            file_put_contents($target, $zip->getFromIndex($i));
        }

        $zip->close();

        // پیدا کردن index.html یا اولین HTML
        $entry = self::find_entry_html($folder);
        if (!$entry) {
            self::admin_notice('فایل index.html در ZIP پیدا نشد.', 'error');
            return false;
        }

        return [
            'path'   => $entry,
            'folder' => $folder,
            'url'    => trailingslashit($folder_url) . $entry,
        ];
    }

    /* ============================================================
       ۴. کپی HTML تکی
       ============================================================ */
    private static function copy_html($post_id, $html_path) {
        $uploads = wp_upload_dir();
        $folder  = trailingslashit($uploads['basedir']) . self::UPLOAD_DIR . '/tpl-' . $post_id;
        $folder_url = trailingslashit($uploads['baseurl']) . self::UPLOAD_DIR . '/tpl-' . $post_id;

        if (!wp_mkdir_p($folder)) return false;
        file_put_contents($folder . '/.htaccess', "php_flag engine off");

        $dest = $folder . '/index.html';
        if (!copy($html_path, $dest)) return false;

        return [
            'path'   => 'index.html',
            'folder' => $folder,
            'url'    => trailingslashit($folder_url) . 'index.html',
        ];
    }

    /* ============================================================
       ۵. پیدا کردن index.html
       ============================================================ */
    private static function find_entry_html($folder) {
        if (file_exists($folder . '/index.html')) return 'index.html';
        if (file_exists($folder . '/index.htm'))  return 'index.htm';

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['html', 'htm'], true)) {
                return str_replace($folder . '/', '', $file->getPathname());
            }
        }
        return false;
    }

    /* ============================================================
       ۶. اسکرین‌شات خودکار (mShots)
       ============================================================ */
    public static function ajax_screenshot() {
        if (!check_ajax_referer('raha_gen_screenshot', 'nonce', false)) {
            wp_send_json_error(['message' => 'نشست منقضی']);
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی ندارید']);
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id || get_post_type($post_id) !== 'raha_template') {
            wp_send_json_error(['message' => 'پست نامعتبر']);
        }

        $demo_url = get_post_meta($post_id, 'demo_url', true);
        if (!$demo_url) wp_send_json_error(['message' => 'آدرس دمو تنظیم نشده']);

        // mShots (رایگان، بدون API key)
        $shot_url = 'https://s.wordpress.com/mshots/v1/' . rawurlencode($demo_url) . '?w=1200&h=800';

        // اولین بار: درخواست تولید
        $response = wp_remote_get($shot_url, ['timeout' => 25]);
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => 'خطا در دریافت اسکرین‌شات']);
        }

        $body = wp_remote_retrieve_body($response);
        if (strlen($body) < 5000) {
            // هنوز آماده نیست — دوباره امتحان کن بعد از ۵ ثانیه
            sleep(5);
            $response = wp_remote_get($shot_url, ['timeout' => 25]);
            $body = wp_remote_retrieve_body($response);
        }

        if (strlen($body) < 5000) {
            wp_send_json_error(['message' => 'اسکرین‌شات آماده نشد — دوباره تلاش کن']);
        }

        // ذخیره به‌عنوان attachment
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = wp_upload_bits('demo-screenshot-' . $post_id . '.jpg', null, $body);
        if (!empty($upload['error'])) {
            wp_send_json_error(['message' => 'خطا در ذخیره تصویر']);
        }

        $attach_id = wp_insert_attachment([
            'post_mime_type' => 'image/jpeg',
            'post_title'     => 'Demo Screenshot — ' . get_the_title($post_id),
            'post_status'    => 'inherit',
        ], $upload['file'], $post_id);

        wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $upload['file']));
        set_post_thumbnail($post_id, $attach_id);

        wp_send_json_success(['message' => 'اسکرین‌شات تنظیم شد']);
    }

    /* ============================================================
       ۷. پاکسازی
       ============================================================ */
    public static function cleanup($post_id) {
        if (get_post_type($post_id) !== 'raha_template') return;
        self::delete_demo_files($post_id);
    }

    private static function delete_demo_files($post_id) {
        $folder = get_post_meta($post_id, self::META_FOLDER, true);
        if ($folder && is_dir($folder) && strpos($folder, 'raha-templates') !== false) {
            self::rrmdir($folder);
        }
    }

    private static function rrmdir($dir) {
        if (!is_dir($dir)) return;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }

    private static function admin_notice($msg, $type = 'info') {
        set_transient('raha_demo_notice_' . get_current_user_id(), ['msg' => $msg, 'type' => $type], 30);
    }

    public static function admin_assets() {
        // نیازی به asset خاص نیست — همه inline
    }
}

Raha_Template_Demo::init();