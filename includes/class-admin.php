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
        if (isset($settings['limit_2h'])) {
            update_option('vip_booking_limit_2h', intval($settings['limit_2h']));
        }
        if (isset($settings['limit_12h'])) {
            update_option('vip_booking_limit_12h', intval($settings['limit_12h']));
        }
    }
    public static function get_settings() {
        return array(
            'exchange_rate' => get_option('vip_booking_exchange_rate', 25000),
            'limit_2h' => intval(get_option('vip_booking_limit_2h', 2)),
            'limit_12h' => intval(get_option('vip_booking_limit_12h', 4))
        );
    }
    public static function save_flags($flags) { update_option('vip_booking_flags', $flags); }
    public static function get_flags() { return get_option('vip_booking_flags', array('🇺🇸', '🇰🇷', '🇷🇺', '🇨🇳', '🇯🇵')); }

    // Cleanup period management
    public static function save_cleanup_period($period) {
        $period = intval($period);
        // Validate: must be negative and reasonable (between -1 and -3650 days)
        if ($period >= 0) $period = -90;
        if ($period < -3650) $period = -3650;
        update_option('vip_booking_cleanup_period', $period);
    }
    public static function get_cleanup_period() {
        return intval(get_option('vip_booking_cleanup_period', -90));
    }

    // Badge URL management
    public static function save_badge_url($url) {
        $url = esc_url_raw($url);
        update_option('vip_booking_badge_url', $url);
    }
    public static function get_badge_url() {
        return get_option('vip_booking_badge_url', '');
    }

    // Popup Login settings management
    public static function save_popup_settings($settings) {
        $defaults = array(
            'trigger_class' => '',
            'auto_open_enabled' => false,
            'auto_open_seconds' => 0
        );

        $settings = array_merge($defaults, $settings);

        // Sanitize
        $settings['trigger_class'] = sanitize_text_field($settings['trigger_class']);
        $settings['auto_open_enabled'] = (bool) $settings['auto_open_enabled'];
        $settings['auto_open_seconds'] = max(0, intval($settings['auto_open_seconds']));

        update_option('vip_booking_popup_settings', $settings);
    }

    public static function get_popup_settings() {
        $defaults = array(
            'trigger_class' => '',
            'auto_open_enabled' => false,
            'auto_open_seconds' => 0
        );

        return array_merge($defaults, get_option('vip_booking_popup_settings', array()));
    }

    public static function save_login_message_settings($settings) {
        $settings['message'] = sanitize_text_field($settings['message']);
        $settings['login_url'] = esc_url_raw($settings['login_url']);

        update_option('vip_booking_login_message_settings', $settings);
    }

    public static function get_login_message_settings() {
        $defaults = array(
            'message' => '⚠️ Please login to make reservation!',
            'login_url' => '/login'
        );

        return array_merge($defaults, get_option('vip_booking_login_message_settings', array()));
    }
}
