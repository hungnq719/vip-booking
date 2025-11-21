# CLAUDE.md - VIP Booking WordPress Plugin

## Project Overview

**VIP Booking** is a WordPress plugin that provides a complete booking management system with:
- Frontend booking forms (public and user-only)
- User dashboard for managing bookings
- Admin dashboard with statistics and data management
- Configurable rate limiting system (admin can set limits)
- Multi-language UI support (English, Korean, Russian, Chinese) with automatic language detection
- Flag-based nationality selection in booking forms
- Configurable automatic cleanup of old bookings (admin can set period)
- Real-time notifications (Telegram & Email) for new bookings with card image generation
- Dynamic booking badge (cache-compatible, displays user's upcoming booking count)

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
│   ├── class-i18n.php       # Internationalization (multi-language support)
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
    ├── css/
    │   └── frontend.css     # Frontend styles (badge, etc.)
    └── js/
        ├── admin.js         # Admin JavaScript
        └── frontend.js      # Frontend JavaScript (badge updates, etc.)
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
- `_booking_card_image` (string) - Absolute path to frontend-generated card image

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
- `save_badge_url($url)` - Saves badge click URL
- `get_badge_url()` - Gets badge click URL (default: empty string)

### 3. AJAX Handlers - `class-ajax.php`

**Admin Endpoints (require `manage_options`):**
- `vip_booking_save_data` - Save booking configuration
- `vip_booking_get_data` - Load booking configuration
- `vip_booking_save_settings` - Save exchange rate and rate limiter settings
- `vip_booking_get_settings` - Load settings (exchange_rate, limit_2h, limit_12h)
- `vip_booking_save_cleanup_period` - Save cleanup period setting
- `vip_booking_get_cleanup_period` - Load cleanup period setting
- `vip_booking_save_badge_url` - Save badge click URL setting
- `vip_booking_save_flags` - Save nationality flags
- `vip_booking_get_flags` - Load flags
- `vip_booking_delete_booking` - Delete single booking
- `vip_booking_delete_multiple` - Bulk delete bookings
- `vip_booking_mark_complete` - Mark booking as completed
- `vip_booking_save_notification_settings` - Save notification settings
- `vip_booking_get_notification_settings` - Load notification settings
- `vip_booking_test_telegram` - Test Telegram bot connection
- `vip_booking_test_email` - Send test email to specified address

**Frontend Endpoints:**
- `vip_booking_check_rate_limit` - Check if user can book (logged in only)
- `vip_booking_record_booking` - Record booking attempt (deprecated)
- `vip_booking_create_booking` - Create new booking (logged in only)
- `vip_booking_check_login` - Check login status (public + logged in)
- `vip_booking_get_badge_count` - Get user's upcoming bookings count (public + logged in)
- `vip_booking_get_badge_url` - Get badge click URL setting (public + logged in)

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
- `[vip_booking_badge]` - Dynamic badge showing upcoming bookings count

**Badge Shortcode Details:**

The `[vip_booking_badge]` shortcode displays a simple, circular red badge showing the user's upcoming bookings count. It's designed to work with full-page caching systems (like LiteSpeed Cache, WP Super Cache, etc.) by using JavaScript to fetch user-specific data after page load.

**Attributes:**
- `size` - Badge size (default: "medium", options: "small", "medium", "large")
- `show_zero` - Show badge when count is 0 (default: "yes", options: "yes" or "no")

**Examples:**
```
[vip_booking_badge]
[vip_booking_badge size="small" show_zero="no"]
[vip_booking_badge size="large"]
```

**Click Navigation:**
- Badge click URL is configured globally in **WordPress Admin > VIP Booking > Booking Manager** tab
- Set once, applies to all badges site-wide
- Typically set to your user dashboard page containing `[vip_booking_user]` shortcode
- **Language-Aware Navigation**: Automatically detects current page language and prepends to URL
  - Example: Korean page → `/ko/my-bookings/`, English page → `/en/my-bookings/`
  - Supports multilingual plugins (WPML, Polylang, etc.)
  - Detection methods: URL path, query string, HTML lang attribute

**How It Works (Cache-Compatible):**
1. Shortcode renders a static placeholder HTML (cached with page)
2. JavaScript makes AJAX call on page load to check login status
3. **Badge only displays for logged-in users** - hidden completely for guests
4. Badge updates dynamically with current user's booking count
5. Click handler fetches badge URL from server settings
6. Detects current page language and prepends to URL
7. Navigates to language-specific dashboard
8. No need to exclude from cache - works seamlessly with all caching plugins

**Design:**
- Simple circular badge with gradient red background (#ff416c → #ff4b2b)
- Displays only the number (no text label)
- **Visible only for logged-in users** - automatically hidden for guests
- **Fast "tada" animation** (1.2s) - bouncy with rotation to grab attention
- Animation pauses on hover, scales on click
- Three size options:
  - Small: 24px diameter
  - Medium: 32px diameter (default)
  - Large: 44px diameter
- Responsive design (auto-scales on mobile)
- Loading spinner while fetching data
- Fully accessible (keyboard navigation with Enter/Space keys)

**Interactivity:**
- Clickable: Navigates to language-aware URL configured in admin settings
- Keyboard accessible: Tab to focus, Enter or Space to activate
- Hover effect: Pauses animation and scales up slightly
- Active/click effect: Scales down for tactile feedback

**Language Detection:**
- Automatically detects language from:
  1. URL path (e.g., `/ko/page/` → detects Korean)
  2. Query parameter (e.g., `?lang=ko`)
  3. HTML lang attribute (e.g., `<html lang="ko-KR">`)
- Supported languages: ko, en, zh, ru, ja, vi, th, id, es, fr, de, it, pt
- Console logs detection for debugging

**Use Cases:**
- Navigation menu badge (e.g., next to "My Bookings" link)
- User account icon overlay (clickable to dashboard)
- Dashboard widget with navigation
- Header notification indicator

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
  - **Primary Method:** Frontend canvas-generated card (preferred)
    - Generated during booking creation in `frontend-form.php`
    - Captured as base64-encoded PNG image
    - Sent to server and saved in WordPress uploads directory
    - Stored path in `_booking_card_image` meta field
  - **Fallback Method:** Backend GD library generation
    - Used only if frontend card is not available
    - Dynamic PNG image generation using GD library
    - Gradient background (purple/blue theme)
  - Both methods display all booking details
  - Temporary generated cards auto-cleanup after sending
  - Frontend-generated cards are preserved for future use

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

### 7. Internationalization (I18n) System - `class-i18n.php`

**Purpose:** Provides multi-language support for all user-facing text in frontend booking forms and user dashboard.

**Supported Languages:**
- English (en) - Default language
- Korean (ko) - 한국어
- Russian (ru) - Русский
- Chinese (zh) - 中文

**Automatic Language Detection:**
- Uses WordPress `get_locale()` function to detect site language
- Maps WordPress locale codes to language codes:
  - `en_US`, `en_GB`, etc. → `en`
  - `ko_KR` → `ko`
  - `ru_RU` → `ru`
  - `zh_CN`, `zh_TW` → `zh`
- Falls back to English if unsupported language detected

**Architecture:**
```php
class VIP_Booking_I18n {
    // Returns current language code (en, ko, ru, zh)
    public static function get_current_language();

    // Returns translations array for current language
    public static function get_translations();

    // Returns translations as JSON for JavaScript
    public static function get_translations_json();
}
```

**Usage in Templates:**

PHP (Server-side):
```php
$i18n = VIP_Booking_I18n::get_translations();
echo esc_html($i18n['choose_service']);
```

JavaScript (Client-side):
```javascript
var i18n = <?php echo VIP_Booking_I18n::get_translations_json(); ?>;
alert(i18n.choose_service);
```

**Key Translation Keys:**
- Form labels: `choose_service`, `choose_store`, `choose_package`, `select_nation`, `select_pax`, `select_date`, `select_time`
- Field labels: `service`, `store`, `package`, `nation_label`, `guests`, `date_label`, `time_label`, `price`
- Actions: `submit_booking`, `view_card`, `save_to_photos`, `close`
- Status: `upcoming`, `completed`, `login_required`
- Rate limiting: `time_singular`, `times_plural`, `remaining_bookings`
- Messages: `no_bookings_yet`, `no_bookings_message`, `login_to_view`

**Consistency Guidelines:**
- All dashboard labels include colons (e.g., `nation_label: 'Nation:'`)
- Time-related keys differentiate singular/plural (`time_singular: 'Time'`, `times_plural: 'Times'`)
- All labels properly capitalized across all languages
- Month names abbreviated (Jan, Feb, Mar, etc.) for card generation

**Implementation Notes:**
- Frontend form (`frontend-form.php`) uses translations for all UI text
- User dashboard (`user-dashboard.php`) uses translations with inline H3 headers
- Booking card canvas generation uses translated month names
- Rate limit messages dynamically use singular/plural forms based on count

**Adding New Translation Keys:**
1. Add key to all 4 language arrays in `class-i18n.php`
2. Update templates to use new key with `$i18n['new_key']`
3. For JavaScript, ensure `get_translations_json()` is called in template
4. Test all 4 languages to verify translations display correctly

**UI Enhancements:**
- **Disabled Time Picker:** Time selection boxes are disabled until all previous booking steps (Service, Store, Package, Nation, Pax, Date) are completed
- **Inline Labels:** Dashboard labels display inline with their values using CSS `display: inline`
- **Consistent Formatting:** All labels formatted as H3 headers with emoji icons (🎯 🌍 ⏰ 🏷️ etc.)
- **Store Name Prominence:** Store name displayed as H2 header in user dashboard for better visual hierarchy

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
- [ ] Test multi-language support by changing WordPress site language (English, Korean, Russian, Chinese)
- [ ] Verify time picker is disabled until all previous steps are completed
- [ ] Check that all UI text translates correctly in frontend form and user dashboard

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

### Notifications Not Sending

**Telegram Issues:**
1. Verify bot token is correct (get from @BotFather)
2. Ensure you've started the bot with `/start` command
3. Verify chat ID is correct (get from @userinfobot)
4. Test connection using admin panel test button
5. Check `debug.log` for API error messages

**Email Issues:**
1. **WP Mail SMTP Configuration:**
   - Ensure WP Mail SMTP plugin is installed and configured
   - Verify SMTP credentials are correct
   - Do NOT set a custom From header - let WP Mail SMTP handle it
   - From header mismatch with SMTP server will cause failures
2. Test WordPress email functionality with simple test first
3. Check spam/junk folder for test emails
4. Review `debug.log` for wp_mail() errors
5. Verify recipient email addresses are valid
6. Use admin panel test email button to diagnose issues

**Card Image Not Attaching:**
1. Check if `_booking_card_image` meta field exists for booking
2. Verify file exists at the stored path
3. Check WordPress uploads directory permissions (775)
4. Ensure GD library is installed for fallback generation
5. Check if "Send Card Image" option is enabled in settings

### Multi-Language Not Working

1. Verify WordPress site language is set correctly (Settings > General > Site Language)
2. Check if `get_locale()` returns expected value (use debug.log)
3. Ensure `VIP_Booking_I18n::get_current_language()` maps locale correctly
4. Clear browser cache and reload page
5. Verify all translation keys exist in `class-i18n.php` for all 4 languages
6. Check for JavaScript console errors that might prevent translations from loading
7. Ensure `VIP_Booking_I18n::get_translations_json()` is called in template

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
   - **IMPORTANT:** Start your bot by sending `/start` command in Telegram
   - Navigate to **WordPress Admin > VIP Booking > Notifications**
   - Enter bot token and chat ID(s)
   - Enable Telegram notifications

2. **Test Connection:**
   - Click "🧪 Test Telegram Connection" button
   - Check for success message in admin panel
   - Verify test message received in Telegram
   - **If you get "chat not found" error:** Send `/start` to your bot first

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

2. **Test Connection:**
   - Enter a test email address in "Test Email Address" field
   - Click "🧪 Test Email Connection" button
   - Check for success message in admin panel
   - Verify test email received at specified address
   - Check spam/junk folder if not received

3. **Test Live Booking:**
   - Create a new booking via frontend form
   - Check recipient inbox for HTML email
   - Verify card image is attached (if enabled)
   - Check spam folder if not received

4. **Troubleshooting:**
   - Verify WP Mail SMTP is properly configured
   - Check `debug.log` for errors
   - Ensure `wp_mail()` function is working
   - Do NOT set custom From headers (let WP Mail SMTP handle it)
   - Test with simple test button first before creating bookings

## Future Enhancement Ideas

- Payment integration (Stripe, PayPal)
- Calendar view for bookings
- Export bookings to CSV
- Additional language support beyond current 4 languages (WPML/Polylang integration)
- REST API endpoints
- Booking approval workflow
- SMS notifications (Twilio)
- Custom booking statuses
- Booking conflicts prevention
- WhatsApp notifications
- Push notifications (browser)

---

**Last Updated:** 2025-11-20 (Added multi-language UI support with automatic language detection for 4 languages: English, Korean, Russian, Chinese. Improved user dashboard with inline labels, H2/H3 header formatting, and disabled time picker until all booking steps completed)
**Maintainer:** VIP Booking Development Team
**WordPress Version Tested:** 6.x+
**PHP Version Required:** 7.4+
