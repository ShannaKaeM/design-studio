<?php
/**
 * S Design System Admin Page
 * Clean, organized admin interface for the S system
 */

// Include dependencies
require_once 'scan-variables-s.php';
require_once 'selector-builder-enhanced.php';

// Add admin menu
add_action('admin_menu', function() {
    add_theme_page(
        'S Design System',
        'S System',
        'manage_options',
        's-system',
        's_admin_page'
    );
});

// Enqueue admin assets
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'appearance_page_s-system') {
        return;
    }
    
    // WordPress color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Custom admin styles
    wp_add_inline_style('wp-admin', '
        .s-admin-wrap {
            max-width: 1400px;
            margin: 20px 0;
        }
        .s-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .s-tab {
            padding: 10px 20px;
            background: #f0f0f0;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .s-tab.active {
            background: #fff;
            border-bottom: 2px solid #2271b1;
            margin-bottom: -2px;
        }
        .s-tab-content {
            display: none;
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .s-tab-content.active {
            display: block;
        }
        .s-variable-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .s-category {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
        }
        .s-category h3 {
            margin: 0 0 20px 0;
            color: #1e1e1e;
            font-size: 16px;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }
        .s-control {
            margin-bottom: 20px;
        }
        .s-control label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #1e1e1e;
        }
        .s-control input[type="text"],
        .s-control input[type="number"],
        .s-control select {
            width: 100%;
            max-width: 250px;
        }
        .s-control input[type="range"] {
            width: 100%;
            max-width: 200px;
        }
        .s-range-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .s-range-value {
            min-width: 60px;
            font-family: monospace;
            color: #2271b1;
        }
        .s-actions {
            position: sticky;
            top: 32px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 10;
            margin-bottom: 30px;
        }
        .s-actions .button {
            margin-right: 10px;
        }
        .s-color-preview {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            vertical-align: middle;
            margin-right: 10px;
            border: 1px solid #ddd;
        }
    ');
    
    // Custom admin script
    wp_add_inline_script('wp-color-picker', '
        jQuery(document).ready(function($) {
            // Initialize color pickers
            $(".s-color-picker").wpColorPicker({
                change: function(event, ui) {
                    updatePreview();
                }
            });
            
            // Tab switching
            $(".s-tab").on("click", function() {
                const target = $(this).data("tab");
                $(".s-tab").removeClass("active");
                $(".s-tab-content").removeClass("active");
                $(this).addClass("active");
                $("#" + target).addClass("active");
            });
            
            // Range input updates
            $("input[type=range]").on("input", function() {
                $(this).siblings(".s-range-value").text($(this).val() + ($(this).data("unit") || ""));
                updatePreview();
            });
            
            // Live preview
            function updatePreview() {
                const preview = $(".s-preview");
                if (preview.length) {
                    // Update preview styles based on current values
                }
            }
        });
    ');
});

// Handle save
add_action('admin_post_s_save_variables', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['s_nonce'], 's_save')) {
        wp_die('Unauthorized');
    }
    
    $variables = $_POST['s_vars'] ?? [];
    
    // Save to database
    update_option('s_custom_variables', $variables);
    
    // Generate custom CSS
    s_generate_custom_css($variables);
    
    wp_redirect(add_query_arg('updated', '1', wp_get_referer()));
    exit;
});

// Handle selector saving
add_action('admin_post_s_save_selector', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['s_selector_nonce'], 's_selector')) {
        wp_die('Unauthorized');
    }
    
    $selector_name = sanitize_text_field($_POST['selector_name']);
    $selector_css = sanitize_text_field($_POST['selector_css']);
    $properties = $_POST['properties'] ?? [];
    
    // Build variables array from properties
    $variables = [];
    foreach ($properties as $prop) {
        if (!empty($prop['name']) && !empty($prop['value'])) {
            $variables[sanitize_text_field($prop['name'])] = sanitize_text_field($prop['value']);
        }
    }
    
    // Save selector
    $builder = new StudioSelectorBuilder();
    $builder->add_selector($selector_name, $selector_css, $variables);
    
    // Generate CSS file
    $css = $builder->generate_css();
    $selector_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    file_put_contents($selector_file, $css);
    
    wp_redirect(add_query_arg(['tab' => 'selectors', 'selector_saved' => '1'], wp_get_referer()));
    exit;
});

// Handle selector deletion
add_action('admin_post_s_delete_selector', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['nonce'], 's_delete_selector')) {
        wp_die('Unauthorized');
    }
    
    $selector_name = sanitize_text_field($_GET['selector_name']);
    
    // Delete selector
    $builder = new StudioSelectorBuilder();
    $builder->remove_selector($selector_name);
    
    // Regenerate CSS file
    $css = $builder->generate_css();
    $selector_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    file_put_contents($selector_file, $css);
    
    wp_redirect(add_query_arg(['tab' => 'selectors', 'selector_deleted' => '1'], wp_get_referer()));
    exit;
});

// Handle selector update
add_action('admin_post_s_update_selector', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['s_selector_nonce'], 's_selector')) {
        wp_die('Unauthorized');
    }
    
    $original_name = sanitize_text_field($_POST['original_name']);
    $selector_name = sanitize_text_field($_POST['selector_name']);
    $selector_css = sanitize_text_field($_POST['selector_css']);
    $properties = $_POST['properties'] ?? [];
    
    // Build variables array from properties
    $variables = [];
    foreach ($properties as $prop) {
        if (!empty($prop['name']) && !empty($prop['value'])) {
            $variables[sanitize_text_field($prop['name'])] = sanitize_text_field($prop['value']);
        }
    }
    
    // Update selector
    $builder = new StudioSelectorBuilder();
    
    // If name changed, remove old and add new
    if ($original_name !== $selector_name) {
        $builder->remove_selector($original_name);
        $builder->add_selector($selector_name, $selector_css, $variables);
    } else {
        // Just update existing
        $builder->update_selector($selector_name, [
            'selector' => $selector_css,
            'variables' => $variables
        ]);
    }
    
    // Generate CSS file
    $css = $builder->generate_css();
    $selector_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    file_put_contents($selector_file, $css);
    
    wp_redirect(add_query_arg(['tab' => 'selectors', 'selector_updated' => '1'], wp_get_referer()));
    exit;
});

// Handle selector toggle
add_action('admin_post_s_toggle_selector', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['nonce'], 's_toggle_selector')) {
        wp_die('Unauthorized');
    }
    
    $selector_name = sanitize_text_field($_GET['selector_name']);
    
    // Toggle selector
    $builder = new StudioSelectorBuilder();
    $builder->toggle_selector($selector_name);
    
    // Regenerate CSS file
    $css = $builder->generate_css();
    $selector_file = get_stylesheet_directory() . '/assets/css/s-selectors.css';
    file_put_contents($selector_file, $css);
    
    wp_redirect(add_query_arg(['tab' => 'selectors', 'selector_toggled' => '1'], wp_get_referer()));
    exit;
});

// Handle utility generation
add_action('admin_post_s_generate_utilities', function() {
    if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['nonce'], 's_generate_utilities')) {
        wp_die('Unauthorized');
    }
    
    // Include the utility generator
    require_once get_stylesheet_directory() . '/studio-system/generate-utilities.php';
    
    // Generate utilities
    if (function_exists('Studio\studio_generate_utilities')) {
        $result = \Studio\studio_generate_utilities();
    } else if (function_exists('studio_generate_utilities')) {
        $result = studio_generate_utilities();
    } else {
        // Try creating a new instance directly
        $generator = new \Studio\UtilityGenerator();
        $result = $generator->generate();
    }
    
    if ($result) {
        wp_redirect(add_query_arg('utilities_generated', '1', wp_get_referer()));
    } else {
        wp_redirect(add_query_arg('utilities_error', '1', wp_get_referer()));
    }
    exit;
});

// Generate custom CSS file
function s_generate_custom_css($variables) {
    $css = ":root {\n";
    
    foreach ($variables as $var_name => $value) {
        $css .= "    {$var_name}: {$value};\n";
    }
    
    $css .= "}\n";
    
    $custom_file = get_stylesheet_directory() . '/assets/css/s-custom.css';
    file_put_contents($custom_file, $css);
}

// Main admin page
function s_admin_page() {
    $saved_vars = get_option('s_custom_variables', []);
    ?>
    <div class="wrap s-admin-wrap">
        <h1>S Design System</h1>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Design system variables updated successfully!</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['utilities_generated'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Utility classes generated successfully!</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['utilities_error'])): ?>
            <div class="notice notice-error is-dismissible">
                <p>Error generating utility classes. Please check file permissions.</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['selector_saved'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Selector rule saved successfully!</p>
            </div>
        <?php endif; ?>
        
        <div class="s-tabs">
            <button class="s-tab active" data-tab="variables">Variables</button>
            <button class="s-tab" data-tab="selectors">Selectors</button>
            <button class="s-tab" data-tab="utilities">Utilities</button>
            <button class="s-tab" data-tab="preview">Preview</button>
        </div>
        
        <!-- Variables Tab -->
        <div id="variables" class="s-tab-content active">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="s_save_variables">
                <?php wp_nonce_field('s_save', 's_nonce'); ?>
                
                <div class="s-actions">
                    <button type="submit" class="button button-primary">Save All Changes</button>
                    <button type="button" class="button" onclick="resetDefaults()">Reset to Defaults</button>
                    <button type="button" class="button" onclick="exportConfig()">Export Config</button>
                </div>
                
                <?php s_render_variables_form($saved_vars); ?>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">Save All Changes</button>
                </p>
            </form>
        </div>
        
        <!-- Selectors Tab -->
        <div id="selectors" class="s-tab-content">
            <h2>Selector Builder</h2>
            <p>Apply CSS variables to any element on your site without writing code.</p>
            <?php s_render_selector_builder(); ?>
        </div>
        
        <!-- Utilities Tab -->
        <div id="utilities" class="s-tab-content">
            <h2>Utility Classes</h2>
            <p>Auto-generated utility classes based on your design system variables.</p>
            
            <?php
            $utils_file = get_stylesheet_directory() . '/assets/css/s-utilities.css';
            if (file_exists($utils_file)): ?>
                <div class="notice notice-info inline">
                    <p>Utilities last generated: <?php echo date('Y-m-d H:i:s', filemtime($utils_file)); ?></p>
                </div>
                
                <h3>Available Utility Classes:</h3>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <h4>Colors</h4>
                    <p><code>.text-primary</code>, <code>.bg-primary</code>, <code>.border-primary</code> (and all color variants)</p>
                    
                    <h4>Spacing</h4>
                    <p><code>.p-sm</code>, <code>.m-lg</code>, <code>.px-md</code>, <code>.my-xl</code>, etc.</p>
                    
                    <h4>Typography</h4>
                    <p><code>.text-sm</code>, <code>.text-lg</code>, <code>.font-bold</code>, <code>.leading-tight</code></p>
                    
                    <h4>Layout</h4>
                    <p><code>.flex</code>, <code>.grid</code>, <code>.hidden</code>, <code>.w-full</code></p>
                    
                    <h4>Border Radius</h4>
                    <p><code>.rounded-sm</code>, <code>.rounded-lg</code>, <code>.rounded-xl</code></p>
                </div>
            <?php else: ?>
                <p class="description">No utilities generated yet. Click the button below to generate them.</p>
            <?php endif; ?>
            
            <p>
                <button class="button button-primary" onclick="generateUtilities()">Generate Utilities</button>
            </p>
        </div>
        
        <!-- Preview Tab -->
        <div id="preview" class="s-tab-content">
            <h2>Live Preview</h2>
            <div class="s-preview">
                <div style="padding: 40px; background: var(--s-base-lighter); border-radius: var(--s-radius-xl);">
                    <h1 style="color: var(--s-primary); font-size: var(--s-text-4xl); margin-bottom: var(--s-space-md);">
                        Preview Heading
                    </h1>
                    <p style="color: var(--s-base-dark); font-size: var(--s-text-lg); line-height: var(--s-leading-relaxed);">
                        This is a preview of your design system in action. As you change variables, this preview updates live.
                    </p>
                    <button style="background: var(--s-primary); color: var(--s-white); padding: var(--s-space-sm) var(--s-space-lg); border-radius: var(--s-radius-md); border: none; font-weight: var(--s-font-semibold);">
                        Sample Button
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function resetDefaults() {
        if (confirm('Reset all variables to their default values?')) {
            document.querySelectorAll('[data-default]').forEach(input => {
                input.value = input.dataset.default;
                if (input.type === 'range') {
                    input.nextElementSibling.textContent = input.value + (input.dataset.unit || '');
                }
            });
        }
    }
    
    function exportConfig() {
        const config = {};
        document.querySelectorAll('.s-control input, .s-control select').forEach(input => {
            config[input.name.replace('s_vars[', '').replace(']', '')] = input.value;
        });
        
        const blob = new Blob([JSON.stringify(config, null, 2)], {type: 'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 's-design-system.json';
        a.click();
    }
    
    function generateUtilities() {
        if (confirm('Generate utility classes from your S variables?')) {
            window.location.href = '<?php echo add_query_arg(['action' => 's_generate_utilities', 'nonce' => wp_create_nonce('s_generate_utilities')], admin_url('admin-post.php')); ?>';
        }
    }
    </script>
    <?php
}

// Render variables form
function s_render_variables_form($saved_vars) {
    $categories = s_get_variables_by_category();
    
    if (empty($categories)) {
        echo '<div class="notice notice-warning"><p>No variables found. Make sure your CSS files contain variables with @control annotations.</p></div>';
        return;
    }
    
    echo '<div class="s-variable-grid">';
    
    foreach ($categories as $category => $variables) {
        echo '<div class="s-category">';
        echo '<h3>' . esc_html($category) . '</h3>';
        
        foreach ($variables as $var) {
            $current_value = $saved_vars[$var['name']] ?? $var['value'];
            s_render_control($var, $current_value);
        }
        
        echo '</div>';
    }
    
    echo '</div>';
}

// Render individual control
function s_render_control($var, $current_value) {
    $input_name = "s_vars[{$var['name']}]";
    
    echo '<div class="s-control">';
    echo '<label>' . esc_html($var['label']) . '</label>';
    
    switch ($var['control']) {
        case 'color':
            echo '<div style="display: flex; align-items: center;">';
            echo '<span class="s-color-preview" style="background: ' . esc_attr($current_value) . '"></span>';
            echo '<input type="text" name="' . esc_attr($input_name) . '" value="' . esc_attr($current_value) . '" class="s-color-picker" data-default="' . esc_attr($var['value']) . '">';
            echo '</div>';
            break;
            
        case 'range':
            $params = explode(',', $var['params']);
            $min = $params[0] ?? 0;
            $max = $params[1] ?? 100;
            $step = $params[2] ?? 1;
            
            // Extract unit from value
            preg_match('/^([\d.]+)(.*)$/', $current_value, $matches);
            $numeric_value = $matches[1] ?? $current_value;
            $unit = $matches[2] ?? '';
            
            echo '<div class="s-range-wrapper">';
            echo '<input type="range" name="' . esc_attr($input_name) . '" value="' . esc_attr($numeric_value) . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" data-default="' . esc_attr($var['value']) . '" data-unit="' . esc_attr($unit) . '">';
            echo '<span class="s-range-value">' . esc_html($current_value) . '</span>';
            echo '</div>';
            break;
            
        case 'select':
            $options = explode(',', $var['params']);
            echo '<select name="' . esc_attr($input_name) . '" data-default="' . esc_attr($var['value']) . '">';
            foreach ($options as $option) {
                $option = trim($option);
                $selected = ($option == $current_value) ? 'selected' : '';
                echo '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html($option) . '</option>';
            }
            echo '</select>';
            break;
            
        default:
            echo '<input type="text" name="' . esc_attr($input_name) . '" value="' . esc_attr($current_value) . '" data-default="' . esc_attr($var['value']) . '">';
    }
    
    echo '</div>';
}

// Render selector builder interface
function s_render_selector_builder() {
    $builder = new StudioSelectorBuilder();
    $selectors = $builder->get_selectors();
    $presets = $builder->get_presets();
    
    // Display success messages
    if (isset($_GET['selector_saved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Selector saved successfully!</p></div>';
    }
    if (isset($_GET['selector_updated'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Selector updated successfully!</p></div>';
    }
    if (isset($_GET['selector_deleted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Selector deleted successfully!</p></div>';
    }
    if (isset($_GET['selector_toggled'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Selector toggled successfully!</p></div>';
    }
    ?>
    <div class="s-selector-builder">
        <div class="s-selector-form">
            <h3>Create New Selector Rule</h3>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="s_save_selector">
                <input type="hidden" name="original_name" value="">
                <?php wp_nonce_field('s_selector', 's_selector_nonce'); ?>
                
                <div class="s-control">
                    <label>Rule Name</label>
                    <input type="text" name="selector_name" placeholder="e.g., hero-title" required>
                </div>
                
                <div class="s-control">
                    <label>CSS Selector</label>
                    <input type="text" name="selector_css" placeholder="e.g., .hero h1" required>
                    <p class="description">Enter any valid CSS selector</p>
                </div>
                
                <div class="s-control">
                    <label>Or Choose from Presets:</label>
                    <select name="selector_preset" onchange="updateSelectorFromPreset(this)">
                        <option value="">-- Choose a preset --</option>
                        <?php foreach ($presets as $category => $items): ?>
                            <optgroup label="<?php echo esc_attr($category); ?>">
                                <?php foreach ($items as $key => $selector): ?>
                                    <option value="<?php echo esc_attr($selector); ?>" data-name="<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($key); ?> (<?php echo esc_html($selector); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="s-control">
                    <label>CSS Properties</label>
                    <div id="selector-properties">
                        <div class="property-row">
                            <input type="text" name="properties[0][name]" placeholder="Property (e.g., color)">
                            <input type="text" name="properties[0][value]" placeholder="Value (e.g., --s-primary)">
                            <button type="button" onclick="removeProperty(this)">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addProperty()" class="button">Add Property</button>
                </div>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">Create Selector Rule</button>
                    <button type="button" class="button" onclick="cancelEdit()" style="display: none;" id="cancel-edit">Cancel Edit</button>
                </p>
            </form>
        </div>
        
        <?php if (!empty($selectors)): ?>
        <div class="s-selector-list">
            <h3>Existing Selector Rules</h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Selector</th>
                        <th>Properties</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($selectors as $name => $data): ?>
                    <tr>
                        <td><?php echo esc_html($name); ?></td>
                        <td><code><?php echo esc_html($data['selector']); ?></code></td>
                        <td>
                            <?php 
                            if (!empty($data['variables'])) {
                                foreach ($data['variables'] as $prop => $val) {
                                    echo '<code>' . esc_html($prop) . ': ' . esc_html($val) . '</code><br>';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo $data['enabled'] ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Disabled</span>'; ?>
                        </td>
                        <td>
                            <button class="button button-small" onclick="toggleSelector('<?php echo esc_attr($name); ?>')">
                                <?php echo $data['enabled'] ? 'Disable' : 'Enable'; ?>
                            </button>
                            <button class="button button-primary button-small" onclick="editSelector('<?php echo esc_attr($name); ?>')">Edit</button>
                            <button class="button button-small" onclick="deleteSelector('<?php echo esc_attr($name); ?>')">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>No selector rules created yet. Create your first rule above!</p>
        <?php endif; ?>
    </div>
    
    <script>
    let propertyCount = 1;
    
    function addProperty() {
        const container = document.getElementById('selector-properties');
        const row = document.createElement('div');
        row.className = 'property-row';
        row.innerHTML = `
            <input type="text" name="properties[${propertyCount}][name]" placeholder="Property (e.g., font-size)">
            <input type="text" name="properties[${propertyCount}][value]" placeholder="Value (e.g., --s-text-xl)">
            <button type="button" onclick="removeProperty(this)">Remove</button>
        `;
        container.appendChild(row);
        propertyCount++;
    }
    
    function removeProperty(button) {
        button.parentElement.remove();
    }
    
    function updateSelectorFromPreset(select) {
        const selector = select.value;
        const name = select.options[select.selectedIndex].getAttribute('data-name');
        if (selector) {
            document.querySelector('input[name="selector_css"]').value = selector;
            document.querySelector('input[name="selector_name"]').value = name || '';
        }
    }
    
    function toggleSelector(name) {
        // Create toggle URL with proper nonce
        const toggleUrl = '<?php echo admin_url('admin-post.php'); ?>?' + 
            'action=s_toggle_selector&' +
            'selector_name=' + encodeURIComponent(name) + '&' +
            'nonce=<?php echo wp_create_nonce('s_toggle_selector'); ?>';
        
        window.location.href = toggleUrl;
    }
    
    function editSelector(name) {
        // Find the selector data
        const selectors = <?php echo json_encode($selectors); ?>;
        const selectorData = selectors[name];
        
        if (selectorData) {
            // Populate the form with existing data
            document.querySelector('input[name="selector_name"]').value = name;
            document.querySelector('input[name="selector_css"]').value = selectorData.selector;
            
            // Clear existing properties
            document.getElementById('selector-properties').innerHTML = '';
            propertyCount = 0;
            
            // Add existing properties
            if (selectorData.variables) {
                Object.entries(selectorData.variables).forEach(([prop, value]) => {
                    addPropertyWithValues(prop, value);
                });
            }
            
            // Change form action to update instead of create
            document.querySelector('input[name="action"]').value = 's_update_selector';
            document.querySelector('input[name="original_name"]').value = name;
            
            // Change button text
            document.querySelector('button[type="submit"]').textContent = 'Update Selector';
            
            // Show cancel button
            document.getElementById('cancel-edit').style.display = 'inline-block';
            
            // Update form title
            document.querySelector('.s-selector-form h3').textContent = 'Edit Selector Rule';
            
            // Scroll to form
            document.querySelector('.s-selector-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    function cancelEdit() {
        // Reset form
        document.querySelector('input[name="selector_name"]').value = '';
        document.querySelector('input[name="selector_css"]').value = '';
        document.querySelector('input[name="action"]').value = 's_save_selector';
        document.querySelector('input[name="original_name"]').value = '';
        
        // Clear properties
        document.getElementById('selector-properties').innerHTML = `
            <div class="property-row">
                <input type="text" name="properties[0][name]" placeholder="Property (e.g., color)">
                <input type="text" name="properties[0][value]" placeholder="Value (e.g., --s-primary)">
                <button type="button" onclick="removeProperty(this)">Remove</button>
            </div>
        `;
        propertyCount = 1;
        
        // Reset button text
        document.querySelector('button[type="submit"]').textContent = 'Create Selector Rule';
        
        // Hide cancel button
        document.getElementById('cancel-edit').style.display = 'none';
        
        // Reset form title
        document.querySelector('.s-selector-form h3').textContent = 'Create New Selector Rule';
    }
    
    function addPropertyWithValues(prop, value) {
        const container = document.getElementById('selector-properties');
        const row = document.createElement('div');
        row.className = 'property-row';
        row.innerHTML = `
            <input type="text" name="properties[${propertyCount}][name]" placeholder="Property (e.g., font-size)" value="${prop}">
            <input type="text" name="properties[${propertyCount}][value]" placeholder="Value (e.g., --s-text-xl)" value="${value}">
            <button type="button" onclick="removeProperty(this)">Remove</button>
        `;
        container.appendChild(row);
        propertyCount++;
    }
    
    function deleteSelector(name) {
        if (confirm('Are you sure you want to delete the selector "' + name + '"? This cannot be undone.')) {
            // Create delete URL with proper nonce
            const deleteUrl = '<?php echo admin_url('admin-post.php'); ?>?' + 
                'action=s_delete_selector&' +
                'selector_name=' + encodeURIComponent(name) + '&' +
                'nonce=<?php echo wp_create_nonce('s_delete_selector'); ?>';
            
            window.location.href = deleteUrl;
        }
    }
    </script>
    
    <style>
    .s-selector-builder {
        max-width: 1200px;
    }
    .s-selector-form {
        background: #fff;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .property-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }
    .property-row input {
        flex: 1;
    }
    .property-row button {
        flex-shrink: 0;
    }
    </style>
    <?php
}