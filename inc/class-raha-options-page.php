<?php
if (!defined('ABSPATH')) exit;

class Raha_Options_Page {

    use Raha_Fields_Renderer;

    protected $id, $title, $menu_title, $icon, $position, $tabs, $prefix;

    public function __construct(array $args) {
        $this->id         = $args['id'];
        $this->title      = $args['title'];
        $this->menu_title = $args['menu_title'] ?? $args['title'];
        $this->icon       = $args['icon'] ?? 'dashicons-admin-generic';
        $this->position   = $args['position'] ?? 59;
        $this->tabs       = $args['tabs'] ?? [];
        $this->prefix     = 'raha_';

        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_color_picker']);
        add_action('wp_ajax_raha_options_save_section', [$this, 'ajax_save_section']);
    }

    public function register_menu() {
        add_menu_page(
            $this->title, $this->menu_title, 'manage_options',
            $this->id, [$this, 'render'], $this->icon, $this->position
        );
    }

    public function enqueue_color_picker($hook) {
        if (isset($_GET['page']) && $_GET['page'] === $this->id) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
        }
    }

    /**
     * AJAX: ذخیره یک بخش مشخص
     */
    public function ajax_save_section() {
        // دیباگ کامل
        $debug = [
            'user_id'     => get_current_user_id(),
            'can_manage'  => current_user_can('manage_options'),
            'post_keys'   => array_keys($_POST),
            'tab_received'    => $_POST['tab_id']    ?? '(missing)',
            'subtab_received' => $_POST['subtab_id'] ?? '(missing)',
        ];

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی ندارید', 'debug' => $debug]);
        }

        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'raha_options_ajax_save')) {
            wp_send_json_error(['message' => 'Nonce نامعتبر — صفحه را رفرش کنید', 'debug' => $debug]);
        }

        $tab_id    = sanitize_key($_POST['tab_id']    ?? '');
        $subtab_id = sanitize_key($_POST['subtab_id'] ?? '');

        // پیدا کردن تب و زیرتب
        $fields = null;
        foreach ($this->tabs as $tab) {
            if ($tab['id'] !== $tab_id) continue;

            if ($subtab_id && !empty($tab['sub_tabs'])) {
                foreach ($tab['sub_tabs'] as $sub) {
                    if ($sub['id'] === $subtab_id) {
                        $fields = $sub['fields'] ?? [];
                        break 2;
                    }
                }
                wp_send_json_error(['message' => 'زیرتب پیدا نشد: ' . $subtab_id, 'debug' => $debug]);
            }

            $fields = $tab['fields'] ?? [];
            break;
        }

        if ($fields === null) {
            wp_send_json_error(['message' => 'تب پیدا نشد: ' . $tab_id, 'debug' => $debug]);
        }

        $debug['fields_count'] = count($fields);
        $debug['saved'] = [];

        // ذخیره فیلدها
        foreach ($fields as $field) {
            if (empty($field['id'])) continue;

            $key  = $this->prefix . $field['id'];
            $type = $field['type'] ?? 'text';

            // رمز SMTP
            if ($field['id'] === 'smtp_pass') {
                $posted = $_POST[$key] ?? '';
                if ($posted === '') {
                    $debug['saved'][$key] = 'skipped (empty — keeping old)';
                    continue;
                }
                $encrypted = raha_encrypt(wp_unslash($posted));
                update_option($key, $encrypted);
                $debug['saved'][$key] = 'encrypted (' . strlen($encrypted) . ' chars)';
                continue;
            }

            $raw   = $_POST[$key] ?? null;
            $clean = $this->sanitize($raw, $type, $field);

            // چک‌باکس خاص: اگر unchecked بود، مقدار '0' بگذار
            if ($type === 'checkbox' && $raw === null) {
                $clean = 0;
            }

            update_option($key, $clean);
            $debug['saved'][$key] = is_array($clean)
                ? 'array(' . count($clean) . ')'
                : substr((string) $clean, 0, 60);
        }

        wp_cache_flush();

        wp_send_json_success([
            'message' => 'ذخیره شد. ' . count($debug['saved']) . ' فیلد به‌روزرسانی شد.',
            'debug'   => $debug,
        ]);
    }

    /**
     * رندر صفحه
     */
    public function render() {
        if (!current_user_can('manage_options')) return;
        ?>
        <div class="wrap raha-admin-wrap">

            <div class="raha-admin-header">
                <h1><?php echo esc_html($this->title); ?></h1>
                <span class="raha-admin-badge">AJAX v3</span>
            </div>

            <div class="raha-admin-breadcrumb" id="rahaBreadcrumb">
                <span>🏠 تنظیمات استودیو</span>
                <span class="bc-sep">›</span>
                <span class="bc-active" id="bcMainTab">عمومی</span>
                <span class="bc-sep" id="bcSepSub" style="display:none;">›</span>
                <span class="bc-active" id="bcSubTab" style="display:none;"></span>
            </div>

            <!-- نوار ابزار بالا: Save All -->
            <div class="raha-toolbar">
                <button type="button" id="rahaSaveAllBtn" class="raha-admin-btn-save" style="background:#16a34a;">
                    💾 ذخیره همه تب‌ها
                </button>
                <span id="rahaSaveAllStatus" class="raha-toolbar-status"></span>
            </div>

            <!-- تب‌های اصلی -->
            <nav class="raha-admin-tabs">
                <?php foreach ($this->tabs as $i => $tab) : ?>
                    <button type="button" class="raha-admin-tab<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            data-tab="<?php echo esc_attr($tab['id']); ?>"
                            data-label="<?php echo esc_attr($tab['label']); ?>">
                        <?php echo esc_html($tab['label']); ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <!-- پنل‌های تب -->
            <?php foreach ($this->tabs as $i => $tab) : ?>
                <div class="raha-admin-panel" data-panel="<?php echo esc_attr($tab['id']); ?>"
                     data-label="<?php echo esc_attr($tab['label']); ?>"<?php echo $i > 0 ? ' hidden' : ''; ?>>

                    <?php if (isset($tab['sub_tabs'])) : ?>
                        <nav class="raha-admin-subtabs">
                            <?php $sub_i = 0; foreach ($tab['sub_tabs'] as $sub) : ?>
                                <button type="button" class="raha-admin-subtab<?php echo $sub_i === 0 ? ' is-active' : ''; ?>"
                                        data-subtab="<?php echo esc_attr($sub['id']); ?>"
                                        data-label="<?php echo esc_attr($sub['label']); ?>">
                                    <?php echo esc_html($sub['label']); ?>
                                </button>
                            <?php $sub_i++; endforeach; ?>
                        </nav>

                        <?php $sub_i = 0; foreach ($tab['sub_tabs'] as $sub) : ?>
                            <div class="raha-admin-subpanel" data-subpanel="<?php echo esc_attr($sub['id']); ?>"<?php echo $sub_i > 0 ? ' hidden' : ''; ?>>

                                <?php if (!empty($sub['render_callback']) && is_callable($sub['render_callback'])) : ?>
                                    <?php call_user_func($sub['render_callback'], $this); ?>
                                <?php elseif (!empty($sub['fields']) && is_array($sub['fields'])) : ?>
                                    <?php foreach ($sub['fields'] as $field) :
                                        if (empty($field['id'])) continue;
                                        $option_key = $this->prefix . $field['id'];
                                        $value      = get_option($option_key, $field['default'] ?? '');
                                    ?>
                                        <div class="raha-admin-field">
                                            <label class="raha-admin-label"><?php echo esc_html($field['label'] ?? $field['id']); ?></label>
                                            <?php if (!empty($field['desc'])) : ?><p class="raha-admin-desc"><?php echo esc_html($field['desc']); ?></p><?php endif; ?>
                                            <?php $this->render_input($field, $option_key, $value); ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- دکمه ذخیره این زیرتب -->
                                    <div class="raha-section-footer">
                                        <button type="button" class="raha-section-save"
                                                data-tab="<?php echo esc_attr($tab['id']); ?>"
                                                data-subtab="<?php echo esc_attr($sub['id']); ?>"
                                                data-nonce="<?php echo esc_attr(wp_create_nonce('raha_options_ajax_save')); ?>">
                                            💾 ذخیره این بخش
                                        </button>
                                        <span class="raha-section-status"></span>
                                    </div>
                                <?php else : ?>
                                    <p style="padding:16px;color:#999;">این بخش فیلدی برای ذخیره ندارد (فقط محتوا نمایش می‌دهد).</p>
                                <?php endif; ?>
                            </div>
                        <?php $sub_i++; endforeach; ?>

                    <?php else : ?>
                        <?php if (!empty($tab['render_callback']) && is_callable($tab['render_callback'])) : ?>
                            <?php call_user_func($tab['render_callback'], $this); ?>
                        <?php elseif (!empty($tab['fields']) && is_array($tab['fields'])) : ?>
                            <?php foreach ($tab['fields'] as $field) :
                                if (empty($field['id'])) continue;
                                $option_key = $this->prefix . $field['id'];
                                $value      = get_option($option_key, $field['default'] ?? '');
                            ?>
                                <div class="raha-admin-field">
                                    <label class="raha-admin-label"><?php echo esc_html($field['label'] ?? $field['id']); ?></label>
                                    <?php if (!empty($field['desc'])) : ?><p class="raha-admin-desc"><?php echo esc_html($field['desc']); ?></p><?php endif; ?>
                                    <?php $this->render_input($field, $option_key, $value); ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="raha-section-footer">
                                <button type="button" class="raha-section-save"
                                        data-tab="<?php echo esc_attr($tab['id']); ?>"
                                        data-subtab=""
                                        data-nonce="<?php echo esc_attr(wp_create_nonce('raha_options_ajax_save')); ?>">
                                    💾 ذخیره این بخش
                                </button>
                                <span class="raha-section-status"></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>

        <style>
            .raha-admin-wrap { font-family: 'Vazirmatn', system-ui, sans-serif; max-width: 1000px; margin: 32px auto; padding: 0 20px; direction: rtl; }
            .raha-admin-header { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
            .raha-admin-header h1 { font-size: 28px; font-weight: 900; color: #14110f; margin: 0; padding: 0; line-height: 1; }
            .raha-admin-badge { background: #facc15; border: 2px solid #14110f; border-radius: 6px; padding: 4px 12px; font-size: 12px; font-weight: 800; box-shadow: 2px 2px 0 #14110f; }
            .raha-admin-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #64748b; margin-bottom: 24px; }
            .raha-admin-breadcrumb .bc-active { color: #0c4a6e; font-weight: 900; }
            .raha-admin-breadcrumb .bc-sep { color: #cbd5e1; font-size: 16px; }
            .raha-toolbar { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding: 16px; background: #f0fdf4; border: 2px solid #16a34a; border-radius: 10px; }
            .raha-toolbar-status { font-size: 13px; font-weight: 800; color: #16a34a; }
            .raha-admin-tabs { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
            .raha-admin-tab { background: #fff; color: #14110f; border: 2px solid #14110f; border-radius: 8px; padding: 10px 20px; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.15s ease; box-shadow: 3px 3px 0 #14110f; }
            .raha-admin-tab:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #14110f; }
            .raha-admin-tab.is-active { background: #facc15; }
            .raha-admin-subtabs { display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; flex-wrap: wrap; }
            .raha-admin-subtab { background: transparent; color: #64748b; border: none; padding: 8px 16px; font-size: 14px; font-weight: 800; cursor: pointer; border-radius: 6px; transition: all 0.2s; }
            .raha-admin-subtab:hover { background: #f1f5f9; color: #14110f; }
            .raha-admin-subtab.is-active { background: #0c4a6e; color: #fff; }
            .raha-admin-panel { background: #fff; border: 2px solid #14110f; border-radius: 10px; padding: 32px; box-shadow: 6px 6px 0 #14110f; margin-bottom: 24px; }
            .raha-admin-field { margin-bottom: 28px; padding-bottom: 28px; border-bottom: 2px dashed rgba(20,17,15,0.1); }
            .raha-admin-field:last-child { border-bottom: 0; margin-bottom: 0; padding-bottom: 0; }
            .raha-admin-label { display: block; font-size: 14px; font-weight: 800; color: #14110f; margin-bottom: 8px; }
            .raha-admin-desc { font-size: 12px; color: #666; margin: 0 0 12px 0; }
            .raha-admin-wrap input[type="text"], .raha-admin-wrap input[type="url"], .raha-admin-wrap input[type="email"], .raha-admin-wrap input[type="tel"], .raha-admin-wrap input[type="number"], .raha-admin-wrap input[type="password"], .raha-admin-wrap input[type="date"], .raha-admin-wrap textarea, .raha-admin-wrap select {
                width: 100%; max-width: 100%; background: #f8fafc !important; border: 2px solid #14110f !important; border-radius: 6px !important; padding: 12px 14px !important; font-family: 'Vazirmatn', sans-serif !important; font-size: 14px !important; font-weight: 600 !important; color: #14110f !important; box-shadow: none !important; transition: all 0.2s ease !important; height: auto !important;
            }
            .raha-admin-wrap textarea { resize: vertical; min-height: 120px; font-family: ui-monospace, monospace !important; direction: ltr; text-align: left; }
            .raha-admin-wrap input:focus, .raha-admin-wrap textarea:focus, .raha-admin-wrap select:focus { outline: none !important; background: #fff !important; transform: translate(-1px, -1px); box-shadow: 2px 2px 0 #14110f !important; }
            .raha-admin-btn-save { display: inline-flex; align-items: center; justify-content: center; background: #0c4a6e; color: #fff; border: 2px solid #14110f; border-radius: 8px; padding: 14px 28px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.15s ease; box-shadow: 4px 4px 0 #14110f; }
            .raha-admin-btn-save:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0 #14110f; }
            .raha-admin-btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

            /* دکمه ذخیره بخش + وضعیت */
            .raha-section-footer { margin-top: 32px; padding-top: 24px; border-top: 2px dashed rgba(20,17,15,0.15); display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
            .raha-section-save { display: inline-flex; align-items: center; gap: 6px; background: #0c4a6e; color: #fff; border: 2px solid #14110f; border-radius: 8px; padding: 10px 22px; font-size: 14px; font-weight: 900; cursor: pointer; transition: all 0.15s ease; box-shadow: 3px 3px 0 #14110f; font-family: inherit; }
            .raha-section-save:hover:not(:disabled) { transform: translate(-2px, -2px); box-shadow: 5px 5px 0 #14110f; background: #0a3d5c; }
            .raha-section-save:disabled { opacity: 0.6; cursor: wait; }
            .raha-section-save.is-success { background: #16a34a !important; }
            .raha-section-save.is-error { background: #dc2626 !important; }
            .raha-section-status { font-size: 13px; font-weight: 800; }
            .raha-section-status.success { color: #16a34a; }
            .raha-section-status.error { color: #dc2626; }
        </style>

        <script>
        jQuery(function($){
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.raha-color-picker').wpColorPicker();
            }

            // ================= تب‌ها =================
            var $hiddenTab    = $('#rahaActiveTab');
            var $hiddenSubtab = $('#rahaActiveSubtab');
            var urlParams     = new URLSearchParams(window.location.search);
            var urlTab        = urlParams.get('tab');
            var urlSubtab     = urlParams.get('subtab');

            function activateTab(tabId) {
                var $btn = $('.raha-admin-tab[data-tab="' + tabId + '"]');
                if (!$btn.length) return false;
                $('.raha-admin-tab').removeClass('is-active');
                $btn.addClass('is-active');
                $('.raha-admin-panel').prop('hidden', true);
                $('.raha-admin-panel[data-panel="' + tabId + '"]').prop('hidden', false);
                $('#bcMainTab').text($btn.data('label'));
                $('#bcSubTab, #bcSepSub').hide();
                var $firstSub = $('.raha-admin-panel[data-panel="' + tabId + '"] .raha-admin-subtab').first();
                if ($firstSub.length) $firstSub.trigger('click');
                return true;
            }

            function activateSubtab(subtabId) {
                var $btn = $('.raha-admin-subtab[data-subtab="' + subtabId + '"]');
                if (!$btn.length) return false;
                var $parentPanel = $btn.closest('.raha-admin-panel');
                $parentPanel.find('.raha-admin-subtab').removeClass('is-active');
                $btn.addClass('is-active');
                $parentPanel.find('.raha-admin-subpanel').prop('hidden', true);
                $parentPanel.find('.raha-admin-subpanel[data-subpanel="' + subtabId + '"]').prop('hidden', false);
                $('#bcSubTab').text($btn.data('label')).show();
                $('#bcSepSub').show();
                return true;
            }

            $('.raha-admin-tab').on('click', function(){ activateTab($(this).data('tab')); });
            $('.raha-admin-subtab').on('click', function(){ activateSubtab($(this).data('subtab')); });

            if (urlTab && activateTab(urlTab)) {
                if (urlSubtab) activateSubtab(urlSubtab);
            } else {
                $('.raha-admin-tab').first().trigger('click');
            }

            // ================= ذخیره یک بخش (AJAX) =================
            function collectSectionData($container, tabId, subtabId, nonce) {
                var data = {
                    action:    'raha_options_save_section',
                    tab_id:    tabId,
                    subtab_id: subtabId || '',
                    nonce:     nonce,
                };

                $container.find('input, textarea, select').each(function() {
                    var $el  = $(this);
                    var name = $el.attr('name');
                    if (!name) return;
                    if ($el.closest('template').length) return; // نادیده گرفتن template ریپیتر
                    if ($el.hasClass('raha-repeater-add')) return;

                    var type = $el.attr('type');

                    if (type === 'checkbox') {
                        data[name] = $el.is(':checked') ? '1' : '';
                    } else if (type === 'radio') {
                        if ($el.is(':checked')) data[name] = $el.val();
                    } else if (type === 'file') {
                        // نادیده بگیر — فایل‌ها با فرم AJAX جدا مدیریت می‌شوند
                    } else {
                        data[name] = $el.val();
                    }
                });

                return data;
            }

            function saveSection($btn, $container, $status) {
                var tabId    = $btn.data('tab');
                var subtabId = $btn.data('subtab') || '';
                var nonce    = $btn.data('nonce');

                var data = collectSectionData($container, tabId, subtabId, nonce);

                $btn.prop('disabled', true)
                    .removeClass('is-success is-error')
                    .data('origText', $btn.text())
                    .text('⏳ در حال ذخیره...');
                $status.text('').removeClass('success error');

                $.ajax({
                    url:  ajaxurl,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    timeout: 20000,
                })
                .done(function(resp) {
                    if (resp && resp.success) {
                        var msg = (resp.data && resp.data.message) || 'ذخیره شد';
                        $btn.addClass('is-success').text('✅ ذخیره شد');
                        $status.addClass('success').text(msg);
                        console.log('✅ Save success:', resp.data);
                    } else {
                        var msg = (resp && resp.data && resp.data.message) || 'خطای نامشخص';
                        $btn.addClass('is-error').text('❌ خطا');
                        $status.addClass('error').text(msg);
                        console.error('❌ Save error:', resp);
                    }
                })
                .fail(function(xhr, status, err) {
                    var msg = 'خطای شبکه: ' + xhr.status + ' — ' + status;
                    $btn.addClass('is-error').text('❌ خطا');
                    $status.addClass('error').text(msg);
                    console.error('❌ AJAX fail:', xhr, status, err);
                })
                .always(function() {
                    setTimeout(function() {
                        $btn.prop('disabled', false)
                            .removeClass('is-success is-error')
                            .text($btn.data('origText') || '💾 ذخیره این بخش');
                        $status.text('').removeClass('success error');
                    }, 3000);
                });
            }

            $(document).on('click', '.raha-section-save', function(e) {
                e.preventDefault();
                var $btn   = $(this);
                var $status = $btn.siblings('.raha-section-status');

                var subtabId = $btn.data('subtab') || '';
                var $container;
                if (subtabId) {
                    $container = $btn.closest('.raha-admin-subpanel');
                } else {
                    $container = $btn.closest('.raha-admin-panel');
                }

                if (!$container.length) {
                    alert('بخش پیدا نشد. یک بار صفحه را رفرش کنید.');
                    return;
                }

                saveSection($btn, $container, $status);
            });

            // ================= ذخیره همه تب‌ها =================
            $('#rahaSaveAllBtn').on('click', function() {
                var $btn    = $(this);
                var $status = $('#rahaSaveAllStatus');
                var $allBtns = $('.raha-section-save:visible');
                var total = $allBtns.length;
                var done  = 0;

                if (total === 0) {
                    $status.text('هیچ بخشی برای ذخیره پیدا نشد').addClass('error');
                    return;
                }

                $btn.prop('disabled', true).text('⏳ در حال ذخیره همه...');
                $status.text('در حال ذخیره ' + total + ' بخش...').removeClass('success error');

                // به ترتیب همه دکمه‌ها را کلیک کن
                function saveNext() {
                    if (done >= total) {
                        $btn.prop('disabled', false).text('💾 ذخیره همه تب‌ها');
                        $status.text('✅ همه بخش‌ها ذخیره شدند').addClass('success');
                        setTimeout(function(){ $status.text('').removeClass('success error'); }, 4000);
                        return;
                    }
                    var $nextBtn = $allBtns.eq(done);
                    done++;
                    $nextBtn.trigger('click');
                    setTimeout(saveNext, 700);
                }
                saveNext();
            });
        });
        </script>
        <?php
    }
}

function raha_opt($key, $default = '') {
    return get_option('raha_' . $key, $default);
}