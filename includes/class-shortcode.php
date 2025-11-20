<?php
class VIP_Booking_Shortcode {
    public function __construct() {
        add_shortcode('vip_booking', array($this, 'render_booking'));
        add_shortcode('vip_booking_secret', array($this, 'render_secret'));
        add_shortcode('vip_booking_user', array($this, 'render_dashboard'));
        add_shortcode('vip_booking_badge', array($this, 'render_badge'));
    }
    
    public function render_booking($atts) {
        ob_start();
        $require_login = true;
        include VIP_BOOKING_PLUGIN_DIR . 'templates/frontend-form.php';
        return ob_get_clean();
    }
    
    public function render_secret($atts) {
        ob_start();
        $require_login = false;
        include VIP_BOOKING_PLUGIN_DIR . 'templates/frontend-form.php';
        return ob_get_clean();
    }
    
    public function render_dashboard($atts) {
        ob_start();
        include VIP_BOOKING_PLUGIN_DIR . 'templates/user-dashboard.php';
        return ob_get_clean();
    }

    public function render_badge($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'text' => 'Upcoming Bookings',
            'show_zero' => 'yes',
        ), $atts);

        ob_start();
        ?>
        <span class="vip-booking-badge-wrapper" data-show-zero="<?php echo esc_attr($atts['show_zero']); ?>">
            <span class="vip-booking-badge-text"><?php echo esc_html($atts['text']); ?>:</span>
            <span class="vip-booking-badge-count" data-loading="true">
                <span class="vip-booking-badge-spinner"></span>
            </span>
        </span>
        <?php
        return ob_get_clean();
    }
}
