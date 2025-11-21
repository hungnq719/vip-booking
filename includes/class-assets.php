<?php
class VIP_Booking_Assets {
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend'));
    }
    
    public function enqueue_frontend() {
        if (file_exists(VIP_BOOKING_PLUGIN_DIR . 'assets/css/frontend.css')) {
            wp_enqueue_style('vip-booking-frontend', VIP_BOOKING_PLUGIN_URL . 'assets/css/frontend.css', array(), VIP_BOOKING_VERSION);
        }
        if (file_exists(VIP_BOOKING_PLUGIN_DIR . 'assets/js/frontend.js')) {
            wp_enqueue_script('vip-booking-frontend', VIP_BOOKING_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), VIP_BOOKING_VERSION, true);

            // Localize script for AJAX
            wp_localize_script('vip-booking-frontend', 'vipBookingVars', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vip_booking_nonce')
            ));
        }
    }
}
