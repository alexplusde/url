/**
 * URL Generator - Backend JavaScript
 * Click-to-copy for the URL call snippets in the profile overview
 */

(function ($) {
    'use strict';

    // Delegated on document: the backend loads pages via PJAX, so handlers
    // bound once at document ready would miss cards rendered later.
    $(document).on('click', '.url-code-copy', function (e) {
        e.preventDefault();

        var $code = $(this);
        var text = $.trim($code.find('.url-code-copy-text').text());
        if (text === '') {
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                showCopyFeedback($code);
            }, function () {
                fallbackCopy($code, text);
            });
        } else {
            fallbackCopy($code, text);
        }
    });

    // Enter or Space triggers the copy, like on a button
    $(document).on('keydown', '.url-code-copy', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    /**
     * Fallback for browsers without the Clipboard API or outside HTTPS
     */
    function fallbackCopy($code, text) {
        var $textarea = $('<textarea readonly></textarea>')
            .val(text)
            .css({position: 'fixed', top: 0, left: '-9999px'})
            .appendTo(document.body);

        $textarea[0].select();
        try {
            if (document.execCommand('copy')) {
                showCopyFeedback($code);
            }
        } catch (err) {
            // Copying is not possible, the snippet stays selectable by hand
        }
        $textarea.remove();
        $code.trigger('focus');
    }

    /**
     * Shows a checkmark for 2 seconds after copying
     */
    function showCopyFeedback($code) {
        var $icon = $code.find('.rex-icon');

        $icon.removeClass('fa-copy').addClass('fa-check');
        $code.addClass('url-copied');

        window.clearTimeout($code.data('urlCopyTimer'));
        $code.data('urlCopyTimer', window.setTimeout(function () {
            $icon.removeClass('fa-check').addClass('fa-copy');
            $code.removeClass('url-copied');
        }, 2000));
    }
})(jQuery);
