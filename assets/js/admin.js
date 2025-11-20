jQuery(document).ready(function($) {
    let rowCounter = 0;
    let flagsData = [];
    const ajaxurl = vipBookingAdmin.ajaxurl;
    const nonce = vipBookingAdmin.nonce;
    
    // Load data for Booking Data tab
    loadFlags();
    loadData();
    loadSettings();
    loadCleanupPeriod();
    
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
                alert(response.success ? '✅ Settings saved!' : '❌ Failed to save settings');
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

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_save_cleanup_period',
                nonce: nonce,
                cleanup_period: cleanupPeriodNegative
            },
            success: function(response) {
                alert(response.success ? '✅ Cleanup settings saved!' : '❌ Failed to save cleanup settings');
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

    // ===== NOTIFICATION SETTINGS =====
    let telegramChatIds = [];
    let emailRecipients = [];

    loadNotificationSettings();

    function loadNotificationSettings() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_notification_settings', nonce: nonce },
            success: function(response) {
                if (response.success && response.data) {
                    const settings = response.data;

                    $('#telegram-enabled').prop('checked', settings.telegram_enabled);
                    $('#telegram-bot-token').val(settings.telegram_bot_token || '');
                    telegramChatIds = settings.telegram_chat_ids || [];
                    renderTelegramChatIds();

                    $('#email-enabled').prop('checked', settings.email_enabled);
                    emailRecipients = settings.email_recipients || [];
                    renderEmailRecipients();

                    $('#send-card-image').prop('checked', settings.send_card_image);
                    $('#notification-template').val(settings.notification_template || '');
                }
            }
        });
    }

    function renderTelegramChatIds() {
        const container = $('#telegram-chat-ids-container');
        container.empty();
        telegramChatIds.forEach(function(chatId, index) {
            const chatIdDiv = $('<div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f0f0f0; margin-bottom: 5px; border-radius: 4px;">' +
                '<code style="flex: 1; font-family: monospace;">' + chatId + '</code>' +
                '<button class="button button-small remove-chat-id" data-index="' + index + '" style="padding: 2px 8px;">❌ Remove</button>' +
                '</div>');
            container.append(chatIdDiv);
        });
    }

    function renderEmailRecipients() {
        const container = $('#email-recipients-container');
        container.empty();
        emailRecipients.forEach(function(email, index) {
            const emailDiv = $('<div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f0f0f0; margin-bottom: 5px; border-radius: 4px;">' +
                '<span style="flex: 1;">' + email + '</span>' +
                '<button class="button button-small remove-email" data-index="' + index + '" style="padding: 2px 8px;">❌ Remove</button>' +
                '</div>');
            container.append(emailDiv);
        });
    }

    $('#add-telegram-chat-id').click(function() {
        const chatId = $('#new-telegram-chat-id').val().trim();
        if (!chatId) {
            alert('Please enter a chat ID');
            return;
        }
        if (telegramChatIds.indexOf(chatId) !== -1) {
            alert('This chat ID already exists');
            return;
        }
        telegramChatIds.push(chatId);
        renderTelegramChatIds();
        $('#new-telegram-chat-id').val('');
    });

    $(document).on('click', '.remove-chat-id', function() {
        const index = $(this).data('index');
        telegramChatIds.splice(index, 1);
        renderTelegramChatIds();
    });

    $('#add-email-recipient').click(function() {
        const email = $('#new-email-recipient').val().trim();
        if (!email) {
            alert('Please enter an email address');
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Please enter a valid email address');
            return;
        }
        if (emailRecipients.indexOf(email) !== -1) {
            alert('This email address already exists');
            return;
        }
        emailRecipients.push(email);
        renderEmailRecipients();
        $('#new-email-recipient').val('');
    });

    $(document).on('click', '.remove-email', function() {
        const index = $(this).data('index');
        emailRecipients.splice(index, 1);
        renderEmailRecipients();
    });

    $('#save-notification-settings').click(function() {
        const settings = {
            telegram_enabled: $('#telegram-enabled').is(':checked'),
            telegram_bot_token: $('#telegram-bot-token').val().trim(),
            telegram_chat_ids: JSON.stringify(telegramChatIds),
            email_enabled: $('#email-enabled').is(':checked'),
            email_recipients: JSON.stringify(emailRecipients),
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
                telegram_enabled: settings.telegram_enabled ? 'true' : 'false',
                telegram_bot_token: settings.telegram_bot_token,
                telegram_chat_ids: settings.telegram_chat_ids,
                email_enabled: settings.email_enabled ? 'true' : 'false',
                email_recipients: settings.email_recipients,
                send_card_image: settings.send_card_image ? 'true' : 'false',
                notification_template: settings.notification_template
            },
            success: function(response) {
                $('#loading-overlay').hide();
                const resultSpan = $('#notification-save-result');
                if (response.success) {
                    resultSpan.text('✅ Settings saved successfully!').css('color', '#00a32a');
                } else {
                    resultSpan.text('❌ Failed to save settings').css('color', '#d63638');
                }
                setTimeout(function() { resultSpan.text(''); }, 3000);
            }
        });
    });

    $('#test-telegram').click(function() {
        const botToken = $('#telegram-bot-token').val().trim();
        const resultSpan = $('#telegram-test-result');

        if (!botToken) {
            resultSpan.text('⚠️ Please enter bot token').css('color', '#d63638');
            setTimeout(function() { resultSpan.text(''); }, 3000);
            return;
        }

        if (telegramChatIds.length === 0) {
            resultSpan.text('⚠️ Please add at least one chat ID').css('color', '#d63638');
            setTimeout(function() { resultSpan.text(''); }, 3000);
            return;
        }

        const chatId = telegramChatIds[0];

        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_test_telegram',
                nonce: nonce,
                bot_token: botToken,
                chat_id: chatId
            },
            success: function(response) {
                $('#loading-overlay').hide();
                if (response.success) {
                    resultSpan.text('✅ Test message sent!').css('color', '#00a32a');
                } else {
                    resultSpan.text('❌ ' + (response.data.message || 'Failed to send')).css('color', '#d63638');
                }
                setTimeout(function() { resultSpan.text(''); }, 5000);
            }
        });
    });

    $('#test-email').click(function() {
        const resultSpan = $('#email-test-result');

        if (emailRecipients.length === 0) {
            resultSpan.text('⚠️ Please add at least one email').css('color', '#d63638');
            setTimeout(function() { resultSpan.text(''); }, 3000);
            return;
        }

        const email = emailRecipients[0];

        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'vip_booking_test_email',
                nonce: nonce,
                email: email
            },
            success: function(response) {
                $('#loading-overlay').hide();
                if (response.success) {
                    resultSpan.text('✅ Test email sent!').css('color', '#00a32a');
                } else {
                    resultSpan.text('❌ ' + (response.data.message || 'Failed to send')).css('color', '#d63638');
                }
                setTimeout(function() { resultSpan.text(''); }, 5000);
            }
        });
    });
});
