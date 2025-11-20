<?php
/**
 * Temporary Email Test Script
 * Place this in your WordPress root directory and access via browser
 * URL: http://yoursite.com/test-email.php
 * DELETE THIS FILE AFTER TESTING!
 */

// Load WordPress
require_once('wp-load.php');

// Check if WordPress loaded
if (!function_exists('wp_mail')) {
    die('WordPress not loaded properly');
}

echo '<h1>VIP Booking Email Test</h1>';

// Test 1: Simple wp_mail test
echo '<h2>Test 1: Simple Email Test</h2>';
$to = 'your-email@example.com'; // CHANGE THIS TO YOUR EMAIL
$subject = 'VIP Booking Test Email - ' . date('Y-m-d H:i:s');
$message = 'This is a test email from VIP Booking plugin. If you receive this, wp_mail() is working!';
$headers = array('Content-Type: text/html; charset=UTF-8');

$sent = wp_mail($to, $subject, $message, $headers);

if ($sent) {
    echo '<p style="color: green;">✅ Email sent successfully to: ' . esc_html($to) . '</p>';
    echo '<p>Check your inbox (and spam folder)</p>';
} else {
    echo '<p style="color: red;">❌ Email failed to send</p>';
}

// Test 2: Check mail configuration
echo '<h2>Test 2: WordPress Email Configuration</h2>';
echo '<ul>';
echo '<li>Admin Email: ' . get_option('admin_email') . '</li>';
echo '<li>Site URL: ' . get_site_url() . '</li>';
echo '<li>WordPress Version: ' . get_bloginfo('version') . '</li>';
echo '</ul>';

// Test 3: Check if SMTP plugin is active
echo '<h2>Test 3: SMTP Plugin Check</h2>';
$smtp_plugins = array(
    'wp-mail-smtp/wp_mail_smtp.php' => 'WP Mail SMTP',
    'easy-wp-smtp/easy-wp-smtp.php' => 'Easy WP SMTP',
    'post-smtp/postman-smtp.php' => 'Post SMTP',
);

$found_smtp = false;
foreach ($smtp_plugins as $plugin => $name) {
    if (is_plugin_active($plugin)) {
        echo '<p style="color: green;">✅ ' . esc_html($name) . ' is active</p>';
        $found_smtp = true;
    }
}

if (!$found_smtp) {
    echo '<p style="color: orange;">⚠️ No SMTP plugin detected. You should install "WP Mail SMTP" plugin for reliable email delivery.</p>';
}

// Test 4: Try VIP Booking Email Notifier
echo '<h2>Test 4: VIP Booking Email System Check</h2>';
if (class_exists('VIP_Booking_Email_Notifier')) {
    echo '<p style="color: green;">✅ VIP_Booking_Email_Notifier class exists</p>';
} else {
    echo '<p style="color: red;">❌ VIP_Booking_Email_Notifier class not found</p>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ol>';
echo '<li>Check your email inbox and spam folder</li>';
echo '<li>If no email received, install and configure "WP Mail SMTP" plugin</li>';
echo '<li>Configure WP Mail SMTP with your email provider (Gmail, SendGrid, etc.)</li>';
echo '<li>Test again using VIP Booking admin panel</li>';
echo '<li><strong style="color: red;">DELETE THIS FILE (test-email.php) AFTER TESTING!</strong></li>';
echo '</ol>';
?>
