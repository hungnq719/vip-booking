jQuery(document).ready(function($) {
    /**
     * VIP Booking Badge - Dynamic Update
     * Updates badge count via AJAX to bypass full-page caching
     */
    function updateBadgeCounts() {
        $('.vip-booking-badge-wrapper').each(function() {
            var $wrapper = $(this);
            var $countElement = $wrapper.find('.vip-booking-badge-count');
            var showZero = $wrapper.data('show-zero') === 'yes';

            // Make AJAX request to get current user's booking count
            $.ajax({
                url: vipBookingVars.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'vip_booking_get_badge_count'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        var count = parseInt(response.data.count) || 0;

                        // Remove loading state
                        $countElement.removeAttr('data-loading');

                        // Update count
                        $countElement.html('<span class="vip-booking-badge-number">' + count + '</span>');

                        // Add count class for styling
                        $countElement.attr('data-count', count);

                        // Hide wrapper if count is 0 and show_zero is 'no'
                        if (count === 0 && !showZero) {
                            $wrapper.hide();
                        } else {
                            $wrapper.show();
                        }

                        // Add loaded class
                        $wrapper.addClass('vip-booking-badge-loaded');
                    }
                },
                error: function() {
                    // On error, show 0
                    $countElement.removeAttr('data-loading');
                    $countElement.html('<span class="vip-booking-badge-number">0</span>');
                    $countElement.attr('data-count', 0);

                    if (!showZero) {
                        $wrapper.hide();
                    }
                }
            });
        });
    }

    // Update badges on page load
    if ($('.vip-booking-badge-wrapper').length > 0) {
        updateBadgeCounts();
    }

    // Optional: Auto-refresh every 60 seconds if on same page
    // Uncomment the following lines to enable auto-refresh
    /*
    if ($('.vip-booking-badge-wrapper').length > 0) {
        setInterval(function() {
            updateBadgeCounts();
        }, 60000); // Refresh every 60 seconds
    }
    */
});
