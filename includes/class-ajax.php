<?php
class VIP_Booking_AJAX {
    private $rate_limiter;
    
    public function __construct() {
        $this->rate_limiter = new VIP_Booking_Rate_Limiter();
        
        // Admin AJAX
        add_action('wp_ajax_vip_booking_save_data', array($this, 'save_data'));
        add_action('wp_ajax_vip_booking_get_data', array($this, 'get_data'));
        add_action('wp_ajax_vip_booking_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_vip_booking_get_settings', array($this, 'get_settings'));
        add_action('wp_ajax_vip_booking_save_flags', array($this, 'save_flags'));
        add_action('wp_ajax_vip_booking_get_flags', array($this, 'get_flags'));
        add_action('wp_ajax_vip_booking_delete_booking', array($this, 'delete_booking'));
        add_action('wp_ajax_vip_booking_delete_multiple', array($this, 'delete_multiple'));
        add_action('wp_ajax_vip_booking_mark_complete', array($this, 'mark_complete'));
        add_action('wp_ajax_vip_booking_save_cleanup_period', array($this, 'save_cleanup_period'));
        add_action('wp_ajax_vip_booking_get_cleanup_period', array($this, 'get_cleanup_period'));
        add_action('wp_ajax_vip_booking_save_badge_url', array($this, 'save_badge_url'));
        add_action('wp_ajax_vip_booking_get_badge_url', array($this, 'get_badge_url'));
        add_action('wp_ajax_nopriv_vip_booking_get_badge_url', array($this, 'get_badge_url'));
        add_action('wp_ajax_vip_booking_save_notification_settings', array($this, 'save_notification_settings'));
        add_action('wp_ajax_vip_booking_get_notification_settings', array($this, 'get_notification_settings'));
        add_action('wp_ajax_vip_booking_test_telegram', array($this, 'test_telegram'));
        add_action('wp_ajax_vip_booking_test_email', array($this, 'test_email'));

        // Frontend AJAX
        add_action('wp_ajax_vip_booking_check_rate_limit', array($this, 'check_rate_limit'));
        add_action('wp_ajax_vip_booking_record_booking', array($this, 'record_booking'));
        add_action('wp_ajax_vip_booking_create_booking', array($this, 'create_booking'));
        add_action('wp_ajax_vip_booking_check_login', array($this, 'check_login'));
        add_action('wp_ajax_nopriv_vip_booking_check_login', array($this, 'check_login'));
        add_action('wp_ajax_vip_booking_get_badge_count', array($this, 'get_badge_count'));
        add_action('wp_ajax_nopriv_vip_booking_get_badge_count', array($this, 'get_badge_count'));
    }
    
    // Admin endpoints
    public function save_data() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();
        VIP_Booking_Admin::save_data($data);
        wp_send_json_success();
    }
    
    public function get_data() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        wp_send_json_success(VIP_Booking_Admin::get_data());
    }
    
    public function save_settings() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $settings = array(
            'exchange_rate' => isset($_POST['exchange_rate']) ? $_POST['exchange_rate'] : 25000,
            'limit_2h' => isset($_POST['limit_2h']) ? intval($_POST['limit_2h']) : 2,
            'limit_12h' => isset($_POST['limit_12h']) ? intval($_POST['limit_12h']) : 4
        );
        VIP_Booking_Admin::save_settings($settings);
        wp_send_json_success();
    }

    public function get_settings() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        wp_send_json_success(VIP_Booking_Admin::get_settings());
    }

    public function save_cleanup_period() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $period = isset($_POST['cleanup_period']) ? intval($_POST['cleanup_period']) : -90;
        VIP_Booking_Admin::save_cleanup_period($period);
        wp_send_json_success();
    }

    public function get_cleanup_period() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        wp_send_json_success(array('cleanup_period' => VIP_Booking_Admin::get_cleanup_period()));
    }

    public function save_badge_url() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $badge_url = isset($_POST['badge_url']) ? sanitize_text_field($_POST['badge_url']) : '';
        VIP_Booking_Admin::save_badge_url($badge_url);
        wp_send_json_success();
    }

    public function get_badge_url() {
        // Public endpoint - no nonce or permission check needed
        wp_send_json_success(array('badge_url' => VIP_Booking_Admin::get_badge_url()));
    }

    public function save_flags() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $flags = isset($_POST['flags']) ? json_decode(stripslashes($_POST['flags']), true) : array();
        VIP_Booking_Admin::save_flags($flags);
        wp_send_json_success();
    }
    
    public function get_flags() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        wp_send_json_success(VIP_Booking_Admin::get_flags());
    }
    
    public function delete_booking() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        if (wp_delete_post($id, true)) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete');
        }
    }
    
    public function delete_multiple() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        
        $booking_ids = isset($_POST['booking_ids']) ? $_POST['booking_ids'] : array();
        if (empty($booking_ids) || !is_array($booking_ids)) {
            wp_send_json_error('No booking IDs provided');
        }
        
        $deleted_count = 0;
        foreach ($booking_ids as $id) {
            $id = intval($id);
            if ($id > 0 && wp_delete_post($id, true)) {
                $deleted_count++;
            }
        }
        
        if ($deleted_count > 0) {
            wp_send_json_success(array('deleted' => $deleted_count));
        } else {
            wp_send_json_error('Failed to delete bookings');
        }
    }
    
    public function mark_complete() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
        $id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        update_post_meta($id, '_booking_status', 'completed');
        wp_send_json_success();
    }
    
    // Frontend endpoints
    public function check_rate_limit() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        $result = $this->rate_limiter->check_limit();
        if ($result['allowed']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function record_booking() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        $this->rate_limiter->record_booking();
        wp_send_json_success();
    }
    
    public function create_booking() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!is_user_logged_in()) wp_send_json_error('Not logged in');

        $data = json_decode(stripslashes($_POST['booking_data']), true);

        $booking_id = wp_insert_post(array(
            'post_type' => 'vip_booking',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
            'post_title' => 'Booking ' . time(),
        ));

        if ($booking_id) {
            $booking_number = 'VIP-' . str_pad($booking_id, 6, '0', STR_PAD_LEFT);

            try {
                $tz = wp_timezone();
                $dt = new DateTime($data['date'] . ' ' . $data['time'], $tz);
                $booking_timestamp = $dt->getTimestamp();
            } catch (Exception $e) {
                $booking_timestamp = strtotime($data['date'] . ' ' . $data['time']);
            }

            update_post_meta($booking_id, '_booking_number', $booking_number);
            update_post_meta($booking_id, '_booking_service', sanitize_text_field($data['service']));
            update_post_meta($booking_id, '_booking_store', sanitize_text_field($data['store']));
            update_post_meta($booking_id, '_booking_package', sanitize_text_field($data['package']));
            update_post_meta($booking_id, '_booking_price', intval($data['price']));
            update_post_meta($booking_id, '_booking_nation', sanitize_text_field($data['nation']));
            update_post_meta($booking_id, '_booking_pax', intval($data['pax']));
            update_post_meta($booking_id, '_booking_date', sanitize_text_field($data['date']));
            update_post_meta($booking_id, '_booking_time', sanitize_text_field($data['time']));
            update_post_meta($booking_id, '_booking_timestamp', $booking_timestamp);
            update_post_meta($booking_id, '_booking_status', 'confirmed');
            update_post_meta($booking_id, '_booking_created_at', time());

            // Save frontend-generated card image
            if (isset($_POST['card_image']) && !empty($_POST['card_image'])) {
                $card_image_data = $_POST['card_image'];
                $card_path = $this->save_card_image($booking_id, $card_image_data);
                if ($card_path) {
                    update_post_meta($booking_id, '_booking_card_image', $card_path);
                }
            }

            // Send notifications
            $this->send_booking_notifications($booking_id);

            wp_send_json_success(array('booking_id' => $booking_id, 'booking_number' => $booking_number));
        } else {
            wp_send_json_error('Failed to create booking');
        }
    }

    private function save_card_image($booking_id, $base64_image) {
        // Remove data:image/png;base64, prefix if present
        if (strpos($base64_image, ',') !== false) {
            $base64_image = explode(',', $base64_image)[1];
        }

        // Decode base64
        $image_data = base64_decode($base64_image);
        if ($image_data === false) {
            return false;
        }

        // Save to uploads directory
        $upload_dir = wp_upload_dir();
        $filename = 'booking-card-' . $booking_id . '-' . time() . '.png';
        $filepath = $upload_dir['path'] . '/' . $filename;

        // Write file
        $saved = file_put_contents($filepath, $image_data);
        if ($saved === false) {
            return false;
        }

        return $filepath;
    }
    
    public function check_login() {
        wp_send_json_success(array('logged_in' => is_user_logged_in()));
    }

    public function get_badge_count() {
        // No nonce check needed for public read-only endpoint
        // Return 0 if not logged in
        if (!is_user_logged_in()) {
            wp_send_json_success(array('count' => 0, 'logged_in' => false));
            return;
        }

        $user_id = get_current_user_id();
        $current_time = time();

        // Query upcoming bookings (confirmed status and future timestamp)
        $args = array(
            'post_type' => 'vip_booking',
            'post_status' => 'publish',
            'author' => $user_id,
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_booking_status',
                    'value' => 'confirmed',
                    'compare' => '='
                ),
                array(
                    'key' => '_booking_timestamp',
                    'value' => $current_time,
                    'compare' => '>',
                    'type' => 'NUMERIC'
                )
            )
        );

        $query = new WP_Query($args);
        $count = $query->found_posts;

        wp_send_json_success(array('count' => $count, 'logged_in' => true));
    }

    // Notification endpoints
    public function save_notification_settings() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');

        $settings = array(
            'telegram_enabled' => isset($_POST['telegram_enabled']) ? (bool) $_POST['telegram_enabled'] : false,
            'telegram_bot_token' => isset($_POST['telegram_bot_token']) ? sanitize_text_field($_POST['telegram_bot_token']) : '',
            'telegram_chat_ids' => isset($_POST['telegram_chat_ids']) ? array_map('sanitize_text_field', $_POST['telegram_chat_ids']) : array(),
            'email_enabled' => isset($_POST['email_enabled']) ? (bool) $_POST['email_enabled'] : false,
            'email_recipients' => isset($_POST['email_recipients']) ? array_map('sanitize_email', $_POST['email_recipients']) : array(),
            'send_card_image' => isset($_POST['send_card_image']) ? (bool) $_POST['send_card_image'] : true,
            'notification_template' => isset($_POST['notification_template']) ? wp_kses_post($_POST['notification_template']) : VIP_Booking_Notification_Settings::get_default_template()
        );

        VIP_Booking_Notification_Settings::save_notification_settings($settings);
        wp_send_json_success();
    }

    public function get_notification_settings() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');

        $settings = VIP_Booking_Notification_Settings::get_notification_settings();
        wp_send_json_success($settings);
    }

    public function test_telegram() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');

        $bot_token = isset($_POST['bot_token']) ? sanitize_text_field($_POST['bot_token']) : '';
        $chat_id = isset($_POST['chat_id']) ? sanitize_text_field($_POST['chat_id']) : '';

        if (empty($bot_token) || empty($chat_id)) {
            wp_send_json_error('Bot token and chat ID are required');
        }

        $result = VIP_Booking_Telegram_Notifier::test_connection($bot_token, $chat_id);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    public function test_email() {
        check_ajax_referer('vip_booking_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');

        $test_email = isset($_POST['test_email']) ? sanitize_email($_POST['test_email']) : '';

        if (empty($test_email) || !is_email($test_email)) {
            wp_send_json_error('Valid email address is required');
        }

        $subject = '🎯 VIP Booking - Test Email - ' . date('Y-m-d H:i:s');

        $current_time = date('Y-m-d H:i:s');
        $site_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');

        // Use heredoc for cleaner HTML
        $message = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: rgba(255,255,255,0.1); padding: 30px; text-align: center;">
            <h1 style="color: #fff; margin: 0; font-size: 28px;">✅ VIP Booking Test Email</h1>
        </div>
        <div style="background-color: #fff; padding: 30px;">
            <h2 style="color: #667eea; margin-top: 0;">Email Test Successful!</h2>
            <p style="color: #333; font-size: 16px; line-height: 1.6;">
                If you are reading this, your WordPress email system is working correctly!
            </p>
            <p style="color: #333; font-size: 16px; line-height: 1.6;">
                <strong>Test Details:</strong><br>
                🕐 Time: {$current_time}<br>
                🌐 Site: {$site_name}<br>
                📧 From: {$admin_email}
            </p>
            <div style="background-color: #d4edda; border-left: 4px solid #00a32a; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; color: #155724;">
                    ✅ Your VIP Booking notification system is ready to send emails!
                </p>
            </div>
        </div>
        <div style="background-color: #f8f8f8; padding: 20px; text-align: center; color: #666; font-size: 12px;">
            This is an automated test from VIP Booking System
        </div>
    </div>
</body>
</html>
HTML;

        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );

        // Note: Not setting From header - let WP Mail SMTP handle it
        // This prevents conflicts when SMTP is configured with a different email

        // Add error logging
        add_action('wp_mail_failed', function($wp_error) {
            error_log('VIP Booking - Email test failed: ' . $wp_error->get_error_message());
        });

        $sent = wp_mail($test_email, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => 'Test email sent successfully! Check your inbox (and spam folder).'));
        } else {
            // Get the last error
            $error_message = 'Failed to send test email.';

            // Check if wp_mail_failed hook captured an error
            if (function_exists('error_get_last')) {
                $last_error = error_get_last();
                if ($last_error && strpos($last_error['message'], 'mail') !== false) {
                    $error_message .= ' Error: ' . $last_error['message'];
                }
            }

            wp_send_json_error($error_message . ' Please check your WordPress email configuration or install WP Mail SMTP plugin.');
        }
    }

    private function send_booking_notifications($booking_id) {
        $settings = VIP_Booking_Notification_Settings::get_notification_settings();

        // Send Telegram notification
        if ($settings['telegram_enabled']) {
            VIP_Booking_Telegram_Notifier::send_notification($booking_id);
        }

        // Send Email notification
        if ($settings['email_enabled']) {
            VIP_Booking_Email_Notifier::send_notification($booking_id);
        }
    }
}
