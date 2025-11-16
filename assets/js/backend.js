/**
 * Click-to-copy functionality for URL snippets
 * 
 * Adds click-to-copy functionality to code elements with the class 'url-copyable'
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Add click event handler to all copyable code elements
        $(document).on('click', '.url-copyable', function(e) {
            e.preventDefault();
            
            var $this = $(this);
            var textToCopy = $this.data('copy-text');
            
            // Create temporary textarea to copy text
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(textToCopy).select();
            
            try {
                // Copy to clipboard
                document.execCommand('copy');
                
                // Show feedback
                var originalHtml = $this.html();
                $this.html('<i class="rex-icon fa-check"></i> Kopiert!');
                $this.addClass('url-copied');
                
                // Reset after 2 seconds
                setTimeout(function() {
                    $this.html(originalHtml);
                    $this.removeClass('url-copied');
                }, 2000);
            } catch (err) {
                console.error('Copy failed:', err);
            }
            
            // Remove temporary textarea
            $temp.remove();
        });
        
        // Add cursor pointer style to indicate clickable elements
        $('.url-copyable').css('cursor', 'pointer');
    });
})(jQuery);
