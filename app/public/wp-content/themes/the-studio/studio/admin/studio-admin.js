/**
 * Studio Admin JavaScript
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Studio Admin: Initializing...');
        initializeStudioAdmin();
    });
    
    /**
     * Initialize Studio Admin
     */
    function initializeStudioAdmin() {
        // Initialize color pickers
        initColorPickers();
        
        // Initialize range controls
        initRangeControls();
        
        // Initialize tabs
        initTabs();
        
        // Scan variables button
        $('#studio-scan-variables').on('click', scanVariables);
        
        // Save variable buttons
        $('.studio-save-variable').on('click', saveVariable);
        
        // Auto-save on change (optional)
        $('.studio-variable-input input, .studio-variable-input select').on('change', function() {
            // Uncomment to enable auto-save
            // $(this).closest('.studio-variable-control').find('.studio-save-variable').click();
        });
    }
    
    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        console.log('Studio Admin: Initializing color pickers...');
        console.log('Found color controls:', $('.studio-control-color').length);
        
        $('.studio-control-color').each(function() {
            console.log('Initializing color picker for:', $(this).data('variable'));
            $(this).wpColorPicker({
                change: function(event, ui) {
                    $(this).val(ui.color.toString());
                },
                clear: false
            });
        });
    }
    
    /**
     * Initialize range controls
     */
    function initRangeControls() {
        $('.studio-control-range').each(function() {
            const $range = $(this);
            const $number = $range.siblings('.studio-control-range-number');
            const $unit = $range.siblings('.studio-control-range-unit');
            const unit = $unit.text();
            
            // Sync range with number input
            $range.on('input', function() {
                $number.val($(this).val());
                updateVariableValue($range, $(this).val() + unit);
            });
            
            // Sync number with range input
            $number.on('input', function() {
                $range.val($(this).val());
                updateVariableValue($range, $(this).val() + unit);
            });
        });
    }
    
    /**
     * Initialize tabs
     */
    function initTabs() {
        $(document).on('click', '.nav-tab', function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetId = $tab.attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            
            // Show corresponding panel
            $('.studio-category-panel').hide();
            $(targetId).show();
            
            return false;
        });
    }
    
    /**
     * Update variable value in control
     */
    function updateVariableValue($control, value) {
        const variableName = $control.data('variable');
        $control.closest('.studio-variable-control').attr('data-value', value);
    }
    
    /**
     * Scan variables
     */
    function scanVariables() {
        const $button = $(this);
        const $message = $('#studio-scan-message');
        
        // Disable button and show loading
        $button.prop('disabled', true).addClass('updating-message');
        
        $.ajax({
            url: studio_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'studio_scan_variables',
                nonce: studio_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage($message, 'success', response.data.message);
                    
                    // Reload page to show new variables
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage($message, 'error', response.data || 'Failed to scan variables');
                }
            },
            error: function() {
                showMessage($message, 'error', 'An error occurred while scanning variables');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('updating-message');
            }
        });
    }
    
    /**
     * Save variable
     */
    function saveVariable() {
        const $button = $(this);
        const $control = $button.closest('.studio-variable-control');
        const $status = $control.find('.studio-variable-status');
        const variableName = $control.data('variable');
        
        // Get value based on control type
        let value;
        const $colorPicker = $control.find('.studio-control-color');
        const $range = $control.find('.studio-control-range');
        const $select = $control.find('.studio-control-select');
        const $text = $control.find('.studio-control-text, .studio-control-font, .studio-control-shadow');
        
        if ($colorPicker.length) {
            value = $colorPicker.val();
        } else if ($range.length) {
            const $number = $control.find('.studio-control-range-number');
            const $unit = $control.find('.studio-control-range-unit');
            value = $number.val() + $unit.text();
        } else if ($select.length) {
            value = $select.val();
        } else if ($text.length) {
            value = $text.val();
        }
        
        // Disable button and show loading
        $button.prop('disabled', true).addClass('updating-message');
        $status.text('Saving...').removeClass('success error');
        
        $.ajax({
            url: studio_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'studio_save_variable',
                nonce: studio_admin.nonce,
                variable: variableName,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    $status.text('Saved!').addClass('success');
                    
                    // Clear status after 2 seconds
                    setTimeout(function() {
                        $status.text('').removeClass('success');
                    }, 2000);
                } else {
                    $status.text('Error: ' + (response.data || 'Failed to save')).addClass('error');
                }
            },
            error: function() {
                $status.text('Error: Connection failed').addClass('error');
            },
            complete: function() {
                $button.prop('disabled', false).removeClass('updating-message');
            }
        });
    }
    
    /**
     * Show message
     */
    function showMessage($element, type, message) {
        $element
            .removeClass('notice-success notice-error')
            .addClass('notice-' + type)
            .html('<p>' + message + '</p>')
            .show();
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $element.fadeOut();
        }, 5000);
    }
    
})(jQuery);