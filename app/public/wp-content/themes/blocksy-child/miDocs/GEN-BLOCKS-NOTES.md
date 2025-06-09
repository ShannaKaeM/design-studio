theme.json integration? We could:

1. Create JSON token files that extract key design tokens from your theme.json for AI generators to consume
2. Build a CSS variables file that syncs with your theme.json tokens
3. Update your Browse Rooms CSS to use the new design tokens
4. Create a PHP sync function to keep everything in sync
Which approach interests you most?


Perfect! Now let's test that everything is working. Your theme.json should now be controlling the block editor options. Here's what you've accomplished:

✅ Theme.json Integration Complete
What's now available in your block editor:

Colors - Your custom palette (primary, secondary, neutral, base colors) will appear in all color pickers
Font Sizes - Custom sizes (small, medium, large, x-large, xx-large, huge) in typography controls
Font Family - Montserrat will be available in font family dropdowns
Spacing - Your spacing scale (xs through 10xl) in padding/margin controls
Typography Settings - Font weights, line heights, letter spacing options
Border Radius - Custom radius values in border controls
Files Created:

✅ Enhanced theme.json with styles section
✅ design-tokens.css - CSS custom properties for all components
✅ design-tokens.json - JSON file for AI generators
✅ Updated functions.php - Proper enqueueing and theme.json priority
How to Test:

Go to your WordPress admin → Appearance → Editor
Create a new page or post
Add any block (like a paragraph or button)
Check the color picker - you should see your custom colors
Check typography settings - you should see your custom font sizes
Check spacing controls - you should see your custom spacing scale
The design tokens are now centralized and will override Blocksy's defaults. Your AI generators can consume the JSON file to stay consistent with your theme.

Want to test this in your WordPress admin to see the custom options appear?