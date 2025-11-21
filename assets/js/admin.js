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
    
    // Store management: Group flat rows by Store
    let storesData = [];

    function loadData() {
        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_get_data', nonce: nonce },
            success: function(response) {
                const data = response.success ? (response.data || []) : [];
                storesData = groupDataByStore(data);
                renderStores();
                $('#loading-overlay').hide();
            }
        });
    }

    function groupDataByStore(flatData) {
        const storesMap = {};

        flatData.forEach(function(row) {
            const storeKey = (row.store || '') + '|' + (row.store_id || '');

            if (!storesMap[storeKey]) {
                storesMap[storeKey] = {
                    service: row.service || '',
                    store_name: row.store || '',
                    store_id: row.store_id || '',
                    opening_hours: row.opening_hours || '',
                    closing_hours: row.closing_hours || '',
                    packages: []
                };
            }

            storesMap[storeKey].packages.push({
                package: row.package || '',
                price: row.price || '',
                prebook_time: row.prebook_time || '15'
            });
        });

        return Object.values(storesMap);
    }

    function renderStores() {
        const container = $('#stores-container');
        container.empty();

        if (storesData.length === 0) {
            container.html('<div class="empty-store-message">No stores yet. Click "➕ Add New Store" to get started.</div>');
            return;
        }

        storesData.forEach(function(store, storeIndex) {
            const storeSection = renderStoreSection(store, storeIndex);
            container.append(storeSection);
        });
    }

    function renderStoreSection(store, storeIndex) {
        const section = $('<div class="store-section" data-store-index="' + storeIndex + '"></div>');

        // Store header
        const header = $(`
            <div class="store-header">
                <div class="store-header-left">
                    <div class="store-header-icon">▼</div>
                    <div class="store-header-info">
                        <div class="store-info-item">
                            <span class="store-info-label">Service</span>
                            <span class="store-info-value">${store.service || 'N/A'}</span>
                        </div>
                        <div class="store-info-item">
                            <span class="store-info-label">Store Name</span>
                            <span class="store-info-value">${store.store_name || 'N/A'}</span>
                        </div>
                        <div class="store-info-item">
                            <span class="store-info-label">Store ID</span>
                            <span class="store-info-value">${store.store_id || 'N/A'}</span>
                        </div>
                        <div class="store-info-item">
                            <span class="store-info-label">Hours</span>
                            <span class="store-info-value">${store.opening_hours || 'N/A'} - ${store.closing_hours || 'N/A'}</span>
                        </div>
                        <div class="store-info-item">
                            <span class="store-info-label">Packages</span>
                            <span class="store-info-value">${store.packages.length}</span>
                        </div>
                    </div>
                </div>
                <div class="store-header-actions">
                    <button class="delete-store-btn" data-store-index="${storeIndex}">🗑️ Delete Store</button>
                </div>
            </div>
        `);

        // Store body (collapsed by default)
        const body = $('<div class="store-body"></div>');

        // Fixed fields (editable)
        const fixedFields = $(`
            <div class="store-fixed-fields">
                <div class="store-field">
                    <label>Service</label>
                    <input type="text" class="store-service" value="${store.service || ''}" placeholder="Karaoke">
                </div>
                <div class="store-field">
                    <label>Store Name</label>
                    <input type="text" class="store-name" value="${store.store_name || ''}" placeholder="Store name">
                </div>
                <div class="store-field">
                    <label>Store ID</label>
                    <input type="text" class="store-id" value="${store.store_id || ''}" placeholder="S1">
                </div>
                <div class="store-field">
                    <label>Opening Hours</label>
                    <input type="time" class="store-opening" value="${normalizeTime(store.opening_hours)}">
                </div>
                <div class="store-field">
                    <label>Closing Hours</label>
                    <input type="time" class="store-closing" value="${normalizeTime(store.closing_hours)}">
                </div>
            </div>
        `);

        // Packages section
        const packagesSection = $('<div class="packages-section"></div>');
        packagesSection.append('<h4>📦 Service Packages</h4>');

        const packagesTable = $(`
            <table class="packages-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Service Package</th>
                        <th style="width: 30%;">Price (VND)</th>
                        <th style="width: 20%;">Prebook (min)</th>
                        <th style="width: 5%; text-align: center;">Delete</th>
                    </tr>
                </thead>
                <tbody class="packages-tbody"></tbody>
            </table>
        `);

        const packagesTbody = packagesTable.find('.packages-tbody');
        store.packages.forEach(function(pkg, pkgIndex) {
            const row = renderPackageRow(pkg, storeIndex, pkgIndex);
            packagesTbody.append(row);
        });

        packagesSection.append(packagesTable);
        packagesSection.append(`<button class="add-package-btn" data-store-index="${storeIndex}">➕ Add Package</button>`);

        body.append(fixedFields);
        body.append(packagesSection);

        section.append(header);
        section.append(body);

        // Toggle accordion
        header.on('click', function() {
            const icon = $(this).find('.store-header-icon');
            const body = $(this).siblings('.store-body');

            if (body.hasClass('active')) {
                body.removeClass('active');
                icon.addClass('collapsed');
            } else {
                body.addClass('active');
                icon.removeClass('collapsed');
            }
        });

        return section;
    }

    function renderPackageRow(pkg, storeIndex, pkgIndex) {
        return $(`
            <tr data-package-index="${pkgIndex}">
                <td><input type="text" class="package-name" value="${pkg.package || ''}" placeholder="VIP Package"></td>
                <td><input type="text" class="package-price price-input" value="${formatNumber(pkg.price)}" data-raw-value="${pkg.price}" placeholder="0"></td>
                <td><input type="number" class="package-prebook" value="${pkg.prebook_time || '15'}" min="0" placeholder="15"></td>
                <td style="text-align: center;"><span class="delete-package-btn" data-store-index="${storeIndex}" data-package-index="${pkgIndex}">❌</span></td>
            </tr>
        `);
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

    // Update storesData from DOM before saving
    function updateStoresDataFromDOM() {
        storesData = [];

        $('.store-section').each(function() {
            const $section = $(this);
            const $body = $section.find('.store-body');

            const store = {
                service: $body.find('.store-service').val() || '',
                store_name: $body.find('.store-name').val() || '',
                store_id: $body.find('.store-id').val() || '',
                opening_hours: $body.find('.store-opening').val() || '',
                closing_hours: $body.find('.store-closing').val() || '',
                packages: []
            };

            $body.find('.packages-tbody tr').each(function() {
                const $row = $(this);
                store.packages.push({
                    package: $row.find('.package-name').val() || '',
                    price: $row.find('.package-price').attr('data-raw-value') || unformatNumber($row.find('.package-price').val()) || '',
                    prebook_time: $row.find('.package-prebook').val() || '15'
                });
            });

            storesData.push(store);
        });
    }

    // Flatten stores data to flat rows (for saving to database)
    function flattenStoresData() {
        const flatData = [];

        storesData.forEach(function(store) {
            if (store.packages.length === 0) {
                // Store with no packages - create one row with empty package
                flatData.push({
                    service: store.service,
                    store: store.store_name,
                    store_id: store.store_id,
                    package: '',
                    price: '',
                    opening_hours: store.opening_hours,
                    closing_hours: store.closing_hours,
                    prebook_time: '15'
                });
            } else {
                // Create one row per package
                store.packages.forEach(function(pkg) {
                    flatData.push({
                        service: store.service,
                        store: store.store_name,
                        store_id: store.store_id,
                        package: pkg.package,
                        price: pkg.price,
                        opening_hours: store.opening_hours,
                        closing_hours: store.closing_hours,
                        prebook_time: pkg.prebook_time
                    });
                });
            }
        });

        return flatData;
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

    // Add new store
    $('#add-store').click(function() {
        storesData.push({
            service: '',
            store_name: '',
            store_id: '',
            opening_hours: '',
            closing_hours: '',
            packages: [{
                package: '',
                price: '',
                prebook_time: '15'
            }]
        });
        renderStores();

        // Auto-expand the new store
        const newStore = $('.store-section').last();
        newStore.find('.store-body').addClass('active');
        newStore.find('.store-header-icon').removeClass('collapsed');
    });

    // Delete store
    $(document).on('click', '.delete-store-btn', function(e) {
        e.stopPropagation();
        const storeIndex = $(this).data('store-index');
        const storeName = storesData[storeIndex].store_name || 'Unnamed Store';

        if (confirm(`Delete store "${storeName}" and all its packages?`)) {
            storesData.splice(storeIndex, 1);
            renderStores();
        }
    });

    // Add package to store
    $(document).on('click', '.add-package-btn', function() {
        const storeIndex = $(this).data('store-index');
        const $tbody = $(this).siblings('.packages-table').find('.packages-tbody');

        const newPackage = {
            package: '',
            price: '',
            prebook_time: '15'
        };

        storesData[storeIndex].packages.push(newPackage);

        const pkgIndex = storesData[storeIndex].packages.length - 1;
        const row = renderPackageRow(newPackage, storeIndex, pkgIndex);
        $tbody.append(row);

        // Update header package count
        $(this).closest('.store-section').find('.store-header-info').find('.store-info-value').last().text(storesData[storeIndex].packages.length);
    });

    // Delete package from store
    $(document).on('click', '.delete-package-btn', function() {
        const storeIndex = $(this).data('store-index');
        const pkgIndex = $(this).data('package-index');

        if (storesData[storeIndex].packages.length === 1) {
            alert('⚠️ Cannot delete the last package. Each store must have at least one package.');
            return;
        }

        if (confirm('Delete this package?')) {
            storesData[storeIndex].packages.splice(pkgIndex, 1);
            renderStores();
        }
    });
    
    $('#reset-all').click(function() {
        if (confirm('⚠️ WARNING: This will delete ALL stores and data!\n\nAre you absolutely sure?')) {
            if (confirm('This action CANNOT be undone. Proceed?')) {
                storesData = [];
                renderStores();
                alert('✅ All data has been cleared!');
            }
        }
    });

    $('#save-changes').click(function() {
        // Update storesData from DOM
        updateStoresDataFromDOM();

        // Flatten to flat rows for database
        const flatData = flattenStoresData();

        console.log('Saving stores:', storesData);
        console.log('Flattened data:', flatData);

        $('#loading-overlay').show();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_save_data', nonce: nonce, data: JSON.stringify(flatData) },
            success: function(response) {
                $('#loading-overlay').hide();
                if (response.success) {
                    alert('✅ Data saved!');
                    // Reload to reflect any changes
                    loadData();
                } else {
                    alert('❌ Failed to save');
                }
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
        // Update storesData from DOM
        updateStoresDataFromDOM();

        // Flatten to flat rows for CSV export
        const flatData = flattenStoresData();

        let csvContent = "Service,Store Name,Store ID,Service Package,Price,Opening Hours,Closing Hours,Prebook Time\n";
        flatData.forEach(function(row) {
            csvContent += `"${row.service}","${row.store}","${row.store_id}","${row.package}","${row.price}","${row.opening_hours}","${row.closing_hours}","${row.prebook_time}"\n`;
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

        alert('✅ CSV exported successfully!');
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
            const flatData = [];

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

                // Support both old format (7 columns) and new format (8 columns with Store ID)
                if (values.length >= 7) {
                    // Check if this is the new format (8 columns) or old format (7 columns)
                    const hasStoreId = values.length >= 8;

                    if (hasStoreId) {
                        console.log('Row', i, '- Opening raw:', values[5], '- Closing raw:', values[6]);
                        flatData.push({
                            service: values[0] || '',
                            store: values[1] || '',
                            store_id: values[2] || '',
                            package: values[3] || '',
                            price: values[4] || '',
                            opening_hours: values[5] || '',
                            closing_hours: values[6] || '',
                            prebook_time: values[7] || '15'
                        });
                    } else {
                        // Old format without Store ID
                        console.log('Row', i, '- Opening raw:', values[4], '- Closing raw:', values[5]);
                        flatData.push({
                            service: values[0] || '',
                            store: values[1] || '',
                            store_id: '',
                            package: values[2] || '',
                            price: values[3] || '',
                            opening_hours: values[4] || '',
                            closing_hours: values[5] || '',
                            prebook_time: values[6] || '15'
                        });
                    }
                    importCount++;
                } else {
                    console.warn('Row', i, 'only has', values.length, 'columns:', values);
                    errorCount++;
                }
            }

            console.log('=== CSV IMPORT END ===');
            console.log('Imported:', importCount, '| Errors:', errorCount);

            if (importCount > 0) {
                // Group flat data into stores
                storesData = groupDataByStore(flatData);
                renderStores();

                let message = `✅ Successfully imported ${importCount} row(s) into ${storesData.length} store(s)!`;
                if (errorCount > 0) {
                    message += `\n\n⚠️ Skipped ${errorCount} invalid row(s).`;
                }
                message += '\n\n💾 Click "Save Changes" to save to database.';
                alert(message);
            } else {
                alert('❌ No valid data found in CSV file.\n\nExpected 8 columns:\nService, Store Name, Store ID, Service Package, Price, Opening Hours, Closing Hours, Prebook Time\n\nCheck browser console (F12) for details.');
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
