<?php
class VIP_Booking_CPT {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
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
}
