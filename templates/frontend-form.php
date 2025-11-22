<?php
/**
 * Frontend Template for VIP Booking
 */

// Check if this is secret mode (no login required)
if (!isset($require_login)) {
    $require_login = true; // Default: require login
}

// Check if storeid is provided
if (!isset($storeid)) {
    $storeid = '';
}

$vip_data = get_option('vip_booking_data', array());
$exchange_rate = get_option('vip_booking_exchange_rate', 25000);
$flags = get_option('vip_booking_flags', array('🇺🇸', '🇰🇷', '🇷🇺', '🇨🇳', '🇯🇵'));
$limit_2h = intval(get_option('vip_booking_limit_2h', 2));
$limit_12h = intval(get_option('vip_booking_limit_12h', 4));
$is_logged_in = is_user_logged_in();
$show_login_notice = $require_login && !$is_logged_in;

// Get translations
$i18n = VIP_Booking_I18n::get_translations();
?>

<div id="vip-booking-container">
    <!-- Step-by-step form -->
    <div id="booking-form" class="booking-steps">
        
        <!-- Step 1: Service -->
        <div class="step-item" data-step="1">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">1</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['choose_service']); ?></h3>
                <select id="service" class="step-select">
                    <option value=""><?php echo esc_html($i18n['select_service']); ?></option>
                </select>
            </div>
        </div>

        <!-- Step 2: Store -->
        <div class="step-item" data-step="2">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">2</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['choose_store']); ?></h3>
                <select id="store" disabled class="step-select">
                    <option value=""><?php echo esc_html($i18n['complete_previous_step']); ?></option>
                </select>
            </div>
        </div>

        <!-- Step 3: Package -->
        <div class="step-item" data-step="3">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">3</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['service_package']); ?></h3>
                <select id="package" disabled class="step-select">
                    <option value=""><?php echo esc_html($i18n['complete_previous_steps']); ?></option>
                </select>
                <div id="price-display" style="display: none; text-align: center;">
                    <strong style="color: #ff9800; font-size: 18px;" id="price"></strong>
                </div>
            </div>
        </div>

        <!-- Step 4: Nation -->
        <div class="step-item" data-step="4">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">4</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['nation']); ?></h3>
                <div class="flag-options">
                    <?php foreach ($flags as $flag): ?>
                    <label><input type="radio" name="flag" value="<?php echo esc_attr($flag); ?>"><span><?php echo esc_html($flag); ?></span></label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Step 5: Pax -->
        <div class="step-item" data-step="5">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">5</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['number_of_guests']); ?></h3>
                <div class="pax-selector" id="paxSelector"></div>
            </div>
        </div>

        <!-- Step 6: Date -->
        <div class="step-item" data-step="6">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">6</span>
                    <span class="step-check">✓</span>
                </div>
                <div class="step-line"></div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['date']); ?></h3>
                <div class="date-selector" id="dateSelector"></div>
            </div>
        </div>

        <!-- Step 7: Time -->
        <div class="step-item" data-step="7">
            <div class="step-header">
                <div class="step-circle">
                    <div class="pulse-ring"></div>
                    <span class="step-number">7</span>
                    <span class="step-check">✓</span>
                </div>
            </div>
            <div class="step-content">
                <h3 class="step-title"><?php echo esc_html($i18n['time']); ?></h3>
                <div class="new-time-picker">
                    <!-- Time Display -->
                    <div class="time-display-container">
                        <div class="time-box disabled" id="hourBox" data-active="false" data-disabled="true">
                            <span id="hourDisplay">--</span>
                        </div>
                        <div class="time-separator">:</div>
                        <div class="time-box disabled" id="minuteBox" data-active="false" data-disabled="true">
                            <span id="minuteDisplay">--</span>
                        </div>
                    </div>

                    <!-- Time Options Grid -->
                    <div class="time-options-container" id="timeOptionsContainer" style="display: none;">
                        <!-- Hour options will be inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="button-container">
            <button type="button" id="generateBtn" class="generate-button"><?php echo esc_html($i18n['make_reservation']); ?></button>
            <?php if ($require_login && $is_logged_in): ?>
            <div id="rate-limit-info" style="margin-top: 15px; padding: 10px; font-size: 14px;">
                <span id="rate-limit-text"><?php echo esc_html($i18n['loading_booking_limits']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Result page (hidden by default) -->
    <div id="result-page" style="display: none;">
        <div class="success-header">
            <div class="success-icon">
                <svg width="80" height="80" viewBox="0 0 80 80">
                    <circle cx="40" cy="40" r="38" fill="#4CAF50" stroke="#fff" stroke-width="2"/>
                    <path d="M25 40 L35 50 L55 30" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1 class="success-title"><?php echo esc_html($i18n['successful']); ?></h1>
            <p class="success-message"><?php echo esc_html($i18n['success_message']); ?></p>
        </div>

        <canvas id="canvas" width="750" height="450"></canvas>

        <div class="save-button-container">
            <button type="button" id="saveBtn" class="save-button"><?php echo esc_html($i18n['save_to_photos']); ?></button>
            <button type="button" id="backBtn" class="back-button"><?php echo esc_html($i18n['back_to_form']); ?></button>
        </div>
    </div>
</div>

<style>
#vip-booking-container { margin: 20px auto; padding: 20px 10px; max-width: 800px; overflow-x: hidden; }
#booking-form { opacity: 1; transition: opacity 0.5s ease-in-out; }
#result-page { opacity: 0; transition: opacity 0.5s ease-in-out; }
.login-button:hover { background: #f57c00 !important; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4); }
.booking-steps { position: relative; }
.step-item { display: block; margin-bottom: 20px; position: relative; }
.step-item:last-child .step-line { display: none; }
.step-header { position: absolute; left: 0; top: 0; width: 24px; height: 100%; z-index: 0; }
.step-circle { width: 18px; height: 18px; left: -5px; top: 3px; border-radius: 50%; background: #333; border: 2px solid #333; display: flex; align-items: center; justify-content: center; position: relative; z-index: 2; transition: all 0.3s linear; }
.step-item.active .step-circle { border-color: #ff9800; background: #000; }
.step-item.completed .step-circle { border-color: #ff9800; background: #ff9800; }
.step-number { color: #fff; font-weight: bold; font-size: 10px; }
.step-item.active .step-number, .step-item.completed .step-number { color: #fff; }
.step-item.completed .step-number { display: none; }
.step-check { display: none; color: #fff; font-size: 14px; font-weight: bold; }
.step-item.completed .step-check { display: block; }
.pulse-ring { position: absolute; width: 100%; height: 100%; border: 2px solid #ff9800; border-radius: 50%; animation: pulse 2s infinite; opacity: 0; display: none; }
.step-item.active .pulse-ring { display: block; }
@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(1.5); opacity: 0; } }
@keyframes modalSlideIn { 0% { transform: translateY(-50px); opacity: 0; } 100% { transform: translateY(0); opacity: 1; } }
@keyframes fadeIn { 0% { opacity: 0; } 100% { opacity: 1; } }
@keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.step-line { position: absolute; left: 3px; top: 10px; width: 3px; bottom: -25px; background: #333; z-index: -1; }
.step-item.completed .step-line { background: #ff9800; }
.step-content { position: relative; padding-top: 0; min-width: 0; overflow: visible; z-index: 10; }
.step-title { margin: 0 0 15px 35px; font-size: 20px; }
.step-select { width: 90%; max-width: 600px; margin-left: auto; margin-right: auto; display: block; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: #fff; color: #000; transition: border-color 0.3s; }
.step-select:disabled { background: #e0e0e0; color: #999; }
.step-select:focus { outline: none; border-color: #ff9800; }
input[type="text"] { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; margin-top: 10px; }
.flag-options { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 5px; padding: 5px; max-width: 600px; margin-left: auto; margin-right: auto; justify-content: center; }
.flag-options label { font-size: 40px; transition: transform 0.2s; line-height: 1; display: inline-block; position: relative; }
.flag-options label:hover { transform: scale(1.15); z-index: 10; }
.flag-options input[type="radio"] { display: none; }
.flag-options input[type="radio"]:checked + span { 
    border: 3px solid #ff9800; 
    border-radius: 8px; 
    padding: 5px; 
    box-shadow: 0 0 12px rgba(255, 152, 0, 0.4);
    display: inline-block;
}
.pax-selector { display: grid; grid-template-columns: repeat(8, 1fr); gap: 6px; width: 90%; max-width: 600px; margin-left: auto; margin-right: auto; }
.pax-option { padding: 8px 4px; text-align: center; font-size: 15px; font-weight: 600; border: 1px solid #ddd; border-radius: 8px; transition: all 0.3s; }
.pax-option:not(:hover):not(.selected) { background: #fff; color: #000; }
.date-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; width: 90%; max-width: 600px; margin-left: auto; margin-right: auto; }
.date-option { padding: 10px 6px; text-align: center; border: 1px solid #ddd; border-radius: 15px; transition: all 0.3s; }
.date-option:not(:hover):not(.selected) { background: #fff; color: #000; }
.date-day { font-weight: bold; font-size: 11px; display: block; margin-bottom: 1px; line-height: 1; color: #ff3333; }
.date-month { font-weight: bold; font-size: 11px; line-height: 1; margin-bottom: 1px; display: block; }
.date-date { font-size: 26px; font-weight: bold; line-height: 1.1; }
/* New Time Picker Styles */
.new-time-picker { width: 100%; max-width: 600px; margin: 0 auto; }
.time-display-container { display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 25px; }
.time-box { background: #fff; border: 3px solid transparent; border-radius: 15px; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: 900; color: #000; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.time-box.disabled { background: #e0e0e0; color: #999; opacity: 0.6; }
.time-box[data-active="true"] { border-color: #ff9800; box-shadow: 0 0 15px rgba(255, 152, 0, 0.5), 0 2px 8px rgba(0,0,0,0.1); }
.time-box:not(.disabled):hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.time-separator { font-size: 48px; font-weight: bold; color: #fff; user-select: none; }
.time-options-container { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; margin-top: 20px; opacity: 1; transition: opacity 0.5s ease-in-out; }
.time-option-btn { background: #fff; border: 2px solid #ddd; border-radius: 8px; padding: 12px 8px; font-size: 16px; font-weight: 600; color: #000; transition: all 0.3s ease; text-align: center; }
.time-option-btn:hover:not(.disabled):not(.selected) { background: #ff9800; color: #fff; border-color: #ff9800; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(255, 152, 0, 0.3); }
.time-option-btn.selected { background: #ff9800; color: #fff; border-color: #ff9800; box-shadow: 0 0 12px rgba(255, 152, 0, 0.6); }
.time-option-btn.disabled { background: #e0e0e0; color: #999; border-color: #ccc; opacity: 0.5; }
@media (min-width: 769px) {
    .time-display-container { gap: 20px; }
    .time-box { width: 120px; height: 120px; font-size: 56px; }
    .time-separator { font-size: 56px; }
    .time-options-container { grid-template-columns: repeat(12, 1fr); gap: 10px; }
}
@media (max-width: 768px) {
    .time-box { width: 90px; height: 90px; font-size: 42px; }
    .time-separator { font-size: 42px; }
    .time-options-container { grid-template-columns: repeat(6, 1fr); gap: 6px; }
    .time-option-btn { padding: 10px 6px; font-size: 14px; }
}
.button-container { text-align: center; margin-top: 40px; }
.generate-button { padding: 10px 20px; }
.success-header { text-align: center; margin-bottom: 30px; }
.success-icon { margin: 0px auto; animation: scaleIn 0.5s ease-out; }
@keyframes scaleIn { 0% { transform: scale(0); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
.success-title { font-size: 32px; color: #4CAF50; margin: 0 0 20px 0; }
.success-message { font-size: 18px; color: #fff; line-height: 1.6; max-width: 600px; margin: 0 auto; }
#canvas { display: block; margin: 30px auto; max-width: 100%; height: auto; image-rendering: crisp-edges; }
.save-button-container { text-align: center; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
.save-button, .back-button { padding: 10px 20px; font-size: 18px; transition: all 0.3s; }
.back-button { background: #666; color: #fff; }
.back-button:hover { background: #555; }
@media (max-width: 768px) { 
.pax-selector { grid-template-columns: repeat(8, 1fr); grid-template-rows: repeat(2, 1fr); gap: 4px; }
.pax-option { padding: 6px 2px; font-size: 13px; }
.date-selector { grid-template-columns: repeat(4, 1fr); grid-template-rows: repeat(2, 1fr); gap: 6px; } 
.date-option { padding: 8px 4px; }
.date-day { font-size: 10px; margin-bottom: 1px; }
.date-month { font-size: 10px; margin-bottom: 1px; }
.date-date { font-size: 22px; font-weight: bold; }
.time-column { width: 60px; }
.time-option { padding: 6px 2px; font-size: 12px; }
.success-title { font-size: 24px; } 
.success-message { font-size: 14px; } 
}
</style>

<script>
var vipCardApp = (function() {
    var vipData = <?php echo json_encode($vip_data); ?>;
    var exchangeRate = <?php echo floatval($exchange_rate); ?>;
    var requireLogin = <?php echo $require_login ? 'true' : 'false'; ?>;
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    var preselectedStoreId = <?php echo json_encode($storeid); ?>;
    var rateLimitConfig = {
        limit_2h: <?php echo intval($limit_2h); ?>,
        limit_12h: <?php echo intval($limit_12h); ?>
    };
    var i18n = <?php echo VIP_Booking_I18n::get_translations_json(); ?>;
    var selectedDate = null, selectedTime = null, selectedPax = null;
    var selectedHour = null, selectedMinute = null;
    var currentTimeMode = 'hour'; // 'hour' or 'minute'
    var currentStoreConfig = null;
    var popupSettings = {
        trigger_class: '',
        auto_open_enabled: false,
        auto_open_seconds: 0
    };
    
    function init() {
        initServiceDropdown();
        initPaxSelector();
        initDateSelector();
        initTimeSelector();
        bindEvents();

        // Handle preselected store ID
        if (preselectedStoreId) {
            handlePreselectedStore();
        }

        if (requireLogin && isLoggedIn) {
            loadRateLimitInfo();
        }

        // Load popup settings and handle auto-open
        if (requireLogin && !isLoggedIn) {
            loadPopupSettings();
        }
    }

    function loadPopupSettings() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=vip_booking_get_popup_settings'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                popupSettings = data.data;

                // Create permanent hidden trigger element for Spectra
                if (popupSettings.trigger_class && !document.querySelector('.' + popupSettings.trigger_class)) {
                    createPermanentTrigger();
                }

                // Handle auto-open if enabled
                if (popupSettings.auto_open_enabled && popupSettings.trigger_class) {
                    var delay = Math.max(0, parseInt(popupSettings.auto_open_seconds) || 0) * 1000;
                    setTimeout(function() {
                        triggerSpectraPopup();
                    }, delay);
                }
            }
        })
        .catch(function(error) {
            console.error('Failed to load popup settings:', error);
        });
    }

    function createPermanentTrigger() {
        var permanentTrigger = document.createElement('a');
        permanentTrigger.href = '#';
        permanentTrigger.className = popupSettings.trigger_class;
        permanentTrigger.style.cssText = 'display: none !important; visibility: hidden !important; position: absolute; left: -9999px; pointer-events: none;';
        permanentTrigger.setAttribute('aria-hidden', 'true');
        permanentTrigger.setAttribute('tabindex', '-1');
        document.body.appendChild(permanentTrigger);
        console.log('Created permanent Spectra trigger element:', popupSettings.trigger_class);
    }

    function triggerSpectraPopup() {
        if (!popupSettings.trigger_class) {
            console.warn('Spectra popup trigger class not configured');
            return;
        }

        // Find and click the permanent trigger element
        var triggerElement = document.querySelector('.' + popupSettings.trigger_class);
        if (triggerElement) {
            console.log('Triggering Spectra popup:', popupSettings.trigger_class);
            triggerElement.click();
        } else {
            console.warn('Spectra popup trigger not found. Ensure popup is configured correctly.');
        }
    }

    function handlePreselectedStore() {
        // Find the store by store_id
        var matchingStore = null;
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].store_id === preselectedStoreId) {
                matchingStore = vipData[i];
                break;
            }
        }

        if (!matchingStore) {
            console.warn('Store ID not found:', preselectedStoreId);
            return;
        }

        // Auto-select service and store
        var serviceSelect = document.getElementById('service');
        var storeSelect = document.getElementById('store');

        serviceSelect.value = matchingStore.service;
        serviceSelect.disabled = true;

        // Update store dropdown
        updateStoreDropdown();

        // Auto-select store
        storeSelect.value = matchingStore.store;
        storeSelect.disabled = true;

        // Update package dropdown
        updatePackageDropdown();

        // Hide/disable Steps 1 and 2
        var step1 = document.querySelector('.step-item[data-step="1"]');
        var step2 = document.querySelector('.step-item[data-step="2"]');
        if (step1) step1.style.display = 'none';
        if (step2) step2.style.display = 'none';

        // Renumber visible steps (subtract 2 from original numbers)
        for (var i = 3; i <= 7; i++) {
            var step = document.querySelector('.step-item[data-step="' + i + '"]');
            if (step) {
                var stepNumber = step.querySelector('.step-number');
                if (stepNumber) {
                    stepNumber.textContent = (i - 2);
                }
            }
        }

        // Mark steps as completed and activate step 3
        updateStepStatus(1, 'completed');
        updateStepStatus(2, 'completed');
        updateStepStatus(3, 'active');
    }
    
    function bindEvents() {
        document.getElementById('service').onchange = onServiceChange;
        document.getElementById('store').onchange = onStoreChange;
        document.getElementById('package').onchange = onPackageChange;
        
        var flags = document.querySelectorAll('input[name="flag"]');
        for (var i = 0; i < flags.length; i++) {
            flags[i].disabled = true;
            flags[i].parentElement.style.opacity = '0.5';
            flags[i].onchange = function() {
                if (this.disabled) return;
                if (this.checked) {
                    updateStepStatus(4, 'completed');
                    updateStepStatus(5, 'active');
                    enablePaxSelector();
                }
            };
        }
        
        document.getElementById('generateBtn').onclick = generateCard;
        document.getElementById('saveBtn').onclick = saveToPhotos;
        document.getElementById('backBtn').onclick = backToForm;
    }
    
    function updateStepStatus(stepNum, status) {
        var step = document.querySelector('.step-item[data-step="' + stepNum + '"]');
        if (!step) return;
        step.className = step.className.replace(/\s*(active|completed)/g, '');
        if (status) step.className += ' ' + status;
    }
    
    function initServiceDropdown() {
        var sel = document.getElementById('service');
        var services = [];
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service && services.indexOf(vipData[i].service) === -1) {
                services.push(vipData[i].service);
            }
        }
        sel.innerHTML = '<option value="">' + i18n.select_service + '</option>';
        for (var i = 0; i < services.length; i++) {
            var opt = document.createElement('option');
            opt.value = opt.textContent = services[i];
            sel.appendChild(opt);
        }
        updateStepStatus(1, 'active');
    }
    
    function onServiceChange() {
        if (this.value) {
            updateStepStatus(1, 'completed');
            updateStepStatus(2, 'active');
        } else {
            updateStepStatus(1, 'active');
            updateStepStatus(2, '');
        }
        updateStoreDropdown();
    }
    
    function updateStoreDropdown() {
        var service = document.getElementById('service').value;
        var sel = document.getElementById('store');
        if (!service) {
            sel.disabled = true;
            sel.innerHTML = '<option value="">' + i18n.complete_previous_step + '</option>';
            updatePackageDropdown();
            return;
        }
        var stores = [];
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service === service && vipData[i].store && stores.indexOf(vipData[i].store) === -1) {
                stores.push(vipData[i].store);
            }
        }
        sel.disabled = false;
        sel.innerHTML = '<option value="">' + i18n.select_store + '</option>';
        for (var i = 0; i < stores.length; i++) {
            var opt = document.createElement('option');
            opt.value = opt.textContent = stores[i];
            sel.appendChild(opt);
        }
        updatePackageDropdown();
    }
    
    function onStoreChange() {
        if (this.value) {
            updateStepStatus(2, 'completed');
            updateStepStatus(3, 'active');
        } else {
            updateStepStatus(2, 'active');
            updateStepStatus(3, '');
        }
        updatePackageDropdown();
    }
    
    function updatePackageDropdown() {
        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var sel = document.getElementById('package');
        if (!service || !store) {
            sel.disabled = true;
            sel.innerHTML = '<option value="">' + i18n.complete_previous_steps + '</option>';
            document.getElementById('price-display').style.display = 'none';
            return;
        }
        var packages = [];
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service === service && vipData[i].store === store && vipData[i].package && packages.indexOf(vipData[i].package) === -1) {
                packages.push(vipData[i].package);
            }
        }
        sel.disabled = false;
        sel.innerHTML = '<option value="">' + i18n.select_package + '</option>';
        for (var i = 0; i < packages.length; i++) {
            var opt = document.createElement('option');
            opt.value = opt.textContent = packages[i];
            sel.appendChild(opt);
        }
        updatePrice();
    }
    
    function onPackageChange() {
        if (this.value) {
            updateStepStatus(3, 'completed');
            updateStepStatus(4, 'active');
            enableFlagSelector();
            buildTimeSelector();
        } else {
            updateStepStatus(3, 'active');
            updateStepStatus(4, '');
        }
        updatePrice();
    }
    
    function enableFlagSelector() {
        var flags = document.querySelectorAll('input[name="flag"]');
        for (var i = 0; i < flags.length; i++) {
            flags[i].disabled = false;
            flags[i].parentElement.style.opacity = '1';
        }
    }
    
    function updatePrice() {
        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var pkg = document.getElementById('package').value;
        var priceEl = document.getElementById('price');
        var priceDisp = document.getElementById('price-display');
        if (!service || !store || !pkg) {
            priceDisp.style.display = 'none';
            return;
        }
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service === service && vipData[i].store === store && vipData[i].package === pkg && vipData[i].price) {
                var vnd = parseFloat(vipData[i].price);
                var usd = (vnd / exchangeRate).toFixed(2);
                priceEl.textContent = 'VND ' + vnd.toLocaleString() + ' ~ USD ' + usd;
                priceDisp.style.display = 'block';
                return;
            }
        }
        priceDisp.style.display = 'none';
    }
    
    function initPaxSelector() {
        var cont = document.getElementById('paxSelector');
        for (var i = 1; i <= 16; i++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pax-option';
            btn.textContent = i;
            btn.setAttribute('data-pax', i);
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.onclick = function() {
                if (this.disabled) return;
                var all = document.querySelectorAll('.pax-option');
                for (var j = 0; j < all.length; j++) all[j].className = 'pax-option';
                this.className = 'pax-option selected';
                selectedPax = parseInt(this.getAttribute('data-pax'));
                updateStepStatus(5, 'completed');
                updateStepStatus(6, 'active');
                enableDateSelector();
            };
            cont.appendChild(btn);
        }
    }
    
    function enablePaxSelector() {
        var btns = document.querySelectorAll('.pax-option');
        for (var i = 0; i < btns.length; i++) {
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
        }
    }
    
    function initDateSelector() {
        var cont = document.getElementById('dateSelector');
        var today = new Date();
        var days = [i18n.sun, i18n.mon, i18n.tue, i18n.wed, i18n.thu, i18n.fri, i18n.sat];
        var months = [i18n.jan, i18n.feb, i18n.mar, i18n.apr, i18n.may, i18n.jun, i18n.jul, i18n.aug, i18n.sep, i18n.oct, i18n.nov, i18n.dec];
        for (var i = 0; i < 8; i++) {
            var d = new Date(today);
            d.setDate(today.getDate() + i);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'date-option';
            btn.disabled = true;
            btn.style.opacity = '0.5';
            var dayName = i === 0 ? i18n.today : i === 1 ? i18n.tomorrow : days[d.getDay()];
            btn.innerHTML = '<span class="date-day">' + dayName + '</span>' +
              '<span class="date-date">' + d.getDate() + '</span>' +
              '<span class="date-month">' + months[d.getMonth()] + '</span>';
            btn.onclick = (function(date) {
                return function() {
                    if (this.disabled) return;
                    var all = document.querySelectorAll('.date-option');
                    for (var j = 0; j < all.length; j++) all[j].className = 'date-option';
                    this.className = 'date-option selected';
                    selectedDate = date;
                    updateStepStatus(6, 'completed');
                    updateStepStatus(7, 'active');
                    enableTimeSelector();
                };
            })(d);
            cont.appendChild(btn);
        }
    }
    
    function enableDateSelector() {
        var btns = document.querySelectorAll('.date-option');
        for (var i = 0; i < btns.length; i++) {
            btns[i].disabled = false;
            btns[i].style.opacity = '1';
        }
    }
    
    function initTimeSelector() {
        // Bind click events for hour and minute boxes
        document.getElementById('hourBox').onclick = function() {
            if (this.getAttribute('data-disabled') === 'true') return;
            showHourPicker();
        };
        
        document.getElementById('minuteBox').onclick = function() {
            if (this.getAttribute('data-disabled') === 'true') return;
            if (selectedHour === null) {
                alert(i18n.please_select_hour);
                return;
            }
            showMinutePicker();
        };
    }
    
    function buildTimeSelector() {
        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var pkg = document.getElementById('package').value;
        
        if (!service || !store || !pkg) return;

        currentStoreConfig = null;
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service === service && vipData[i].store === store && vipData[i].package === pkg) {
                currentStoreConfig = vipData[i];
                break;
            }
        }

        if (!currentStoreConfig || !currentStoreConfig.opening_hours || !currentStoreConfig.closing_hours) {
            currentStoreConfig = {
                opening_hours: '11:00',
                closing_hours: '02:00',
                prebook_time: 15
            };
        }

        selectedHour = null;
        selectedMinute = null;
        selectedTime = null;
        document.getElementById('hourDisplay').textContent = '--';
        document.getElementById('minuteDisplay').textContent = '--';
        document.getElementById('hourBox').setAttribute('data-active', 'false');
        document.getElementById('minuteBox').setAttribute('data-active', 'false');
        document.getElementById('timeOptionsContainer').style.display = 'none';
    }
    
    function showHourPicker() {
        currentTimeMode = 'hour';

        // Update active states
        document.getElementById('hourBox').setAttribute('data-active', 'true');
        document.getElementById('minuteBox').setAttribute('data-active', 'false');

        // Generate hour options (0-23) with fade transition
        var container = document.getElementById('timeOptionsContainer');
        container.style.opacity = '0';
        setTimeout(function() {
            container.innerHTML = '';
            container.style.display = 'grid';
        
        for (var h = 0; h < 24; h++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-option-btn';
            btn.textContent = (h < 10 ? '0' : '') + h;
            btn.setAttribute('data-value', h);
            
            // Check if this hour is available
            if (!isHourAvailable(h)) {
                btn.className += ' disabled';
            }
            
            if (selectedHour === h) {
                btn.className += ' selected';
            }
            
            btn.onclick = function() {
                if (this.className.indexOf('disabled') > -1) return;
                selectHour(parseInt(this.getAttribute('data-value')));
            };

            container.appendChild(btn);
        }

        // Fade in the options
        setTimeout(function() {
            container.style.opacity = '1';
        }, 50);
        }, 500);
    }

    function showMinutePicker() {
        currentTimeMode = 'minute';

        // Update active states
        document.getElementById('hourBox').setAttribute('data-active', 'false');
        document.getElementById('minuteBox').setAttribute('data-active', 'true');

        // Generate minute options (0, 5, 10, ..., 55) with fade transition
        var container = document.getElementById('timeOptionsContainer');
        container.style.opacity = '0';
        setTimeout(function() {
            container.innerHTML = '';
            container.style.display = 'grid';

        for (var m = 0; m < 60; m += 5) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'time-option-btn';
            btn.textContent = (m < 10 ? '0' : '') + m;
            btn.setAttribute('data-value', m);

            // Check if this minute is available
            if (!isMinuteAvailable(selectedHour, m)) {
                btn.className += ' disabled';
            }

            if (selectedMinute === m) {
                btn.className += ' selected';
            }

            btn.onclick = function() {
                if (this.className.indexOf('disabled') > -1) return;
                selectMinute(parseInt(this.getAttribute('data-value')));
            };

            container.appendChild(btn);
        }

        // Fade in the options
        setTimeout(function() {
            container.style.opacity = '1';
        }, 50);
        }, 500);
    }
    
    function selectHour(hour) {
        selectedHour = hour;
        document.getElementById('hourDisplay').textContent = (hour < 10 ? '0' : '') + hour;
        
        // Update button states
        var buttons = document.querySelectorAll('.time-option-btn');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].className = buttons[i].className.replace(' selected', '');
            if (parseInt(buttons[i].getAttribute('data-value')) === hour) {
                buttons[i].className += ' selected';
            }
        }
        
        // Auto switch to minute picker
        setTimeout(function() {
            showMinutePicker();
        }, 300);
    }
    
    function selectMinute(minute) {
        selectedMinute = minute;
        document.getElementById('minuteDisplay').textContent = (minute < 10 ? '0' : '') + minute;
        
        // Update button states
        var buttons = document.querySelectorAll('.time-option-btn');
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].className = buttons[i].className.replace(' selected', '');
            if (parseInt(buttons[i].getAttribute('data-value')) === minute) {
                buttons[i].className += ' selected';
            }
        }
        
        // Update selectedTime
        selectedTime = (selectedHour < 10 ? '0' : '') + selectedHour + ':' + (selectedMinute < 10 ? '0' : '') + selectedMinute;

        // Mark step as completed
        updateStepStatus(7, 'completed');

        // Fade out and hide time options after selection
        var container = document.getElementById('timeOptionsContainer');
        container.style.opacity = '0';
        setTimeout(function() {
            container.style.display = 'none';
            document.getElementById('hourBox').setAttribute('data-active', 'false');
            document.getElementById('minuteBox').setAttribute('data-active', 'false');
        }, 500);
    }
    
    function isHourAvailable(hour) {
        if (!currentStoreConfig) return true;
        
        var openParts = currentStoreConfig.opening_hours.split(':');
        var openHour = parseInt(openParts[0]);
        
        var closeParts = currentStoreConfig.closing_hours.split(':');
        var closeHour = parseInt(closeParts[0]);
        
        // If today, check prebook time with rounding
        if (selectedDate) {
            var now = new Date();
            var isToday = selectedDate.getDate() === now.getDate() && 
                         selectedDate.getMonth() === now.getMonth() && 
                         selectedDate.getFullYear() === now.getFullYear();
            
            if (isToday) {
                var prebookMinutes = currentStoreConfig.prebook_time || 15;
                var cutoffTime = new Date(now.getTime() + prebookMinutes * 60000);

                var cutoffMinutes = cutoffTime.getMinutes();
                var roundedMinutes = Math.ceil(cutoffMinutes / 5) * 5;

                var cutoffHour = cutoffTime.getHours();
                if (roundedMinutes >= 60) {
                    cutoffHour += 1;
                    roundedMinutes = 0;
                }

                if (hour < cutoffHour) return false;

                if (hour === cutoffHour) {
                    var hasAvailableMinute = false;
                    for (var m = roundedMinutes; m <= 55; m += 5) {
                        if (m <= 55) {
                            hasAvailableMinute = true;
                            break;
                        }
                    }
                    if (!hasAvailableMinute) return false;
                }
            }
        }
        
        // Check if hour is within opening hours
        if (closeHour < openHour) {
            // Crosses midnight (e.g., 11:00 - 02:00)
            return hour >= openHour || hour <= closeHour;
        } else {
            // Same day (e.g., 09:00 - 18:00)
            return hour >= openHour && hour <= closeHour;
        }
    }
    
    function isMinuteAvailable(hour, minute) {
        if (!currentStoreConfig || hour === null) return true;
        
        var openParts = currentStoreConfig.opening_hours.split(':');
        var openHour = parseInt(openParts[0]);
        var openMin = parseInt(openParts[1]) || 0;
        
        var closeParts = currentStoreConfig.closing_hours.split(':');
        var closeHour = parseInt(closeParts[0]);
        var closeMin = parseInt(closeParts[1]) || 0;
        
        // If today, check prebook time with rounding
        if (selectedDate) {
            var now = new Date();
            var isToday = selectedDate.getDate() === now.getDate() && 
                         selectedDate.getMonth() === now.getMonth() && 
                         selectedDate.getFullYear() === now.getFullYear();
            
            if (isToday) {
                var prebookMinutes = currentStoreConfig.prebook_time || 15;
                var cutoffTime = new Date(now.getTime() + prebookMinutes * 60000);

                var cutoffMinutes = cutoffTime.getMinutes();
                var roundedMinutes = Math.ceil(cutoffMinutes / 5) * 5;

                var cutoffHour = cutoffTime.getHours();
                if (roundedMinutes >= 60) {
                    cutoffHour += 1;
                    roundedMinutes = 0;
                }

                if (hour < cutoffHour || (hour === cutoffHour && minute < roundedMinutes)) {
                    return false;
                }
            }
        }
        
        // Check opening time
        if (hour === openHour && minute < openMin) {
            return false;
        }
        
        // Check closing time
        if (hour === closeHour && minute > closeMin) {
            return false;
        }
        
        return true;
    }
    
    function enableTimeSelector() {
        var hourBox = document.getElementById('hourBox');
        var minuteBox = document.getElementById('minuteBox');
        hourBox.removeAttribute('data-disabled');
        minuteBox.removeAttribute('data-disabled');
        hourBox.classList.remove('disabled');
        minuteBox.classList.remove('disabled');
        showHourPicker();
    }

    function generateCard() {
        if (requireLogin && !isLoggedIn) {
            triggerSpectraPopup();
            return;
        }

        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var pkg = document.getElementById('package').value;
        var flag = document.querySelector('input[name="flag"]:checked');
        if (!service || !store || !pkg || !flag || !selectedPax || !selectedDate || !selectedTime) {
            var missing = [];
            if (!service) missing.push(i18n.choose_service);
            if (!store) missing.push(i18n.choose_store);
            if (!pkg) missing.push(i18n.service_package);
            if (!flag) missing.push(i18n.nation);
            if (!selectedPax) missing.push(i18n.number_of_guests);
            if (!selectedDate) missing.push(i18n.date);
            if (!selectedTime) missing.push(i18n.time);
            alert(i18n.please_complete + '\n- ' + missing.join('\n- '));
            return;
        }

        // Check rate limit if logged in
        if (requireLogin && isLoggedIn) {
            checkRateLimitAndGenerate();
        } else {
            generateCardNow();
        }
    }
    
    function checkRateLimitAndGenerate() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=vip_booking_check_rate_limit&nonce=<?php echo wp_create_nonce('vip_booking_nonce'); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                generateCardNow();
            } else {
                alert('⚠️ ' + data.data.message);
            }
        })
        .catch(function() {
            alert(i18n.failed_rate_limit);
        });
    }
    
    function generateCardNow() {
        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var pkg = document.getElementById('package').value;
        var flag = document.querySelector('input[name="flag"]:checked');
        
        var canvas = document.getElementById('canvas');
        var ctx = canvas.getContext('2d');
        var img = new Image();
        img.src = '<?php echo esc_url(plugins_url("vip-template.png", __FILE__)); ?>';
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            ctx.clearRect(0, 0, 750, 450);
            ctx.drawImage(img, 0, 0, 750, 450);
            
            var storeText = '⋆⋆⋆✦ ' + store + ' ✦⋆⋆⋆';
            ctx.font = 'bold 24px Arial';
            ctx.textAlign = 'center';
            var tw = ctx.measureText(storeText).width;
            var x = 375, y = 290;
            var grad = ctx.createLinearGradient(x - tw/2, y, x + tw/2, y);
            grad.addColorStop(0, '#EDE3B6');
            grad.addColorStop(1, '#856641');
            ctx.fillStyle = grad;
            ctx.fillText(storeText, x, y);
            
            var months = [i18n.jan, i18n.feb, i18n.mar, i18n.apr, i18n.may, i18n.jun, i18n.jul, i18n.aug, i18n.sep, i18n.oct, i18n.nov, i18n.dec];
            var dateStr = months[selectedDate.getMonth()] + ' ' + selectedDate.getDate();
            var bottomText = flag.value + ' ' + selectedPax + ' ' + i18n.pax + '  ⋆  ⏰ ' + selectedTime + '  ⋆  🗓️ ' + dateStr + '  ⋆  💎 ' + pkg;
            
            ctx.font = 'bold 28px Arial';
            ctx.fillStyle = '#000000';
            ctx.fillText(bottomText, 375, 370);

            // Hide form and show results with smooth transition
            var bookingForm = document.getElementById('booking-form');
            var resultPage = document.getElementById('result-page');

            bookingForm.style.opacity = '0';
            setTimeout(function() {
                bookingForm.style.display = 'none';
                resultPage.style.display = 'block';
                setTimeout(function() {
                    resultPage.style.opacity = '1';
                }, 50);

                // Smooth scroll to top of container
                var container = document.getElementById('vip-booking-container');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 500);

            // Save booking and update rate limit
            if (requireLogin && isLoggedIn) {
                saveBookingToDatabase();
            }
        };
        img.onerror = function() {
            ctx.fillStyle = '#f0f0f0';
            ctx.fillRect(0, 0, 750, 450);
            ctx.fillStyle = '#999';
            ctx.font = '20px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('Template image not found', 375, 225);

            // Hide form and show results with smooth transition
            var bookingForm = document.getElementById('booking-form');
            var resultPage = document.getElementById('result-page');

            bookingForm.style.opacity = '0';
            setTimeout(function() {
                bookingForm.style.display = 'none';
                resultPage.style.display = 'block';
                setTimeout(function() {
                    resultPage.style.opacity = '1';
                }, 50);

                // Smooth scroll to top of container
                var container = document.getElementById('vip-booking-container');
                if (container) {
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 500);
        };
    }
    
    function saveToPhotos() {
        var canvas = document.getElementById('canvas');
        if (navigator.share && navigator.canShare) {
            canvas.toBlob(function(blob) {
                var file = new File([blob], 'vip-booking.png', { type: 'image/png' });
                navigator.share({ files: [file] }).catch(function() {
                    fallbackDownload();
                });
            });
        } else {
            fallbackDownload();
        }
    }
    
    function fallbackDownload() {
        var canvas = document.getElementById('canvas');
        var link = document.createElement('a');
        link.download = 'vip-booking.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }
    
    function backToForm() {
        var resultPage = document.getElementById('result-page');
        var bookingForm = document.getElementById('booking-form');

        // Fade out results
        resultPage.style.opacity = '0';
        setTimeout(function() {
            resultPage.style.display = 'none';
            bookingForm.style.display = 'block';
            setTimeout(function() {
                bookingForm.style.opacity = '1';
            }, 50);

            // Smooth scroll to top of container
            var container = document.getElementById('vip-booking-container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 500);
    }
    
    function loadRateLimitInfo() {
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=vip_booking_check_rate_limit&nonce=<?php echo wp_create_nonce('vip_booking_nonce'); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var textEl = document.getElementById('rate-limit-text');
            if (!textEl) return;
            
            if (data.success) {
                var count2h = data.data.count_2h || 0;
                var count12h = data.data.count_12h || 0;
                var remaining2h = rateLimitConfig.limit_2h - count2h;
                var remaining12h = rateLimitConfig.limit_12h - count12h;
                var actualRemaining = Math.min(remaining2h, remaining12h);

                if (actualRemaining > 0) {
                    var timesText = actualRemaining === 1 ? i18n.time_singular : i18n.times_plural;
                    textEl.innerHTML = i18n.remaining_bookings + ' ' +
                        '<span style="color: #4CAF50; font-size: 18px; font-weight: bold;">' + actualRemaining + '</span>' + ' ' + timesText;
                } else {
                    textEl.innerHTML = i18n.no_bookings_available;
                }
            } else {
                var waitTime2h = data.data.wait_time_2h || 0;
                var waitTime12h = data.data.wait_time_12h || 0;
                var maxWait = Math.max(waitTime2h, waitTime12h);
                
                if (maxWait > 0) {
                    startCountdown(maxWait);
                } else {
                    textEl.innerHTML = i18n.please_refresh;
                }
            }
        });
    }
    
    var countdownInterval = null;
    
    function startCountdown(seconds) {
        var textEl = document.getElementById('rate-limit-text');
        if (!textEl) return;
        
        // Clear existing countdown
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        function updateCountdown() {
            if (seconds <= 0) {
                clearInterval(countdownInterval);
                textEl.innerHTML = '<span style="color: #4CAF50; font-size: 16px; font-weight: bold;">' + i18n.you_can_book_again + '</span>';
                return;
            }

            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var secs = seconds % 60;

            var timeStr = '';
            if (hours > 0) {
                timeStr = hours + 'h ' + minutes + 'm ' + secs + 's';
            } else if (minutes > 0) {
                timeStr = minutes + 'm ' + secs + 's';
            } else {
                timeStr = secs + 's';
            }

            textEl.innerHTML = i18n.next_booking_available + ' ' +
                '<span style="color: #f44336; font-size: 18px; font-weight: bold;">' + timeStr + '</span>';
            seconds--;
        }
        
        updateCountdown();
        countdownInterval = setInterval(updateCountdown, 1000);
    }
    
    function saveBookingToDatabase() {
        var service = document.getElementById('service').value;
        var store = document.getElementById('store').value;
        var pkg = document.getElementById('package').value;
        var flag = document.querySelector('input[name="flag"]:checked');

        var selectedPrice = 0;
        for (var i = 0; i < vipData.length; i++) {
            if (vipData[i].service === service && vipData[i].store === store && vipData[i].package === pkg) {
                selectedPrice = vipData[i].price;
                break;
            }
        }

        var bookingData = {
            service: service,
            store: store,
            package: pkg,
            price: selectedPrice,
            nation: flag.value,
            pax: selectedPax,
            date: selectedDate.getFullYear() + '-' +
                  String(selectedDate.getMonth() + 1).padStart(2, '0') + '-' +
                  String(selectedDate.getDate()).padStart(2, '0'),
            time: selectedTime
        };

        // Capture canvas image as base64
        var canvas = document.getElementById('canvas');
        var cardImage = canvas.toDataURL('image/png');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=vip_booking_create_booking&nonce=<?php echo wp_create_nonce('vip_booking_nonce'); ?>&booking_data=' + encodeURIComponent(JSON.stringify(bookingData)) + '&card_image=' + encodeURIComponent(cardImage)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                console.log('Booking saved:', data.data.booking_number);
                if (document.getElementById('rate-limit-info')) {
                    loadRateLimitInfo();
                }
            }
        })
        .catch(function(err) {
            console.error('Failed to save booking:', err);
        });
    }

    return {
        init: init
    };
})();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', vipCardApp.init);
} else {
    vipCardApp.init();
}
</script>