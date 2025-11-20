<?php
/**
 * VIP Booking Internationalization Class
 * Handles multi-language support for frontend and user dashboard
 */
class VIP_Booking_I18n {

    private static $translations = array(
        'en' => array(
            // Steps
            'choose_service' => 'Choose Service',
            'choose_store' => 'Choose Store',
            'service_package' => 'Service Package',
            'nation' => 'Nation',
            'number_of_guests' => 'Number of Guests',
            'date' => 'Date',
            'time' => 'Time',

            // Select placeholders
            'select_service' => 'Select service...',
            'select_store' => 'Select store...',
            'select_package' => 'Select package...',
            'complete_previous_step' => 'Complete previous step...',
            'complete_previous_steps' => 'Complete previous steps...',

            // Buttons
            'make_reservation' => 'Make Reservation',
            'save_to_photos' => '💾 Save to Photos',
            'back_to_form' => '← Back to Form',
            'view_card' => 'View Card',
            'close' => '❌ Close',
            'login_now' => '🔑 Login Now',
            'cancel' => 'Cancel',

            // Messages
            'successful' => 'Successful!',
            'success_message' => 'Please save this image and present it to the receptionist upon arrival to ensure the best support and service.',
            'login_required' => 'Login Required',
            'login_message' => 'To proceed with your booking, please login using your Telegram or Google account.',
            'login_refresh_message' => 'If you have already logged in, please refresh the Booking page.',
            'please_complete' => 'Please complete:',
            'please_select_hour' => 'Please select hour first',
            'failed_rate_limit' => 'Failed to check rate limit',

            // Rate limit
            'loading_booking_limits' => 'Loading booking limits...',
            'remaining_bookings' => '📊 Remaining bookings:',
            'time_singular' => 'Time',
            'times_plural' => 'Times',
            'no_bookings_available' => '❌ No bookings available at the moment',
            'please_refresh' => '👉 Please refresh to check availability',
            'you_can_book_again' => '✅ You can book again now! Refresh to continue.',
            'next_booking_available' => '⏰ Next booking available in:',

            // User Dashboard
            'my_booking_history' => 'My Booking History',
            'booking' => 'Booking #',
            'upcoming' => '🕐 Upcoming',
            'completed' => '✅ Completed',
            'service' => 'Service:',
            'package' => 'Package:',
            'guests' => 'Guests:',
            'price' => 'Price:',
            'no_bookings_yet' => 'No Bookings Yet',
            'no_bookings_message' => 'You haven\'t made any bookings yet. Start booking now!',
            'your_booking_card' => 'Your Booking Card',
            'login_to_view' => 'Please login to view your booking history.',

            // Days
            'today' => 'Today',
            'tomorrow' => 'Tomorrow',
            'sun' => 'Sun',
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
            'sat' => 'Sat',

            // Months
            'jan' => 'Jan',
            'feb' => 'Feb',
            'mar' => 'Mar',
            'apr' => 'Apr',
            'may' => 'May',
            'jun' => 'Jun',
            'jul' => 'Jul',
            'aug' => 'Aug',
            'sep' => 'Sep',
            'oct' => 'Oct',
            'nov' => 'Nov',
            'dec' => 'Dec',

            // Pax label
            'pax' => 'Pax',
        ),

        'ko' => array(
            // Steps
            'choose_service' => '서비스 선택',
            'choose_store' => '매장 선택',
            'service_package' => '서비스 패키지',
            'nation' => '국적',
            'number_of_guests' => '인원 수',
            'date' => '날짜',
            'time' => '시간',

            // Select placeholders
            'select_service' => '서비스를 선택하세요...',
            'select_store' => '매장을 선택하세요...',
            'select_package' => '패키지를 선택하세요...',
            'complete_previous_step' => '이전 단계를 완료하세요...',
            'complete_previous_steps' => '이전 단계를 완료하세요...',

            // Buttons
            'make_reservation' => '예약하기',
            'save_to_photos' => '💾 사진 저장',
            'back_to_form' => '← 양식으로 돌아가기',
            'view_card' => '카드 보기',
            'close' => '❌ 닫기',
            'login_now' => '🔑 로그인',
            'cancel' => '취소',

            // Messages
            'successful' => '성공!',
            'success_message' => '이 이미지를 저장하고 방문 시 접수처에 제시하여 최상의 지원과 서비스를 받으세요.',
            'login_required' => '로그인 필요',
            'login_message' => '예약을 진행하려면 텔레그램 또는 구글 계정으로 로그인하세요.',
            'login_refresh_message' => '이미 로그인한 경우 예약 페이지를 새로고침하세요.',
            'please_complete' => '다음을 완료하세요:',
            'please_select_hour' => '먼저 시간을 선택하세요',
            'failed_rate_limit' => '예약 한도 확인 실패',

            // Rate limit
            'loading_booking_limits' => '예약 한도 로딩 중...',
            'remaining_bookings' => '📊 남은 예약:',
            'time_singular' => '회',
            'times_plural' => '회',
            'no_bookings_available' => '❌ 현재 예약이 불가능합니다',
            'please_refresh' => '👉 새로고침하여 가능 여부를 확인하세요',
            'you_can_book_again' => '✅ 이제 다시 예약할 수 있습니다! 계속하려면 새로고침하세요.',
            'next_booking_available' => '⏰ 다음 예약 가능 시간:',

            // User Dashboard
            'my_booking_history' => '내 예약 기록',
            'booking' => '예약 번호 #',
            'upcoming' => '🕐 예정',
            'completed' => '✅ 완료',
            'service' => '서비스:',
            'package' => '패키지:',
            'guests' => '인원:',
            'price' => '가격:',
            'no_bookings_yet' => '아직 예약이 없습니다',
            'no_bookings_message' => '아직 예약하지 않으셨습니다. 지금 예약하세요!',
            'your_booking_card' => '예약 카드',
            'login_to_view' => '예약 기록을 보려면 로그인하세요.',

            // Days
            'today' => '오늘',
            'tomorrow' => '내일',
            'sun' => '일',
            'mon' => '월',
            'tue' => '화',
            'wed' => '수',
            'thu' => '목',
            'fri' => '금',
            'sat' => '토',

            // Months
            'jan' => '1월',
            'feb' => '2월',
            'mar' => '3월',
            'apr' => '4월',
            'may' => '5월',
            'jun' => '6월',
            'jul' => '7월',
            'aug' => '8월',
            'sep' => '9월',
            'oct' => '10월',
            'nov' => '11월',
            'dec' => '12월',

            // Pax label
            'pax' => '명',
        ),

        'ru' => array(
            // Steps
            'choose_service' => 'Выберите услугу',
            'choose_store' => 'Выберите заведение',
            'service_package' => 'Пакет услуг',
            'nation' => 'Национальность',
            'number_of_guests' => 'Количество гостей',
            'date' => 'Дата',
            'time' => 'Время',

            // Select placeholders
            'select_service' => 'Выберите услугу...',
            'select_store' => 'Выберите заведение...',
            'select_package' => 'Выберите пакет...',
            'complete_previous_step' => 'Завершите предыдущий шаг...',
            'complete_previous_steps' => 'Завершите предыдущие шаги...',

            // Buttons
            'make_reservation' => 'Забронировать',
            'save_to_photos' => '💾 Сохранить фото',
            'back_to_form' => '← Вернуться к форме',
            'view_card' => 'Посмотреть карту',
            'close' => '❌ Закрыть',
            'login_now' => '🔑 Войти',
            'cancel' => 'Отмена',

            // Messages
            'successful' => 'Успешно!',
            'success_message' => 'Сохраните это изображение и покажите его на ресепшене по прибытии для лучшего обслуживания.',
            'login_required' => 'Требуется вход',
            'login_message' => 'Для продолжения бронирования войдите через Telegram или Google.',
            'login_refresh_message' => 'Если вы уже вошли, обновите страницу бронирования.',
            'please_complete' => 'Пожалуйста, завершите:',
            'please_select_hour' => 'Сначала выберите час',
            'failed_rate_limit' => 'Не удалось проверить лимит',

            // Rate limit
            'loading_booking_limits' => 'Загрузка лимитов...',
            'remaining_bookings' => '📊 Осталось бронирований:',
            'time_singular' => 'Раз',
            'times_plural' => 'Раза',
            'no_bookings_available' => '❌ Бронирование сейчас недоступно',
            'please_refresh' => '👉 Обновите страницу для проверки',
            'you_can_book_again' => '✅ Теперь вы можете снова забронировать! Обновите страницу.',
            'next_booking_available' => '⏰ Следующее бронирование доступно через:',

            // User Dashboard
            'my_booking_history' => 'История моих бронирований',
            'booking' => 'Бронирование #',
            'upcoming' => '🕐 Предстоящее',
            'completed' => '✅ Завершено',
            'service' => 'Услуга:',
            'package' => 'Пакет:',
            'guests' => 'Гостей:',
            'price' => 'Цена:',
            'no_bookings_yet' => 'Пока нет бронирований',
            'no_bookings_message' => 'У вас еще нет бронирований. Начните бронировать сейчас!',
            'your_booking_card' => 'Ваша карта бронирования',
            'login_to_view' => 'Войдите, чтобы увидеть историю бронирований.',

            // Days
            'today' => 'Сегодня',
            'tomorrow' => 'Завтра',
            'sun' => 'Вс',
            'mon' => 'Пн',
            'tue' => 'Вт',
            'wed' => 'Ср',
            'thu' => 'Чт',
            'fri' => 'Пт',
            'sat' => 'Сб',

            // Months
            'jan' => 'Янв',
            'feb' => 'Фев',
            'mar' => 'Мар',
            'apr' => 'Апр',
            'may' => 'Май',
            'jun' => 'Июн',
            'jul' => 'Июл',
            'aug' => 'Авг',
            'sep' => 'Сен',
            'oct' => 'Окт',
            'nov' => 'Ноя',
            'dec' => 'Дек',

            // Pax label
            'pax' => 'чел',
        ),

        'zh' => array(
            // Steps
            'choose_service' => '选择服务',
            'choose_store' => '选择店铺',
            'service_package' => '服务套餐',
            'nation' => '国籍',
            'number_of_guests' => '客人数量',
            'date' => '日期',
            'time' => '时间',

            // Select placeholders
            'select_service' => '选择服务...',
            'select_store' => '选择店铺...',
            'select_package' => '选择套餐...',
            'complete_previous_step' => '请完成上一步...',
            'complete_previous_steps' => '请完成上一步...',

            // Buttons
            'make_reservation' => '预约',
            'save_to_photos' => '💾 保存照片',
            'back_to_form' => '← 返回表单',
            'view_card' => '查看卡片',
            'close' => '❌ 关闭',
            'login_now' => '🔑 立即登录',
            'cancel' => '取消',

            // Messages
            'successful' => '成功！',
            'success_message' => '请保存此图片，到达时向前台出示，以确保获得最佳支持和服务。',
            'login_required' => '需要登录',
            'login_message' => '要继续预约，请使用 Telegram 或 Google 账号登录。',
            'login_refresh_message' => '如果您已登录，请刷新预约页面。',
            'please_complete' => '请完成：',
            'please_select_hour' => '请先选择小时',
            'failed_rate_limit' => '检查预约限制失败',

            // Rate limit
            'loading_booking_limits' => '加载预约限制中...',
            'remaining_bookings' => '📊 剩余预约次数：',
            'time_singular' => '次',
            'times_plural' => '次',
            'no_bookings_available' => '❌ 目前无法预约',
            'please_refresh' => '👉 请刷新以检查可用性',
            'you_can_book_again' => '✅ 您现在可以再次预约！刷新以继续。',
            'next_booking_available' => '⏰ 下次可预约时间：',

            // User Dashboard
            'my_booking_history' => '我的预约记录',
            'booking' => '预约 #',
            'upcoming' => '🕐 即将到来',
            'completed' => '✅ 已完成',
            'service' => '服务：',
            'package' => '套餐：',
            'guests' => '客人：',
            'price' => '价格：',
            'no_bookings_yet' => '还没有预约',
            'no_bookings_message' => '您还没有任何预约。现在开始预约吧！',
            'your_booking_card' => '您的预约卡',
            'login_to_view' => '请登录以查看预约记录。',

            // Days
            'today' => '今天',
            'tomorrow' => '明天',
            'sun' => '周日',
            'mon' => '周一',
            'tue' => '周二',
            'wed' => '周三',
            'thu' => '周四',
            'fri' => '周五',
            'sat' => '周六',

            // Months
            'jan' => '1月',
            'feb' => '2月',
            'mar' => '3月',
            'apr' => '4月',
            'may' => '5月',
            'jun' => '6月',
            'jul' => '7月',
            'aug' => '8月',
            'sep' => '9月',
            'oct' => '10月',
            'nov' => '11月',
            'dec' => '12月',

            // Pax label
            'pax' => '人',
        ),
    );

    /**
     * Detect WordPress language and map to supported language
     */
    public static function get_current_language() {
        $locale = get_locale(); // Returns like en_US, ko_KR, ru_RU, zh_CN

        // Map WordPress locale to language code
        $locale_map = array(
            'en_US' => 'en',
            'en_GB' => 'en',
            'ko_KR' => 'ko',
            'ru_RU' => 'ru',
            'zh_CN' => 'zh',
            'zh_TW' => 'zh',
            'zh_HK' => 'zh',
        );

        // Extract language prefix (e.g., en from en_US)
        $lang_prefix = substr($locale, 0, 2);

        // Check exact match first
        if (isset($locale_map[$locale])) {
            return $locale_map[$locale];
        }

        // Check prefix match
        if (in_array($lang_prefix, array('en', 'ko', 'ru', 'zh'))) {
            return $lang_prefix;
        }

        // Default to English
        return 'en';
    }

    /**
     * Get all translations for current language
     */
    public static function get_translations() {
        $lang = self::get_current_language();

        if (isset(self::$translations[$lang])) {
            return self::$translations[$lang];
        }

        return self::$translations['en'];
    }

    /**
     * Get a specific translation
     */
    public static function get($key) {
        $translations = self::get_translations();

        if (isset($translations[$key])) {
            return $translations[$key];
        }

        // Fallback to English
        if (isset(self::$translations['en'][$key])) {
            return self::$translations['en'][$key];
        }

        return $key;
    }

    /**
     * Get translations as JSON for JavaScript
     */
    public static function get_translations_json() {
        return json_encode(self::get_translations());
    }
}
