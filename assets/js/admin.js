/**
 * Admin Dashboard JS for Podify Events
 */
jQuery(document).ready(function($) {
    // Tab switching
    $('[data-tab]').on('click', function(e) {
        var tabId = $(this).data('tab');
        
        // If it's a hash link, prevent default
        if ($(this).attr('href') && $(this).attr('href').indexOf('#') === 0) {
            e.preventDefault();
        } else {
            // If it's a real link, don't prevent default unless we're just switching tabs
            if (tabId) e.preventDefault();
        }
        
        if (!tabId) return;
        
        // Update nav items (sidebar)
        $('.seic-nav-item').removeClass('active');
        $('.seic-nav-item[data-tab="' + tabId + '"]').addClass('active');
        
        // Update action boxes if applicable
        $('.seic-action-box').removeClass('active');
        $(this).addClass('active');
        
        // Update content
        $('.seic-tab-content').removeClass('active');
        $('#seic-tab-' + tabId).addClass('active');
        
        // Update URL hash without jumping
        history.pushState(null, null, '#' + tabId);
    });

    // Updater Status Check
    $('.seic-btn-check-update').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $icon = $btn.find('.dashicons');
        var $statusBadge = $('.seic-status-badge');
        var $statusText = $statusBadge.parent().find('p');

        if ($btn.hasClass('loading')) return;

        $btn.addClass('loading');
        $icon.addClass('seic-rotate');
        $statusBadge.removeClass('success warning error').addClass('info').text('Checking...');

        $.ajax({
            url: podifyEventsAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'podify_check_update',
                nonce: podifyEventsAdmin.nonce
            },
            success: function(response) {
                $btn.removeClass('loading');
                $icon.removeClass('seic-rotate');

                if (response.success) {
                    if (response.data.is_available) {
                        $statusBadge.removeClass('info').addClass('warning').text('Update Available');
                        $statusText.html('Latest version: <strong>' + response.data.latest + '</strong> (Current: ' + response.data.current + ')');
                    } else {
                        $statusBadge.removeClass('info').addClass('success').text('Up to Date');
                        $statusText.html('Currently running v' + response.data.current);
                    }
                } else {
                    $statusBadge.removeClass('info').addClass('error').text('Check Failed');
                    $statusText.text(response.data.message || 'Error connecting to GitHub');
                }
            },
            error: function() {
                $btn.removeClass('loading');
                $icon.removeClass('seic-rotate');
                $statusBadge.removeClass('info').addClass('error').text('Error');
                $statusText.text('Server communication error.');
            }
        });
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
