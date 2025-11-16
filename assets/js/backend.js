/**
 * URL Generator - Backend JavaScript
 * Adds click-to-copy functionality for URL call snippets
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Add click-to-copy functionality to code elements with data-copy attribute
        $('.url-code-copy').on('click', function(e) {
            e.preventDefault();
            
            var $this = $(this);
            var textToCopy = $this.data('copy');
            
            // Use the Clipboard API if available, fallback to older method
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    showCopyFeedback($this);
                }).catch(function(err) {
                    fallbackCopyToClipboard(textToCopy, $this);
                });
            } else {
                fallbackCopyToClipboard(textToCopy, $this);
            }
        });
        
        // Add cursor pointer style to copyable code elements
    });
    
    /**
     * Fallback copy method for older browsers or non-HTTPS contexts
     */
    function fallbackCopyToClipboard(text, $element) {
        var $temp = $('<textarea>');
        $('body').append($temp);
        $temp.val(text).select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback($element);
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
        
        $temp.remove();
    }
    
    /**
     * Show visual feedback when text is copied
     */
    function showCopyFeedback($element) {
        var $icon = $element.find('.rex-icon');
        
        // Change icon to checkmark temporarily
        if ($icon.length) {
            $icon.removeClass('fa-copy').addClass('fa-check');
        }
        
        // Add success class
        $element.addClass('url-copied');
        
        // Reset after 2 seconds
        setTimeout(function() {
            if ($icon.length) {
                $icon.removeClass('fa-check').addClass('fa-copy');
            }
            $element.removeClass('url-copied');
        }, 2000);
    }
    
})(jQuery);
