/**
 * Studio Admin JavaScript
 */
(function($) {
    'use strict';

    // Studio Admin Manager
    const StudioAdmin = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initTokenSync();
        },

        bindEvents: function() {
            // Token value changes
            $(document).on('change input', '.studio-token-input, .studio-token-label-input', this.handleTokenChange);
            
            // Add token buttons
            $(document).on('click', '.studio-add-token', this.showAddTokenForm);
            
            // Delete token buttons
            $(document).on('click', '.studio-delete-token', this.deleteToken);
            
            // Sync tokens button
            $(document).on('click', '#studio-sync-tokens', this.syncTokens);
            
            // Add preset button
            $(document).on('click', '#studio-add-preset', this.showAddPresetForm);
            
            // Save preset
            $(document).on('click', '#studio-save-preset', this.savePreset);
            
            // Delete preset
            $(document).on('click', '.studio-delete-preset', this.deletePreset);
            
            // Convert HTML
            $(document).on('click', '#studio-convert-html', this.convertHTML);
            
            // Copy blocks
            $(document).on('click', '#studio-copy-blocks', this.copyBlocks);
        },

        initColorPickers: function() {
            $('.studio-color-input').each(function() {
                const $input = $(this);
                const $preview = $input.siblings('.studio-color-preview');
                
                $input.on('input', function() {
                    $preview.css('background-color', $input.val());
                });
            });
        },

        initTokenSync: function() {
            // Check sync status on load
            this.checkSyncStatus();
            
            // Auto-sync every 30 seconds
            setInterval(() => {
                this.checkSyncStatus();
            }, 30000);
        },

        handleTokenChange: function(e) {
            const $input = $(e.target);
            const tokenType = $input.data('token-type');
            const tokenName = $input.data('token-name');
            const value = $input.val();
            
            // Mark as changed
            $input.addClass('changed');
            
            // Enable sync button
            $('#studio-sync-tokens').prop('disabled', false).addClass('has-changes');
            
            // Update preview if color
            if (tokenType === 'color' && $input.hasClass('studio-color-input')) {
                $input.siblings('.studio-color-preview').css('background-color', value);
            }
        },
        
        showAddTokenForm: function(e) {
            e.preventDefault();
            const $button = $(e.target);
            const tokenType = $button.data('token-type');
            
            // Create form HTML
            let formHtml = `
                <div class="studio-add-token-form" data-token-type="${tokenType}">
                    <h3>Add New ${tokenType.charAt(0).toUpperCase() + tokenType.slice(1)} Token</h3>
                    <div class="studio-form-group">
                        <label>Token Key (e.g., primary-light)</label>
                        <input type="text" class="studio-new-token-key" placeholder="Token key">
                    </div>
            `;
            
            if (tokenType === 'color') {
                formHtml += `
                    <div class="studio-form-group">
                        <label>Label</label>
                        <input type="text" class="studio-new-token-label" placeholder="Display name">
                    </div>
                    <div class="studio-form-group">
                        <label>Color Value</label>
                        <input type="color" class="studio-new-token-value" value="#000000">
                    </div>
                `;
            } else {
                formHtml += `
                    <div class="studio-form-group">
                        <label>Value</label>
                        <input type="text" class="studio-new-token-value" placeholder="Token value">
                    </div>
                `;
            }
            
            formHtml += `
                    <div class="studio-form-actions">
                        <button class="studio-button studio-button-primary studio-save-new-token">Save Token</button>
                        <button class="studio-button studio-cancel-new-token">Cancel</button>
                    </div>
                </div>
            `;
            
            // Remove any existing forms
            $('.studio-add-token-form').remove();
            
            // Insert form after the button's container
            $button.closest('.studio-token-section-header').after(formHtml);
            
            // Bind form events
            $('.studio-save-new-token').on('click', this.saveNewToken.bind(this));
            $('.studio-cancel-new-token').on('click', function() {
                $('.studio-add-token-form').remove();
            });
        },
        
        saveNewToken: function(e) {
            e.preventDefault();
            const $form = $(e.target).closest('.studio-add-token-form');
            const tokenType = $form.data('token-type');
            const key = $form.find('.studio-new-token-key').val();
            const value = $form.find('.studio-new-token-value').val();
            const label = $form.find('.studio-new-token-label').val();
            
            if (!key || !value) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Create new token element
            let tokenHtml = `
                <div class="studio-token-item" data-token-key="${key}">
                    <span class="studio-token-name">${key}</span>
            `;
            
            if (tokenType === 'color') {
                tokenHtml += `
                    <div class="studio-token-value">
                        <div class="studio-color-preview" style="background-color: ${value}"></div>
                        <input type="color" 
                               class="studio-token-input studio-color-input changed" 
                               data-token-type="color"
                               data-token-name="${key}"
                               data-token-label="${label || key}"
                               value="${value}">
                        <input type="text" 
                               class="studio-token-label-input changed" 
                               data-token-name="${key}"
                               value="${label || key}"
                               placeholder="Label">
                        <button class="studio-delete-token" data-token-type="color" data-token-name="${key}">×</button>
                    </div>
                `;
            } else {
                tokenHtml += `
                    <input type="text" 
                           class="studio-token-input studio-${tokenType}-input changed" 
                           data-token-type="${tokenType}"
                           data-token-name="${key}"
                           value="${value}">
                    <button class="studio-delete-token" data-token-type="${tokenType}" data-token-name="${key}">×</button>
                `;
            }
            
            tokenHtml += '</div>';
            
            // Add to appropriate section
            const $section = $form.closest('.studio-token-section');
            const $group = $section.find('.studio-token-group').last();
            $group.append(tokenHtml);
            
            // Remove form
            $form.remove();
            
            // Enable sync button
            $('#studio-sync-tokens').prop('disabled', false).addClass('has-changes');
            
            // Re-init color pickers
            this.initColorPickers();
        },
        
        deleteToken: function(e) {
            e.preventDefault();
            const $button = $(e.target);
            const tokenType = $button.data('token-type');
            const tokenName = $button.data('token-name');
            
            if (confirm(`Are you sure you want to delete the "${tokenName}" token?`)) {
                // Mark token as deleted
                $button.closest('.studio-token-item').addClass('deleted').hide();
                
                // Enable sync button
                $('#studio-sync-tokens').prop('disabled', false).addClass('has-changes');
            }
        },

        syncTokens: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $status = $('.studio-sync-status');
            
            // Show syncing status
            $button.prop('disabled', true).text('Syncing...');
            $status.removeClass('error').addClass('syncing').text('Syncing tokens...');
            
            // Collect all token values
            const tokens = {
                colors: {},
                typography: {
                    fontSizes: {},
                    fontWeights: {}
                },
                spacing: {}
            };
            
            // Collect color tokens (excluding deleted ones)
            $('.studio-token-item:not(.deleted) .studio-color-input').each(function() {
                const name = $(this).data('token-name');
                const value = $(this).val();
                const label = $(this).siblings('.studio-token-label-input').val() || $(this).data('token-label');
                tokens.colors[name] = {
                    name: label,
                    value: value
                };
            });
            
            // Collect typography tokens
            $('.studio-token-item:not(.deleted) .studio-font-size-input').each(function() {
                const name = $(this).data('token-name');
                tokens.typography.fontSizes[name] = $(this).val();
            });
            
            $('.studio-token-item:not(.deleted) .studio-font-weight-input').each(function() {
                const name = $(this).data('token-name');
                tokens.typography.fontWeights[name] = $(this).val();
            });
            
            // Collect spacing tokens
            $('.studio-token-item:not(.deleted) .studio-spacing-input').each(function() {
                const name = $(this).data('token-name');
                tokens.spacing[name] = $(this).val();
            });
            
            // Send AJAX request
            $.ajax({
                url: studioAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'studio_sync_tokens',
                    nonce: studioAdmin.nonce,
                    tokens: JSON.stringify(tokens)
                },
                success: function(response) {
                    if (response.success) {
                        $status.removeClass('syncing').text('Tokens synced successfully');
                        $button.text('Tokens Synced!');
                        
                        setTimeout(() => {
                            $button.prop('disabled', false).text('Sync Tokens');
                            $status.text('All tokens synced');
                        }, 2000);
                    } else {
                        $status.removeClass('syncing').addClass('error').text('Sync failed: ' + response.data);
                        $button.prop('disabled', false).text('Sync Tokens');
                    }
                },
                error: function() {
                    $status.removeClass('syncing').addClass('error').text('Sync failed: Network error');
                    $button.prop('disabled', false).text('Sync Tokens');
                }
            });
        },

        checkSyncStatus: function() {
            // This would check if studio.json and theme.json are in sync
            // For now, we'll just show as synced
            $('.studio-sync-status').text('All tokens synced');
        },

        markUnsaved: function() {
            $('.studio-sync-status').addClass('error').text('Unsaved changes');
            $('#studio-sync-tokens').prop('disabled', false);
        },

        showAddPresetForm: function(e) {
            e.preventDefault();
            
            // Show preset form modal
            const formHTML = `
                <div class="studio-modal">
                    <div class="studio-modal-content">
                        <h3>Add Typography Preset</h3>
                        <div class="studio-form-group">
                            <label>Preset Name</label>
                            <input type="text" id="preset-name" class="studio-form-control" placeholder="e.g., heading-hero">
                        </div>
                        <div class="studio-form-group">
                            <label>Font Size</label>
                            <select id="preset-font-size" class="studio-form-control">
                                <option value="small">Small (0.875rem)</option>
                                <option value="base">Base (1rem)</option>
                                <option value="medium">Medium (1.125rem)</option>
                                <option value="large">Large (1.25rem)</option>
                                <option value="x-large">X-Large (1.5rem)</option>
                                <option value="xx-large">XX-Large (2rem)</option>
                                <option value="xxx-large">XXX-Large (2.5rem)</option>
                                <option value="huge">Huge (3rem)</option>
                            </select>
                        </div>
                        <div class="studio-form-group">
                            <label>Font Weight</label>
                            <select id="preset-font-weight" class="studio-form-control">
                                <option value="light">Light (300)</option>
                                <option value="normal">Normal (400)</option>
                                <option value="medium">Medium (500)</option>
                                <option value="semibold">Semibold (600)</option>
                                <option value="bold">Bold (700)</option>
                                <option value="extrabold">Extrabold (800)</option>
                            </select>
                        </div>
                        <div class="studio-form-group">
                            <label>Line Height</label>
                            <input type="text" id="preset-line-height" class="studio-form-control" placeholder="e.g., 1.5">
                        </div>
                        <div class="studio-form-group">
                            <label>Letter Spacing</label>
                            <input type="text" id="preset-letter-spacing" class="studio-form-control" placeholder="e.g., -0.02em">
                        </div>
                        <div class="studio-modal-actions">
                            <button class="studio-button studio-button-primary" id="studio-save-preset">Save Preset</button>
                            <button class="studio-button studio-button-secondary" onclick="$('.studio-modal').remove()">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(formHTML);
        },

        savePreset: function(e) {
            e.preventDefault();
            
            const preset = {
                name: $('#preset-name').val(),
                fontSize: $('#preset-font-size').val(),
                fontWeight: $('#preset-font-weight').val(),
                lineHeight: $('#preset-line-height').val(),
                letterSpacing: $('#preset-letter-spacing').val()
            };
            
            // Validate
            if (!preset.name) {
                alert('Please enter a preset name');
                return;
            }
            
            // Save via AJAX
            $.ajax({
                url: studioAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'studio_save_preset',
                    nonce: studioAdmin.nonce,
                    preset: JSON.stringify(preset)
                },
                success: function(response) {
                    if (response.success) {
                        $('.studio-modal').remove();
                        location.reload(); // Reload to show new preset
                    } else {
                        alert('Failed to save preset: ' + response.data);
                    }
                }
            });
        },

        deletePreset: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this preset?')) {
                return;
            }
            
            const presetName = $(this).data('preset-name');
            
            $.ajax({
                url: studioAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'studio_delete_preset',
                    nonce: studioAdmin.nonce,
                    preset_name: presetName
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete preset: ' + response.data);
                    }
                }
            });
        },

        convertHTML: function(e) {
            e.preventDefault();
            
            const html = $('#studio-html-input').val();
            
            if (!html) {
                alert('Please enter some HTML to convert');
                return;
            }
            
            const $button = $(this);
            $button.prop('disabled', true).text('Converting...');
            
            $.ajax({
                url: studioAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'studio_convert_html',
                    nonce: studioAdmin.nonce,
                    html: html
                },
                success: function(response) {
                    if (response.success) {
                        $('#studio-blocks-output').val(response.data.blocks);
                        $button.prop('disabled', false).text('Convert to Blocks');
                    } else {
                        alert('Conversion failed: ' + response.data);
                        $button.prop('disabled', false).text('Convert to Blocks');
                    }
                },
                error: function() {
                    alert('Conversion failed: Network error');
                    $button.prop('disabled', false).text('Convert to Blocks');
                }
            });
        },

        copyBlocks: function(e) {
            e.preventDefault();
            
            const blocks = $('#studio-blocks-output').val();
            
            if (!blocks) {
                alert('No blocks to copy');
                return;
            }
            
            // Copy to clipboard
            const temp = $('<textarea>');
            $('body').append(temp);
            temp.val(blocks).select();
            document.execCommand('copy');
            temp.remove();
            
            // Show feedback
            const $button = $(this);
            const originalText = $button.text();
            $button.text('Copied!');
            
            setTimeout(() => {
                $button.text(originalText);
            }, 2000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.studio-admin-wrap').length > 0) {
            StudioAdmin.init();
        }
    });

})(jQuery);
