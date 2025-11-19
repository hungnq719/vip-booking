<?php
/**
 * Plugin Name: VIP Booking
 * Description: Complete booking management system with dashboard and statistics
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

define('VIP_BOOKING_VERSION', '1.0.0');
define('VIP_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VIP_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload classes
spl_autoload_register(function($class) {
    $prefix = 'VIP_Booking_';
    if (strpos($class, $prefix) !== 0) return;
    $class_name = substr($class, strlen($prefix));
    $file_name = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
    $file_path = VIP_BOOKING_PLUGIN_DIR . 'includes/' . $file_name;
    if (file_exists($file_path)) require_once $file_path;
});

function vip_booking_init() {
    new VIP_Booking_CPT();
    new VIP_Booking_Admin();
    new VIP_Booking_AJAX();
    new VIP_Booking_Shortcode();
    new VIP_Booking_Assets();
}
add_action('plugins_loaded', 'vip_booking_init');

register_activation_hook(__FILE__, function() {
    $cpt = new VIP_Booking_CPT();
    $cpt->register_post_type();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
