<?php
class VIP_Booking_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_menu() {
        add_menu_page(
            'VIP Booking',
            'VIP Booking',
            'manage_options',
            'vip-booking',
            array($this, 'render_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    public function enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_vip-booking') return;
        wp_enqueue_script('vip-booking-admin', VIP_BOOKING_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), VIP_BOOKING_VERSION, true);
        wp_localize_script('vip-booking-admin', 'vipBookingAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vip_booking_nonce')
        ));
    }
    
    public function render_page() {
        include VIP_BOOKING_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    // Data management
    public static function save_data($data) { update_option('vip_booking_data', $data); }
    public static function get_data() { return get_option('vip_booking_data', array()); }
    public static function save_settings($settings) {
        if (isset($settings['exchange_rate'])) {
            update_option('vip_booking_exchange_rate', floatval($settings['exchange_rate']));
        }
    }
    public static function get_settings() {
        return array('exchange_rate' => get_option('vip_booking_exchange_rate', 25000));
    }
    public static function save_flags($flags) { update_option('vip_booking_flags', $flags); }
    public static function get_flags() { return get_option('vip_booking_flags', array('🇺🇸', '🇰🇷', '🇷🇺', '🇨🇳', '🇯🇵')); }
}
