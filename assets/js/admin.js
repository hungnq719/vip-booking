jQuery(document).ready(function($) {
    let rowCounter = 0;
    let flagsData = [];
    const ajaxurl = vipBookingAdmin.ajaxurl;
    const nonce = vipBookingAdmin.nonce;
    
    // Load data for Booking Data tab
    loadFlags();
    loadData();
    
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
        if (parts.length !== 2) return '';
        
        // Parse and validate
        let hours = parseInt(parts[0], 10);
        let minutes = parseInt(parts[1], 10);
        
        if (isNaN(hours) || isNaN(minutes)) return '';
        
        // Validate ranges
        if (hours < 0 || hours > 23) hours = 0;
        if (minutes < 0 || minutes > 59) minutes = 0;
        
        // Pad with zeros and return
        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
    }
    
    function addRow(data = null) {
        const openingTime = data && data.opening_hours ? normalizeTime(data.opening_hours) : '';
        const closingTime = data && data.closing_hours ? normalizeTime(data.closing_hours) : '';
        
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
    
    $('#save-settings').click(function() {
        const exchangeRate = unformatNumber($('#exchange-rate-display').val());
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: { action: 'vip_booking_save_settings', nonce: nonce, exchange_rate: exchangeRate },
            success: function(response) {
                alert(response.success ? '✅ Settings saved!' : '❌ Failed to save');
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
                values.push(current.trim().replace(/^["']|["']$/g, ''));
                
                // Must have at least 7 columns
                if (values.length >= 7) {
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
                    errorCount++;
                }
            }
            
            if (importCount > 0) {
                let message = `✅ Successfully imported ${importCount} row(s)!`;
                if (errorCount > 0) {
                    message += `\n\n⚠️ Skipped ${errorCount} invalid row(s).`;
                }
                message += '\n\n💾 Click "Save Changes" to save to database.';
                alert(message);
            } else {
                alert('❌ No valid data found in CSV file.\n\nExpected 7 columns:\nService, Store Name, Service Package, Price, Opening Hours, Closing Hours, Prebook Time');
            }
        };
        reader.readAsText(file);
        $(this).val('');
    });
});
