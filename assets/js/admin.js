jQuery(document).ready(function($) {
    let rowCounter = 0;
    let flagsData = [];
    const ajaxurl = vipBookingAdmin.ajaxurl;
    const nonce = vipBookingAdmin.nonce;
    
    // Load data for Booking Data tab
    loadFlags();
    loadData();
    // Settings are now loaded via PHP in template, no need for AJAX load
    
    function loadFlags() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_flags', nonce: nonce },
            success: function(response) {
                if (response.success) {
                    flagsData = response.data || ['🇺🇸', '🇰🇷', '🇷🇺', '🇨🇳', '🇯🇵'];
                    renderFlags();
                }
            }
        });
    }
    
    function renderFlags() {
        const container = $('#flags-container');
        container.empty();
        flagsData.forEach(function(flag, index) {
            const flagDiv = $('<div class="flag-item" data-index="' + index + '">' + flag + '</div>');
            flagDiv.click(function() {
                if (confirm('Remove this flag: ' + flag + '?')) {
                    flagsData.splice(index, 1);
                    renderFlags();
                }
            });
            container.append(flagDiv);
        });
    }
    
    $('#add-flag').click(function() {
        const newFlag = $('#new-flag').val().trim();
        if (!newFlag) { alert('Please enter a flag emoji'); return; }
        if (flagsData.indexOf(newFlag) !== -1) { alert('This flag already exists'); return; }
        flagsData.push(newFlag);
        renderFlags();
        $('#new-flag').val('');
    });
    
    $('#save-flags').click(function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_save_flags', nonce: nonce, flags: JSON.stringify(flagsData) },
            success: function(response) {
                alert(response.success ? '✅ Flags saved!' : '❌ Failed to save flags');
            }
        });
    });
    
    function formatNumber(num) {
        if (!num) return '';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    function unformatNumber(str) {
        if (!str) return '';
        return str.toString().replace(/,/g, '');
    }
    
    function loadData() {
        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_data', nonce: nonce },
            success: function(response) {
                $('#vip-booking-tbody').empty();
                const data = response.success ? (response.data || []) : [];
                if (data.length === 0) {
                    addRow();
                } else {
                    data.forEach(function(row) { addRow(row); });
                }
                $('#loading-overlay').hide();
            }
        });
    }
    
    function normalizeTime(timeStr) {
        if (!timeStr) return '';
        
        // Convert to string and clean
        timeStr = String(timeStr).trim();
        
        // Remove any quotes
        timeStr = timeStr.replace(/['"]/g, '');
        
        // Remove any spaces
        timeStr = timeStr.replace(/\s/g, '');
        
        // Split by colon
        const parts = timeStr.split(':');
        if (parts.length !== 2) {
            console.warn('Invalid time format:', timeStr);
            return '';
        }
        
        // Parse and validate
        let hours = parseInt(parts[0], 10);
        let minutes = parseInt(parts[1], 10);
        
        if (isNaN(hours) || isNaN(minutes)) {
            console.warn('Invalid time values:', timeStr);
            return '';
        }
        
        // Validate ranges
        if (hours < 0 || hours > 23) hours = 0;
        if (minutes < 0 || minutes > 59) minutes = 0;
        
        // Pad with zeros
        const normalized = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        
        console.log('Normalized time:', timeStr, '->', normalized);
        return normalized;
    }
    
    function addRow(data = null) {
        console.log('addRow called with data:', data);
        
        const openingTime = data && data.opening_hours ? normalizeTime(data.opening_hours) : '';
        const closingTime = data && data.closing_hours ? normalizeTime(data.closing_hours) : '';
        
        console.log('After normalize - Opening:', openingTime, '| Closing:', closingTime);
        
        const row = $(`
            <tr>
                <td class="check-column" style="padding: 8px 2px;"><input type="checkbox" class="row-checkbox"></td>
                <td><input type="text" name="service[]" value="${data ? data.service : ''}" placeholder="Karaoke" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="text" name="store[]" value="${data ? data.store : ''}" placeholder="Store name" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="text" name="store_id[]" value="${data ? data.store_id : ''}" placeholder="S1" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="text" name="package[]" value="${data ? data.package : ''}" placeholder="VIP" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="text" name="price[]" class="price-input" value="${data && data.price ? formatNumber(data.price) : ''}" placeholder="0" data-raw-value="${data ? data.price : ''}" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="time" name="opening_hours[]" value="${openingTime}" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="time" name="closing_hours[]" value="${closingTime}" style="width:100%; box-sizing:border-box;"></td>
                <td><input type="number" name="prebook_time[]" value="${data ? data.prebook_time : '15'}" placeholder="15" min="0" style="width:100%; box-sizing:border-box;"></td>
                <td style="text-align:center;"><button class="delete-row button" type="button" style="padding:2px 6px;">❌</button></td>
            </tr>
        `);
        $('#vip-booking-tbody').append(row);
        rowCounter++;
    }
    
    $(document).on('input', '.price-input', function() {
        const rawValue = unformatNumber($(this).val());
        $(this).attr('data-raw-value', rawValue);
        $(this).val(formatNumber(rawValue));
    });
    
    $('#exchange-rate-display').on('input', function() {
        const rawValue = unformatNumber($(this).val());
        $('#exchange-rate').val(rawValue);
        $(this).val(formatNumber(rawValue));
    });
    
    $('#add-row').click(function() { addRow(); });
    
    $(document).on('click', '.delete-row', function() {
        $(this).closest('tr').remove();
    });
    
    $('#select-all').change(function() {
        $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    $('#delete-selected').click(function() {
        if (confirm('Delete selected rows?')) {
            $('.row-checkbox:checked').each(function() { $(this).closest('tr').remove(); });
            $('#select-all').prop('checked', false);
        }
    });
    
    $('#reset-all').click(function() {
        if (confirm('⚠️ WARNING: This will delete ALL data in the table!\n\nAre you absolutely sure?')) {
            if (confirm('This action CANNOT be undone. Proceed?')) {
                $('#vip-booking-tbody').empty();
                addRow();
                alert('✅ All data has been cleared!');
            }
        }
    });
    
    $('#save-changes').click(function() {
        const data = [];
        $('#vip-booking-tbody tr').each(function() {
            const $row = $(this);
            data.push({
                service: $row.find('input[name="service[]"]').val(),
                store: $row.find('input[name="store[]"]').val(),
                store_id: $row.find('input[name="store_id[]"]').val(),
                package: $row.find('input[name="package[]"]').val(),
                price: $row.find('input[name="price[]"]').attr('data-raw-value') || unformatNumber($row.find('input[name="price[]"]').val()),
                opening_hours: $row.find('input[name="opening_hours[]"]').val(),
                closing_hours: $row.find('input[name="closing_hours[]"]').val(),
                prebook_time: $row.find('input[name="prebook_time[]"]').val()
            });
        });
        
        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_save_data', nonce: nonce, data: JSON.stringify(data) },
            success: function(response) {
                $('#loading-overlay').hide();
                alert(response.success ? '✅ Data saved!' : '❌ Failed to save');
            }
        });
    });
    
    function loadSettings() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_settings', nonce: nonce },
            success: function(response) {
                if (response.success && response.data) {
                    if (response.data.exchange_rate) {
                        $('#exchange-rate').val(response.data.exchange_rate);
                        $('#exchange-rate-display').val(formatNumber(response.data.exchange_rate));
                    }
                    if (response.data.limit_2h !== undefined) {
                        $('#limit-2h').val(response.data.limit_2h);
                    }
                    if (response.data.limit_12h !== undefined) {
                        $('#limit-12h').val(response.data.limit_12h);
                    }
                }
            }
        });
    }

    function loadCleanupPeriod() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_cleanup_period', nonce: nonce },
            success: function(response) {
                if (response.success && response.data && response.data.cleanup_period !== undefined) {
                    // Convert negative backend value to positive display value
                    $('#cleanup-period').val(Math.abs(response.data.cleanup_period));
                }
            }
        });
    }

    $('#save-settings').click(function() {
        const exchangeRate = unformatNumber($('#exchange-rate-display').val());
        const limit2h = parseInt($('#limit-2h').val()) || 2;
        const limit12h = parseInt($('#limit-12h').val()) || 4;

        console.log('Saving settings:', { exchangeRate, limit2h, limit12h });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_save_settings',
                nonce: nonce,
                exchange_rate: exchangeRate,
                limit_2h: limit2h,
                limit_12h: limit12h
            },
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    alert('✅ Settings saved! Please reload the page to see changes.');
                } else {
                    alert('❌ Failed to save settings: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('❌ AJAX error: ' + error);
            }
        });
    });

    $('#save-cleanup-period').click(function() {
        const cleanupPeriodPositive = parseInt($('#cleanup-period').val()) || 90;

        // Validate positive value
        if (cleanupPeriodPositive <= 0) {
            alert('⚠️ Cleanup period must be a positive number (e.g., 90 for 90 days old)');
            return;
        }
        if (cleanupPeriodPositive > 3650) {
            alert('⚠️ Cleanup period cannot exceed 3650 days (10 years)');
            return;
        }

        // Convert to negative for backend storage
        const cleanupPeriodNegative = -Math.abs(cleanupPeriodPositive);

        console.log('Saving cleanup period:', cleanupPeriodPositive, '->', cleanupPeriodNegative);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_save_cleanup_period',
                nonce: nonce,
                cleanup_period: cleanupPeriodNegative
            },
            success: function(response) {
                console.log('Cleanup save response:', response);
                if (response.success) {
                    alert('✅ Cleanup settings saved! Please reload the page to see changes.');
                } else {
                    alert('❌ Failed to save cleanup settings: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('❌ AJAX error: ' + error);
            }
        });
    });

    $('#save-badge-url').click(function() {
        const badgeUrl = $('#badge-url').val().trim();

        console.log('Saving badge URL:', badgeUrl);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_save_badge_url',
                nonce: nonce,
                badge_url: badgeUrl
            },
            success: function(response) {
                console.log('Badge URL save response:', response);
                if (response.success) {
                    alert('✅ Badge settings saved!');
                } else {
                    alert('❌ Failed to save badge settings: ' + (response.data || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert('❌ AJAX error: ' + error);
            }
        });
    });

    $('#export-csv').click(function() {
        const data = [];
        $('#vip-booking-tbody tr').each(function() {
            const $row = $(this);
            data.push({
                service: $row.find('input[name="service[]"]').val(),
                store: $row.find('input[name="store[]"]').val(),
                package: $row.find('input[name="package[]"]').val(),
                price: $row.find('input[name="price[]"]').attr('data-raw-value') || unformatNumber($row.find('input[name="price[]"]').val()),
                opening_hours: $row.find('input[name="opening_hours[]"]').val(),
                closing_hours: $row.find('input[name="closing_hours[]"]').val(),
                prebook_time: $row.find('input[name="prebook_time[]"]').val()
            });
        });
        
        let csvContent = "Service,Store Name,Service Package,Price,Opening Hours,Closing Hours,Prebook Time\n";
        data.forEach(function(row) {
            csvContent += `"${row.service}","${row.store}","${row.package}","${row.price}","${row.opening_hours}","${row.closing_hours}","${row.prebook_time}"\n`;
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'vip-booking-data.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
    
    $('#import-csv').click(function() { $('#csv-file-input').click(); });
    
    $('#csv-file-input').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            let csv = e.target.result;
            
            // Normalize line endings (handle \r\n, \r, \n)
            csv = csv.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            
            const lines = csv.split('\n');
            $('#vip-booking-tbody').empty();
            
            let importCount = 0;
            let errorCount = 0;
            
            console.log('=== CSV IMPORT START ===');
            
            for (let i = 1; i < lines.length; i++) {
                const line = lines[i].trim();
                if (!line) continue;
                
                // Parse CSV - handle quoted values properly
                const values = [];
                let current = '';
                let inQuotes = false;
                
                for (let j = 0; j < line.length; j++) {
                    const char = line[j];
                    if (char === '"') {
                        inQuotes = !inQuotes;
                    } else if (char === ',' && !inQuotes) {
                        values.push(current.trim().replace(/^["']|["']$/g, ''));
                        current = '';
                    } else {
                        current += char;
                    }
                }
                // Don't forget the last value!
                values.push(current.trim().replace(/^["']|["']$/g, ''));
                
                // Debug every row
                console.log('Row', i, 'raw values:', values);
                
                // Must have at least 7 columns
                if (values.length >= 7) {
                    console.log('Row', i, '- Opening raw:', values[4], '- Closing raw:', values[5]);
                    
                    addRow({
                        service: values[0] || '',
                        store: values[1] || '',
                        package: values[2] || '',
                        price: values[3] || '',
                        opening_hours: values[4] || '',
                        closing_hours: values[5] || '',
                        prebook_time: values[6] || '15'
                    });
                    importCount++;
                } else {
                    console.warn('Row', i, 'only has', values.length, 'columns:', values);
                    errorCount++;
                }
            }
            
            console.log('=== CSV IMPORT END ===');
            console.log('Imported:', importCount, '| Errors:', errorCount);
            
            if (importCount > 0) {
                let message = `✅ Successfully imported ${importCount} row(s)!`;
                if (errorCount > 0) {
                    message += `\n\n⚠️ Skipped ${errorCount} invalid row(s).`;
                }
                message += '\n\n💾 Click "Save Changes" to save to database.';
                alert(message);
            } else {
                alert('❌ No valid data found in CSV file.\n\nExpected 7 columns:\nService, Store Name, Service Package, Price, Opening Hours, Closing Hours, Prebook Time\n\nCheck browser console (F12) for details.');
            }
        };
        reader.readAsText(file);
        $(this).val('');
    });

    // ===== Notification Settings =====

    // Load notification settings on page load
    loadNotificationSettings();

    function loadNotificationSettings() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_notification_settings', nonce: nonce },
            success: function(response) {
                if (response.success && response.data) {
                    const settings = response.data;

                    // Telegram settings
                    $('#telegram-enabled').prop('checked', settings.telegram_enabled || false);
                    $('#telegram-bot-token').val(settings.telegram_bot_token || '');

                    // Load chat IDs
                    const chatIds = settings.telegram_chat_ids || [];
                    renderTelegramChatIds(chatIds.length > 0 ? chatIds : ['']);

                    // Email settings
                    $('#email-enabled').prop('checked', settings.email_enabled || false);

                    // Load email recipients
                    const emailRecipients = settings.email_recipients || [];
                    renderEmailRecipients(emailRecipients.length > 0 ? emailRecipients : ['']);

                    // Card image setting
                    $('#send-card-image').prop('checked', settings.send_card_image !== false);

                    // Template
                    $('#notification-template').val(settings.notification_template || '');
                }
            }
        });
    }

    // Render Telegram Chat IDs
    function renderTelegramChatIds(chatIds) {
        const container = $('#telegram-chat-ids-container');
        container.empty();

        chatIds.forEach(function(chatId, index) {
            addTelegramChatIdRow(chatId, index);
        });
    }

    function addTelegramChatIdRow(value, index) {
        const container = $('#telegram-chat-ids-container');
        const isFirst = container.children().length === 0;

        const row = $('<div class="notification-input-row"></div>');
        const input = $('<input type="text" placeholder="Enter Chat ID (e.g., 1869690411)" value="' + (value || '') + '">');

        row.append(input);

        if (!isFirst) {
            const removeBtn = $('<button type="button" class="remove-btn">✕ Remove</button>');
            removeBtn.click(function() {
                row.remove();
            });
            row.append(removeBtn);
        }

        container.append(row);
    }

    $('#add-telegram-chat-id').click(function() {
        addTelegramChatIdRow('', $('#telegram-chat-ids-container').children().length);
    });

    // Render Email Recipients
    function renderEmailRecipients(recipients) {
        const container = $('#email-recipients-container');
        container.empty();

        recipients.forEach(function(recipient, index) {
            addEmailRecipientRow(recipient, index);
        });
    }

    function addEmailRecipientRow(value, index) {
        const container = $('#email-recipients-container');
        const isFirst = container.children().length === 0;

        const row = $('<div class="notification-input-row"></div>');
        const input = $('<input type="email" placeholder="Enter email address" value="' + (value || '') + '">');

        row.append(input);

        if (!isFirst) {
            const removeBtn = $('<button type="button" class="remove-btn">✕ Remove</button>');
            removeBtn.click(function() {
                row.remove();
            });
            row.append(removeBtn);
        }

        container.append(row);
    }

    $('#add-email-recipient').click(function() {
        addEmailRecipientRow('', $('#email-recipients-container').children().length);
    });

    // Save notification settings
    $('#save-notification-settings').click(function() {
        // Collect Telegram chat IDs
        const telegramChatIds = [];
        $('#telegram-chat-ids-container input').each(function() {
            const val = $(this).val().trim();
            if (val !== '') {
                telegramChatIds.push(val);
            }
        });

        // Collect email recipients
        const emailRecipients = [];
        $('#email-recipients-container input').each(function() {
            const val = $(this).val().trim();
            if (val !== '') {
                emailRecipients.push(val);
            }
        });

        const settings = {
            telegram_enabled: $('#telegram-enabled').is(':checked'),
            telegram_bot_token: $('#telegram-bot-token').val().trim(),
            telegram_chat_ids: telegramChatIds,
            email_enabled: $('#email-enabled').is(':checked'),
            email_recipients: emailRecipients,
            send_card_image: $('#send-card-image').is(':checked'),
            notification_template: $('#notification-template').val()
        };

        $('#loading-overlay').show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_save_notification_settings',
                nonce: nonce,
                telegram_enabled: settings.telegram_enabled ? 1 : 0,
                telegram_bot_token: settings.telegram_bot_token,
                telegram_chat_ids: settings.telegram_chat_ids,
                email_enabled: settings.email_enabled ? 1 : 0,
                email_recipients: settings.email_recipients,
                send_card_image: settings.send_card_image ? 1 : 0,
                notification_template: settings.notification_template
            },
            success: function(response) {
                $('#loading-overlay').hide();
                if (response.success) {
                    alert('✅ Notification settings saved successfully!');
                } else {
                    alert('❌ Failed to save notification settings: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                $('#loading-overlay').hide();
                alert('❌ Failed to save notification settings. Please try again.');
            }
        });
    });

    // Test Email connection
    $('#test-email').click(function() {
        const testEmail = $('#test-email-address').val().trim();

        if (!testEmail) {
            alert('❌ Please enter a test email address first.');
            return;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(testEmail)) {
            alert('❌ Please enter a valid email address.');
            return;
        }

        $('#email-test-result').html('<span style="color: #666;">Sending test email...</span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_test_email',
                nonce: nonce,
                test_email: testEmail
            },
            success: function(response) {
                if (response.success) {
                    $('#email-test-result').html('<span style="color: #00a32a; font-weight: bold;">✅ Email sent! Check inbox and spam folder.</span>');
                    setTimeout(function() {
                        $('#email-test-result').html('');
                    }, 8000);
                } else {
                    $('#email-test-result').html('<span style="color: #d63638; font-weight: bold;">❌ ' + (response.data || 'Failed to send email') + '</span>');
                }
            },
            error: function() {
                $('#email-test-result').html('<span style="color: #d63638; font-weight: bold;">❌ Network error</span>');
            }
        });
    });

    // Test Telegram connection
    $('#test-telegram').click(function() {
        const botToken = $('#telegram-bot-token').val().trim();

        // Collect chat IDs from inputs
        const chatIds = [];
        $('#telegram-chat-ids-container input').each(function() {
            const val = $(this).val().trim();
            if (val !== '') {
                chatIds.push(val);
            }
        });

        if (!botToken) {
            alert('❌ Please enter a bot token first.');
            return;
        }

        if (chatIds.length === 0) {
            alert('❌ Please enter at least one chat ID first.');
            return;
        }

        $('#telegram-test-result').html('<span style="color: #666;">Testing...</span>');

        // Test with the first chat ID
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_test_telegram',
                nonce: nonce,
                bot_token: botToken,
                chat_id: chatIds[0]
            },
            success: function(response) {
                if (response.success) {
                    $('#telegram-test-result').html('<span style="color: #00a32a; font-weight: bold;">✅ Connection successful!</span>');
                    setTimeout(function() {
                        $('#telegram-test-result').html('');
                    }, 5000);
                } else {
                    $('#telegram-test-result').html('<span style="color: #d63638; font-weight: bold;">❌ ' + (response.data.message || 'Connection failed') + '</span>');
                }
            },
            error: function() {
                $('#telegram-test-result').html('<span style="color: #d63638; font-weight: bold;">❌ Network error</span>');
            }
        });
    });

    // Reset template to default
    $('#reset-template').click(function() {
        if (confirm('Reset to default notification template?')) {
            const defaultTemplate = "🎯 New VIP Booking Received!\n\n" +
                "📋 Booking Number: {booking_number}\n" +
                "👤 Customer: {customer_name}\n" +
                "🏪 Service: {service}\n" +
                "📍 Store: {store}\n" +
                "📦 Package: {package}\n" +
                "🌍 Nationality: {nation}\n" +
                "👥 Number of People: {pax}\n" +
                "📅 Date: {date}\n" +
                "🕐 Time: {time}\n" +
                "💰 Price: {price} VND\n" +
                "⏰ Created: {created_at}";

            $('#notification-template').val(defaultTemplate);
            alert('✅ Template reset to default. Click "Save All Notification Settings" to save.');
        }
    });
});
