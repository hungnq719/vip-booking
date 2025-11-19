<?php
class VIP_Booking_Rate_Limiter {
    const LIMIT_2H = 2;
    const LIMIT_12H = 4;
    const WINDOW_2H = 7200;
    const WINDOW_12H = 43200;
    
    public function check_limit($user_id = null) {
        if (!$user_id) $user_id = get_current_user_id();
        if (!$user_id) return array('allowed' => true, 'count_2h' => 0, 'count_12h' => 0, 'wait_time_2h' => 0, 'wait_time_12h' => 0);
        
        // Admin không bị giới hạn
        if (user_can($user_id, 'administrator')) {
            return array('allowed' => true, 'count_2h' => 0, 'count_12h' => 0, 'wait_time_2h' => 0, 'wait_time_12h' => 0, 'is_admin' => true);
        }
        
        // QUAN TRỌNG: Dùng time() (UTC) để tránh lỗi múi giờ
        $now = time();
        $two_hours_ago = $now - self::WINDOW_2H;
        $twelve_hours_ago = $now - self::WINDOW_12H;
        
        // Lấy tất cả bookings trong 12h gần nhất
        $bookings = get_posts(array(
            'post_type' => 'vip_booking',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_booking_created_at',
                    'value' => $twelve_hours_ago,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                )
            ),
            'orderby' => 'meta_value_num',
            'meta_key' => '_booking_created_at',
            'order' => 'ASC'
        ));
        
        // Lấy timestamps
        $timestamps = array();
        foreach ($bookings as $booking) {
            $timestamps[] = intval(get_post_meta($booking->ID, '_booking_created_at', true));
        }
        
        // Đếm bookings trong 2h và 12h
        $count_2h = 0;
        $count_12h = count($timestamps);
        
        foreach ($timestamps as $t) {
            if ($t > $two_hours_ago) {
                $count_2h++;
            }
        }
        
        // Tính thời gian chờ
        $wait_2h = 0;
        $wait_12h = 0;
        
        // Nếu hết limit 2h: đợi booking cũ nhất trong 2h window hết hạn
        if ($count_2h >= self::LIMIT_2H && !empty($timestamps)) {
            foreach ($timestamps as $t) {
                if ($t > $two_hours_ago) {
                    $wait_2h = ($t + self::WINDOW_2H) - $now;
                    break;
                }
            }
        }
        
        // Nếu hết limit 12h: đợi booking cũ nhất hết hạn
        if ($count_12h >= self::LIMIT_12H && !empty($timestamps)) {
            $wait_12h = ($timestamps[0] + self::WINDOW_12H) - $now;
        }
        
        // Cho phép nếu CẢ 2 điều kiện đều OK
        $allowed = ($count_2h < self::LIMIT_2H) && ($count_12h < self::LIMIT_12H);
        
        return array(
            'allowed' => $allowed,
            'count_2h' => $count_2h,
            'count_12h' => $count_12h,
            'remaining_2h' => max(0, self::LIMIT_2H - $count_2h),
            'remaining_12h' => max(0, self::LIMIT_12H - $count_12h),
            'wait_time_2h' => max(0, $wait_2h),
            'wait_time_12h' => max(0, $wait_12h),
            'message' => $allowed ? '' : 'Rate limit exceeded'
        );
    }
    
    public function record_booking($user_id = null) {
        // Không cần lưu vào user_meta nữa
        // Bookings đã được lưu vào wp_posts bởi create_booking()
        return true;
    }
}
