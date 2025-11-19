<?php
if (!is_user_logged_in()) {
    echo '<div style="text-align:center;padding:40px;background:white;border-radius:8px;margin:20px 0;">
        <div style="font-size:48px;margin-bottom:20px;">🔐</div>
        <h2>Login Required</h2>
        <p>Please login to view your booking history.</p>
        <a href="' . wp_login_url(get_permalink()) . '" style="display:inline-block;margin-top:20px;padding:10px 20px;text-decoration:none;">Login Now</a>
    </div>';
    return;
}

$user_id = get_current_user_id();
$now = current_time('timestamp');
$bookings = get_posts(array(
    'post_type' => 'vip_booking',
    'author' => $user_id,
    'posts_per_page' => -1,
    'orderby' => 'meta_value_num',
    'meta_key' => '_booking_timestamp',
    'order' => 'DESC',
));
$exchange_rate = get_option('vip_booking_exchange_rate', 25000);
?>

<div id="user-booking-dashboard" style="max-width:1200px;margin:0 auto;padding:20px;">
    <h1 style="text-align:center;margin-bottom:30px;">My Booking History</h1>
    
    <?php if (!empty($bookings)): ?>
    <div style="display:grid;gap:20px;">
        <?php foreach ($bookings as $booking): 
            $timestamp = get_post_meta($booking->ID, '_booking_timestamp', true);
            $is_upcoming = $timestamp > $now;
            $status_label = $is_upcoming ? '🕐 Upcoming' : '✅ Completed';
            $status_bg = $is_upcoming ? '#fff' : '#4CAF50';
            $status_color = $is_upcoming ? '#000' : '#fff';
            
            $price_vnd = get_post_meta($booking->ID, '_booking_price', true);
            $price_usd = number_format($price_vnd / $exchange_rate, 2);
            
            $booking_data = array(
                'id' => $booking->ID,
                'number' => get_post_meta($booking->ID, '_booking_number', true),
                'service' => get_post_meta($booking->ID, '_booking_service', true),
                'store' => get_post_meta($booking->ID, '_booking_store', true),
                'package' => get_post_meta($booking->ID, '_booking_package', true),
                'price' => $price_vnd,
                'nation' => get_post_meta($booking->ID, '_booking_nation', true),
                'pax' => get_post_meta($booking->ID, '_booking_pax', true),
                'date' => get_post_meta($booking->ID, '_booking_date', true),
                'time' => get_post_meta($booking->ID, '_booking_time', true),
            );
        ?>
            <div class="booking-card" data-booking='<?php echo esc_attr(json_encode($booking_data)); ?>' style="border:3px solid #ff9800;padding:25px;border-radius:30px;box-shadow:0 4px 12px rgba(255,152,0,.3);">
                <div style="display:grid;grid-template-columns:1fr auto;gap:20px;align-items:start;">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <div style="font-size:12px;">Booking #<?php echo $booking_data['number']; ?></div>
                            <div style="padding:10px 20px;border-radius:.3em;background:<?php echo $status_bg; ?>;color:<?php echo $status_color; ?>;"><?php echo $status_label; ?></div>
                        </div>
                        <h3 style="margin:0 0 15px 0;font-size:22px;color:#ff9800;">⋆⋆⋆✦ <?php echo esc_html($booking_data['store']); ?> ✦⋆⋆⋆</h3>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;color:#fff;">
                            <div><strong style="color:#ff9800;">Service:</strong> <?php echo esc_html($booking_data['service']); ?></div>
                            <div><strong style="color:#ff9800;">Package:</strong> 💎 <?php echo esc_html($booking_data['package']); ?></div>
                            <div><strong style="color:#ff9800;">Nation:</strong> <?php echo $booking_data['nation']; ?></div>
                            <div><strong style="color:#ff9800;">Guests:</strong> <?php echo $booking_data['pax']; ?> Pax</div>
                            <div><strong style="color:#ff9800;">Date:</strong> 🗓️ <?php echo date('M d, Y', strtotime($booking_data['date'])); ?></div>
                            <div><strong style="color:#ff9800;">Time:</strong> ⏰ <?php echo $booking_data['time']; ?></div>
                            <div><strong style="color:#ff9800;">Price:</strong> <?php echo number_format($price_vnd); ?> ₫ ~ $<?php echo $price_usd; ?></div>
                        </div>
                    </div>
                    <button class="show-card-btn" onclick="showBookingCard(this)" style="padding:10px 20px;">View Card</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:60px 20px;background:#000;border:3px solid #ff9800;border-radius:30px;">
        <div style="font-size:72px;margin-bottom:20px;">📭</div>
        <h2 style="color:#ff9800;margin:0 0 10px 0;">No Bookings Yet</h2>
        <p style="color:#999;">You haven't made any bookings yet. Start booking now!</p>
    </div>
    <?php endif; ?>
</div>

<div id="card-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:30px;padding:30px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;position:relative;box-shadow:0 10px 40px rgba(0,0,0,.5);">
        <h2 style="text-align:center;margin:0 0 20px 0;">Your Booking Card</h2>
        <canvas id="card-canvas" width="750" height="450" style="display:block;max-width:100%;height:auto;margin:20px auto;"></canvas>
        <div style="text-align:center;margin-top:20px;display:flex;gap:10px;justify-content:center;">
            <button onclick="downloadCard()" style="padding:10px 20px;">💾 Download Card</button>
            <button onclick="closeCardModal()" style="padding:10px 20px;background:#666;color:#fff;">Close</button>
        </div>
    </div>
</div>

<script>
var currentBookingData = null;
var templateImageUrl = '<?php echo esc_url(VIP_BOOKING_PLUGIN_URL . 'templates/vip-template.png'); ?>';

function showBookingCard(btn) {
    var card = btn.closest('.booking-card');
    var bookingData = JSON.parse(card.getAttribute('data-booking'));
    currentBookingData = bookingData;
    
    var modal = document.getElementById('card-modal');
    modal.style.display = 'flex';
    
    generateCardImage(bookingData);
}

function closeCardModal() {
    document.getElementById('card-modal').style.display = 'none';
}

function generateCardImage(data) {
    var canvas = document.getElementById('card-canvas');
    var ctx = canvas.getContext('2d');
    
    var img = new Image();
    img.src = templateImageUrl;
    img.crossOrigin = 'anonymous';
    
    img.onload = function() {
        ctx.clearRect(0, 0, 750, 450);
        ctx.drawImage(img, 0, 0, 750, 450);
        
        // Store name
        var storeText = '⋆⋆⋆✦ ' + data.store + ' ✦⋆⋆⋆';
        ctx.font = 'bold 24px Arial';
        ctx.textAlign = 'center';
        var tw = ctx.measureText(storeText).width;
        var x = 375, y = 290;
        var grad = ctx.createLinearGradient(x - tw/2, y, x + tw/2, y);
        grad.addColorStop(0, '#EDE3B6');
        grad.addColorStop(1, '#856641');
        ctx.fillStyle = grad;
        ctx.fillText(storeText, x, y);
        
        // Bottom info
        var dateObj = new Date(data.date);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var dateStr = months[dateObj.getMonth()] + ' ' + dateObj.getDate();
        var bottomText = data.nation + ' ' + data.pax + ' Pax  ⋆  ⏰ ' + data.time + '  ⋆  🗓️ ' + dateStr + '  ⋆  💎 ' + data.package;
        
        ctx.font = 'bold 28px Arial';
        ctx.fillStyle = '#000000';
        ctx.fillText(bottomText, 375, 370);
    };
    
    img.onerror = function() {
        ctx.fillStyle = '#f0f0f0';
        ctx.fillRect(0, 0, 750, 450);
        ctx.fillStyle = '#999';
        ctx.font = '20px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Template image not found', 375, 225);
        ctx.fillText('Path: ' + templateImageUrl, 375, 260);
    };
}

function downloadCard() {
    if (!currentBookingData) return;
    var canvas = document.getElementById('card-canvas');
    var link = document.createElement('a');
    link.download = 'vip-booking-' + currentBookingData.number + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// Close modal when clicking outside
document.getElementById('card-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCardModal();
    }
});
</script>

<style>
#user-booking-dashboard{background:transparent}
.booking-card{transition:all .3s}
.booking-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(255,152,0,.5)!important}
@media (max-width:768px){
.booking-card>div{grid-template-columns:1fr!important}
.booking-card>div>div:last-child{text-align:center}
.show-card-btn{width:100%}
#user-booking-dashboard h1{font-size:24px}
.booking-card h3{font-size:18px!important}
}
</style>