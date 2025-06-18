<?php
/**
 * Test the Studio Variable Scanner
 * Run this to verify the scanner is working correctly
 */

require_once 'scan-variables.php';

echo "<h2>Studio Variable Scanner Test</h2>\n";

$variables = scan_all_studio_variables();

echo "<h3>Found " . count($variables) . " variables:</h3>\n";
echo "<pre>\n";

foreach ($variables as $var) {
    echo "Variable: {$var['name']}\n";
    echo "  Value: {$var['value']}\n";
    echo "  Control: {$var['control']}\n";
    echo "  Params: {$var['params']}\n";
    echo "  Category: {$var['category']}\n";
    echo "  Label: {$var['label']}\n\n";
}

echo "</pre>\n";

$categorized = get_studio_variables_by_category();
echo "<h3>Variables by Category:</h3>\n";

foreach ($categorized as $category => $vars) {
    echo "<h4>{$category} (" . count($vars) . " variables)</h4>\n";
    echo "<ul>\n";
    foreach ($vars as $var) {
        echo "<li>{$var['name']} ({$var['control']})</li>\n";
    }
    echo "</ul>\n";
}
