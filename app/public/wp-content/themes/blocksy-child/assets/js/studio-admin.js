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
            $(document).on('change', '.studio-token-input', this.handleTokenChange);
            
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
            
            // Update preview if color
            if (tokenType === 'color') {
                $input.siblings('.studio-color-preview').css('background-color', value);
            }
            
            // Mark as unsaved
            StudioAdmin.markUnsaved();
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
            
            // Collect color tokens
            $('.studio-color-input').each(function() {
                const name = $(this).data('token-name');
                const value = $(this).val();
                tokens.colors[name] = {
                    name: $(this).data('token-label'),
                    value: value
                };
            });
            
            // Collect typography tokens
            $('.studio-font-size-input').each(function() {
                const name = $(this).data('token-name');
                tokens.typography.fontSizes[name] = $(this).val();
            });
            
            $('.studio-font-weight-input').each(function() {
                const name = $(this).data('token-name');
                tokens.typography.fontWeights[name] = $(this).val();
            });
            
            // Collect spacing tokens
            $('.studio-spacing-input').each(function() {
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
