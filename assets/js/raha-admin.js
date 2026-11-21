(function ($) {
    'use strict';

    /* ---------- Image picker ---------- */
    $(document).on('click', '.raha-image-select', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.raha-image');
        var frame = wp.media({
            title: 'انتخاب تصویر',
            multiple: false,
            library: { type: 'image' },
            button: { text: 'استفاده' }
        });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            $wrap.find('input[type="hidden"]').val(att.id);
            $wrap.find('.raha-image-preview').html('<img src="' + att.url + '" alt="">');
            $wrap.addClass('has-image');
            $wrap.find('.raha-image-remove').show();
        });
        frame.open();
    });

    $(document).on('click', '.raha-image-remove', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.raha-image');
        $wrap.find('input[type="hidden"]').val('');
        $wrap.find('.raha-image-preview').empty();
        $wrap.removeClass('has-image');
        $(this).hide();
    });

    /* ---------- Repeater: add row ---------- */
    $(document).on('click', '.raha-repeater-add', function (e) {
        e.preventDefault();
        var $wrap = $(this).closest('.raha-repeater');
        var $rows = $wrap.children('.raha-repeater-rows');
        var $tpl  = $wrap.children('.raha-repeater-template');

        // پیدا کردن بزرگ‌ترین ایندکس موجود + ۱
        var maxIndex = -1;
        $rows.children('.raha-repeater-row').each(function () {
            var name = $(this).find('input, select, textarea').first().attr('name') || '';
            var m = name.match(/\[(\d+)\]/);
            if (m) maxIndex = Math.max(maxIndex, parseInt(m[1], 10));
        });
        var next = maxIndex + 1;

        var html = $tpl.html().replace(/__INDEX__/g, next);
        var $new = $(html);
        $rows.append($new);
        initSortable($rows);
    });

    /* ---------- Repeater: remove row ---------- */
    $(document).on('click', '.raha-repeater-remove', function (e) {
        e.preventDefault();
        if (!confirm('این ردیف حذف شود؟')) return;
        $(this).closest('.raha-repeater-row').remove();
    });

    /* ---------- Repeater: sortable ---------- */
    function initSortable($rows) {
        if (!$.fn.sortable) return;
        if ($rows.hasClass('ui-sortable')) return;
        $rows.sortable({
            handle: '.raha-repeater-handle',
            axis: 'y',
            placeholder: 'raha-sortable-placeholder',
            forcePlaceholderSize: true,
        });
    }

    $(document).ready(function () {
        $('.raha-repeater-rows').each(function () {
            initSortable($(this));
        });
    });

})(jQuery);



/* ============================================================
   Menu image picker
   ============================================================ */
jQuery(document).on('click', '.raha-menu-image-select', function(e) {
    e.preventDefault();
    var $wrap = jQuery(this).closest('p');
    var frame = wp.media({
        title: 'انتخاب تصویر',
        multiple: false,
        library: { type: 'image' },
        button: { text: 'استفاده' }
    });
    frame.on('select', function() {
        var att = frame.state().get('selection').first().toJSON();
        $wrap.find('.raha-menu-image-id').val(att.id);
        $wrap.find('.raha-menu-image-preview').html('<img src="' + att.url + '" style="max-width:80px;border-radius:4px;">');
    });
    frame.open();
});