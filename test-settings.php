<?php
/**
 * Test script to verify settings are being saved
 * Place this file in wp-content/plugins/vip-booking/ and access via browser
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Must be logged in as administrator.');
}

echo '<h1>VIP Booking Settings Test</h1>';

// Read current values
$exchange_rate = get_option('vip_booking_exchange_rate', 'NOT SET');
$limit_2h = get_option('vip_booking_limit_2h', 'NOT SET');
$limit_12h = get_option('vip_booking_limit_12h', 'NOT SET');
$cleanup_period = get_option('vip_booking_cleanup_period', 'NOT SET');

echo '<h2>Current Settings in Database:</h2>';
echo '<ul>';
echo '<li><strong>Exchange Rate:</strong> ' . $exchange_rate . '</li>';
echo '<li><strong>Rate Limit (2h):</strong> ' . $limit_2h . '</li>';
echo '<li><strong>Rate Limit (12h):</strong> ' . $limit_12h . '</li>';
echo '<li><strong>Cleanup Period:</strong> ' . $cleanup_period . ' (displayed as: ' . abs(intval($cleanup_period)) . ')</li>';
echo '</ul>';

// Test write
if (isset($_GET['test_write'])) {
    echo '<h2>Testing Write Operations...</h2>';

    update_option('vip_booking_limit_2h', 10);
    update_option('vip_booking_limit_12h', 20);
    update_option('vip_booking_cleanup_period', -120);

    echo '<p style="color: green;">✅ Test values written:</p>';
    echo '<ul>';
    echo '<li>limit_2h = 10</li>';
    echo '<li>limit_12h = 20</li>';
    echo '<li>cleanup_period = -120</li>';
    echo '</ul>';
    echo '<p><a href="?">Refresh to see values</a> | <a href="?reset=1">Reset to defaults</a></p>';
}

// Reset to defaults
if (isset($_GET['reset'])) {
    update_option('vip_booking_limit_2h', 2);
    update_option('vip_booking_limit_12h', 4);
    update_option('vip_booking_cleanup_period', -90);
    echo '<p style="color: blue;">✅ Reset to defaults</p>';
    echo '<p><a href="?">Refresh</a></p>';
}

if (!isset($_GET['test_write']) && !isset($_GET['reset'])) {
    echo '<p><a href="?test_write=1" style="padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 3px;">Test Write Operations</a></p>';
}
?>
