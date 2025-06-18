/**
 * Studio Admin JavaScript - Simple Version
 */

jQuery(document).ready(function($) {
    console.log('Studio Admin Simple JS loaded');
    
    // Ensure ajaxurl is defined
    if (typeof ajaxurl === 'undefined') {
        window.ajaxurl = studio_admin.ajax_url;
    }
    
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var target = $this.attr('href');
        
        console.log('Tab clicked:', target);
        
        // Update tabs
        $('.nav-tab').removeClass('nav-tab-active');
        $this.addClass('nav-tab-active');
        
        // Update panels
        $('.studio-category-panel').hide();
        $(target).show();
        
        return false;
    });
    
    // Scan Variables
    $('#studio-scan-variables').on('click', function() {
        var $button = $(this);
        var $message = $('#studio-scan-message');
        
        $button.prop('disabled', true).text('Scanning...');
        
        $.post(ajaxurl, {
            action: 'studio_scan_variables',
            nonce: studio_admin.nonce
        }, function(response) {
            if (response.success) {
                $message.removeClass('notice-error').addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                $message.removeClass('notice-success').addClass('notice notice-error').html('<p>Error: ' + response.data + '</p>').show();
            }
        }).always(function() {
            $button.prop('disabled', false).text('Scan Variables');
        });
    });
    
    // Initialize color pickers with a slight delay to ensure DOM is ready
    setTimeout(function() {
        if ($.fn.wpColorPicker) {
            console.log('Initializing color pickers. Found:', $('.studio-control-color').length);
            $('.studio-control-color').each(function() {
                var $this = $(this);
                console.log('Initializing color picker for:', $this.data('variable'));
                $this.wpColorPicker({
                    defaultColor: $this.val(),
                    change: function(event, ui) {
                        $this.trigger('change');
                    },
                    clear: false
                });
            });
        } else {
            console.log('wpColorPicker not available');
        }
    }, 100);
    
    // Range controls
    $('.studio-control-range').each(function() {
        var $range = $(this);
        var $number = $range.siblings('.studio-control-range-number');
        
        $range.on('input', function() {
            $number.val($(this).val());
        });
        
        $number.on('input', function() {
            $range.val($(this).val());
        });
    });
    
    // Save variable
    $('.studio-save-variable').on('click', function() {
        var $button = $(this);
        var $control = $button.closest('.studio-variable-control');
        var variableName = $control.data('variable');
        var value;
        
        // Get value
        var $input = $control.find('input[type="text"], input[type="number"], select').first();
        if ($control.find('.studio-control-range').length) {
            value = $control.find('.studio-control-range-number').val() + $control.find('.studio-control-range-unit').text();
        } else {
            value = $input.val();
        }
        
        console.log('Saving:', variableName, value);
        
        $button.prop('disabled', true).text('Saving...');
        
        $.post(ajaxurl, {
            action: 'studio_save_variable',
            nonce: studio_admin.nonce,
            variable: variableName,
            value: value
        }, function(response) {
            if (response.success) {
                $control.find('.studio-variable-status').text('Saved!').addClass('success');
                setTimeout(function() {
                    $control.find('.studio-variable-status').text('').removeClass('success');
                }, 2000);
            } else {
                $control.find('.studio-variable-status').text('Error!').addClass('error');
            }
        }).always(function() {
            $button.prop('disabled', false).text('Save');
        });
    });
});