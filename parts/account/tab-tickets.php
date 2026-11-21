<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();

/* ۱. ثبت تیکت جدید */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['raha_action']) && $_POST['raha_action'] === 'new_ticket') {
    if (wp_verify_nonce($_POST['_raha_ticket_nonce'] ?? '', 'raha_new_ticket')) {
        $subject  = sanitize_text_field($_POST['ticket_subject'] ?? '');
        $message  = sanitize_textarea_field($_POST['ticket_message'] ?? '');
        $priority = sanitize_text_field($_POST['ticket_priority'] ?? 'normal');
        $category = sanitize_text_field($_POST['ticket_category'] ?? '');

        if ($subject && strlen($message) >= 10) {
            $ticket_id = wp_insert_post([
                'post_type'    => 'raha_ticket',
                'post_status'  => 'publish',
                'post_title'   => $subject,
                'post_content' => $message,
                'post_author'  => $user->ID,
                'meta_input'   => [
                    'ticket_status' => 'open',
                    'priority'      => $priority,
                    'category'      => $category,
                ],
            ]);
            if (!is_wp_error($ticket_id)) {
                $admin_email = get_option('admin_email');
                @wp_mail($admin_email, '[تیکت جدید] ' . $subject, $message . "\n\n" . admin_url('post.php?post=' . $ticket_id . '&action=edit'));
                echo '<div class="raha-auth__alert" style="background:#dcfce7;border-color:#16a34a;color:#14532d;">تیکت شما با موفقیت ثبت شد.</div>';
            }
        } else {
            echo '<div class="raha-auth__alert">موضوع و متن (حداقل ۱۰ کاراکتر) الزامی است.</div>';
        }
    }
}

/* ۲. ثبت پاسخ کاربر به تیکت */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['raha_action']) && $_POST['raha_action'] === 'reply_ticket') {
    if (wp_verify_nonce($_POST['_raha_reply_nonce'] ?? '', 'raha_reply_ticket')) {
        $ticket_id = absint($_POST['ticket_id'] ?? 0);
        $reply_text = sanitize_textarea_field($_POST['ticket_reply'] ?? '');
        
        $ticket = get_post($ticket_id);
        if ($ticket && $ticket->post_author == $user->ID && $reply_text) {
            $comment_data = [
                'comment_post_ID'      => $ticket_id,
                'comment_author'       => $user->display_name,
                'comment_author_email' => $user->user_email,
                'comment_content'      => $reply_text,
                'comment_approved'     => 1,
                'user_id'              => $user->ID,
            ];
            wp_insert_comment($comment_data);
            update_post_meta($ticket_id, 'ticket_status', 'waiting'); // تغییر وضعیت به در انتظار ادمین
            echo '<div class="raha-auth__alert" style="background:#dcfce7;border-color:#16a34a;color:#14532d;">پاسخ شما ارسال شد.</div>';
        }
    }
}

$tickets = get_posts([
    'post_type'      => 'raha_ticket',
    'author'         => $user->ID,
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>
<div class="raha-tab">
    <div class="raha-tab__head">
        <h1>تیکت‌های پشتیبانی</h1>
        <p>ارتباط مستقیم با تیم</p>
    </div>

    <details class="raha-ticket-create">
        <summary class="raha-ticket-create__summary">+ ثبت تیکت جدید</summary>
        <form method="post" class="raha-ticket-create__form">
            <?php wp_nonce_field('raha_new_ticket', '_raha_ticket_nonce'); ?>
            <input type="hidden" name="raha_action" value="new_ticket">
            <label class="raha-field">
                <span class="raha-field__label">موضوع</span>
                <input type="text" name="ticket_subject" class="raha-field__input" required>
            </label>
            <div class="raha-field-row">
                <label class="raha-field">
                    <span class="raha-field__label">اولویت</span>
                    <select name="ticket_priority" class="raha-field__input">
                        <option value="low">کم</option>
                        <option value="normal" selected>متوسط</option>
                        <option value="high">زیاد</option>
                    </select>
                </label>
                <label class="raha-field">
                    <span class="raha-field__label">دسته</span>
                    <select name="ticket_category" class="raha-field__input">
                        <option value="technical">مشکل فنی</option>
                        <option value="billing">مالی و پرداخت</option>
                        <option value="request">درخواست تغییر</option>
                        <option value="other">سایر</option>
                    </select>
                </label>
            </div>
            <label class="raha-field">
                <span class="raha-field__label">پیام</span>
                <textarea name="ticket_message" class="raha-field__input" rows="4" required></textarea>
            </label>
            <button type="submit" class="raha-btn raha-btn--primary">ارسال تیکت</button>
        </form>
    </details>

    <?php if (empty($tickets)) : ?>
        <div class="raha-empty">
            <div class="raha-empty__icon">💬</div>
            <h2>تیکتی ثبت نکرده‌اید</h2>
        </div>
    <?php else : ?>
        <div class="raha-ticket-list">
            <?php foreach ($tickets as $t) :
                $status = get_post_meta($t->ID, 'ticket_status', true) ?: 'open';
                $comments = get_comments(['post_id' => $t->ID, 'status' => 'approve', 'order' => 'ASC']);
            ?>
                <div class="raha-ticket">
                    <div class="raha-ticket__head">
                        <strong><?php echo esc_html($t->post_title); ?></strong>
                        <span class="raha-badge raha-badge--ticket-<?php echo esc_attr($status); ?>"><?php echo esc_html($status); ?></span>
                    </div>
                    <p class="raha-ticket__meta"><?php echo esc_html(get_the_date('', $t)); ?></p>
                    <div class="raha-ticket__content">
                        <?php echo wp_kses_post(wpautop($t->post_content)); ?>
                    </div>
                    
                    <?php if ($comments) : ?>
                        <div class="raha-ticket__replies">
                            <?php foreach ($comments as $c) : 
                                $is_admin = user_can($c->user_id, 'manage_options');
                            ?>
                                <div class="raha-ticket__reply" style="<?php echo $is_admin ? 'background:var(--bg); border-right:3px solid var(--accent);' : ''; ?>">
                                    <strong><?php echo esc_html($c->comment_author); ?> <?php if($is_admin) echo ' (پشتیبانی)'; ?></strong>
                                    <small><?php echo esc_html(get_comment_date('', $c)); ?></small>
                                    <div><?php echo wp_kses_post($c->comment_content); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- فرم پاسخ کاربر -->
                    <form method="post" style="margin-top: 16px; border-top: 1px dashed rgba(0,0,0,0.1); padding-top: 16px;">
                        <?php wp_nonce_field('raha_reply_ticket', '_raha_reply_nonce'); ?>
                        <input type="hidden" name="raha_action" value="reply_ticket">
                        <input type="hidden" name="ticket_id" value="<?php echo esc_attr($t->ID); ?>">
                        <textarea name="ticket_reply" class="raha-field__input" rows="2" placeholder="پاسخ خود را بنویسید..." required style="margin-bottom:8px;"></textarea>
                        <button type="submit" class="raha-btn raha-btn--ghost" style="font-size:12px; min-height:36px; padding:0 16px;">ارسال پاسخ</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>