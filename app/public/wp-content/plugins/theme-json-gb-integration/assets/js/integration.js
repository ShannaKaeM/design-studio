/**
 * Theme.json → GenerateBlocks Integration
 * 
 * Enhances GenerateBlocks styling controls with theme.json design tokens
 * Provides dropdown presets for colors, font sizes, spacing, etc.
 */

(function() {
    'use strict';
    
    // Wait for WordPress and GenerateBlocks to be ready
    if (typeof wp === 'undefined' || typeof wp.hooks === 'undefined') {
        console.warn('Theme.json GB Integration: WordPress hooks not available');
        return;
    }
    
    // Get theme.json tokens from localized script
    const tokens = window.themeJsonGbIntegration?.tokens || {};
    
    console.log('🎨 Theme.json GB Integration: Loaded with tokens:', tokens);
    
    /**
     * Enhance GenerateBlocks font size controls with theme.json presets
     */
    function enhanceFontSizeControls() {
        if (!tokens.fontSizes || tokens.fontSizes.length === 0) {
            return;
        }
        
        // Convert theme.json font sizes to GenerateBlocks format
        const fontSizePresets = tokens.fontSizes.map(size => ({
            label: size.name,
            value: size.size,
            slug: size.slug
        }));
        
        // Hook into GenerateBlocks font size control
        wp.hooks.addFilter(
            'generateblocks.editor.fontSizePresets',
            'theme-json-gb-integration/font-sizes',
            function(presets) {
                return [
                    ...presets,
                    {
                        label: 'Theme Sizes',
                        options: fontSizePresets
                    }
                ];
            }
        );
        
        console.log('✅ Font size presets enhanced:', fontSizePresets);
    }
    
    /**
     * Enhance GenerateBlocks color controls with theme.json presets
     */
    function enhanceColorControls() {
        if (!tokens.colors || tokens.colors.length === 0) {
            return;
        }
        
        // Convert theme.json colors to GenerateBlocks format
        const colorPresets = tokens.colors.map(color => ({
            name: color.name,
            color: color.color,
            slug: color.slug
        }));
        
        // Hook into GenerateBlocks color control
        wp.hooks.addFilter(
            'generateblocks.editor.colorPresets',
            'theme-json-gb-integration/colors',
            function(presets) {
                return [
                    ...presets,
                    {
                        name: 'Theme Colors',
                        colors: colorPresets
                    }
                ];
            }
        );
        
        console.log('✅ Color presets enhanced:', colorPresets);
    }
    
    /**
     * Enhance GenerateBlocks spacing controls with theme.json presets
     */
    function enhanceSpacingControls() {
        if (!tokens.spacing || tokens.spacing.length === 0) {
            return;
        }
        
        // Convert theme.json spacing to GenerateBlocks format
        const spacingPresets = tokens.spacing.map(space => ({
            label: space.name,
            value: space.size,
            slug: space.slug
        }));
        
        // Hook into GenerateBlocks spacing control
        wp.hooks.addFilter(
            'generateblocks.editor.spacingPresets',
            'theme-json-gb-integration/spacing',
            function(presets) {
                return [
                    ...presets,
                    {
                        label: 'Theme Spacing',
                        options: spacingPresets
                    }
                ];
            }
        );
        
        console.log('✅ Spacing presets enhanced:', spacingPresets);
    }
    
    /**
     * Enhance GenerateBlocks border radius controls with theme.json presets
     */
    function enhanceBorderRadiusControls() {
        if (!tokens.borderRadius || tokens.borderRadius.length === 0) {
            return;
        }
        
        // Convert theme.json border radius to GenerateBlocks format
        const borderRadiusPresets = tokens.borderRadius.map(radius => ({
            label: radius.name,
            value: radius.size,
            slug: radius.slug
        }));
        
        // Hook into GenerateBlocks border radius control
        wp.hooks.addFilter(
            'generateblocks.editor.borderRadiusPresets',
            'theme-json-gb-integration/border-radius',
            function(presets) {
                return [
                    ...presets,
                    {
                        label: 'Theme Radius',
                        options: borderRadiusPresets
                    }
                ];
            }
        );
        
        console.log('✅ Border radius presets enhanced:', borderRadiusPresets);
    }
    
    /**
     * Add design token inspector panel
     */
    function addDesignTokenInspector() {
        // Only add if we have tokens
        if (Object.keys(tokens).length === 0) {
            return;
        }
        
        // Create floating design token inspector
        const inspector = document.createElement('div');
        inspector.id = 'theme-json-gb-inspector';
        inspector.innerHTML = `
            <div class="inspector-header">
                <h3>🎨 Design Tokens</h3>
                <button class="inspector-toggle">−</button>
            </div>
            <div class="inspector-content">
                ${Object.entries(tokens).map(([category, items]) => {
                    if (!Array.isArray(items) || items.length === 0) return '';
                    return `
                        <div class="token-category">
                            <h4>${category.charAt(0).toUpperCase() + category.slice(1)}</h4>
                            <div class="token-list">
                                ${items.map(item => `
                                    <div class="token-item" title="${item.slug || item.name}">
                                        <span class="token-name">${item.name}</span>
                                        <span class="token-value">${item.size || item.color || item.fontFamily}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
        
        // Add styles
        const styles = document.createElement('style');
        styles.textContent = `
            #theme-json-gb-inspector {
                position: fixed;
                top: 100px;
                right: 20px;
                width: 280px;
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                z-index: 999999;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 12px;
                max-height: 400px;
                overflow: hidden;
            }
            #theme-json-gb-inspector .inspector-header {
                padding: 10px;
                background: #f0f0f0;
                border-bottom: 1px solid #ddd;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            #theme-json-gb-inspector h3 {
                margin: 0;
                font-size: 13px;
                font-weight: 600;
            }
            #theme-json-gb-inspector .inspector-toggle {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 16px;
                padding: 0;
                width: 20px;
                height: 20px;
            }
            #theme-json-gb-inspector .inspector-content {
                padding: 10px;
                max-height: 350px;
                overflow-y: auto;
            }
            #theme-json-gb-inspector .token-category {
                margin-bottom: 15px;
            }
            #theme-json-gb-inspector h4 {
                margin: 0 0 8px 0;
                font-size: 12px;
                font-weight: 600;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            #theme-json-gb-inspector .token-item {
                display: flex;
                justify-content: space-between;
                padding: 4px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            #theme-json-gb-inspector .token-name {
                font-weight: 500;
                color: #333;
            }
            #theme-json-gb-inspector .token-value {
                color: #666;
                font-family: monospace;
                font-size: 11px;
            }
            #theme-json-gb-inspector.collapsed .inspector-content {
                display: none;
            }
        `;
        
        // Add to page
        document.head.appendChild(styles);
        document.body.appendChild(inspector);
        
        // Add toggle functionality
        inspector.querySelector('.inspector-toggle').addEventListener('click', function() {
            inspector.classList.toggle('collapsed');
            this.textContent = inspector.classList.contains('collapsed') ? '+' : '−';
        });
        
        console.log('✅ Design token inspector added');
    }
    
    /**
     * Initialize all enhancements when DOM is ready
     */
    function initializeEnhancements() {
        // Wait for GenerateBlocks to be available
        if (typeof window.generateBlocksEditor === 'undefined') {
            setTimeout(initializeEnhancements, 100);
            return;
        }
        
        enhanceFontSizeControls();
        enhanceColorControls();
        enhanceSpacingControls();
        enhanceBorderRadiusControls();
        addDesignTokenInspector();
        
        console.log('🚀 Theme.json GB Integration: All enhancements initialized');
    }
    
    // Start initialization when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeEnhancements);
    } else {
        initializeEnhancements();
    }
    
})();
