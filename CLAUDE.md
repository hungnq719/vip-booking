# CLAUDE.md - VIP Booking WordPress Plugin

## Project Overview

**VIP Booking** is a WordPress plugin that provides a complete booking management system with:
- Frontend booking forms (public and user-only)
- User dashboard for managing bookings
- Admin dashboard with statistics and data management
- Configurable rate limiting system (admin can set limits)
- Multi-language support (flag-based nationality selection)
- Configurable automatic cleanup of old bookings (admin can set period)
- Real-time notifications (Telegram & Email) for new bookings with card image generation

**Version:** 1.0.0
**Platform:** WordPress Plugin
**Language:** PHP, JavaScript (jQuery)
**Database:** WordPress Custom Post Type (CPT)

---

## Directory Structure

```
vip-booking/
├── vip-booking.php          # Main plugin file - entry point
├── includes/                 # PHP classes
│   ├── class-admin.php      # Admin interface and menu
│   ├── class-ajax.php       # AJAX handlers (admin + frontend)
│   ├── class-assets.php     # Asset enqueueing
│   ├── class-cpt.php        # Custom Post Type registration
│   ├── class-rate-limiter.php # Rate limiting logic
│   ├── class-shortcode.php  # Shortcode handlers
│   ├── class-notification-settings.php # Notification settings management
│   ├── class-telegram-notifier.php # Telegram notification sender
│   └── class-email-notifier.php # Email notification sender
├── templates/                # PHP templates
│   ├── admin-page.php       # Admin dashboard UI
│   ├── frontend-form.php    # Booking form (frontend)
│   ├── user-dashboard.php   # User booking dashboard
│   └── vip-template.png     # Template screenshot
└── assets/
    └── js/
        └── admin.js         # Admin JavaScript
```

---

## Architecture & Key Components

### 1. Custom Post Type (CPT) - `class-cpt.php`

**Post Type:** `vip_booking`
**Meta Fields:**
- `_booking_service` (string) - Service type
- `_booking_store` (string) - Store location
- `_booking_package` (string) - Package name
- `_booking_price` (integer) - Price in VND
- `_booking_nation` (string) - Nationality (flag emoji)
- `_booking_pax` (integer) - Number of people
- `_booking_date` (string) - Booking date
- `_booking_time` (string) - Booking time
- `_booking_timestamp` (integer) - Unix timestamp (UTC)
- `_booking_status` (string) - Status: 'confirmed' or 'completed'
- `_booking_created_at` (integer) - Creation timestamp (UTC)
- `_booking_number` (string) - Format: 'VIP-XXXXXX'

**Automated Tasks:**
- Daily cron job (`vip_booking_daily_cleanup`) removes bookings older than configured period (default: 90 days)
- Cleanup period is configurable via admin settings
- Uses `_booking_timestamp` for cleanup logic

### 2. Admin Interface - `class-admin.php`

**Menu Location:** WordPress Admin > VIP Booking
**Capabilities Required:** `manage_options` (Administrator only)

**Static Methods for Data Management:**
- `save_data($data)` - Saves booking configuration data
- `get_data()` - Retrieves booking configuration
- `save_settings($settings)` - Saves exchange rate and rate limiter settings
- `get_settings()` - Gets settings (exchange_rate, limit_2h, limit_12h)
- `save_flags($flags)` - Saves nationality flags
- `get_flags()` - Gets flags (default: 🇺🇸, 🇰🇷, 🇷🇺, 🇨🇳, 🇯🇵)
- `save_cleanup_period($period)` - Saves cleanup period (must be negative, e.g., -90)
- `get_cleanup_period()` - Gets cleanup period (default: -90)

### 3. AJAX Handlers - `class-ajax.php`

**Admin Endpoints (require `manage_options`):**
- `vip_booking_save_data` - Save booking configuration
- `vip_booking_get_data` - Load booking configuration
- `vip_booking_save_settings` - Save exchange rate and rate limiter settings
- `vip_booking_get_settings` - Load settings (exchange_rate, limit_2h, limit_12h)
- `vip_booking_save_cleanup_period` - Save cleanup period setting
- `vip_booking_get_cleanup_period` - Load cleanup period setting
- `vip_booking_save_flags` - Save nationality flags
- `vip_booking_get_flags` - Load flags
- `vip_booking_delete_booking` - Delete single booking
- `vip_booking_delete_multiple` - Bulk delete bookings
- `vip_booking_mark_complete` - Mark booking as completed
- `vip_booking_save_notification_settings` - Save notification settings
- `vip_booking_get_notification_settings` - Load notification settings
- `vip_booking_test_telegram` - Test Telegram bot connection

**Frontend Endpoints:**
- `vip_booking_check_rate_limit` - Check if user can book (logged in only)
- `vip_booking_record_booking` - Record booking attempt (deprecated)
- `vip_booking_create_booking` - Create new booking (logged in only)
- `vip_booking_check_login` - Check login status (public + logged in)

### 4. Rate Limiting System - `class-rate-limiter.php`

**Limits (Configurable via Admin Settings):**
- Default: 2 bookings per 2 hours
- Default: 4 bookings per 12 hours
- Limits stored in `wp_options` table: `vip_booking_limit_2h` and `vip_booking_limit_12h`
- Time windows are fixed: 2 hours (7200 seconds) and 12 hours (43200 seconds)

**Administrator Exemption:** Admins bypass all rate limits

**Implementation Details:**
- Uses UTC timestamps (`time()`) for consistency
- Queries `_booking_created_at` meta field
- Returns detailed limit status including wait times
- Sliding window algorithm
- Loads limits dynamically from database using `get_limit_2h()` and `get_limit_12h()` methods
- Frontend displays remaining bookings based on configured limits

### 5. Shortcodes - `class-shortcode.php`

**Available Shortcodes:**
- `[vip_booking]` - Booking form (requires login)
- `[vip_booking_secret]` - Booking form (public, no login required)
- `[vip_booking_user]` - User dashboard (displays user's bookings)

### 6. Notification System

**Components:**
- `class-notification-settings.php` - Settings management and message formatting
- `class-telegram-notifier.php` - Telegram API integration
- `class-email-notifier.php` - Email sending with HTML formatting

**Features:**
- **Telegram Notifications:**
  - Configurable bot token and multiple chat IDs
  - Sends text messages with booking details
  - Optional booking card image attachment
  - Test connection functionality

- **Email Notifications:**
  - Multiple recipient support
  - HTML-formatted emails with gradient design
  - Optional booking card image attachment
  - Uses WordPress default email (WP Mail SMTP compatible)

- **Booking Card Generation:**
  - Dynamic PNG image generation using GD library
  - Gradient background (purple/blue theme)
  - All booking details displayed
  - Auto-cleanup after sending

- **Customizable Template:**
  - Template with placeholders: `{booking_number}`, `{customer_name}`, `{service}`, `{store}`, `{package}`, `{nation}`, `{pax}`, `{date}`, `{time}`, `{price}`, `{created_at}`
  - Default template provided
  - Reset to default option available

**Notification Trigger:**
- Automatically sent when a new booking is created
- Fires after successful booking creation (in `class-ajax.php:create_booking()`)
- Both Telegram and Email sent asynchronously (if enabled)

**Settings Storage:**
- Stored in `wp_options` table as `vip_booking_notification_settings`
- All settings accessible via admin interface (Notifications tab)

---

## Development Workflows

### Adding a New Feature

1. **Identify the Layer:**
   - Backend logic → Add to appropriate class in `includes/`
   - Frontend UI → Modify templates in `templates/`
   - AJAX endpoint → Add to `class-ajax.php`
   - Admin UI → Modify `templates/admin-page.php` and `assets/js/admin.js`

2. **Follow the Class Pattern:**
   - Constructor registers hooks
   - Use WordPress actions/filters appropriately
   - Follow PSR-style naming (underscores for methods)

3. **Security Checks:**
   - Always use `check_ajax_referer()` for AJAX
   - Check capabilities with `current_user_can()`
   - Sanitize inputs with `sanitize_text_field()`, `intval()`, etc.
   - Escape outputs with `esc_html()`, `esc_attr()`, etc.

### Modifying Booking Fields

1. **Update CPT Registration** (`class-cpt.php:15-30`)
   - Add new meta field to `register_post_meta()`
   - Set correct type: 'string' or 'integer'

2. **Update AJAX Handler** (`class-ajax.php:130-172`)
   - Add sanitization for new field
   - Use `update_post_meta()` to save

3. **Update Templates**
   - Add form fields to `frontend-form.php`
   - Add display columns to `admin-page.php` and `user-dashboard.php`

### Working with Timestamps

**CRITICAL CONVENTION:** All timestamps are stored in UTC using `time()`

**Timezone-Aware Booking:**
```php
try {
    $tz = wp_timezone();
    $dt = new DateTime($date . ' ' . $time, $tz);
    $timestamp = $dt->getTimestamp();
} catch (Exception $e) {
    $timestamp = strtotime($date . ' ' . $time);
}
```

**Display to Users:** Convert using WordPress timezone functions

---

## Code Conventions

### PHP Standards

1. **Class Naming:** `VIP_Booking_Classname`
2. **File Naming:** `class-classname.php`
3. **Method Naming:** `snake_case`
4. **Hook Callbacks:** Use `array($this, 'method_name')`

### WordPress Best Practices

1. **Nonce Verification:** Always verify nonces in AJAX
   ```php
   check_ajax_referer('vip_booking_nonce', 'nonce');
   ```

2. **Capability Checks:** Verify user permissions
   ```php
   if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');
   ```

3. **Data Sanitization:**
   - Text: `sanitize_text_field()`
   - Integers: `intval()` or `absint()`
   - Arrays: Iterate and sanitize each element

4. **Database Queries:** Use WP_Query or `get_posts()`, never raw SQL

### JavaScript Conventions

1. **jQuery Wrapper:** All code in `jQuery(document).ready()`
2. **AJAX Pattern:**
   ```javascript
   $.ajax({
       url: ajaxurl,
       type: 'POST',
       data: { action: 'action_name', nonce: nonce, ...data },
       success: function(response) { ... }
   });
   ```

3. **Data Formatting:**
   - Numbers: Use `formatNumber()` and `unformatNumber()` for display
   - Time: Normalize with `normalizeTime()`

---

## Security Considerations

### OWASP Top 10 Compliance

1. **Injection Prevention:**
   - Never use raw SQL queries
   - Always use `$wpdb->prepare()` if direct DB access needed
   - Sanitize all inputs

2. **Broken Authentication:**
   - Rate limiting prevents brute force booking attempts
   - Admin functions require `manage_options` capability

3. **XSS Prevention:**
   - Escape all outputs in templates
   - Use `wp_send_json_success()` for structured AJAX responses

4. **Access Control:**
   - Nonce verification on all AJAX endpoints
   - Capability checks before sensitive operations
   - User ID validation (users can only see their own bookings)

5. **Security Misconfiguration:**
   - CPT set to `public => false` to prevent direct access
   - Admin menu restricted to administrators

### Input Validation Patterns

```php
// Integer validation
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

// Text validation
$service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';

// Array validation
$booking_ids = isset($_POST['booking_ids']) ? $_POST['booking_ids'] : array();
if (!is_array($booking_ids)) wp_send_json_error('Invalid input');

// JSON validation
$data = isset($_POST['data']) ? json_decode(stripslashes($_POST['data']), true) : array();
```

---

## Testing Approaches

### Manual Testing Checklist

**Frontend:**
- [ ] Create booking as logged-in user
- [ ] Verify rate limiting with default settings (2 bookings in 2h)
- [ ] Change rate limits in admin and verify frontend respects new limits
- [ ] Test public form (`[vip_booking_secret]`)
- [ ] Check user dashboard displays correct bookings
- [ ] Verify timezone handling for booking dates

**Admin:**
- [ ] Save and load booking configuration data
- [ ] Test bulk delete functionality
- [ ] Verify statistics calculations
- [ ] Test flag management (add/remove)
- [ ] Check exchange rate updates
- [ ] Configure and save rate limiter settings (limit_2h, limit_12h)
- [ ] Configure and save cleanup period setting
- [ ] Verify settings persist after page reload

**Security:**
- [ ] Attempt AJAX calls without nonce
- [ ] Try accessing admin endpoints as non-admin
- [ ] Verify users can't see others' bookings
- [ ] Test SQL injection in form fields

**Automated Cleanup:**
- [ ] Create test booking with old `_booking_timestamp`
- [ ] Configure cleanup period in admin (e.g., 30 days)
- [ ] Run `wp cron run vip_booking_daily_cleanup`
- [ ] Verify bookings older than configured period are deleted
- [ ] Verify bookings newer than configured period are kept

---

## Common Development Tasks

### 1. Change Rate Limits

**Method:** Use Admin UI (Recommended)

Navigate to **WordPress Admin > VIP Booking > Booking Data** tab:
1. Update "Rate Limit (2 hours)" field (default: 2)
2. Update "Rate Limit (12 hours)" field (default: 4)
3. Click "Save Settings"

**Direct Database Method** (Not recommended - use admin UI instead):
```php
update_option('vip_booking_limit_2h', 10);  // Set to desired limit
update_option('vip_booking_limit_12h', 20); // Set to desired limit
```

### 2. Modify Cleanup Period

**Method:** Use Admin UI (Recommended)

Navigate to **WordPress Admin > VIP Booking > Booking Manager** tab:
1. Update "Auto-cleanup period" field (default: 90 days)
2. Click "Save Cleanup Settings"
3. Note: Enter positive number in UI (e.g., 90), stored as negative in database (e.g., -90)

**Direct Database Method** (Not recommended - use admin UI instead):
```php
update_option('vip_booking_cleanup_period', -90); // Negative value, e.g., -90 for 90 days old
```

### 3. Add New AJAX Endpoint

**File:** `includes/class-ajax.php`

```php
// In constructor:
add_action('wp_ajax_vip_booking_new_action', array($this, 'new_action'));

// Add method:
public function new_action() {
    check_ajax_referer('vip_booking_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Permission denied');

    // Your logic here
    wp_send_json_success($data);
}
```

### 4. Add New Shortcode

**File:** `includes/class-shortcode.php`

```php
// In constructor:
add_shortcode('vip_booking_custom', array($this, 'render_custom'));

// Add method:
public function render_custom($atts) {
    ob_start();
    include VIP_BOOKING_PLUGIN_DIR . 'templates/custom-template.php';
    return ob_get_clean();
}
```

### 5. Debug AJAX Issues

**Enable WordPress Debug:**
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Check logs:** `wp-content/debug.log`

**Browser Console:**
```javascript
console.log('AJAX Response:', response);
```

---

## Database Schema

### WordPress Options Table

- `vip_booking_data` - Serialized array of booking configuration
- `vip_booking_exchange_rate` - Float, default 25000
- `vip_booking_limit_2h` - Integer, rate limit for 2 hour window (default: 2)
- `vip_booking_limit_12h` - Integer, rate limit for 12 hour window (default: 4)
- `vip_booking_cleanup_period` - Integer, negative value for cleanup days (default: -90)
- `vip_booking_flags` - Serialized array of flag emojis
- `vip_booking_notification_settings` - Serialized array of notification settings (telegram_enabled, telegram_bot_token, telegram_chat_ids, email_enabled, email_recipients, send_card_image, notification_template)

### WordPress Posts Table

- **post_type:** `vip_booking`
- **post_status:** `publish`
- **post_author:** User ID who created booking
- **post_title:** `'Booking ' . time()`

### WordPress Postmeta Table

See "Custom Post Type (CPT)" section above for all meta fields.

---

## Git Workflow

### Branch Naming Convention

- Feature: `claude/claude-md-{session-id}`
- Ensure branch starts with `claude/` for successful push operations

### Commit Message Guidelines

- Use imperative mood: "Add feature" not "Added feature"
- Format: `<type>: <description>`
- Types: feat, fix, refactor, docs, style, test

**Examples:**
```
feat: Add bulk delete functionality for bookings
fix: Correct timezone handling in booking creation
refactor: Simplify rate limiter logic
docs: Update CLAUDE.md with testing section
```

### Push Retry Logic

Network failures on push/fetch should retry up to 4 times with exponential backoff (2s, 4s, 8s, 16s).

---

## Important Notes for AI Assistants

### Before Making Changes

1. **Read existing code** to understand patterns
2. **Check security implications** of all changes
3. **Verify WordPress compatibility** (hooks, functions, capabilities)
4. **Test timezone handling** for any datetime-related changes

### When Adding Features

1. **Maintain backward compatibility** with existing data
2. **Follow existing naming conventions** (classes, files, methods)
3. **Add appropriate security checks** (nonce, capabilities, sanitization)
4. **Update this CLAUDE.md** if adding new patterns or conventions

### Red Flags to Avoid

- ❌ Direct `$_POST` access without sanitization
- ❌ Raw SQL queries (use WP_Query instead)
- ❌ Hardcoded user IDs or capabilities
- ❌ Missing nonce verification on AJAX
- ❌ Timezone-naive datetime handling
- ❌ Exposing sensitive data to non-authenticated users
- ❌ Hardcoded rate limits or cleanup periods (use configurable wp_options instead)

### Green Patterns to Follow

- ✅ Use WordPress core functions (sanitize, escape, wp_send_json)
- ✅ Check capabilities before sensitive operations
- ✅ Use UTC timestamps with WordPress timezone conversion
- ✅ Follow WordPress Coding Standards
- ✅ Add error logging for debugging

---

## Troubleshooting Guide

### Bookings Not Saving

1. Check browser console for AJAX errors
2. Verify nonce is being passed correctly
3. Check user is logged in (for protected endpoints)
4. Review `debug.log` for PHP errors

### Rate Limiting Not Working

1. Verify `_booking_created_at` is being saved correctly
2. Check if user is admin (admins bypass limits)
3. Ensure timestamps are in UTC (use `time()`, not `current_time()`)
4. Check rate limit settings in admin (VIP Booking > Booking Data > Settings)
5. Verify frontend loads dynamic rate limits from database (not hardcoded)
6. Clear browser cache and reload frontend booking page

### Cleanup Not Running

1. Check if cron is enabled: `wp cron event list`
2. Manually trigger: `wp cron run vip_booking_daily_cleanup`
3. Verify bookings have `_booking_timestamp` meta field
4. Check cleanup period setting in admin (VIP Booking > Booking Manager)
5. Verify `vip_booking_cleanup_period` option exists in database

### Admin Page Not Loading

1. Check user has `manage_options` capability
2. Verify `admin.js` is enqueued properly
3. Check for JavaScript errors in browser console

---

## Plugin Constants

```php
VIP_BOOKING_VERSION      // Plugin version (1.0.0)
VIP_BOOKING_PLUGIN_DIR   // Absolute path to plugin directory
VIP_BOOKING_PLUGIN_URL   // URL to plugin directory
```

---

## Hooks Reference

### Actions Used

- `plugins_loaded` - Initialize plugin
- `init` - Register CPT
- `admin_menu` - Add admin page
- `admin_enqueue_scripts` - Enqueue admin assets
- `wp_enqueue_scripts` - Enqueue frontend assets
- `wp` - Schedule cron jobs
- `vip_booking_daily_cleanup` - Custom cron event

### Filters Used

(None currently - add here if filters are implemented)

---

## Testing Notifications

### Testing Telegram Notifications

1. **Setup:**
   - Create a bot using [@BotFather](https://t.me/BotFather) on Telegram
   - Get your chat ID from [@userinfobot](https://t.me/userinfobot)
   - Navigate to **WordPress Admin > VIP Booking > Notifications**
   - Enter bot token and chat ID(s)
   - Enable Telegram notifications

2. **Test Connection:**
   - Click "Test Telegram Connection" button
   - Check for success message in admin panel
   - Verify test message received in Telegram

3. **Test Live Booking:**
   - Create a new booking via frontend form
   - Check Telegram for notification with booking details
   - Verify card image is attached (if enabled)

### Testing Email Notifications

1. **Setup:**
   - Ensure WP Mail SMTP or similar plugin is configured
   - Navigate to **WordPress Admin > VIP Booking > Notifications**
   - Enter recipient email address(es)
   - Enable email notifications

2. **Test Live Booking:**
   - Create a new booking via frontend form
   - Check recipient inbox for HTML email
   - Verify card image is attached (if enabled)
   - Check spam folder if not received

3. **Troubleshooting:**
   - Verify WordPress email configuration
   - Check `debug.log` for errors
   - Ensure `wp_mail()` function is working
   - Test with a simple email first

## Future Enhancement Ideas

- Payment integration (Stripe, PayPal)
- Calendar view for bookings
- Export bookings to CSV
- Multi-language support (WPML/Polylang)
- REST API endpoints
- Booking approval workflow
- SMS notifications (Twilio)
- Custom booking statuses
- Booking conflicts prevention
- WhatsApp notifications
- Push notifications (browser)

---

**Last Updated:** 2025-11-20 (Added notification system with Telegram and Email support)
**Maintainer:** VIP Booking Development Team
**WordPress Version Tested:** 6.x+
**PHP Version Required:** 7.4+
