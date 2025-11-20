<?php
class VIP_Booking_Notification_Settings {

    public static function save_notification_settings($settings) {
        update_option('vip_booking_notification_settings', $settings);
    }

    public static function get_notification_settings() {
        $defaults = array(
            'telegram_enabled' => false,
            'telegram_bot_token' => '',
            'telegram_chat_ids' => array(),
            'email_enabled' => false,
            'email_recipients' => array(),
            'send_card_image' => true,
            'notification_template' => self::get_default_template()
        );

        $settings = get_option('vip_booking_notification_settings', $defaults);

        return array_merge($defaults, $settings);
    }

    public static function get_default_template() {
        return "🎯 New VIP Booking Received!\n\n" .
               "📋 Booking Number: {booking_number}\n" .
               "👤 Customer: {customer_name}\n" .
               "🏪 Service: {service}\n" .
               "📍 Store: {store}\n" .
               "📦 Package: {package}\n" .
               "🌍 Nationality: {nation}\n" .
               "👥 Number of People: {pax}\n" .
               "📅 Date: {date}\n" .
               "🕐 Time: {time}\n" .
               "💰 Price: {price} VND\n" .
               "⏰ Created: {created_at}";
    }

    public static function format_message($booking_id) {
        $template = self::get_notification_settings()['notification_template'];

        $booking_number = get_post_meta($booking_id, '_booking_number', true);
        $service = get_post_meta($booking_id, '_booking_service', true);
        $store = get_post_meta($booking_id, '_booking_store', true);
        $package = get_post_meta($booking_id, '_booking_package', true);
        $price = get_post_meta($booking_id, '_booking_price', true);
        $nation = get_post_meta($booking_id, '_booking_nation', true);
        $pax = get_post_meta($booking_id, '_booking_pax', true);
        $date = get_post_meta($booking_id, '_booking_date', true);
        $time = get_post_meta($booking_id, '_booking_time', true);
        $created_at = get_post_meta($booking_id, '_booking_created_at', true);

        $post = get_post($booking_id);
        $user = get_user_by('id', $post->post_author);
        $customer_name = $user ? $user->display_name : 'Guest';

        $formatted_price = number_format($price, 0, '.', ',');
        $formatted_created = date('Y-m-d H:i:s', $created_at);

        $replacements = array(
            '{booking_number}' => $booking_number,
            '{customer_name}' => $customer_name,
            '{service}' => $service,
            '{store}' => $store,
            '{package}' => $package,
            '{nation}' => $nation,
            '{pax}' => $pax,
            '{date}' => $date,
            '{time}' => $time,
            '{price}' => $formatted_price,
            '{created_at}' => $formatted_created
        );

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
