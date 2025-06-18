<?php
/**
 * Debug Variables Page
 */

// Load WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

use Studio\Core\StudioLoader;

$loader = StudioLoader::get_instance();
$scanner = $loader->get_scanner();

// Force scan
$css_file = get_stylesheet_directory() . '/studio/css/studio-vars.css';
$scanner->scan_file($css_file);

$variables = $scanner->get_variables_by_category();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Variables</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
        .variable { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        .has-control { background: #e8f5e9; }
        .no-control { background: #ffebee; }
    </style>
</head>
<body>
    <h1>Studio Variables Debug</h1>
    
    <h2>CSS File: <?php echo $css_file; ?></h2>
    <p>File exists: <?php echo file_exists($css_file) ? 'YES' : 'NO'; ?></p>
    
    <h2>Total Variables: <?php echo count($variables); ?></h2>
    
    <h3>First 10 Variables:</h3>
    <?php
    $count = 0;
    foreach ($variables as $variable) {
        $has_control = !empty($variable['control']);
        ?>
        <div class="variable <?php echo $has_control ? 'has-control' : 'no-control'; ?>">
            <h4><?php echo $variable['name']; ?></h4>
            <p><strong>Value:</strong> <?php echo $variable['value']; ?></p>
            <p><strong>Category:</strong> <?php echo $variable['category']; ?></p>
            <p><strong>Has Control:</strong> <?php echo $has_control ? 'YES' : 'NO'; ?></p>
            <?php if ($has_control) : ?>
                <p><strong>Control Type:</strong> <?php echo $variable['control']['type']; ?></p>
                <pre><?php print_r($variable['control']); ?></pre>
            <?php endif; ?>
        </div>
        <?php
        $count++;
        if ($count >= 10) break;
    }
    ?>
    
    <h3>Raw Variable Dump (first 3):</h3>
    <pre><?php 
    $first_three = array_slice($variables, 0, 3);
    print_r($first_three); 
    ?></pre>
</body>
</html>