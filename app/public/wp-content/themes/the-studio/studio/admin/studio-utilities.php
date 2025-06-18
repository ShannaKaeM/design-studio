<?php
/**
 * Studio Utilities Admin Page
 * 
 * @package TheStudio
 */

use Studio\Core\StudioLoader;

/**
 * Utilities admin page
 */
function studio_admin_utilities_page() {
    $loader = StudioLoader::get_instance();
    $scanner = $loader->get_scanner();
    $utilities = [];
    
    // Check if utilities file exists
    $utilities_file = STUDIO_DIR . '/studio/css/studio-utilities.css';
    $utilities_exist = file_exists($utilities_file);
    
    if ($utilities_exist) {
        // Get utilities from generator
        $generator = new \Studio\Core\UtilityGenerator($scanner);
        $generator->generate();
        $utilities = $generator->get_utilities();
    }
    
    // Group utilities by category
    $by_category = [];
    foreach ($utilities as $name => $utility) {
        $category = $utility['category'];
        if (!isset($by_category[$category])) {
            $by_category[$category] = [];
        }
        $by_category[$category][$name] = $utility;
    }
    
    ?>
    <div class="wrap studio-admin-wrap">
        <h1><?php _e('Studio Utilities', 'the-studio'); ?></h1>
        
        <div id="studio-generate-message" style="display: none;"></div>
        
        <div class="studio-admin-header">
            <p><?php _e('Auto-generated utility classes based on your CSS variables.', 'the-studio'); ?></p>
            <div class="studio-header-buttons">
                <button type="button" class="button button-primary" id="studio-generate-utilities">
                    <?php _e('Generate Utilities', 'the-studio'); ?>
                </button>
                <?php if ($utilities_exist) : ?>
                    <a href="<?php echo STUDIO_URL . '/studio/css/studio-utilities.css'; ?>" 
                       class="button button-secondary" 
                       target="_blank">
                        <?php _e('View CSS File', 'the-studio'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div id="studio-generate-message" class="notice" style="display: none;"></div>
        
        <?php if (!$utilities_exist) : ?>
            <div class="studio-no-utilities">
                <p><?php _e('No utilities generated yet. Click "Generate Utilities" to create utility classes from your variables.', 'the-studio'); ?></p>
            </div>
        <?php else : ?>
            <div class="studio-utilities-info">
                <h2><?php _e('Generated Utilities', 'the-studio'); ?></h2>
                <p><?php printf(__('Total utilities: %d', 'the-studio'), count($utilities)); ?></p>
                
                <?php foreach ($by_category as $category => $category_utilities) : ?>
                    <div class="studio-utility-category">
                        <h3><?php echo esc_html(ucfirst($category)); ?> Utilities</h3>
                        
                        <div class="studio-utilities-grid">
                            <?php foreach ($category_utilities as $name => $utility) : ?>
                                <div class="studio-utility-item">
                                    <div class="studio-utility-class">
                                        <code><?php echo esc_html($utility['class']); ?></code>
                                    </div>
                                    <div class="studio-utility-css">
                                        <code><?php echo esc_html($utility['css']); ?></code>
                                    </div>
                                    <div class="studio-utility-preview">
                                        <?php studio_render_utility_preview($name, $utility); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="studio-utility-usage">
                    <h3><?php _e('Usage Examples', 'the-studio'); ?></h3>
                    <pre><code>&lt;div class="text-primary bg-neutral-100 p-lg rounded-md shadow-sm"&gt;
    Example with multiple utilities
&lt;/div&gt;

&lt;!-- Responsive utilities --&gt;
&lt;div class="p-sm md:p-lg xl:p-xl"&gt;
    Responsive padding
&lt;/div&gt;</code></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
        .studio-utilities-info {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .studio-utility-category {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }
        
        .studio-utility-category:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }
        
        .studio-utilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .studio-utility-item {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            font-size: 12px;
        }
        
        .studio-utility-class {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .studio-utility-css {
            color: #666;
            margin-bottom: 10px;
        }
        
        .studio-utility-css code,
        .studio-utility-class code {
            background: none;
            padding: 0;
        }
        
        .studio-utility-preview {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .studio-utility-usage {
            margin-top: 40px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        
        .studio-utility-usage pre {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow-x: auto;
        }
        
        .studio-no-utilities {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 40px;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Generate Utilities
        $('#studio-generate-utilities').on('click', function() {
            var $button = $(this);
            var $message = $('#studio-generate-message');
            
            $button.prop('disabled', true).text('Generating...');
            
            $.post(ajaxurl, {
                action: 'studio_generate_utilities',
                nonce: '<?php echo wp_create_nonce('studio_admin_nonce'); ?>'
            }, function(response) {
                if (response.success) {
                    $message.removeClass('notice-error').addClass('notice notice-success')
                        .html('<p>Utilities generated successfully! Generated ' + response.data.count + ' utility classes.</p>')
                        .show();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $message.removeClass('notice-success').addClass('notice notice-error')
                        .html('<p>Error: ' + response.data + '</p>')
                        .show();
                }
            }).always(function() {
                $button.prop('disabled', false).text('Generate Utilities');
            });
        });
    });
    </script>
    <?php
}

/**
 * Render utility preview
 */
function studio_render_utility_preview($name, $utility) {
    $category = $utility['category'];
    
    switch ($category) {
        case 'color':
            if (strpos($name, 'text-') === 0) {
                echo '<div class="' . esc_attr($name) . '">Text Color</div>';
            } elseif (strpos($name, 'bg-') === 0) {
                echo '<div class="' . esc_attr($name) . '" style="padding: 5px;">Background</div>';
            } elseif (strpos($name, 'border-') === 0) {
                echo '<div class="' . esc_attr($name) . '" style="border: 2px solid; padding: 5px;">Border</div>';
            }
            break;
            
        case 'typography':
            echo '<div class="' . esc_attr($name) . '">Sample Text</div>';
            break;
            
        case 'spacing':
            echo '<div style="background: #e0e0e0;"><div class="' . esc_attr($name) . '" style="background: #fff;">Spacing</div></div>';
            break;
            
        case 'borders':
            if (strpos($name, 'rounded-') === 0) {
                echo '<div class="' . esc_attr($name) . '" style="background: #e0e0e0; padding: 10px; width: 50px; height: 50px;"></div>';
            } else {
                echo '<div class="' . esc_attr($name) . '" style="border: 1px solid #333; padding: 5px;">Border</div>';
            }
            break;
            
        case 'shadows':
            echo '<div class="' . esc_attr($name) . '" style="background: #fff; padding: 10px;">Shadow</div>';
            break;
            
        default:
            echo '<div>Preview</div>';
    }
}