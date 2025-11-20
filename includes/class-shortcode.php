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
            'size' => 'medium', // Options: small, medium, large
            'show_zero' => 'yes',
            'dashboard_url' => '', // URL to navigate on click
        ), $atts);

        // Sanitize size attribute
        $valid_sizes = array('small', 'medium', 'large');
        $size = in_array($atts['size'], $valid_sizes) ? $atts['size'] : 'medium';

        // Sanitize dashboard URL
        $dashboard_url = !empty($atts['dashboard_url']) ? esc_url($atts['dashboard_url']) : '';

        ob_start();
        ?>
        <span class="vip-booking-badge"
              data-show-zero="<?php echo esc_attr($atts['show_zero']); ?>"
              data-size="<?php echo esc_attr($size); ?>"
              data-dashboard-url="<?php echo esc_attr($dashboard_url); ?>"
              data-loading="true"
              role="button"
              tabindex="0"
              aria-label="View bookings">
            <span class="vip-booking-badge-spinner"></span>
        </span>
        <?php
        return ob_get_clean();
    }
}
