<?php
class VIP_Booking_Email_Notifier {

    public static function send_notification($booking_id) {
        $settings = VIP_Booking_Notification_Settings::get_notification_settings();

        if (!$settings['email_enabled']) {
            return array('success' => false, 'message' => 'Email notifications disabled');
        }

        if (empty($settings['email_recipients'])) {
            return array('success' => false, 'message' => 'No email recipients configured');
        }

        $booking_number = get_post_meta($booking_id, '_booking_number', true);
        $subject = '🎯 New VIP Booking: ' . $booking_number;

        $message_plain = VIP_Booking_Notification_Settings::format_message($booking_id);

        $message_html = self::format_html_message($booking_id);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: VIP Booking <' . get_option('admin_email') . '>'
        );

        $attachments = array();

        if ($settings['send_card_image']) {
            $card_path = self::generate_booking_card($booking_id);
            if ($card_path && file_exists($card_path)) {
                $attachments[] = $card_path;
            }
        }

        $results = array();
        $success_count = 0;
        $error_count = 0;

        foreach ($settings['email_recipients'] as $recipient) {
            $sent = wp_mail($recipient, $subject, $message_html, $headers, $attachments);

            if ($sent) {
                $success_count++;
                $results[] = array('success' => true, 'recipient' => $recipient);
            } else {
                $error_count++;
                $results[] = array('success' => false, 'recipient' => $recipient, 'message' => 'Failed to send');
            }
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    @unlink($attachment);
                }
            }
        }

        return array(
            'success' => $success_count > 0,
            'message' => sprintf('Sent to %d/%d recipients', $success_count, count($settings['email_recipients'])),
            'details' => $results
        );
    }

    private static function format_html_message($booking_id) {
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

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header {
                    background-color: rgba(255,255,255,0.1);
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    color: #fff;
                    margin: 0;
                    font-size: 28px;
                }
                .content {
                    background-color: #fff;
                    padding: 30px;
                }
                .booking-info {
                    margin: 20px 0;
                }
                .info-row {
                    display: flex;
                    padding: 12px 0;
                    border-bottom: 1px solid #eee;
                }
                .info-label {
                    font-weight: bold;
                    color: #667eea;
                    width: 180px;
                    flex-shrink: 0;
                }
                .info-value {
                    color: #333;
                }
                .booking-number {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #fff;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }
                .price-highlight {
                    background-color: #ffd700;
                    color: #333;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    font-size: 24px;
                    font-weight: bold;
                    margin-top: 20px;
                }
                .footer {
                    background-color: #f8f8f8;
                    padding: 20px;
                    text-align: center;
                    color: #666;
                    font-size: 12px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎯 New VIP Booking Received!</h1>
                </div>
                <div class="content">
                    <div class="booking-number">
                        📋 Booking #' . esc_html($booking_number) . '
                    </div>
                    <div class="booking-info">
                        <div class="info-row">
                            <div class="info-label">👤 Customer:</div>
                            <div class="info-value">' . esc_html($customer_name) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">🏪 Service:</div>
                            <div class="info-value">' . esc_html($service) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">📍 Store:</div>
                            <div class="info-value">' . esc_html($store) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">📦 Package:</div>
                            <div class="info-value">' . esc_html($package) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">🌍 Nationality:</div>
                            <div class="info-value">' . esc_html($nation) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">👥 Number of People:</div>
                            <div class="info-value">' . esc_html($pax) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">📅 Date:</div>
                            <div class="info-value">' . esc_html($date) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">🕐 Time:</div>
                            <div class="info-value">' . esc_html($time) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">⏰ Created:</div>
                            <div class="info-value">' . esc_html($formatted_created) . '</div>
                        </div>
                    </div>
                    <div class="price-highlight">
                        💰 Price: ' . esc_html($formatted_price) . ' VND
                    </div>
                </div>
                <div class="footer">
                    This is an automated notification from VIP Booking System<br>
                    ' . esc_html(get_bloginfo('name')) . '
                </div>
            </div>
        </body>
        </html>
        ';

        return $html;
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
}
