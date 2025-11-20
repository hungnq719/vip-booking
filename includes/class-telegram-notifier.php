<?php
class VIP_Booking_Telegram_Notifier {

    public static function send_notification($booking_id) {
        $settings = VIP_Booking_Notification_Settings::get_notification_settings();

        if (!$settings['telegram_enabled']) {
            return array('success' => false, 'message' => 'Telegram notifications disabled');
        }

        if (empty($settings['telegram_bot_token'])) {
            return array('success' => false, 'message' => 'Telegram bot token not configured');
        }

        if (empty($settings['telegram_chat_ids'])) {
            return array('success' => false, 'message' => 'No Telegram chat IDs configured');
        }

        $message = VIP_Booking_Notification_Settings::format_message($booking_id);

        $results = array();
        $success_count = 0;
        $error_count = 0;

        foreach ($settings['telegram_chat_ids'] as $chat_id) {
            $result = self::send_telegram_message($settings['telegram_bot_token'], $chat_id, $message, $booking_id, $settings['send_card_image']);

            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
            }

            $results[] = $result;
        }

        return array(
            'success' => $success_count > 0,
            'message' => sprintf('Sent to %d/%d recipients', $success_count, count($settings['telegram_chat_ids'])),
            'details' => $results
        );
    }

    private static function send_telegram_message($bot_token, $chat_id, $message, $booking_id, $send_card = true) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/";

        if ($send_card) {
            // First try to use the saved frontend-generated card
            $saved_card_path = get_post_meta($booking_id, '_booking_card_image', true);

            if ($saved_card_path && file_exists($saved_card_path)) {
                // Use the frontend-generated card
                $result = self::send_photo($api_url, $chat_id, $saved_card_path, $message);
                // Don't delete the saved card - it might be needed again
                return $result;
            }

            // Fallback: generate card if frontend card not available
            $card_path = self::generate_booking_card($booking_id);
            if ($card_path && file_exists($card_path)) {
                return self::send_photo($api_url, $chat_id, $card_path, $message);
            }
        }

        return self::send_text($api_url, $chat_id, $message);
    }

    private static function send_text($api_url, $chat_id, $message) {
        $response = wp_remote_post($api_url . 'sendMessage', array(
            'body' => array(
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['ok']) && $body['ok']) {
            return array('success' => true, 'message' => 'Message sent successfully');
        } else {
            return array('success' => false, 'message' => isset($body['description']) ? $body['description'] : 'Unknown error');
        }
    }

    private static function send_photo($api_url, $chat_id, $photo_path, $caption) {
        $boundary = wp_generate_password(24, false);
        $file_contents = file_get_contents($photo_path);
        $filename = basename($photo_path);

        $body = '';
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"chat_id\"\r\n\r\n{$chat_id}\r\n";

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"caption\"\r\n\r\n{$caption}\r\n";

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Disposition: form-data; name=\"photo\"; filename=\"{$filename}\"\r\n";
        $body .= "Content-Type: image/png\r\n\r\n";
        $body .= $file_contents . "\r\n";
        $body .= "--{$boundary}--\r\n";

        $response = wp_remote_post($api_url . 'sendPhoto', array(
            'headers' => array(
                'Content-Type' => "multipart/form-data; boundary={$boundary}"
            ),
            'body' => $body,
            'timeout' => 30
        ));

        // Only delete if it's a temporary generated file (not the saved frontend card)
        // Temporary files have a different naming pattern
        if (file_exists($photo_path) && strpos($photo_path, 'temp-') !== false) {
            @unlink($photo_path);
        }

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($result['ok']) && $result['ok']) {
            return array('success' => true, 'message' => 'Photo sent successfully');
        } else {
            return array('success' => false, 'message' => isset($result['description']) ? $result['description'] : 'Unknown error');
        }
    }

    private static function generate_booking_card($booking_id) {
        $booking_number = get_post_meta($booking_id, '_booking_number', true);
        $service = get_post_meta($booking_id, '_booking_service', true);
        $store = get_post_meta($booking_id, '_booking_store', true);
        $package = get_post_meta($booking_id, '_booking_package', true);
        $price = get_post_meta($booking_id, '_booking_price', true);
        $nation = get_post_meta($booking_id, '_booking_nation', true);
        $pax = get_post_meta($booking_id, '_booking_pax', true);
        $date = get_post_meta($booking_id, '_booking_date', true);
        $time = get_post_meta($booking_id, '_booking_time', true);

        $post = get_post($booking_id);
        $user = get_user_by('id', $post->post_author);
        $customer_name = $user ? $user->display_name : 'Guest';

        $width = 800;
        $height = 600;
        $image = imagecreatetruecolor($width, $height);

        $bg_gradient_start = imagecolorallocate($image, 26, 35, 126);
        $bg_gradient_end = imagecolorallocate($image, 59, 76, 202);

        for ($i = 0; $i < $height; $i++) {
            $r = 26 + ($i / $height) * (59 - 26);
            $g = 35 + ($i / $height) * (76 - 35);
            $b = 126 + ($i / $height) * (202 - 126);
            $color = imagecolorallocate($image, $r, $g, $b);
            imagefilledrectangle($image, 0, $i, $width, $i + 1, $color);
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $light_blue = imagecolorallocate($image, 173, 216, 230);
        $yellow = imagecolorallocate($image, 255, 215, 0);

        $font_path = dirname(__FILE__) . '/fonts/arial.ttf';
        if (!file_exists($font_path)) {
            $font_path = null;
        }

        $y = 50;

        if ($font_path) {
            imagettftext($image, 32, 0, 250, $y, $yellow, $font_path, 'VIP BOOKING');
            $y += 60;
            imagettftext($image, 18, 0, 50, $y, $white, $font_path, 'Booking #: ' . $booking_number);
            $y += 50;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Customer: ' . $customer_name);
            $y += 40;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Service: ' . $service);
            $y += 40;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Store: ' . $store);
            $y += 40;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Package: ' . $package);
            $y += 40;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Nationality: ' . $nation . '  Pax: ' . $pax);
            $y += 40;
            imagettftext($image, 16, 0, 50, $y, $light_blue, $font_path, 'Date: ' . $date . '  Time: ' . $time);
            $y += 40;
            imagettftext($image, 20, 0, 50, $y, $yellow, $font_path, 'Price: ' . number_format($price, 0, '.', ',') . ' VND');
        } else {
            imagestring($image, 5, 300, $y, 'VIP BOOKING', $yellow);
            $y += 60;
            imagestring($image, 4, 50, $y, 'Booking #: ' . $booking_number, $white);
            $y += 50;
            imagestring($image, 3, 50, $y, 'Customer: ' . $customer_name, $light_blue);
            $y += 40;
            imagestring($image, 3, 50, $y, 'Service: ' . $service, $light_blue);
            $y += 40;
            imagestring($image, 3, 50, $y, 'Store: ' . $store, $light_blue);
            $y += 40;
            imagestring($image, 3, 50, $y, 'Package: ' . $package, $light_blue);
            $y += 40;
            imagestring($image, 3, 50, $y, 'Nationality: ' . $nation . '  Pax: ' . $pax, $light_blue);
            $y += 40;
            imagestring($image, 3, 50, $y, 'Date: ' . $date . '  Time: ' . $time, $light_blue);
            $y += 40;
            imagestring($image, 4, 50, $y, 'Price: ' . number_format($price, 0, '.', ',') . ' VND', $yellow);
        }

        $upload_dir = wp_upload_dir();
        $filename = 'booking-card-' . $booking_id . '-' . time() . '.png';
        $filepath = $upload_dir['path'] . '/' . $filename;

        imagepng($image, $filepath);
        imagedestroy($image);

        return $filepath;
    }

    public static function test_connection($bot_token, $chat_id) {
        $api_url = "https://api.telegram.org/bot{$bot_token}/";

        $response = wp_remote_post($api_url . 'sendMessage', array(
            'body' => array(
                'chat_id' => $chat_id,
                'text' => '✅ VIP Booking: Telegram notification test successful!'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'message' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['ok']) && $body['ok']) {
            return array('success' => true, 'message' => 'Test message sent successfully');
        } else {
            return array('success' => false, 'message' => isset($body['description']) ? $body['description'] : 'Unknown error');
        }
    }
}
