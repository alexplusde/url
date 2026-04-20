/**
 * URL Generator - Backend JavaScript
 * Adds click-to-copy functionality for URL call snippets
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Add click-to-copy functionality to code elements with data-copy-target attribute
        $('.url-code-copy').on('click', function(e) {
            e.preventDefault();
            
            var $this = $(this);
            var targetId = $this.data('copy-target');
            var $textarea = $('#' + targetId);
            
            if ($textarea.length === 0) {
                console.error('Copy target textarea not found:', targetId);
                return;
            }
            
            var textToCopy = $textarea.val();
            
            // Use the Clipboard API if available, fallback to older method
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    showCopyFeedback($this);
                }).catch(function(err) {
                    fallbackCopyToClipboard($textarea, $this);
                });
            } else {
                fallbackCopyToClipboard($textarea, $this);
            }
        });
        
        // Add keyboard accessibility (Enter or Space to trigger copy)
        $('.url-code-copy').on('keydown', function(e) {
            // Enter or Space key
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    });
    
    /**
     * Fallback copy method for older browsers or non-HTTPS contexts
     */
    function fallbackCopyToClipboard($textarea, $element) {
        $textarea[0].select();
        
        try {
            document.execCommand('copy');
            showCopyFeedback($element);
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
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
