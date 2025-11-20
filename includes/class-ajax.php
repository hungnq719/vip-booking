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

            // Send notifications
            $this->send_booking_notifications($booking_id);

            wp_send_json_success(array('booking_id' => $booking_id, 'booking_number' => $booking_number));
        } else {
            wp_send_json_error('Failed to create booking');
        }
    }
    
    public function check_login() {
        wp_send_json_success(array('logged_in' => is_user_logged_in()));
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

        $message = '<!DOCTYPE html>
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
                        🕐 Time: ' . date('Y-m-d H:i:s') . '<br>
                        🌐 Site: ' . get_bloginfo('name') . '<br>
                        📧 From: ' . get_option('admin_email') . '
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
        </html>';

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: VIP Booking <' . get_option('admin_email') . '>'
        );

        $sent = wp_mail($test_email, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(array('message' => 'Test email sent successfully! Check your inbox (and spam folder).'));
        } else {
            wp_send_json_error('Failed to send test email. Please check your WordPress email configuration or install WP Mail SMTP plugin.');
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
