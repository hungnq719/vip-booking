<?php
class VIP_Booking_CPT {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('wp', array($this, 'schedule_cleanup'));
        add_action('vip_booking_daily_cleanup', array($this, 'cleanup_old_bookings'));
    }
    
    public function register_post_type() {
        register_post_type('vip_booking', array(
            'public' => false,
            'show_ui' => false,
            'supports' => array('author', 'custom-fields'),
        ));
        
        $fields = array(
            '_booking_service', '_booking_store', '_booking_package', 
            '_booking_price', '_booking_nation', '_booking_pax', 
            '_booking_date', '_booking_time', '_booking_timestamp', 
            '_booking_status', '_booking_created_at', '_booking_number'
        );
        
        foreach ($fields as $field) {
            $type = in_array($field, array('_booking_price', '_booking_pax', '_booking_timestamp', '_booking_created_at')) ? 'integer' : 'string';
            register_post_meta('vip_booking', $field, array(
                'type' => $type,
                'single' => true,
                'show_in_rest' => false,
            ));
        }
    }
    
    public function schedule_cleanup() {
        if (!wp_next_scheduled('vip_booking_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'vip_booking_daily_cleanup');
        }
    }
    
    public function cleanup_old_bookings() {
        // Get configurable cleanup period (default: -90 days)
        $cleanup_period = intval(get_option('vip_booking_cleanup_period', -90));

        // Ensure valid negative value
        if ($cleanup_period >= 0) $cleanup_period = -90;
        if ($cleanup_period < -3650) $cleanup_period = -3650;

        // Calculate timestamp using configurable period
        $cleanup_timestamp = strtotime($cleanup_period . ' days');

        // Query bookings older than the configured period
        $old_bookings = get_posts(array(
            'post_type' => 'vip_booking',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_booking_timestamp',
                    'value' => $cleanup_timestamp,
                    'compare' => '<',
                    'type' => 'NUMERIC'
                )
            )
        ));

        // Delete old bookings
        foreach ($old_bookings as $booking_id) {
            wp_delete_post($booking_id, true);
        }

        // Log cleanup for debugging
        if (!empty($old_bookings)) {
            error_log('VIP Booking: Cleaned up ' . count($old_bookings) . ' old bookings (>' . abs($cleanup_period) . ' days)');
        }
    }
}
