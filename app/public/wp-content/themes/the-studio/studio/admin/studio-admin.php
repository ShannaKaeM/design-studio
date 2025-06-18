<?php
/**
 * Studio Admin Pages
 * 
 * @package TheStudio
 */

use Studio\Core\StudioLoader;

/**
 * Variables admin page
 */
function studio_admin_variables_page() {
    $loader = StudioLoader::get_instance();
    $scanner = $loader->get_scanner();
    
    // Check if we need to scan first
    $variables = $scanner->get_variables_by_category();
    if (empty($variables)) {
        // Try scanning
        $loader->scan_variables();
        $variables = $scanner->get_variables_by_category();
    }
    
    // Group variables by category
    $categories = [];
    foreach ($variables as $variable) {
        $category = $variable['category'];
        if (!isset($categories[$category])) {
            $categories[$category] = [];
        }
        $categories[$category][] = $variable;
    }
    
    ?>
    <div class="wrap studio-admin-wrap">
        <h1><?php _e('Studio Variables', 'the-studio'); ?></h1>
        
        <div class="studio-admin-header">
            <p><?php _e('CSS variables with @control annotations automatically generate controls below.', 'the-studio'); ?></p>
            <div class="studio-header-buttons">
                <button type="button" class="button button-primary" id="studio-scan-variables">
                    <?php _e('Scan Variables', 'the-studio'); ?>
                </button>
                <button type="button" class="button button-secondary" id="studio-save-all-variables">
                    <?php _e('Save All Changes', 'the-studio'); ?>
                </button>
                <button type="button" class="button button-secondary" id="studio-generate-utilities">
                    <?php _e('Generate Utilities', 'the-studio'); ?>
                </button>
            </div>
        </div>
        
        <?php if (isset($_GET['debug'])) : ?>
            <div class="notice notice-info">
                <h3>Debug Info:</h3>
                <p>Total variables: <?php echo count($variables); ?></p>
                <p>First variable:</p>
                <pre><?php print_r(isset($variables[0]) ? $variables[0] : 'No variables found'); ?></pre>
            </div>
        <?php endif; ?>
        
        <div id="studio-scan-message" class="notice" style="display: none;"></div>
        
        <?php if (empty($variables)) : ?>
            <div class="studio-no-variables">
                <p><?php _e('No variables found. Click "Scan Variables" to detect CSS variables from your studio-vars.css file.', 'the-studio'); ?></p>
            </div>
        <?php else : ?>
            <div class="studio-variables-container">
                <div class="nav-tab-wrapper">
                    <?php
                    $first = true;
                    foreach ($categories as $category => $vars) {
                        $active = $first ? 'nav-tab-active' : '';
                        $first = false;
                        ?>
                        <a href="#studio-category-<?php echo esc_attr($category); ?>" 
                           class="nav-tab <?php echo $active; ?>"
                           data-category="<?php echo esc_attr($category); ?>">
                            <?php echo esc_html(ucfirst($category)); ?>
                            <span class="count">(<?php echo count($vars); ?>)</span>
                        </a>
                        <?php
                    }
                    ?>
                </div>
                
                <div class="studio-variables-content">
                    <?php
                    $first = true;
                    foreach ($categories as $category => $category_vars) :
                        $style = $first ? '' : 'display: none;';
                        $first = false;
                        ?>
                        <div id="studio-category-<?php echo esc_attr($category); ?>" 
                             class="studio-category-panel" 
                             style="<?php echo $style; ?>">
                            
                            <div class="studio-variables-grid">
                                <?php foreach ($category_vars as $variable) : ?>
                                    <div class="studio-variable-control" 
                                         data-variable="<?php echo esc_attr($variable['name']); ?>">
                                        
                                        <label class="studio-variable-label">
                                            <?php echo esc_html($variable['label']); ?>
                                            <code><?php echo esc_html($variable['name']); ?></code>
                                        </label>
                                        
                                        <div class="studio-variable-input">
                                            <?php studio_render_variable_control($variable); ?>
                                        </div>
                                        
                                        <div class="studio-variable-actions">
                                            <button type="button" class="button button-small studio-save-variable">
                                                <?php _e('Save', 'the-studio'); ?>
                                            </button>
                                            <span class="studio-variable-status"></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('Inline script loaded');
        console.log('Color inputs found:', $('.studio-control-color').length);
        
        // Simple tab switching
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            console.log('Tab clicked:', target);
            
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            $('.studio-category-panel').hide();
            $(target).show();
        });
        
        // Initialize color pickers with a delay
        setTimeout(function() {
            console.log('Checking wpColorPicker after delay...');
            console.log('jQuery.fn.wpColorPicker:', typeof jQuery.fn.wpColorPicker);
            console.log('wp object:', typeof wp);
            
            if (jQuery.fn.wpColorPicker) {
                console.log('wpColorPicker is NOW available');
                jQuery('.studio-control-color').each(function() {
                    console.log('Initializing picker for:', jQuery(this).attr('data-variable'));
                    jQuery(this).wpColorPicker();
                });
            } else {
                console.log('wpColorPicker still NOT available');
                // Fallback: use HTML5 color input
                jQuery('.studio-control-color').each(function() {
                    var $input = jQuery(this);
                    var value = $input.val();
                    
                    // Check if value is a CSS variable reference
                    if (value.startsWith('var(')) {
                        // Try to get the actual color value
                        var varName = value.match(/var\((--[^)]+)\)/);
                        if (varName) {
                            // For now, just use a default color
                            value = '#cccccc';
                        }
                    }
                    
                    // Ensure we have a valid hex color
                    if (!value.startsWith('#')) {
                        value = '#' + value.replace('#', '');
                    }
                    
                    $input.val(value);
                    $input.attr('type', 'color');
                });
            }
        }, 500);
        
        // Save functionality - use document.on for dynamic elements
        $(document).on('click', '.studio-save-variable', function() {
            console.log('Save button clicked');
            var $button = $(this);
            var $control = $button.closest('.studio-variable-control');
            var variableName = $control.data('variable');
            var value;
            
            // Handle different input types
            if ($control.find('.studio-control-range').length) {
                // Range input with unit
                var $number = $control.find('.studio-control-range-number');
                var $unit = $control.find('.studio-control-range-unit');
                value = $number.val() + $unit.text().trim();
            } else {
                // Other inputs
                var $input = $control.find('input[type="color"], input[type="text"], input[type="number"], select').first();
                value = $input.val();
            }
            
            console.log('Saving variable:', variableName, 'with value:', value);
            
            $button.text('Saving...').prop('disabled', true);
            
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'studio_save_variable',
                nonce: '<?php echo wp_create_nonce('studio_admin_nonce'); ?>',
                variable: variableName,
                value: value
            }, function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    $control.find('.studio-variable-status').text('Saved!').addClass('success');
                    setTimeout(function() {
                        $control.find('.studio-variable-status').text('').removeClass('success');
                    }, 2000);
                } else {
                    $control.find('.studio-variable-status').text('Error!').addClass('error');
                }
            }).always(function() {
                $button.text('Save').prop('disabled', false);
            });
        });
        
        // Save All functionality
        $('#studio-save-all-variables').on('click', function() {
            console.log('Save All clicked');
            var $button = $(this);
            var variables = [];
            
            // Collect all changed variables
            $('.studio-variable-control').each(function() {
                var $control = $(this);
                var variableName = $control.data('variable');
                var value;
                
                // Handle different input types
                if ($control.find('.studio-control-range').length) {
                    // Range input with unit
                    var $number = $control.find('.studio-control-range-number');
                    var $unit = $control.find('.studio-control-range-unit');
                    value = $number.val() + $unit.text().trim();
                } else {
                    // Other inputs
                    var $input = $control.find('input[type="color"], input[type="text"], input[type="number"], select').first();
                    value = $input.val();
                }
                
                if (value) {
                    variables.push({
                        name: variableName,
                        value: value
                    });
                }
            });
            
            console.log('Saving ' + variables.length + ' variables');
            
            if (variables.length === 0) {
                alert('No variables to save');
                return;
            }
            
            $button.text('Saving All...').prop('disabled', true);
            
            // Save all variables
            var saved = 0;
            var errors = 0;
            
            variables.forEach(function(variable) {
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'studio_save_variable',
                    nonce: '<?php echo wp_create_nonce('studio_admin_nonce'); ?>',
                    variable: variable.name,
                    value: variable.value
                }, function(response) {
                    if (response.success) {
                        saved++;
                    } else {
                        errors++;
                    }
                    
                    // Check if all are done
                    if (saved + errors === variables.length) {
                        var message = 'Saved ' + saved + ' variables';
                        if (errors > 0) {
                            message += ' (' + errors + ' errors)';
                        }
                        alert(message);
                        $button.text('Save All Changes').prop('disabled', false);
                        
                        // Reload to show updated values
                        if (saved > 0) {
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    }
                });
            });
        });
        
        // Generate Utilities
        $('#studio-generate-utilities').on('click', function() {
            console.log('Generate Utilities clicked');
            var $button = $(this);
            
            $button.text('Generating...').prop('disabled', true);
            
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'studio_generate_utilities',
                nonce: '<?php echo wp_create_nonce('studio_admin_nonce'); ?>'
            }, function(response) {
                console.log('Generate response:', response);
                if (response.success) {
                    alert('Utilities generated successfully!');
                } else {
                    alert('Error generating utilities: ' + response.data);
                }
            }).always(function() {
                $button.text('Generate Utilities').prop('disabled', false);
            });
        });
    });
    </script>
    <?php
}

/**
 * Render variable control based on type
 */
function studio_render_variable_control($variable) {
    $control = $variable['control'];
    $value = $variable['value'];
    $name = $variable['name'];
    
    if (!$control) {
        // Default text input
        ?>
        <input type="text" 
               class="studio-control-text" 
               value="<?php echo esc_attr($value); ?>" 
               data-variable="<?php echo esc_attr($name); ?>" />
        <?php
        return;
    }
    
    switch ($control['type']) {
        case 'color':
            ?>
            <input type="text" 
                   class="studio-control-color" 
                   value="<?php echo esc_attr($value); ?>" 
                   data-variable="<?php echo esc_attr($name); ?>" />
            <?php
            break;
            
        case 'range':
            $params = $control['params'];
            ?>
            <div class="studio-control-range-wrapper">
                <input type="range" 
                       class="studio-control-range" 
                       min="<?php echo esc_attr($params['min']); ?>" 
                       max="<?php echo esc_attr($params['max']); ?>" 
                       step="<?php echo esc_attr($params['step']); ?>" 
                       value="<?php echo esc_attr(floatval($value)); ?>" 
                       data-variable="<?php echo esc_attr($name); ?>" />
                <input type="number" 
                       class="studio-control-range-number" 
                       min="<?php echo esc_attr($params['min']); ?>" 
                       max="<?php echo esc_attr($params['max']); ?>" 
                       step="<?php echo esc_attr($params['step']); ?>" 
                       value="<?php echo esc_attr(floatval($value)); ?>" />
                <span class="studio-control-range-unit">
                    <?php 
                    // Extract unit from value
                    preg_match('/[\d.]+(.*)/', $value, $matches);
                    echo isset($matches[1]) ? esc_html($matches[1]) : '';
                    ?>
                </span>
            </div>
            <?php
            break;
            
        case 'select':
            $params = $control['params'];
            ?>
            <select class="studio-control-select" data-variable="<?php echo esc_attr($name); ?>">
                <?php foreach ($params['options'] as $option) : ?>
                    <option value="<?php echo esc_attr($option); ?>" 
                            <?php selected($value, $option); ?>>
                        <?php echo esc_html($option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
            break;
            
        case 'font':
            ?>
            <input type="text" 
                   class="studio-control-font" 
                   value="<?php echo esc_attr($value); ?>" 
                   data-variable="<?php echo esc_attr($name); ?>" 
                   placeholder="<?php esc_attr_e('Enter font family', 'the-studio'); ?>" />
            <?php
            break;
            
        case 'shadow':
            ?>
            <input type="text" 
                   class="studio-control-shadow large-text" 
                   value="<?php echo esc_attr($value); ?>" 
                   data-variable="<?php echo esc_attr($name); ?>" 
                   placeholder="<?php esc_attr_e('e.g., 0 2px 4px rgba(0,0,0,0.1)', 'the-studio'); ?>" />
            <?php
            break;
            
        default:
            ?>
            <input type="text" 
                   class="studio-control-text" 
                   value="<?php echo esc_attr($value); ?>" 
                   data-variable="<?php echo esc_attr($name); ?>" />
            <?php
    }
}