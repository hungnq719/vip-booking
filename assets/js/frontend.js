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
                        // Check if user is logged in
                        if (!response.data.logged_in) {
                            // Keep badge hidden for non-logged-in users
                            $badge.hide();
                            return;
                        }

                        var count = parseInt(response.data.count) || 0;

                        // Remove loading state
                        $badge.removeAttr('data-loading');

                        // Update badge with count
                        $badge.text(count);

                        // Set count data attribute for styling
                        $badge.attr('data-count', count);
                        $badge.attr('aria-label', 'You have ' + count + ' upcoming booking' + (count !== 1 ? 's' : ''));

                        // Show badge with proper display style, or hide if count is 0 and show_zero is 'no'
                        if (count === 0 && !showZero) {
                            $badge.hide();
                        } else {
                            $badge.css('display', 'inline-flex').show();
                        }
                    }
                },
                error: function() {
                    // On error, hide badge
                    $badge.hide();
                }
            });
        });
    }

    // Update badges on page load
    if ($('.vip-booking-badge').length > 0) {
        updateBadgeCounts();
    }

    // Detect current page language from URL
    function getCurrentLanguage() {
        var path = window.location.pathname;

        // Common language codes
        var langCodes = ['ko', 'en', 'zh', 'ru', 'ja', 'vi', 'th', 'id', 'es', 'fr', 'de', 'it', 'pt'];

        // Check for language in URL path (e.g., /ko/, /en/)
        var pathParts = path.split('/').filter(function(part) { return part.length > 0; });

        // Check first part of path for language code
        if (pathParts.length > 0 && langCodes.indexOf(pathParts[0]) !== -1) {
            return pathParts[0];
        }

        // Check for language in query string (e.g., ?lang=ko)
        var urlParams = new URLSearchParams(window.location.search);
        var langParam = urlParams.get('lang');
        if (langParam && langCodes.indexOf(langParam) !== -1) {
            return langParam;
        }

        // Check HTML lang attribute
        var htmlLang = $('html').attr('lang');
        if (htmlLang) {
            var langCode = htmlLang.split('-')[0].toLowerCase();
            if (langCodes.indexOf(langCode) !== -1) {
                return langCode;
            }
        }

        return null; // No language detected
    }

    // Prepend language code to URL if needed
    function getLanguageAwareUrl(baseUrl, langCode) {
        if (!baseUrl || !langCode) {
            return baseUrl;
        }

        try {
            var url = new URL(baseUrl, window.location.origin);
            var pathParts = url.pathname.split('/').filter(function(part) { return part.length > 0; });

            // Check if language code is already in URL
            if (pathParts.length > 0 && pathParts[0] === langCode) {
                return baseUrl; // Language already in URL
            }

            // Prepend language code to path
            url.pathname = '/' + langCode + url.pathname;

            return url.toString();
        } catch (e) {
            console.error('Error parsing URL:', e);
            return baseUrl;
        }
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
                    var baseUrl = response.data.badge_url;
                    var currentLang = getCurrentLanguage();
                    var finalUrl = getLanguageAwareUrl(baseUrl, currentLang);

                    console.log('Badge navigation:', {
                        baseUrl: baseUrl,
                        detectedLang: currentLang,
                        finalUrl: finalUrl
                    });

                    window.location.href = finalUrl;
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
