jQuery(document).ready(function($) {
    /**
     * VIP Booking Badge - Dynamic Update
     * Updates badge count via AJAX to bypass full-page caching
     */
    function updateBadgeCounts() {
        $('.vip-booking-badge').each(function() {
            var $badge = $(this);
            var showZero = $badge.data('show-zero') === 'yes';

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
                        $badge.removeAttr('data-loading');

                        // Update badge with count
                        $badge.text(count);

                        // Set count data attribute for styling
                        $badge.attr('data-count', count);
                        $badge.attr('aria-label', 'You have ' + count + ' upcoming booking' + (count !== 1 ? 's' : ''));

                        // Hide badge if count is 0 and show_zero is 'no'
                        if (count === 0 && !showZero) {
                            $badge.hide();
                        } else {
                            $badge.show();
                        }
                    }
                },
                error: function() {
                    // On error, show 0
                    $badge.removeAttr('data-loading');
                    $badge.text('0');
                    $badge.attr('data-count', 0);
                    $badge.attr('aria-label', 'View bookings');

                    if (!showZero) {
                        $badge.hide();
                    }
                }
            });
        });
    }

    // Update badges on page load
    if ($('.vip-booking-badge').length > 0) {
        updateBadgeCounts();
    }

    // Click handler for badge navigation
    $(document).on('click', '.vip-booking-badge', function(e) {
        e.preventDefault();

        // Fetch badge URL from server settings
        $.ajax({
            url: vipBookingVars.ajaxUrl,
            type: 'POST',
            data: {
                action: 'vip_booking_get_badge_url'
            },
            success: function(response) {
                if (response.success && response.data && response.data.badge_url) {
                    window.location.href = response.data.badge_url;
                }
            }
        });
    });

    // Keyboard accessibility (Enter or Space key)
    $(document).on('keydown', '.vip-booking-badge', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });

    // Optional: Auto-refresh every 60 seconds if on same page
    // Uncomment the following lines to enable auto-refresh
    /*
    if ($('.vip-booking-badge').length > 0) {
        setInterval(function() {
            updateBadgeCounts();
        }, 60000); // Refresh every 60 seconds
    }
    */
});
