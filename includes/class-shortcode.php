<?php
class VIP_Booking_Shortcode {
    public function __construct() {
        add_shortcode('vip_booking', array($this, 'render_booking'));
        add_shortcode('vip_booking_secret', array($this, 'render_secret'));
        add_shortcode('vip_booking_user', array($this, 'render_dashboard'));
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
}
