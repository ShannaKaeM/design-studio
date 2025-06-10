<?php
// Load WordPress
require_once('wp-load.php');

// Regenerate utilities
$generator = new DS_Studio_Utility_Generator();
$generator->regenerate_utilities();

echo "✅ Utilities regenerated with responsive and fluid support!\n";
echo "📁 CSS file location: " . wp_upload_dir()['basedir'] . "/ds-studio/utilities.css\n";

// Show some example utilities that were generated
$utilities = $generator->get_utilities_by_category();
echo "\n📊 Generated utility categories:\n";
foreach ($utilities as $category => $utils) {
    echo "- {$category}: " . count($utils) . " utilities\n";
}

// Show some examples
echo "\n🔍 Example utilities:\n";
if (!empty($utilities['responsive'])) {
    echo "Responsive: " . implode(', ', array_slice($utilities['responsive'], 0, 5)) . "\n";
}
if (!empty($utilities['fluid'])) {
    echo "Fluid: " . implode(', ', array_slice($utilities['fluid'], 0, 5)) . "\n";
}
?>
