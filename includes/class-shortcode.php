<?php
class VIP_Booking_Shortcode {
    public function __construct() {
        add_shortcode('vip_booking', array($this, 'render_booking'));
        add_shortcode('vip_booking_secret', array($this, 'render_secret'));
        add_shortcode('vip_booking_user', array($this, 'render_dashboard'));
        add_shortcode('vip_booking_badge', array($this, 'render_badge'));
    }
    
    public function render_booking($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'storeid' => '',
        ), $atts);

        ob_start();
        $require_login = true;
        $storeid = sanitize_text_field($atts['storeid']);
        include VIP_BOOKING_PLUGIN_DIR . 'templates/frontend-form.php';
        return ob_get_clean();
    }

    public function render_secret($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'storeid' => '',
        ), $atts);

        ob_start();
        $require_login = false;
        $storeid = sanitize_text_field($atts['storeid']);
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
        ), $atts);

        // Sanitize size attribute
        $valid_sizes = array('small', 'medium', 'large');
        $size = in_array($atts['size'], $valid_sizes) ? $atts['size'] : 'medium';

        ob_start();
        ?>
        <span class="vip-booking-badge"
              data-show-zero="<?php echo esc_attr($atts['show_zero']); ?>"
              data-size="<?php echo esc_attr($size); ?>"
              role="button"
              tabindex="0"
              aria-label="View bookings"></span>
        <?php
        return ob_get_clean();
    }
}
