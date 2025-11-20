<?php
$exchange_rate = get_option('vip_booking_exchange_rate', 25000);

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
            </div>
            
            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #d63638; margin-bottom: 15px;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking_secret]</code>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>Guest booking form</strong> - Allows bookings WITHOUT login (use with caution).
                </p>
            </div>
            
            <div class="shortcode-box" style="background: #f5f5f5; padding: 15px; border-left: 4px solid #00a32a; margin-bottom: 0;">
                <code style="font-size: 14px; color: #d63638; font-weight: bold;">[vip_booking_user]</code>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <strong>User dashboard</strong> - Displays booking history for logged-in users (with card regeneration).
                </p>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Booking Manager -->
    <div id="tab-bookings" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0;">Booking Manager</h2>
            <button id="delete-selected-bookings" class="button button-secondary" style="border-color: #dc3232;">🗑️ Delete Selected</button>
        </div>

        <div class="vip-booking-cleanup-settings" style="background: white; padding: 15px; margin-bottom: 20px; border: 1px solid #ddd;">
            <h3>Cleanup Settings</h3>
            <div style="margin-bottom: 10px;">
                <label style="display: inline-block; width: 200px;">Auto-cleanup period:</label>
                <input type="number" id="cleanup-period" value="-90" min="-3650" max="-1" style="width: 100px; padding: 5px;">
                <span style="color: #666; margin-left: 10px;">days (negative value, e.g., -90 for 90 days old)</span>
            </div>
            <button id="save-cleanup-period" class="button button-primary">Save Cleanup Settings</button>
            <p style="color: #666; font-size: 12px; margin: 10px 0 0 0;">
                ℹ️ Bookings older than this period will be automatically deleted daily. Default: -90 days
            </p>
        </div>

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
                <input type="number" id="limit-2h" value="2" min="1" max="100" style="width: 100px; padding: 5px;">
                <span style="color: #666; margin-left: 10px;">bookings per 2 hours</span>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: inline-block; width: 200px;">Rate Limit (12 hours):</label>
                <input type="number" id="limit-12h" value="4" min="1" max="100" style="width: 100px; padding: 5px;">
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
            <button id="add-row" class="button button-primary">➕ Add New Row</button>
            <button id="save-changes" class="button button-primary">💾 Save Changes</button>
            <button id="delete-selected" class="button button-secondary">🗑️ Delete Selected</button>
            <button id="reset-all" class="button button-secondary" style="background: #dc3232; border-color: #dc3232; color: white;">🔄 Reset All Data</button>
            <button id="export-csv" class="button button-secondary">📤 Export CSV</button>
            <button id="import-csv" class="button button-secondary">📥 Import CSV</button>
            <input type="file" id="csv-file-input" accept=".csv" style="display: none;">
        </div>
        
        <div class="vip-booking-table-container">
            <table class="wp-list-table widefat fixed striped" id="vip-booking-table">
                <thead>
                    <tr>
                        <th class="check-column" style="width: 30px;"><input type="checkbox" id="select-all"></th>
                        <th style="width: 100px;">Service</th>
                        <th style="width: 150px;">Store Name</th>
                        <th style="width: 100px;">Service Package</th>
                        <th style="width: 100px;">Price (VND)</th>
                        <th style="width: 80px;">Opening</th>
                        <th style="width: 80px;">Closing</th>
                        <th style="width: 50px;">Prebook</th>
                        <th style="width: 30px;">Delete</th>
                    </tr>
                </thead>
                <tbody id="vip-booking-tbody"></tbody>
            </table>
        </div>
    </div>
    
    <div id="loading-overlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
</div>

<style>
.nav-tab-wrapper { margin-bottom: 20px; }
.tab-content { display: none; padding: 20px 0; }
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
.vip-booking-toolbar { position: sticky; top: 32px; background: #fff; padding: 15px 0; margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
.vip-booking-table-container { overflow-x: auto; width: 100%; }
#vip-booking-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
#vip-booking-table th, #vip-booking-table td { padding: 8px 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
#vip-booking-table input[type="text"], #vip-booking-table input[type="time"], #vip-booking-table input[type="number"] { width: 100%; box-sizing: border-box; padding: 4px 6px; font-size: 13px; }
#vip-booking-table .check-column { text-align: center; padding: 8px 2px; }
#vip-booking-table .delete-row { padding: 2px 6px; min-width: auto; }
#bookings-table .check-column { text-align: center; padding-left: 5px; }
#bookings-table .booking-checkbox { margin: 0 auto; display: block; }
#bookings-table #select-all-bookings { margin: 0 auto; display: block; }
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