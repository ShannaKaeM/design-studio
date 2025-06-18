<?php
/**
 * Studio Selector Builder UI
 * Admin interface for the selector builder
 */

require_once 'selector-builder.php';

/**
 * Add selector builder to Studio admin page
 */
add_action('studio_admin_tabs', function() {
    ?>
    <a href="#selector-builder" class="nav-tab" data-tab="selector-builder">Selector Builder</a>
    <?php
});

add_action('studio_admin_tab_content', function() {
    $builder = studio_selector_builder();
    $selectors = $builder->get_selectors();
    $variables = $builder->get_available_variables();
    $presets = $builder->get_selector_presets();
    ?>
    
    <div id="selector-builder" class="tab-content" style="display:none;">
        <h2>Selector Builder</h2>
        <p>Create custom CSS rules by targeting any element and applying Studio variables.</p>
        
        <div class="selector-builder-container">
            <div class="selector-builder-main">
                <button class="button button-primary" onclick="studioAddNewSelector()">
                    Add New Selector Rule
                </button>
                
                <div class="selector-rules">
                    <?php foreach ($selectors as $id => $rule): ?>
                        <div class="selector-rule" data-id="<?php echo esc_attr($id); ?>">
                            <div class="rule-header">
                                <h3><?php echo esc_html($rule['name']); ?></h3>
                                <div class="rule-actions">
                                    <button class="button-link" onclick="studioEditSelector('<?php echo esc_js($id); ?>')">Edit</button>
                                    <button class="button-link" onclick="studioToggleSelector('<?php echo esc_js($id); ?>')"><?php echo $rule['active'] ? 'Disable' : 'Enable'; ?></button>
                                    <button class="button-link" onclick="studioDeleteSelector('<?php echo esc_js($id); ?>')">Delete</button>
                                </div>
                            </div>
                            <div class="rule-info">
                                <code><?php echo esc_html($rule['selector']); ?></code>
                                <span class="rule-scope"><?php echo esc_html($rule['scope']); ?></span>
                            </div>
                            <div class="rule-variables">
                                <?php foreach ($rule['variables'] as $prop => $value): ?>
                                    <span class="variable-tag"><?php echo esc_html($prop); ?>: <?php echo esc_html($value); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="selector-builder-sidebar">
                <h3>Selector Presets</h3>
                <div class="preset-groups">
                    <?php foreach ($presets as $group => $group_presets): ?>
                        <details>
                            <summary><?php echo esc_html(ucfirst($group)); ?></summary>
                            <ul class="preset-list">
                                <?php foreach ($group_presets as $name => $selector): ?>
                                    <li>
                                        <a href="#" onclick="studioUsePreset('<?php echo esc_js($selector); ?>', '<?php echo esc_js($name); ?>'); return false;">
                                            <?php echo esc_html(str_replace('-', ' ', $name)); ?>
                                        </a>
                                        <code><?php echo esc_html($selector); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endforeach; ?>
                </div>
                
                <h3>Available Variables</h3>
                <div class="variable-list">
                    <?php 
                    $categorized_vars = [];
                    foreach ($variables as $var) {
                        $categorized_vars[$var['category']][] = $var;
                    }
                    ?>
                    <?php foreach ($categorized_vars as $category => $vars): ?>
                        <details>
                            <summary><?php echo esc_html($category); ?></summary>
                            <ul>
                                <?php foreach ($vars as $var): ?>
                                    <li>
                                        <a href="#" onclick="studioAddVariable('<?php echo esc_js($var['name']); ?>'); return false;">
                                            <?php echo esc_html($var['label']); ?>
                                        </a>
                                        <code><?php echo esc_html($var['name']); ?></code>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Selector Editor Modal -->
    <div id="selector-editor-modal" class="studio-modal" style="display:none;">
        <div class="modal-content">
            <h2>Selector Rule Editor</h2>
            <form id="selector-editor-form">
                <input type="hidden" name="selector_id" value="">
                
                <div class="form-field">
                    <label>Rule Name</label>
                    <input type="text" name="name" placeholder="e.g., Hero Headings">
                </div>
                
                <div class="form-field">
                    <label>CSS Selector</label>
                    <input type="text" name="selector" placeholder="e.g., .hero h1, .hero h2">
                    <p class="description">Enter any valid CSS selector</p>
                </div>
                
                <div class="form-field">
                    <label>Scope</label>
                    <select name="scope">
                        <option value="global">Global</option>
                        <option value="header">Header Only</option>
                        <option value="content">Content Only</option>
                        <option value="footer">Footer Only</option>
                        <option value="sidebar">Sidebar Only</option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label>Variables</label>
                    <div id="variable-assignments">
                        <!-- Dynamic variable assignments will be added here -->
                    </div>
                    <button type="button" class="button" onclick="studioAddVariableAssignment()">
                        Add Variable
                    </button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">Save Rule</button>
                    <button type="button" class="button" onclick="studioCloseModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <style>
    .selector-builder-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        margin-top: 20px;
    }
    
    .selector-rule {
        background: #fff;
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 4px;
    }
    
    .rule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .rule-header h3 {
        margin: 0;
    }
    
    .rule-info {
        margin-bottom: 10px;
    }
    
    .rule-info code {
        background: #f0f0f0;
        padding: 2px 6px;
        border-radius: 3px;
    }
    
    .rule-scope {
        margin-left: 10px;
        padding: 2px 8px;
        background: #e0e0e0;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .variable-tag {
        display: inline-block;
        background: #0073aa;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        margin: 2px;
        font-size: 12px;
    }
    
    .selector-builder-sidebar {
        background: #f9f9f9;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 600px;
        overflow-y: auto;
    }
    
    .preset-list, .variable-list ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .preset-list li, .variable-list li {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    
    .preset-list code, .variable-list code {
        display: block;
        font-size: 11px;
        color: #666;
    }
    
    .studio-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 4px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .form-field {
        margin-bottom: 20px;
    }
    
    .form-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .form-field input[type="text"],
    .form-field select {
        width: 100%;
        padding: 8px;
    }
    
    .variable-assignment {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    </style>
    
    <script>
    let currentVariables = {};
    
    function studioAddNewSelector() {
        document.getElementById('selector-editor-form').reset();
        document.querySelector('input[name="selector_id"]').value = '';
        document.getElementById('variable-assignments').innerHTML = '';
        studioAddVariableAssignment();
        document.getElementById('selector-editor-modal').style.display = 'flex';
    }
    
    function studioEditSelector(id) {
        // Load selector data via AJAX
        fetch(ajaxurl + '?action=studio_get_selector&id=' + id)
            .then(response => response.json())
            .then(data => {
                document.querySelector('input[name="selector_id"]').value = id;
                document.querySelector('input[name="name"]').value = data.name;
                document.querySelector('input[name="selector"]').value = data.selector;
                document.querySelector('select[name="scope"]').value = data.scope;
                
                // Load variables
                document.getElementById('variable-assignments').innerHTML = '';
                for (const [prop, value] of Object.entries(data.variables)) {
                    studioAddVariableAssignment(prop, value);
                }
                
                document.getElementById('selector-editor-modal').style.display = 'flex';
            });
    }
    
    function studioAddVariableAssignment(property = '', value = '') {
        const container = document.getElementById('variable-assignments');
        const div = document.createElement('div');
        div.className = 'variable-assignment';
        
        div.innerHTML = `
            <select name="property[]">
                <option value="">Select Property</option>
                <option value="color" ${property === 'color' ? 'selected' : ''}>Text Color</option>
                <option value="background-color" ${property === 'background-color' ? 'selected' : ''}>Background</option>
                <option value="font-size" ${property === 'font-size' ? 'selected' : ''}>Font Size</option>
                <option value="font-weight" ${property === 'font-weight' ? 'selected' : ''}>Font Weight</option>
                <option value="padding" ${property === 'padding' ? 'selected' : ''}>Padding</option>
                <option value="margin" ${property === 'margin' ? 'selected' : ''}>Margin</option>
                <option value="border-radius" ${property === 'border-radius' ? 'selected' : ''}>Border Radius</option>
                <option value="box-shadow" ${property === 'box-shadow' ? 'selected' : ''}>Shadow</option>
            </select>
            <input type="text" name="value[]" value="${value}" placeholder="Variable or value">
            <button type="button" class="button" onclick="this.parentElement.remove()">Remove</button>
        `;
        
        container.appendChild(div);
    }
    
    function studioUsePreset(selector, name) {
        document.querySelector('input[name="selector"]').value = selector;
        document.querySelector('input[name="name"]').value = name.replace(/-/g, ' ');
        studioAddNewSelector();
    }
    
    function studioAddVariable(varName) {
        // Add to the last empty value field or create new
        const valueFields = document.querySelectorAll('input[name="value[]"]');
        let added = false;
        
        valueFields.forEach(field => {
            if (!field.value && !added) {
                field.value = `var(${varName})`;
                added = true;
            }
        });
        
        if (!added) {
            studioAddVariableAssignment('', `var(${varName})`);
        }
    }
    
    function studioCloseModal() {
        document.getElementById('selector-editor-modal').style.display = 'none';
    }
    
    function studioToggleSelector(id) {
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'studio_toggle_selector',
                id: id,
                nonce: '<?php echo wp_create_nonce('studio_selector'); ?>'
            })
        }).then(() => location.reload());
    }
    
    function studioDeleteSelector(id) {
        if (confirm('Delete this selector rule?')) {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'studio_delete_selector',
                    id: id,
                    nonce: '<?php echo wp_create_nonce('studio_selector'); ?>'
                })
            }).then(() => location.reload());
        }
    }
    
    // Handle form submission
    document.getElementById('selector-editor-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const variables = {};
        
        // Collect variable assignments
        const properties = formData.getAll('property[]');
        const values = formData.getAll('value[]');
        
        properties.forEach((prop, index) => {
            if (prop && values[index]) {
                variables[prop] = values[index];
            }
        });
        
        const data = {
            action: 'studio_save_selector',
            id: formData.get('selector_id'),
            name: formData.get('name'),
            selector: formData.get('selector'),
            scope: formData.get('scope'),
            variables: variables,
            nonce: '<?php echo wp_create_nonce('studio_selector'); ?>'
        };
        
        fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        }).then(() => {
            location.reload();
        });
    });
    </script>
    <?php
});

// AJAX handlers
add_action('wp_ajax_studio_save_selector', function() {
    check_ajax_referer('studio_selector', 'nonce');
    
    $builder = studio_selector_builder();
    $id = sanitize_text_field($_POST['id'] ?? '');
    $name = sanitize_text_field($_POST['name']);
    $selector = sanitize_text_field($_POST['selector']);
    $scope = sanitize_text_field($_POST['scope']);
    $variables = $_POST['variables'] ?? [];
    
    if ($id) {
        $builder->update_selector($id, [
            'name' => $name,
            'selector' => $selector,
            'scope' => $scope,
            'variables' => $variables
        ]);
    } else {
        $builder->add_selector($selector, $variables, $name, $scope);
    }
    
    wp_send_json_success();
});

add_action('wp_ajax_studio_get_selector', function() {
    $id = sanitize_text_field($_GET['id']);
    $builder = studio_selector_builder();
    $selector = $builder->get_selectors($id);
    
    wp_send_json($selector);
});

add_action('wp_ajax_studio_toggle_selector', function() {
    check_ajax_referer('studio_selector', 'nonce');
    
    $id = sanitize_text_field($_POST['id']);
    $builder = studio_selector_builder();
    $selector = $builder->get_selectors($id);
    
    if ($selector) {
        $builder->update_selector($id, [
            'active' => !$selector['active']
        ]);
    }
    
    wp_send_json_success();
});

add_action('wp_ajax_studio_delete_selector', function() {
    check_ajax_referer('studio_selector', 'nonce');
    
    $id = sanitize_text_field($_POST['id']);
    $builder = studio_selector_builder();
    $builder->delete_selector($id);
    
    wp_send_json_success();
});