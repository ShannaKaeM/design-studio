<?php
/**
 * Test Scanner - Debug the variable scanner
 */

// Load WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Load our scanner
require_once(__DIR__ . '/variable-scanner.php');

use Studio\Core\VariableScanner;

// Create scanner instance
$scanner = new VariableScanner();

// Scan the CSS file
$css_file = dirname(dirname(__FILE__)) . '/css/studio-vars.css';

echo "<h1>Testing Variable Scanner</h1>";
echo "<p>CSS File: " . $css_file . "</p>";
echo "<p>File exists: " . (file_exists($css_file) ? 'YES' : 'NO') . "</p>";

if (file_exists($css_file)) {
    $content = file_get_contents($css_file);
    echo "<h2>File Content (first 500 chars):</h2>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "</pre>";
    
    // Test pattern matching
    echo "<h2>Testing Pattern Match:</h2>";
    $pattern = '/(--([\w-]+)):\s*([^;]+);\s*(?:\/\*\s*@control:\s*([^\*]+)\s*\*\/)?/m';
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
    
    echo "<p>Total matches found: " . count($matches) . "</p>";
    
    // Show first 5 matches
    echo "<h3>First 5 matches:</h3>";
    for ($i = 0; $i < min(5, count($matches)); $i++) {
        echo "<pre>";
        print_r($matches[$i]);
        echo "</pre>";
    }
    
    // Now scan with the scanner
    echo "<h2>Scanner Results:</h2>";
    $variables = $scanner->scan_file($css_file);
    
    echo "<p>Variables found: " . count($variables) . "</p>";
    
    // Group by category
    $categories = [];
    foreach ($variables as $var) {
        $cat = $var['category'];
        if (!isset($categories[$cat])) {
            $categories[$cat] = 0;
        }
        $categories[$cat]++;
    }
    
    echo "<h3>Categories:</h3>";
    echo "<pre>";
    print_r($categories);
    echo "</pre>";
    
    // Show first few variables
    echo "<h3>First 5 variables with controls:</h3>";
    $count = 0;
    foreach ($variables as $var) {
        if ($var['control']) {
            echo "<pre>";
            echo "Name: " . $var['name'] . "\n";
            echo "Value: " . $var['value'] . "\n";
            echo "Control: ";
            print_r($var['control']);
            echo "</pre>";
            $count++;
            if ($count >= 5) break;
        }
    }
}