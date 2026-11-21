<?php
/**
 * Raha Fields — trait مشترک بین meta box و options page
 */
if (!defined('ABSPATH')) exit;

trait Raha_Fields_Renderer {

    protected function render_input($field, $name, $value) {
        $type = $field['type'] ?? 'text';

        switch ($type) {
            case 'text':
            case 'url':
            case 'email':
            case 'tel':
            case 'number':
            case 'date':
            case 'password':
                // برای جلوگیری از خطای مرورگر در لینک‌های داخلی (مانند #contact)، نوع url را به text تبدیل می‌کنیم
                $html_type = ($type === 'url') ? 'text' : $type;
                printf(
                    '<input type="%s" name="%s" value="%s" class="widefat raha-input" />',
                    esc_attr($html_type), esc_attr($name), esc_attr((string) $value)
                );
                break;

            case 'textarea':
                printf(
                    '<textarea name="%s" rows="4" class="widefat raha-input">%s</textarea>',
                    esc_attr($name), esc_textarea((string) $value)
                );
                break;

            case 'checkbox':
                printf(
                    '<label class="raha-checkbox"><input type="checkbox" name="%s" value="1" %s /> %s</label>',
                    esc_attr($name), checked((bool) $value, true, false),
                    esc_html($field['checkbox_label'] ?? 'فعال')
                );
                break;

            case 'select':
                echo '<select name="' . esc_attr($name) . '" class="widefat raha-input">';
                echo '<option value="">— انتخاب کنید —</option>';
                foreach (($field['options'] ?? []) as $k => $v) {
                    if (is_int($k)) $k = $v;
                    echo '<option value="' . esc_attr($k) . '"' . selected((string) $value, (string) $k, false) . '>' . esc_html($v) . '</option>';
                }
                echo '</select>';
                break;

            case 'image':
                $url = $value ? wp_get_attachment_image_url((int) $value, 'medium') : '';
                echo '<div class="raha-image' . ($url ? ' has-image' : '') . '">';
                echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '">';
                echo '<div class="raha-image-preview">' . ($url ? '<img src="' . esc_url($url) . '" alt="">' : '') . '</div>';
                echo '<div class="raha-image-actions">';
                echo '<button type="button" class="button raha-image-select">انتخاب تصویر</button>';
                echo '<button type="button" class="button raha-image-remove"' . ($url ? '' : ' style="display:none"') . '>حذف</button>';
                echo '</div></div>';
                break;

            case 'svg':
                printf(
                    '<textarea name="%s" rows="6" class="widefat raha-input" style="font-family:monospace; direction:ltr;">%s</textarea>',
                    esc_attr($name), esc_textarea((string) $value)
                );
                break;

            case 'repeater':
                $this->render_repeater($field, $name, is_array($value) ? $value : []);
                break;

            case 'color':
                printf(
                    '<input type="text" name="%s" value="%s" class="widefat raha-color-picker" style="max-width:120px;" />',
                    esc_attr($name), esc_attr((string) $value)
                );
                break;
        }
    }

    protected function render_repeater(array $field, $name, array $rows) {
        $subfields = $field['fields'] ?? [];
        $add_label = $field['add_label'] ?? '+ افزودن ردیف';

        echo '<div class="raha-repeater" data-name="' . esc_attr($name) . '">';
        echo '<div class="raha-repeater-rows">';

        foreach ($rows as $i => $row) {
            $this->render_repeater_row($subfields, $name, $i, $row, false);
        }

        echo '</div>';
        echo '<button type="button" class="button button-primary raha-repeater-add">' . esc_html($add_label) . '</button>';
        echo '<template class="raha-repeater-template">';
        $this->render_repeater_row($subfields, $name, '__INDEX__', [], true);
        echo '</template>';
        echo '</div>';
    }

    protected function render_repeater_row(array $subfields, $parent_name, $index, $row, $is_template) {
        echo '<div class="raha-repeater-row">';
        echo '<div class="raha-repeater-row-header">';
        echo '<span class="raha-repeater-handle" title="جابجایی">≡</span>';
        echo '<button type="button" class="raha-repeater-remove" title="حذف">×</button>';
        echo '</div><div class="raha-repeater-row-body">';

        foreach ($subfields as $sub) {
            $sub_id    = $sub['id'];
            $sub_name  = $parent_name . '[' . $index . '][' . $sub_id . ']';
            $sub_value = $is_template ? '' : ($row[$sub_id] ?? '');
            echo '<div class="raha-field raha-field--' . esc_attr($sub['type']) . '">';
            echo '<label class="raha-label">' . esc_html($sub['label'] ?? $sub_id) . '</label>';
            $this->render_input($sub, $sub_name, $sub_value);
            echo '</div>';
        }

        echo '</div></div>';
    }

    protected function sanitize($raw, $type, $field) {
        if ($raw === null) return null;

        switch ($type) {
            case 'text':     return sanitize_text_field($raw);
            case 'password': return sanitize_text_field($raw);
            case 'textarea': return wp_kses_post($raw); // اصلاح شد: حفظ تگ‌های HTML
            case 'url':      return esc_url_raw($raw);
            case 'email':    return sanitize_email($raw);
            case 'tel':      return sanitize_text_field($raw);
            case 'number':   return (int) $raw;
            case 'date':     return sanitize_text_field($raw);
            case 'checkbox': return $raw ? 1 : 0;
            case 'select':
            case 'radio':
                $allowed = array_map('strval', array_keys($field['options'] ?? []));
                return in_array((string) $raw, $allowed, true) ? sanitize_text_field($raw) : '';
            case 'color':    return sanitize_hex_color($raw) ?: '';
            case 'image':    return absint($raw);

            case 'svg':
                $allowed_svg = [
                    'svg' => ['viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true, 'xmlns' => true, 'aria-hidden' => true],
                    'path' => ['d' => true, 'fill' => true, 'fill-rule' => true, 'clip-rule' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true],
                    'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'ellipse' => ['cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true],
                    'polyline' => ['points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true],
                    'polygon' => ['points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true],
                    'g' => ['fill' => true, 'stroke' => true, 'transform' => true],
                ];
                return wp_kses($raw, $allowed_svg);

            case 'repeater':
                if (!is_array($raw)) return [];
                $out = [];
                $subfields = $field['fields'] ?? [];
                foreach ($raw as $row) {
                    if (!is_array($row)) continue;
                    $clean = [];
                    foreach ($subfields as $sub) {
                        $clean[$sub['id']] = $this->sanitize($row[$sub['id']] ?? null, $sub['type'] ?? 'text', $sub);
                    }
                    $has = false;
                    foreach ($clean as $v) { if ($v !== '' && $v !== null && $v !== [] && $v !== 0 && $v !== '0') { $has = true; break; } }
                    if ($has) $out[] = $clean;
                }
                return $out;
            default: return sanitize_text_field($raw);
        }
    }
}

/* ============================================================
   Meta box — همان‌طور که بود + trait
   ============================================================ */
class Raha_Meta_Box {

    use Raha_Fields_Renderer;

    protected $id, $title, $post_types, $fields, $context, $priority;

    public function __construct(array $args) {
        $this->id         = $args['id']         ?? 'raha_box_' . wp_generate_uuid4();
        $this->title      = $args['title']      ?? '';
        $this->post_types = (array) ($args['post_types'] ?? ['post']);
        $this->fields     = $args['fields']     ?? [];
        $this->context    = $args['context']    ?? 'normal';
        $this->priority   = $args['priority']   ?? 'default';

        add_action('add_meta_boxes', [$this, 'add_box']);
        add_action('save_post',      [$this, 'save'], 10, 2);
    }

    public function add_box() {
        foreach ($this->post_types as $pt) {
            add_meta_box($this->id, $this->title, [$this, 'render'], $pt, $this->context, $this->priority);
        }
    }

    public function render($post) {
        wp_nonce_field('raha_save_' . $this->id, 'raha_nonce_' . $this->id);
        echo '<div class="raha-fields">';
        foreach ($this->fields as $field) {
            if (empty($field['id'])) continue;
            $value = get_post_meta($post->ID, $field['id'], true);
            echo '<div class="raha-field">';
            echo '<label class="raha-label">' . esc_html($field['label'] ?? $field['id']) . '</label>';
            if (!empty($field['desc'])) echo '<p class="raha-desc">' . esc_html($field['desc']) . '</p>';
            $this->render_input($field, $field['id'], $value);
            echo '</div>';
        }
        echo '</div>';
    }

    public function save($post_id, $post) {
        if (!isset($_POST['raha_nonce_' . $this->id])) return;
        if (!wp_verify_nonce($_POST['raha_nonce_' . $this->id], 'raha_save_' . $this->id)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (!in_array($post->post_type, $this->post_types, true)) return;

        foreach ($this->fields as $field) {
            $id = $field['id'];
            $type = $field['type'] ?? 'text';
            $clean = $this->sanitize($_POST[$id] ?? null, $type, $field);
            if ($clean === '' || $clean === null || $clean === [] || $clean === 0 || $clean === '0') {
                delete_post_meta($post_id, $id);
            } else {
                update_post_meta($post_id, $id, $clean);
            }
        }
    }
}

function raha_meta_box(array $args) {
    return new Raha_Meta_Box($args);
}