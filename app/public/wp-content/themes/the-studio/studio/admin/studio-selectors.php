<?php
/**
 * Studio Selectors Admin Page
 * 
 * UI for building CSS selectors and assigning variable groups
 * 
 * @package TheStudio
 */

// Security check
if (!defined('ABSPATH')) {
    exit;
}

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

/**
 * Render selectors page
 */
function studio_admin_selectors_page() {
    // Get selector builder instance
    $builder = \Studio\Core\SelectorBuilder::get_instance();
    $selectors = $builder->get_selectors();
    $variable_groups = $builder->get_variable_groups();
    
    ?>
    <div class="wrap studio-selectors-page">
        <h1>Studio Selectors</h1>
        <p>Target any element on your site and apply groups of CSS variables.</p>
        
        <div class="studio-selectors-container" style="display: grid; grid-template-columns: 1fr 350px; gap: 20px; margin-top: 20px;">
            
            <!-- Main Content -->
            <div class="selectors-main">
                
                <!-- Add New Selector -->
                <div class="studio-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px;">
                    <h2 style="margin-top: 0;">Add New Selector</h2>
                    
                    <form id="new-selector-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="selector-name">Name</label>
                                </th>
                                <td>
                                    <input type="text" id="selector-name" class="regular-text" placeholder="Primary Button" />
                                    <p class="description">A friendly name for this selector</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="selector-css">CSS Selector</label>
                                </th>
                                <td>
                                    <input type="text" id="selector-css" class="regular-text" placeholder=".button-primary, .wp-block-button__link" />
                                    <p class="description">The CSS selector(s) to target</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="selector-description">Description</label>
                                </th>
                                <td>
                                    <textarea id="selector-description" class="large-text" rows="2" placeholder="Styles for primary action buttons"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Variables</th>
                                <td>
                                    <div class="variable-groups">
                                        <?php foreach ($variable_groups as $prefix => $group) : ?>
                                            <div class="variable-group" style="margin-bottom: 15px;">
                                                <h4 style="margin: 0 0 10px 0;">
                                                    <label>
                                                        <input type="checkbox" class="group-toggle" data-group="<?php echo esc_attr($prefix); ?>" />
                                                        <?php echo esc_html($group['name']); ?>
                                                        <span style="color: #666; font-weight: normal;">(<?php echo count($group['variables']); ?> variables)</span>
                                                    </label>
                                                </h4>
                                                <div class="group-variables" data-group="<?php echo esc_attr($prefix); ?>" style="display: none; margin-left: 20px; max-height: 200px; overflow-y: auto;">
                                                    <?php foreach ($group['variables'] as $var_name => $var_data) : ?>
                                                        <label style="display: block; margin: 5px 0;">
                                                            <input type="checkbox" name="variables[]" value="<?php echo esc_attr($var_name); ?>" />
                                                            <code><?php echo esc_html($var_name); ?></code>
                                                            <?php if ($var_data['value']) : ?>
                                                                <span style="color: #666;">= <?php echo esc_html($var_data['value']); ?></span>
                                                            <?php endif; ?>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary">Add Selector</button>
                            <button type="button" class="button" id="clear-form">Clear</button>
                        </p>
                    </form>
                </div>
                
                <!-- Existing Selectors -->
                <div class="studio-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                    <h2 style="margin-top: 0;">Existing Selectors</h2>
                    
                    <?php if (empty($selectors)) : ?>
                        <p style="color: #666;">No selectors created yet. Add your first selector above!</p>
                    <?php else : ?>
                        <div class="selectors-list">
                            <?php foreach ($selectors as $selector) : ?>
                                <div class="selector-item" data-id="<?php echo esc_attr($selector['id']); ?>" style="border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 15px;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h3 style="margin: 0 0 5px 0;">
                                                <?php echo esc_html($selector['name']); ?>
                                                <?php if (!$selector['active']) : ?>
                                                    <span style="color: #dc3232; font-size: 0.8em;">(Inactive)</span>
                                                <?php endif; ?>
                                            </h3>
                                            <p style="margin: 5px 0;"><code><?php echo esc_html($selector['selector']); ?></code></p>
                                            <?php if ($selector['description']) : ?>
                                                <p style="margin: 5px 0; color: #666;"><?php echo esc_html($selector['description']); ?></p>
                                            <?php endif; ?>
                                            <p style="margin: 5px 0; color: #666;">
                                                <?php echo count($selector['variables']); ?> variables applied
                                            </p>
                                        </div>
                                        <div>
                                            <button class="button button-small toggle-selector" data-id="<?php echo esc_attr($selector['id']); ?>">
                                                <?php echo $selector['active'] ? 'Disable' : 'Enable'; ?>
                                            </button>
                                            <button class="button button-small edit-selector" data-id="<?php echo esc_attr($selector['id']); ?>">Edit</button>
                                            <button class="button button-small delete-selector" data-id="<?php echo esc_attr($selector['id']); ?>" style="color: #dc3232;">Delete</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Show variables on hover/click -->
                                    <div class="selector-variables" style="display: none; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                        <strong>Applied Variables:</strong>
                                        <div style="margin-top: 5px;">
                                            <?php foreach ($selector['variables'] as $var) : ?>
                                                <code style="display: inline-block; margin: 2px; padding: 2px 5px; background: #f0f0f1; border-radius: 3px;">
                                                    <?php echo esc_html($var); ?>
                                                </code>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
            </div>
            
            <!-- Sidebar -->
            <div class="selectors-sidebar">
                
                <!-- Preview -->
                <div class="studio-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; margin-bottom: 20px; position: sticky; top: 32px;">
                    <h3 style="margin-top: 0;">Preview</h3>
                    
                    <div id="selector-preview" style="background: #f0f0f1; border: 1px solid #ddd; border-radius: 4px; padding: 15px; min-height: 150px;">
                        <p style="color: #666; text-align: center;">Select variables to see CSS preview</p>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <button class="button button-primary" id="generate-all-css" style="width: 100%;">Generate All CSS</button>
                        <p class="description" style="text-align: center; margin-top: 5px;">
                            Regenerates studio-selectors.css
                        </p>
                    </div>
                </div>
                
                <!-- Help -->
                <div class="studio-card" style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                    <h3 style="margin-top: 0;">Quick Tips</h3>
                    
                    <h4>Common Selectors:</h4>
                    <ul style="margin: 0; font-size: 0.9em;">
                        <li><code>.button</code> - All buttons</li>
                        <li><code>#header</code> - Header area</li>
                        <li><code>.entry-content h2</code> - Content headings</li>
                        <li><code>.wp-block-*</code> - Gutenberg blocks</li>
                    </ul>
                    
                    <h4 style="margin-top: 15px;">Selector Tips:</h4>
                    <ul style="margin: 0; font-size: 0.9em;">
                        <li>Use commas for multiple selectors</li>
                        <li>Be specific to avoid conflicts</li>
                        <li>Test selectors in browser DevTools</li>
                        <li>Group related elements together</li>
                    </ul>
                </div>
                
            </div>
            
        </div>
        
    </div>
    
    <style>
    .studio-selectors-page .selector-item:hover .selector-variables {
        display: block !important;
    }
    
    .studio-selectors-page .variable-group h4 {
        cursor: pointer;
        user-select: none;
    }
    
    .studio-selectors-page .variable-group h4:hover {
        color: #0073aa;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var studioNonce = '<?php echo wp_create_nonce('studio_nonce'); ?>';
        
        // Toggle variable groups
        $('.group-toggle').on('change', function() {
            var group = $(this).data('group');
            var $variables = $('.group-variables[data-group="' + group + '"]');
            var $checkboxes = $variables.find('input[type="checkbox"]');
            
            if ($(this).is(':checked')) {
                $variables.slideDown();
                $checkboxes.prop('checked', true);
            } else {
                $checkboxes.prop('checked', false);
            }
            
            updatePreview();
        });
        
        // Variable group expand/collapse
        $('.variable-group h4').on('click', function(e) {
            if ($(e.target).is('input')) return;
            
            var $group = $(this).closest('.variable-group');
            var $variables = $group.find('.group-variables');
            
            $variables.slideToggle();
        });
        
        // Update preview when variables change
        $('input[name="variables[]"]').on('change', updatePreview);
        $('#selector-css').on('input', updatePreview);
        
        function updatePreview() {
            var selector = $('#selector-css').val() || '.example';
            var variables = [];
            
            $('input[name="variables[]"]:checked').each(function() {
                variables.push($(this).val());
            });
            
            if (variables.length === 0) {
                $('#selector-preview').html('<p style="color: #666; text-align: center;">Select variables to see CSS preview</p>');
                return;
            }
            
            var css = selector + ' {\n';
            variables.forEach(function(varName) {
                css += '    ' + varName + ': var(' + varName + ');\n';
            });
            css += '}';
            
            $('#selector-preview').html('<pre style="margin: 0; white-space: pre-wrap;">' + css + '</pre>');
        }
        
        // Save new selector
        $('#new-selector-form').on('submit', function(e) {
            e.preventDefault();
            
            var variables = [];
            $('input[name="variables[]"]:checked').each(function() {
                variables.push($(this).val());
            });
            
            var selectorData = {
                name: $('#selector-name').val(),
                selector: $('#selector-css').val(),
                description: $('#selector-description').val(),
                variables: variables,
                active: true
            };
            
            if (!selectorData.name || !selectorData.selector) {
                alert('Please provide a name and CSS selector');
                return;
            }
            
            $.post(ajaxurl, {
                action: 'studio_save_selector',
                nonce: studioNonce,
                selector: selectorData
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error saving selector');
                }
            });
        });
        
        // Clear form
        $('#clear-form').on('click', function() {
            $('#new-selector-form')[0].reset();
            updatePreview();
        });
        
        // Delete selector
        $('.delete-selector').on('click', function() {
            if (!confirm('Delete this selector?')) return;
            
            var id = $(this).data('id');
            
            $.post(ajaxurl, {
                action: 'studio_delete_selector',
                nonce: studioNonce,
                id: id
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        });
        
        // Generate all CSS
        $('#generate-all-css').on('click', function() {
            var $button = $(this);
            $button.text('Generating...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'studio_generate_selector_css',
                nonce: studioNonce
            }, function(response) {
                if (response.success) {
                    $button.text('CSS Generated!');
                    setTimeout(function() {
                        $button.text('Generate All CSS').prop('disabled', false);
                    }, 2000);
                }
            });
        });
    });
    </script>
    <?php
}