/**
 * DS-Studio GenerateBlocks Integration
 * 
 * Enhances GenerateBlocks styling controls with theme.json design tokens
 * Replaces manual inputs with dropdown presets for consistent design system
 */

(function() {
    'use strict';
    
    // Wait for WordPress and GenerateBlocks to be ready
    wp.domReady(function() {
        console.log('🎨 DS-Studio GB Integration: Starting...');
        
        // Check if design tokens are available
        if (typeof dsStudioTokens === 'undefined') {
            console.warn('DS-Studio: Design tokens not found');
            return;
        }
        
        console.log('🎨 DS-Studio: Design tokens loaded:', dsStudioTokens);
        
        // Initialize integrations
        initColorIntegration();
        initFontSizeIntegration();
        initSpacingIntegration();
        initTypographyIntegration();
        initBorderRadiusIntegration();
        
        console.log('✅ DS-Studio GB Integration: Complete!');
    });
    
    /**
     * Enhance color controls with theme.json color palette
     */
    function initColorIntegration() {
        if (!dsStudioTokens.colors) return;
        
        console.log('🎨 Initializing color integration...');
        
        // Hook into GenerateBlocks color picker components
        wp.hooks.addFilter(
            'generateblocks.editor.colorPicker',
            'ds-studio/color-presets',
            function(colorPicker, props) {
                // Add theme colors as presets
                if (colorPicker && colorPicker.props) {
                    colorPicker.props.colors = Object.entries(dsStudioTokens.colors).map(([slug, color]) => ({
                        name: slug.charAt(0).toUpperCase() + slug.slice(1),
                        slug: slug,
                        color: color
                    }));
                }
                return colorPicker;
            }
        );
    }
    
    /**
     * Enhance font size controls with theme.json font size presets
     */
    function initFontSizeIntegration() {
        if (!dsStudioTokens.fontSizes) return;
        
        console.log('📝 Initializing font size integration...');
        
        // Hook into GenerateBlocks font size controls
        wp.hooks.addFilter(
            'generateblocks.editor.fontSize',
            'ds-studio/font-size-presets',
            function(fontSizeControl, props) {
                // Add font size presets to UnitControl
                if (fontSizeControl && fontSizeControl.props) {
                    fontSizeControl.props.units = [
                        {
                            value: 'px',
                            label: 'px',
                            default: 16
                        },
                        {
                            value: 'rem',
                            label: 'rem',
                            default: 1
                        },
                        {
                            value: 'em',
                            label: 'em',
                            default: 1
                        },
                        // Add preset unit
                        {
                            value: 'preset',
                            label: 'Preset',
                            default: 'medium'
                        }
                    ];
                    
                    // Add presets data
                    fontSizeControl.props.presets = dsStudioTokens.fontSizes;
                }
                return fontSizeControl;
            }
        );
    }
    
    /**
     * Enhance spacing controls with theme.json spacing presets
     */
    function initSpacingIntegration() {
        if (!dsStudioTokens.spacing) return;
        
        console.log('📏 Initializing spacing integration...');
        
        // Hook into GenerateBlocks spacing controls (padding, margin)
        wp.hooks.addFilter(
            'generateblocks.editor.spacing',
            'ds-studio/spacing-presets',
            function(spacingControl, props) {
                if (spacingControl && spacingControl.props) {
                    // Add spacing presets
                    spacingControl.props.presets = dsStudioTokens.spacing;
                    
                    // Add preset unit option
                    if (spacingControl.props.units) {
                        spacingControl.props.units.push({
                            value: 'preset',
                            label: 'Preset',
                            default: 'md'
                        });
                    }
                }
                return spacingControl;
            }
        );
    }
    
    /**
     * Enhance typography controls with theme.json typography settings
     */
    function initTypographyIntegration() {
        if (!dsStudioTokens.typography) return;
        
        console.log('🔤 Initializing typography integration...');
        
        // Hook into GenerateBlocks font family controls
        wp.hooks.addFilter(
            'generateblocks.editor.fontFamily',
            'ds-studio/typography-presets',
            function(fontFamilyControl, props) {
                if (fontFamilyControl && fontFamilyControl.props) {
                    // Add theme font families
                    fontFamilyControl.props.options = [
                        ...fontFamilyControl.props.options || [],
                        {
                            label: 'Theme Fonts',
                            options: Object.entries(dsStudioTokens.typography).map(([key, value]) => ({
                                label: key.charAt(0).toUpperCase() + key.slice(1),
                                value: value
                            }))
                        }
                    ];
                }
                return fontFamilyControl;
            }
        );
    }
    
    /**
     * Enhance border radius controls with theme.json border radius presets
     */
    function initBorderRadiusIntegration() {
        if (!dsStudioTokens.borderRadius) return;
        
        console.log('🔘 Initializing border radius integration...');
        
        // Hook into GenerateBlocks border radius controls
        wp.hooks.addFilter(
            'generateblocks.editor.borderRadius',
            'ds-studio/border-radius-presets',
            function(borderRadiusControl, props) {
                if (borderRadiusControl && borderRadiusControl.props) {
                    // Add border radius presets
                    borderRadiusControl.props.presets = dsStudioTokens.borderRadius;
                    
                    // Add preset unit option
                    if (borderRadiusControl.props.units) {
                        borderRadiusControl.props.units.push({
                            value: 'preset',
                            label: 'Preset',
                            default: 'base'
                        });
                    }
                }
                return borderRadiusControl;
            }
        );
    }
    
    /**
     * Add utility class suggestions to GenerateBlocks class editor
     */
    function initUtilityClassIntegration() {
        console.log('🛠️ Initializing utility class integration...');
        
        // Hook into GenerateBlocks additional CSS classes field
        wp.hooks.addFilter(
            'generateblocks.editor.additionalClasses',
            'ds-studio/utility-class-suggestions',
            function(classField, props) {
                if (classField && classField.props) {
                    // Add autocomplete suggestions for utility classes
                    classField.props.suggestions = [
                        // Color utilities
                        'text-primary', 'text-secondary', 'text-neutral-700',
                        'bg-primary', 'bg-secondary', 'bg-neutral-50',
                        
                        // Spacing utilities
                        'p-xs', 'p-sm', 'p-md', 'p-lg', 'p-xl',
                        'm-xs', 'm-sm', 'm-md', 'm-lg', 'm-xl',
                        
                        // Typography utilities
                        'text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl',
                        'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold',
                        
                        // Border radius utilities
                        'rounded-xs', 'rounded-sm', 'rounded-base', 'rounded-lg', 'rounded-xl',
                        
                        // Layout utilities
                        'flex', 'grid', 'block', 'inline-block', 'hidden',
                        'justify-center', 'items-center', 'text-center'
                    ];
                    
                    // Add helper text
                    classField.props.help = 'Use DS-Studio utility classes for consistent styling. Type to see suggestions.';
                }
                return classField;
            }
        );
    }
    
    /**
     * Add design token inspector panel
     */
    function initDesignTokenInspector() {
        console.log('🔍 Initializing design token inspector...');
        
        // Create a floating panel showing available design tokens
        const inspector = document.createElement('div');
        inspector.id = 'ds-studio-token-inspector';
        inspector.style.cssText = `
            position: fixed;
            top: 32px;
            right: 20px;
            width: 300px;
            max-height: 400px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 999999;
            overflow-y: auto;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
            display: none;
        `;
        
        inspector.innerHTML = `
            <div style="padding: 12px; border-bottom: 1px solid #eee; background: #f9f9f9;">
                <strong>🎨 DS-Studio Design Tokens</strong>
                <button id="ds-studio-toggle-inspector" style="float: right; background: none; border: none; cursor: pointer;">×</button>
            </div>
            <div style="padding: 12px;">
                <div><strong>Colors:</strong> ${Object.keys(dsStudioTokens.colors || {}).join(', ')}</div>
                <div><strong>Font Sizes:</strong> ${(dsStudioTokens.fontSizes || []).map(s => s.name).join(', ')}</div>
                <div><strong>Spacing:</strong> ${(dsStudioTokens.spacing || []).map(s => s.name).join(', ')}</div>
            </div>
        `;
        
        document.body.appendChild(inspector);
        
        // Add toggle button to admin bar
        const adminBar = document.getElementById('wpadminbar');
        if (adminBar) {
            const toggleButton = document.createElement('div');
            toggleButton.innerHTML = `
                <a href="#" id="ds-studio-inspector-toggle" style="color: white; text-decoration: none; padding: 0 10px;">
                    🎨 Tokens
                </a>
            `;
            toggleButton.style.cssText = 'display: inline-block; line-height: 32px;';
            adminBar.appendChild(toggleButton);
            
            // Toggle functionality
            document.getElementById('ds-studio-inspector-toggle').addEventListener('click', function(e) {
                e.preventDefault();
                inspector.style.display = inspector.style.display === 'none' ? 'block' : 'none';
            });
            
            document.getElementById('ds-studio-toggle-inspector').addEventListener('click', function() {
                inspector.style.display = 'none';
            });
        }
    }
    
    // Initialize design token inspector
    wp.domReady(function() {
        if (dsStudioTokens && dsStudioTokens.debug) {
            initDesignTokenInspector();
        }
    });
    
})();
