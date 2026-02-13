/**
 * Admin Dashboard JS for Podify Events
 */
jQuery(document).ready(function($) {
    // Tab switching
    $('.seic-nav-item[data-tab]').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Update nav items
        $('.seic-nav-item').removeClass('active');
        $(this).addClass('active');
        
        // Update content
        $('.seic-tab-content').removeClass('active');
        $('#seic-tab-' + tabId).addClass('active');
        
        // Update URL hash without jumping
        history.pushState(null, null, '#' + tabId);
    });

    // Handle initial hash
    var hash = window.location.hash.substring(1);
    if (hash) {
        var $targetTab = $('.seic-nav-item[data-tab="' + hash + '"]');
        if ($targetTab.length) {
            $targetTab.trigger('click');
        }
    }
});
