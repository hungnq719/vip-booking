<?php
$exchange_rate = get_option('vip_booking_exchange_rate', 25000);
$limit_2h = intval(get_option('vip_booking_limit_2h', 2));
$limit_12h = intval(get_option('vip_booking_limit_12h', 4));
$cleanup_period = abs(intval(get_option('vip_booking_cleanup_period', -90)));

// Get statistics with status breakdown
$now = current_time('timestamp', 1);
$all_bookings = get_posts(array(
    'post_type' => 'vip_booking',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'meta_query' => array(
        array(
            'key' => '_booking_timestamp',
            'compare' => 'EXISTS'
        )
    )
));

// Count by status
$total_upcoming = 0;
$total_completed = 0;
foreach ($all_bookings as $booking_id) {
    $timestamp = get_post_meta($booking_id, '_booking_timestamp', true);
    if ($timestamp > $now) {
        $total_upcoming++;
    } else {
        $total_completed++;
    }
}

$total_bookings = count($all_bookings);
$today = current_time('Y-m-d');

// Today bookings
$today_bookings = get_posts(array(
    'post_type' => 'vip_booking',
    'posts_per_page' => -1,
    'date_query' => array(array('after' => $today . ' 00:00:00', 'before' => $today . ' 23:59:59', 'inclusive' => true)),
    'fields' => 'ids'
));
$today_upcoming = 0;
$today_completed = 0;
foreach ($today_bookings as $booking_id) {
    $timestamp = get_post_meta($booking_id, '_booking_timestamp', true);
    if ($timestamp > $now) {
        $today_upcoming++;
    } else {
        $today_completed++;
    }
}

// Week bookings
$week_ago = date('Y-m-d', strtotime('-7 days'));
$week_bookings = get_posts(array(
    'post_type' => 'vip_booking',
    'posts_per_page' => -1,
    'date_query' => array(array('after' => $week_ago, 'before' => $today . ' 23:59:59', 'inclusive' => true)),
    'fields' => 'ids'
));
$week_upcoming = 0;
$week_completed = 0;
foreach ($week_bookings as $booking_id) {
    $timestamp = get_post_meta($booking_id, '_booking_timestamp', true);
    if ($timestamp > $now) {
        $week_upcoming++;
    } else {
        $week_completed++;
    }
}

// Month bookings
$month_ago = date('Y-m-d', strtotime('-30 days'));
$month_bookings = get_posts(array(
    'post_type' => 'vip_booking',
    'posts_per_page' => -1,
    'date_query' => array(array('after' => $month_ago, 'before' => $today . ' 23:59:59', 'inclusive' => true)),
    'fields' => 'ids'
));
$month_upcoming = 0;
$month_completed = 0;
foreach ($month_bookings as $booking_id) {
    $timestamp = get_post_meta($booking_id, '_booking_timestamp', true);
    if ($timestamp > $now) {
        $month_upcoming++;
    } else {
        $month_completed++;
    }
}
?>

<div class="wrap" id="vip-booking-admin">
    <h1>🎯 VIP Booking</h1>
    
    <!-- Tabs Navigation -->
    <div class="nav-tab-wrapper">
        <a href="#dashboard" class="nav-tab nav-tab-active" data-tab="dashboard">📊 Dashboard</a>
        <a href="#bookings" class="nav-tab" data-tab="bookings">📋 Booking Manager</a>
        <a href="#data" class="nav-tab" data-tab="data">🏪 Booking Data</a>
        <a href="#notifications" class="nav-tab" data-tab="notifications">🔔 Notifications</a>
    </div>
    
    <!-- Tab 1: Dashboard -->
    <div id="tab-dashboard" class="tab-content active">
        <h2>Dashboard Overview</h2>
        <div class="booking-stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                    <div class="stat-breakdown">
                        <span class="stat-upcoming">🕐 <?php echo $total_upcoming; ?> Upcoming</span>
                        <span class="stat-completed">✅ <?php echo $total_completed; ?> Completed</span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <div class="stat-label">Today</div>
                    <div class="stat-value"><?php echo count($today_bookings); ?></div>
                    <div class="stat-breakdown">
                        <span class="stat-upcoming">🕐 <?php echo $today_upcoming; ?></span>
                        <span class="stat-completed">✅ <?php echo $today_completed; ?></span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <div class="stat-label">This Week</div>
                    <div class="stat-value"><?php echo count($week_bookings); ?></div>
                    <div class="stat-breakdown">
                        <span class="stat-upcoming">🕐 <?php echo $week_upcoming; ?></span>
                        <span class="stat-completed">✅ <?php echo $week_completed; ?></span>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📆</div>
                <div class="stat-info">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value"><?php echo count($month_bookings); ?></div>
                    <div class="stat-breakdown">
                        <span class="stat-upcoming">🕐 <?php echo $month_upcoming; ?></span>
                        <span class="stat-completed">✅ <?php echo $month_completed; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shortcode Guide -->
        <div class="shortcode-guide" style="background: white; padding: 25px; margin-top: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">📖 Shortcode Usage Guide</h3>
            <p style="color: #666; margin-bottom: 20px;">Use these shortcodes to display booking features on any page or post:</p>
            
            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #2271b1; margin-bottom: 15px;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking]</code>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>Standard booking form</strong> - Requires users to be logged in before booking.
                </p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                    <p style="margin: 5px 0; color: #555; font-size: 13px;">
                        <strong>Optional Attribute:</strong>
                    </p>
                    <ul style="margin: 8px 0; padding-left: 20px; color: #666; font-size: 13px;">
                        <li><code>storeid</code> - Pre-select store and skip Steps 1-2 (e.g., <code>[vip_booking storeid="S1"]</code>)</li>
                    </ul>
                    <p style="margin: 10px 0 5px 0; color: #555; font-size: 13px;">
                        💡 <strong>Direct booking:</strong> When <code>storeid</code> is provided, the form starts directly from Step 3 (Package selection) with the specified store already selected. Perfect for store-specific landing pages, QR codes, and direct booking links.
                    </p>
                </div>
            </div>

            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #d63638; margin-bottom: 15px;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking_secret]</code>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>Guest booking form</strong> - Allows bookings WITHOUT login (use with caution).
                </p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                    <p style="margin: 5px 0; color: #555; font-size: 13px;">
                        <strong>Optional Attribute:</strong>
                    </p>
                    <ul style="margin: 8px 0; padding-left: 20px; color: #666; font-size: 13px;">
                        <li><code>storeid</code> - Pre-select store and skip Steps 1-2 (e.g., <code>[vip_booking_secret storeid="VIP"]</code>)</li>
                    </ul>
                    <p style="margin: 10px 0 5px 0; color: #555; font-size: 13px;">
                        💡 <strong>Direct booking:</strong> When <code>storeid</code> is provided, the form starts directly from Step 3 (Package selection) with the specified store already selected. Perfect for store-specific landing pages, QR codes, and direct booking links.
                    </p>
                </div>
            </div>
            
            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #00a32a; margin-bottom: 15px;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking_user]</code>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>User dashboard</strong> - Displays booking history for logged-in users (with card regeneration).
                </p>
            </div>

            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #d63638; margin-bottom: 0;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking_badge]</code>
                <p style="margin: 10px 0 5px 0; color: #666;">
                    <strong>Booking count badge</strong> - Circular red badge showing upcoming bookings count with fast "tada" animation. <strong>Only visible for logged-in users.</strong> Cache-compatible!
                </p>
                <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ddd;">
                    <p style="margin: 5px 0; color: #555; font-size: 13px;">
                        <strong>Attributes:</strong>
                    </p>
                    <ul style="margin: 8px 0; padding-left: 20px; color: #666; font-size: 13px;">
                        <li><code>size</code> - Badge size: <code>small</code>, <code>medium</code> (default), <code>large</code></li>
                        <li><code>show_zero</code> - Show badge when count is 0: <code>yes</code> (default), <code>no</code></li>
                    </ul>
                    <p style="margin: 10px 0 5px 0; color: #555; font-size: 13px;">
                        <strong>Examples:</strong>
                    </p>
                    <div style="background: #fff; padding: 10px; border-radius: 4px; margin-top: 8px;">
                        <code style="display: block; color: #0073aa; font-size: 12px; margin-bottom: 5px;">[vip_booking_badge]</code>
                        <code style="display: block; color: #0073aa; font-size: 12px;">[vip_booking_badge size="small" show_zero="no"]</code>
                    </div>
                    <p style="margin: 10px 0 5px 0; color: #555; font-size: 13px;">
                        🔒 <strong>Login required:</strong> Badge automatically hides for non-logged-in users.<br>
                        💡 <strong>Click behavior:</strong> Configure badge click URL in <strong>Booking Manager</strong> tab. Badge automatically detects page language (ko, en, zh, etc.) and navigates to the correct language version.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Booking Manager -->
    <div id="tab-bookings" class="tab-content">
        <h2 style="margin-bottom: 15px;">Booking Manager</h2>

        <div class="vip-booking-cleanup-settings" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 20px; margin-bottom: 20px; border: none; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3 style="margin-top: 0; color: #546e7a; display: flex; align-items: center; gap: 8px;"><span style="font-size: 20px;">⚙️</span> Cleanup Settings</h3>
            <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 12px;">
                <label style="font-weight: 600; color: #555; min-width: 160px;">Auto-cleanup period:</label>
                <input type="number" id="cleanup-period" value="<?php echo esc_attr($cleanup_period); ?>" min="1" max="3650" style="width: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                <span style="color: #666;">days old</span>
            </div>
            <button id="save-cleanup-period" class="button button-primary" style="background: linear-gradient(135deg, #5a6c7d 0%, #6d7f8d 100%); border: none; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 500; box-shadow: 0 2px 8px rgba(90,108,125,0.3); transition: all 0.3s;">💾 Save Cleanup Settings</button>
            <p style="color: #666; font-size: 12px; margin: 12px 0 0 0; padding: 12px; background: rgba(90,108,125,0.05); border-left: 3px solid #5a6c7d; border-radius: 4px;">
                ℹ️ Bookings older than this number of days will be automatically deleted daily. Default: 90 days
            </p>
        </div>

        <div class="vip-booking-badge-settings" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 20px; margin-bottom: 20px; border: none; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3 style="margin-top: 0; color: #546e7a; display: flex; align-items: center; gap: 8px;"><span style="font-size: 20px;">🔴</span> Booking Badge Settings</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; color: #555; margin-bottom: 8px;">Badge click URL:</label>
                <input type="url" id="badge-url" value="<?php echo esc_attr(get_option('vip_booking_badge_url', '')); ?>" placeholder="https://yoursite.com/my-bookings/" style="width: 100%; max-width: 500px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
            </div>
            <button id="save-badge-url" class="button button-primary" style="background: linear-gradient(135deg, #5a6c7d 0%, #6d7f8d 100%); border: none; color: white; padding: 10px 20px; border-radius: 6px; font-weight: 500; box-shadow: 0 2px 8px rgba(90,108,125,0.3); transition: all 0.3s;">💾 Save Badge Settings</button>
            <p style="color: #666; font-size: 12px; margin: 12px 0 0 0; padding: 12px; background: rgba(90,108,125,0.05); border-left: 3px solid #5a6c7d; border-radius: 4px;">
                ℹ️ Set the URL where users will be redirected when clicking the booking badge. Usually your user dashboard page with <code style="background: rgba(90,108,125,0.1); padding: 2px 6px; border-radius: 3px;">[vip_booking_user]</code> shortcode.<br>
                🌍 <strong>Multilingual support:</strong> Badge automatically detects page language (ko, en, zh, ru, etc.) and prepends it to the URL. Just set the base URL here (e.g., <code style="background: rgba(90,108,125,0.1); padding: 2px 6px; border-radius: 3px;">/my-bookings/</code>).
            </p>
        </div>

        <div class="bookings-toolbar" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
            <button id="delete-selected-bookings" class="button button-secondary" style="background: linear-gradient(135deg, #dc3232 0%, #c62d2d 100%); border: none; color: white; padding: 8px 16px; border-radius: 6px; font-weight: 500; box-shadow: 0 2px 6px rgba(220,50,50,0.3); transition: all 0.3s;">🗑️ Delete Selected</button>
        </div>

        <div class="bookings-table-wrapper">
            <table class="wp-list-table widefat fixed striped" id="bookings-table">
            <thead>
                <tr>
                    <th class="check-column" style="width:30px"><input type="checkbox" id="select-all-bookings"></th>
                    <th style="width:100px">Booking ID</th>
                    <th style="width:100px">User</th>
                    <th style="width:80px">Service</th>
                    <th style="width:150px">Store</th>
                    <th style="width:100px">Package</th>
                    <th style="width:50px">Nation</th>
                    <th style="width:50px">Pax</th>
                    <th style="width:100px">Date</th>
                    <th style="width:70px">Time</th>
                    <th style="width:100px">Price</th>
                    <th style="width:120px">Status</th>
                    <th style="width:60px">Delete</th>
                </tr>
            </thead>
            <tbody id="bookings-tbody">
                <?php
                $bookings = get_posts(array(
                    'post_type' => 'vip_booking',
                    'posts_per_page' => -1,
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_booking_timestamp',
                    'order' => 'DESC',
                ));

                if (empty($bookings)): ?>
                    <tr><td colspan="13" style="text-align:center;padding:30px;">No bookings yet</td></tr>
                <?php else:
                    foreach ($bookings as $booking):
                        $booking_timestamp = get_post_meta($booking->ID, '_booking_timestamp', true);
                        
                        // Auto determine status based on time
                        if ($booking_timestamp > $now) {
                            $status = 'upcoming';
                            $status_label = '🕐 Upcoming';
                            $status_class = 'upcoming';
                        } else {
                            $status = 'completed';
                            $status_label = '✅ Completed';
                            $status_class = 'completed';
                        }
                        
                        $user = get_user_by('id', $booking->post_author);
                ?>
                    <tr data-id="<?php echo $booking->ID; ?>">
                        <td class="check-column"><input type="checkbox" class="booking-checkbox" value="<?php echo $booking->ID; ?>"></td>
                        <td><strong><?php echo get_post_meta($booking->ID, '_booking_number', true); ?></strong></td>
                        <td><?php echo $user ? $user->display_name : 'Unknown'; ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_service', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_store', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_package', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_nation', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_pax', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_date', true); ?></td>
                        <td><?php echo get_post_meta($booking->ID, '_booking_time', true); ?></td>
                        <td><?php echo number_format(get_post_meta($booking->ID, '_booking_price', true)); ?> ₫</td>
                        <td>
                            <span class="status-badge status-<?php echo $status_class; ?>">
                                <?php echo $status_label; ?>
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <button class="button delete-booking-btn" data-booking-id="<?php echo $booking->ID; ?>" style="padding:2px 8px; border:none; background:transparent; cursor:pointer; font-size:16px;">❌</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    
    <!-- Tab 3: Booking Data -->
    <div id="tab-data" class="tab-content">
        <h2>Booking Data Management</h2>
        
        <div class="vip-booking-settings" style="background: white; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd;">
            <h3>Settings</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: inline-block; width: 200px;">Exchange Rate (VND/USD):</label>
                <input type="text" id="exchange-rate-display" value="<?php echo number_format($exchange_rate, 0, '.', ','); ?>" style="width: 200px; padding: 5px;">
                <input type="hidden" id="exchange-rate" value="<?php echo esc_attr($exchange_rate); ?>">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: inline-block; width: 200px;">Rate Limit (2 hours):</label>
                <input type="number" id="limit-2h" value="<?php echo esc_attr($limit_2h); ?>" min="1" max="100" style="width: 100px; padding: 5px;">
                <span style="color: #666; margin-left: 10px;">bookings per 2 hours</span>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: inline-block; width: 200px;">Rate Limit (12 hours):</label>
                <input type="number" id="limit-12h" value="<?php echo esc_attr($limit_12h); ?>" min="1" max="100" style="width: 100px; padding: 5px;">
                <span style="color: #666; margin-left: 10px;">bookings per 12 hours</span>
            </div>
            <button id="save-settings" class="button button-primary">Save Settings</button>
        </div>
        
        <div class="vip-booking-flags" style="background: white; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd;">
            <h3>Nation Flags</h3>
            <div id="flags-container" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; padding: 10px;"></div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="text" id="new-flag" placeholder="Enter flag emoji (e.g., 🇻🇳)" style="width: 200px; padding: 8px; font-size: 15px;" maxlength="4">
                <button id="add-flag" class="button button-secondary">➕ Add Flag</button>
                <button id="save-flags" class="button button-primary">💾 Save Flags</button>
            </div>
        </div>
        
        <div class="vip-booking-toolbar">
            <button id="add-store" class="button button-primary">➕ Add New Store</button>
            <button id="save-changes" class="button button-primary">💾 Save Changes</button>
            <button id="reset-all" class="button button-secondary" style="background: #dc3232; border-color: #dc3232; color: white;">🔄 Reset All Data</button>
            <button id="export-csv" class="button button-secondary">📤 Export CSV</button>
            <button id="import-csv" class="button button-secondary">📥 Import CSV</button>
            <input type="file" id="csv-file-input" accept=".csv" style="display: none;">
        </div>

        <div id="stores-container" class="stores-accordion">
            <!-- Store accordions will be dynamically rendered here -->
        </div>
    </div>

    <!-- Tab 4: Notifications -->
    <div id="tab-notifications" class="tab-content">
        <h2>Notification Settings</h2>

        <!-- Telegram Settings -->
        <div class="vip-notification-section" style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3 style="margin-top: 0;">📱 Telegram Notifications</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="telegram-enabled" style="width: auto;">
                    <strong>Enable Telegram Notifications</strong>
                </label>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;"><strong>Bot Token:</strong></label>
                <input type="text" id="telegram-bot-token" placeholder="Enter Telegram Bot Token" style="width: 100%; max-width: 450px; padding: 8px;">
                <p style="color: #666; font-size: 12px; margin: 5px 0 0 0;">
                    ℹ️ Get your bot token from <a href="https://t.me/BotFather" target="_blank">@BotFather</a> on Telegram
                </p>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px;"><strong>Chat IDs:</strong></label>
                <div id="telegram-chat-ids-container"></div>
                <button type="button" id="add-telegram-chat-id" class="button button-secondary" style="margin-top: 8px;">➕ Add Chat ID</button>
                <p style="color: #666; font-size: 12px; margin: 8px 0 0 0;">
                    ℹ️ Get your chat ID from <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a> on Telegram
                </p>
            </div>
            <div style="margin-bottom: 15px;">
                <button id="test-telegram" class="button button-secondary">🧪 Test Telegram Connection</button>
                <span id="telegram-test-result" style="margin-left: 10px;"></span>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="vip-notification-section" style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3 style="margin-top: 0;">📧 Email Notifications</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="email-enabled" style="width: auto;">
                    <strong>Enable Email Notifications</strong>
                </label>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px;"><strong>Email Recipients:</strong></label>
                <div id="email-recipients-container"></div>
                <button type="button" id="add-email-recipient" class="button button-secondary" style="margin-top: 8px;">➕ Add Recipient</button>
                <p style="color: #666; font-size: 12px; margin: 8px 0 0 0;">
                    ℹ️ Emails will be sent using your WordPress SMTP settings
                </p>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;"><strong>Test Email Address:</strong></label>
                <input type="email" id="test-email-address" placeholder="your-email@example.com" style="width: 100%; max-width: 450px; padding: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <button id="test-email" class="button button-secondary">🧪 Test Email Connection</button>
                <span id="email-test-result" style="margin-left: 10px;"></span>
            </div>
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-top: 15px;">
                <p style="margin: 0; color: #856404; font-size: 13px;">
                    ⚠️ <strong>Important:</strong> For reliable email delivery, install and configure <a href="<?php echo admin_url('plugin-install.php?s=WP+Mail+SMTP&tab=search&type=term'); ?>" target="_blank">WP Mail SMTP</a> plugin.
                </p>
            </div>
        </div>

        <!-- Card Image Settings -->
        <div class="vip-notification-section" style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3 style="margin-top: 0;">🎨 Card Image Settings</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" id="send-card-image" style="width: auto;">
                    <strong>Include Booking Card Image in Notifications</strong>
                </label>
                <p style="color: #666; font-size: 12px; margin: 10px 0 0 0;">
                    ℹ️ When enabled, a visual booking card will be generated and attached to notifications
                </p>
            </div>
        </div>

        <!-- Template Settings -->
        <div class="vip-notification-section" style="background: white; padding: 20px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 5px;">
            <h3 style="margin-top: 0;">📝 Notification Template</h3>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;"><strong>Message Template:</strong></label>
                <textarea id="notification-template" rows="12" style="width: 100%; max-width: 700px; padding: 8px; font-family: monospace;"></textarea>
                <p style="color: #666; font-size: 12px; margin: 10px 0 0 0;">
                    ℹ️ Available placeholders: {booking_number}, {customer_name}, {service}, {store}, {package}, {nation}, {pax}, {date}, {time}, {price}, {created_at}
                </p>
            </div>
            <div style="margin-bottom: 15px;">
                <button id="reset-template" class="button button-secondary">🔄 Reset to Default Template</button>
            </div>
        </div>

        <!-- Save Button -->
        <div style="padding: 15px 0;">
            <button id="save-notification-settings" class="button button-primary" style="padding: 10px 30px; font-size: 14px;">💾 Save All Notification Settings</button>
        </div>
    </div>

    <div id="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
</div>

<style>
.nav-tab-wrapper { margin-bottom: 20px; }
.tab-content { display: none; padding: 20px 0; }
.notification-input-row { display: flex; gap: 8px; align-items: center; margin-bottom: 8px; }
.notification-input-row input { flex: 1; max-width: 450px; padding: 8px; }
.notification-input-row .remove-btn { background: #dc3232; color: white; border: none; padding: 8px 12px; border-radius: 3px; cursor: pointer; font-size: 14px; }
.notification-input-row .remove-btn:hover { background: #c62d2d; }
.tab-content.active { display: block; }
.booking-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
.stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; }
.stat-icon { font-size: 48px; opacity: 0.8; }
.stat-label { color: #666; font-size: 14px; margin-bottom: 5px; }
.stat-value { font-size: 32px; font-weight: bold; color: #2271b1; }
.stat-breakdown { display: flex; gap: 12px; margin-top: 8px; font-size: 12px; }
.stat-upcoming { color: #d63638; font-weight: 600; }
.stat-completed { color: #00a32a; font-weight: 600; }
.status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.status-upcoming { background: #fff3cd; color: #856404; }
.status-completed { background: #d4edda; color: #155724; }
.vip-booking-toolbar { position: sticky; top: 32px; padding: 10px 0; display: flex; gap: 10px; flex-wrap: wrap; z-index: 99 }
.stores-accordion { margin-top: 20px; }
.store-section { background: linear-gradient(135deg, #f5f7fa 0%, #e3e8ec 100%); border: none; margin-bottom: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: all 0.3s; }
.store-section:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.12); transform: translateY(-2px); }
.store-header { background: linear-gradient(135deg, #546e7a 0%, #78909c 100%); color: white; padding: 18px 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.store-header:hover { background: linear-gradient(135deg, #465a65 0%, #607d8b 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.store-header-left { display: flex; align-items: center; gap: 15px; flex: 1; }
.store-header-icon { font-size: 20px; transition: transform 0.3s; }
.store-header-icon.collapsed { transform: rotate(-90deg); }
.store-header-info { display: flex; gap: 25px; align-items: center; flex-wrap: wrap; }
.store-info-item { display: flex; flex-direction: column; }
.store-info-label { font-size: 11px; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.5px; }
.store-info-value { font-size: 14px; font-weight: bold; margin-top: 2px; }
.store-header-actions { display: flex; gap: 8px; }
.store-header-actions button { background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.25); padding: 7px 14px; border-radius: 5px; cursor: pointer; font-size: 12px; transition: all 0.2s; font-weight: 500; }
.store-header-actions button:hover { background: rgba(255,255,255,0.25); transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.store-body { padding: 25px; display: none; background: linear-gradient(135deg, #fafbfc 0%, #f0f2f4 100%); }
.store-body.active { display: block; }
.store-fixed-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; padding: 20px; background: white; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.store-field { display: flex; flex-direction: column; }
.store-field label { font-size: 12px; font-weight: bold; color: #555; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; }
.store-field input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; transition: all 0.2s; }
.store-field input:focus { border-color: #5a6c7d; outline: none; box-shadow: 0 0 0 3px rgba(90,108,125,0.1); }
.packages-section h4 { margin-top: 0; margin-bottom: 12px; color: #333; font-size: 15px; }
.packages-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.packages-table th { background: linear-gradient(135deg, #f5f6f7 0%, #e8eaed 100%); padding: 12px 10px; text-align: left; font-size: 12px; font-weight: bold; color: #555; border-bottom: 2px solid #ddd; text-transform: uppercase; letter-spacing: 0.3px; }
.packages-table td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; }
.packages-table tr:last-child td { border-bottom: none; }
.packages-table tr:hover { background: #f8f9fa; }
.packages-table input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; transition: all 0.2s; }
.packages-table input:focus { border-color: #5a6c7d; outline: none; box-shadow: 0 0 0 3px rgba(90,108,125,0.1); }
.packages-table .delete-package-btn { cursor: pointer; font-size: 16px; transition: all 0.2s; opacity: 0.7; display: inline-block; }
.packages-table .delete-package-btn:hover { opacity: 1; transform: scale(1.2); }
.add-package-btn { margin-top: 12px; padding: 10px 18px; background: linear-gradient(135deg, #5a6c7d 0%, #6d7f8d 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.add-package-btn:hover { background: linear-gradient(135deg, #4a5c6d 0%, #5d6f7d 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.empty-store-message { text-align: center; padding: 40px; color: #666; font-style: italic; font-size: 15px; }
.bookings-table-wrapper { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
#bookings-table { margin: 0; border: none; border-radius: 0; }
#bookings-table thead tr { background: linear-gradient(135deg, #546e7a 0%, #78909c 100%); }
#bookings-table thead th { color: white; padding: 15px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: left; }
#bookings-table tbody tr { transition: all 0.2s; border-bottom: 1px solid #f0f0f0; }
#bookings-table tbody tr:hover { background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%); transform: scale(1.002); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
#bookings-table tbody tr:last-child { border-bottom: none; }
#bookings-table tbody td { padding: 15px 12px; font-size: 13px; vertical-align: middle; border: none; }
#bookings-table .check-column { text-align: center; padding: 15px 8px; width: 40px; }
#bookings-table .booking-checkbox { margin: 0 auto; display: block; width: 18px; height: 18px; cursor: pointer; }
#bookings-table #select-all-bookings { margin: 0 auto; display: block; width: 18px; height: 18px; cursor: pointer; }
.delete-booking-btn { background: transparent !important; border: none !important; padding: 6px 10px !important; cursor: pointer; font-size: 18px; opacity: 0.6; transition: all 0.2s; }
.delete-booking-btn:hover { opacity: 1; transform: scale(1.3); background: transparent !important; }
.status-badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.status-upcoming { background: linear-gradient(135deg, #fff3cd 0%, #ffe89d 100%); color: #856404; border: 1px solid #ffeaa7; }
.status-completed { background: linear-gradient(135deg, #d4edda 0%, #b8e6c0 100%); color: #155724; border: 1px solid #a3d9b1; }
.bookings-toolbar button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(220,50,50,0.4); }
.vip-booking-cleanup-settings button:hover, .vip-booking-badge-settings button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(90,108,125,0.4); }
.flag-item { font-size: 40px; padding: 10px; border: 2px solid #ddd; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
.flag-item:hover { border-color: #ff4444; transform: scale(1.15); }
#loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999; }
.loading-spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        $('#tab-' + $(this).data('tab')).addClass('active');
    });
    
    // Booking Manager: Select all checkboxes
    $('#select-all-bookings').change(function() {
        $('.booking-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Booking Manager: Delete selected bookings
    $('#delete-selected-bookings').click(function() {
        var selectedIds = [];
        $('.booking-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one booking to delete.');
            return;
        }

        if (!confirm('Delete ' + selectedIds.length + ' selected booking(s) permanently?')) {
            return;
        }

        $('#loading-overlay').show();
        $.post(ajaxurl, {
            action: 'vip_booking_delete_multiple',
            nonce: vipBookingAdmin.nonce,
            booking_ids: selectedIds
        }, function(response) {
            $('#loading-overlay').hide();
            if (response.success) {
                location.reload();
            } else {
                alert('Failed to delete bookings: ' + (response.data || 'Unknown error'));
            }
        });
    });

    // Booking Manager: Delete individual booking
    $(document).on('click', '.delete-booking-btn', function() {
        var bookingId = $(this).data('booking-id');
        var $row = $(this).closest('tr');

        if (!confirm('Delete this booking permanently?')) {
            return;
        }

        $('#loading-overlay').show();
        $.post(ajaxurl, {
            action: 'vip_booking_delete_booking',
            nonce: vipBookingAdmin.nonce,
            booking_id: bookingId
        }, function(response) {
            $('#loading-overlay').hide();
            if (response.success) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                    // Check if table is empty
                    if ($('#bookings-tbody tr').length === 0) {
                        $('#bookings-tbody').html('<tr><td colspan="13" style="text-align:center;padding:30px;">No bookings yet</td></tr>');
                    }
                });
            } else {
                alert('Failed to delete booking: ' + (response.data || 'Unknown error'));
            }
        });
    });
});
</script>