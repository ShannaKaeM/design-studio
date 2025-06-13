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
        initUtilityClassIntegration();
        
        // Hook into GenerateBlocks block registration
        wp.hooks.addFilter(
            'blocks.registerBlockType',
            'ds-studio/enhance-generateblocks',
            enhanceGenerateBlocksControls
        );
        
        console.log('✅ DS-Studio GB Integration: Complete!');
    });
    
    /**
     * Enhance GenerateBlocks controls with utility class options
     */
    function enhanceGenerateBlocksControls(settings, name) {
        // Only enhance GenerateBlocks blocks
        if (!name.startsWith('generateblocks/')) {
            return settings;
        }
        
        console.log('🔧 Enhancing GenerateBlocks block:', name);
        
        // Store original edit function
        const originalEdit = settings.edit;
        
        // Enhance the edit function
        settings.edit = function(props) {
            // Get the original edit component
            const OriginalEdit = originalEdit(props);
            
            // Add our utility class controls
            return wp.element.createElement(
                wp.element.Fragment,
                null,
                OriginalEdit,
                wp.element.createElement(UtilityClassControls, { 
                    blockProps: props,
                    blockName: name 
                })
            );
        };
        
        return settings;
    }
    
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
        console.log('🔧 Initializing utility class integration...');
        
        // Hook into GenerateBlocks class field
        wp.hooks.addFilter(
            'generateblocks.editor.classField',
            'ds-studio/utility-class-editor',
            function(classField, props) {
                if (classField && classField.props) {
                    // Add utility class picker button
                    const originalOnChange = classField.props.onChange;
                    
                    // Enhance the class field with utility picker
                    classField.props.help = 'Click "Add Utilities" button to browse DS-Studio utility classes, or type manually.';
                    
                    // Add utility picker functionality
                    classField.props.onFocus = function() {
                        // Show utility class picker when field is focused
                        showUtilityClassPicker(classField.props.value || '', function(selectedUtilities) {
                            if (originalOnChange) {
                                originalOnChange(selectedUtilities);
                            }
                        });
                    };
                }
                return classField;
            }
        );
    }
    
    /**
     * Show utility class picker modal
     */
    function showUtilityClassPicker(currentClasses, onApply) {
        // Remove existing picker if any
        const existingPicker = document.getElementById('ds-studio-utility-picker');
        if (existingPicker) {
            existingPicker.remove();
        }
        
        // Create utility picker modal
        const picker = document.createElement('div');
        picker.id = 'ds-studio-utility-picker';
        picker.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        // Get utilities from PHP via AJAX
        fetch(dsStudioTokens.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'ds_studio_get_utilities_by_category',
                nonce: dsStudioTokens.nonce || ''
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderUtilityPicker(picker, data.data, currentClasses, onApply);
            } else {
                console.error('Failed to load utilities:', data);
            }
        })
        .catch(error => {
            console.error('Error loading utilities:', error);
        });
        
        document.body.appendChild(picker);
    }
    
    /**
     * Render the utility picker interface
     */
    function renderUtilityPicker(picker, utilitiesByCategory, currentClasses, onApply) {
        const currentClassArray = currentClasses.split(' ').filter(c => c.trim());
        let selectedUtilities = [...currentClassArray];
        
        picker.innerHTML = `
            <div style="
                background: white;
                border-radius: 8px;
                width: 90%;
                max-width: 800px;
                max-height: 80vh;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            ">
                <div style="
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                    background: #f9f9f9;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <h3 style="margin: 0; color: #333;">🎨 DS-Studio Utility Classes</h3>
                    <button id="ds-close-picker" style="
                        background: none;
                        border: none;
                        font-size: 20px;
                        cursor: pointer;
                        color: #666;
                    ">×</button>
                </div>
                
                <div style="
                    padding: 20px;
                    max-height: 60vh;
                    overflow-y: auto;
                ">
                    <div id="utility-categories" style="
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                        gap: 20px;
                    "></div>
                </div>
                
                <div style="
                    padding: 20px;
                    border-top: 1px solid #eee;
                    background: #f9f9f9;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div style="flex: 1;">
                        <strong>Selected:</strong>
                        <div id="selected-utilities" style="
                            margin-top: 8px;
                            padding: 8px;
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            min-height: 20px;
                            font-family: monospace;
                            font-size: 12px;
                        ">${selectedUtilities.join(' ')}</div>
                    </div>
                    <div style="margin-left: 20px;">
                        <button id="ds-clear-utilities" style="
                            padding: 8px 16px;
                            margin-right: 10px;
                            background: #f0f0f0;
                            border: 1px solid #ccc;
                            border-radius: 4px;
                            cursor: pointer;
                        ">Clear All</button>
                        <button id="ds-apply-utilities" style="
                            padding: 8px 16px;
                            background: #0073aa;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                        ">Apply Classes</button>
                    </div>
                </div>
            </div>
        `;
        
        // Render utility categories
        const categoriesContainer = picker.querySelector('#utility-categories');
        Object.entries(utilitiesByCategory).forEach(([category, utilities]) => {
            if (utilities.length === 0) return;
            
            const categoryDiv = document.createElement('div');
            categoryDiv.innerHTML = `
                <h4 style="
                    margin: 0 0 10px 0;
                    color: #333;
                    font-size: 14px;
                    text-transform: capitalize;
                    border-bottom: 2px solid #0073aa;
                    padding-bottom: 5px;
                ">${getCategoryIcon(category)} ${category}</h4>
                <div class="utility-grid" style="
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 4px;
                    max-height: 200px;
                    overflow-y: auto;
                ">
                    ${utilities.map(utility => `
                        <label style="
                            display: flex;
                            align-items: center;
                            padding: 4px 8px;
                            border: 1px solid #e1e5e9;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 11px;
                            font-family: monospace;
                            background: ${selectedUtilities.includes(utility) ? '#e1f5fe' : '#fafafa'};
                            transition: all 0.2s ease;
                        " onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='${selectedUtilities.includes(utility) ? '#e1f5fe' : '#fafafa'}'">
                            <input type="checkbox" 
                                   value="${utility}" 
                                   ${selectedUtilities.includes(utility) ? 'checked' : ''}
                                   style="margin-right: 6px;"
                                   onchange="toggleUtility('${utility}')">
                            <span>${utility}</span>
                        </label>
                    `).join('')}
                </div>
            `;
            categoriesContainer.appendChild(categoryDiv);
        });
        
        // Add event handlers
        picker.querySelector('#ds-close-picker').onclick = () => picker.remove();
        picker.querySelector('#ds-clear-utilities').onclick = () => {
            selectedUtilities = [];
            updateSelectedDisplay();
            updateCheckboxes();
        };
        picker.querySelector('#ds-apply-utilities').onclick = () => {
            onApply(selectedUtilities.join(' '));
            picker.remove();
        };
        
        // Global functions for utility management
        window.toggleUtility = function(utility) {
            if (selectedUtilities.includes(utility)) {
                selectedUtilities = selectedUtilities.filter(u => u !== utility);
            } else {
                selectedUtilities.push(utility);
            }
            updateSelectedDisplay();
        };
        
        function updateSelectedDisplay() {
            const display = picker.querySelector('#selected-utilities');
            display.textContent = selectedUtilities.join(' ');
        }
        
        function updateCheckboxes() {
            picker.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = selectedUtilities.includes(checkbox.value);
                const label = checkbox.closest('label');
                label.style.background = checkbox.checked ? '#e1f5fe' : '#fafafa';
            });
        }
    }
    
    /**
     * Get category icon
     */
    function getCategoryIcon(category) {
        const icons = {
            spacing: '📏',
            colors: '🎨',
            typography: '📝',
            layout: '📐',
            borders: '🔲',
            effects: '✨',
            responsive: '📱',
            fluid: '🌊'
        };
        return icons[category] || '🔧';
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
    
    /**
     * Utility Class Controls Component
     */
    function UtilityClassControls({ blockProps, blockName }) {
        const { attributes, setAttributes } = blockProps;
        const { className = '' } = attributes;
        
        // Get current utility classes
        const currentUtilities = className.split(' ').filter(cls => 
            cls.startsWith('text-') || 
            cls.startsWith('font-') || 
            cls.startsWith('p-') || 
            cls.startsWith('m-') ||
            cls.startsWith('bg-') ||
            cls.startsWith('border-') ||
            cls.startsWith('rounded-')
        );
        
        return wp.element.createElement(
            wp.blockEditor.InspectorControls,
            null,
            wp.element.createElement(
                wp.components.PanelBody,
                {
                    title: '🎨 DS-Studio Utilities',
                    initialOpen: false
                },
                // Typography utilities for text blocks
                (blockName === 'generateblocks/headline' || blockName === 'generateblocks/button') && 
                wp.element.createElement(TypographyUtilities, { 
                    currentUtilities, 
                    onChange: updateUtilities 
                }),
                
                // Spacing utilities for all blocks
                wp.element.createElement(SpacingUtilities, { 
                    currentUtilities, 
                    onChange: updateUtilities 
                }),
                
                // Color utilities for all blocks
                wp.element.createElement(ColorUtilities, { 
                    currentUtilities, 
                    onChange: updateUtilities 
                }),
                
                // Border utilities for containers
                (blockName === 'generateblocks/container' || blockName === 'generateblocks/button') &&
                wp.element.createElement(BorderUtilities, { 
                    currentUtilities, 
                    onChange: updateUtilities 
                })
            )
        );
        
        function updateUtilities(newUtilities) {
            // Remove existing utility classes and add new ones
            const nonUtilityClasses = className.split(' ').filter(cls => 
                !cls.startsWith('text-') && 
                !cls.startsWith('font-') && 
                !cls.startsWith('p-') && 
                !cls.startsWith('m-') &&
                !cls.startsWith('bg-') &&
                !cls.startsWith('border-') &&
                !cls.startsWith('rounded-')
            );
            
            const updatedClassName = [...nonUtilityClasses, ...newUtilities]
                .filter(cls => cls.trim())
                .join(' ');
                
            setAttributes({ className: updatedClassName });
        }
    }
    
    /**
     * Typography Utilities Component
     */
    function TypographyUtilities({ currentUtilities, onChange }) {
        const fontSizes = ['text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl'];
        const fontWeights = ['font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold'];
        
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                'h4',
                { style: { margin: '16px 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                '📝 Typography'
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Font Size',
                    value: currentUtilities.find(cls => cls.startsWith('text-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...fontSizes.map(size => ({ 
                            label: size.replace('text-', '').toUpperCase(), 
                            value: size 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('text-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Font Weight',
                    value: currentUtilities.find(cls => cls.startsWith('font-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...fontWeights.map(weight => ({ 
                            label: weight.replace('font-', '').charAt(0).toUpperCase() + weight.replace('font-', '').slice(1), 
                            value: weight 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('font-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            )
        );
    }
    
    /**
     * Spacing Utilities Component
     */
    function SpacingUtilities({ currentUtilities, onChange }) {
        const spacingSizes = ['xs', 'sm', 'md', 'lg', 'xl'];
        
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                'h4',
                { style: { margin: '16px 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                '📏 Spacing'
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Padding',
                    value: currentUtilities.find(cls => cls.startsWith('p-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...spacingSizes.map(size => ({ 
                            label: size.toUpperCase(), 
                            value: `p-${size}` 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('p-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Margin',
                    value: currentUtilities.find(cls => cls.startsWith('m-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...spacingSizes.map(size => ({ 
                            label: size.toUpperCase(), 
                            value: `m-${size}` 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('m-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            )
        );
    }
    
    /**
     * Color Utilities Component
     */
    function ColorUtilities({ currentUtilities, onChange }) {
        const colors = Object.keys(dsStudioTokens.colors || {});
        
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                'h4',
                { style: { margin: '16px 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                '🎨 Colors'
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Background Color',
                    value: currentUtilities.find(cls => cls.startsWith('bg-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...colors.map(color => ({ 
                            label: color.charAt(0).toUpperCase() + color.slice(1), 
                            value: `bg-${color}` 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('bg-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            )
        );
    }
    
    /**
     * Border Utilities Component
     */
    function BorderUtilities({ currentUtilities, onChange }) {
        const borderSizes = ['xs', 'sm', 'md', 'lg', 'xl'];
        
        return wp.element.createElement(
            wp.element.Fragment,
            null,
            wp.element.createElement(
                'h4',
                { style: { margin: '16px 0 8px 0', fontSize: '13px', fontWeight: '600' } },
                '🔲 Borders'
            ),
            wp.element.createElement(
                wp.components.SelectControl,
                {
                    label: 'Border Radius',
                    value: currentUtilities.find(cls => cls.startsWith('rounded-')) || '',
                    options: [
                        { label: 'Default', value: '' },
                        ...borderSizes.map(size => ({ 
                            label: size.toUpperCase(), 
                            value: `rounded-${size}` 
                        }))
                    ],
                    onChange: (value) => {
                        const newUtilities = currentUtilities.filter(cls => !cls.startsWith('rounded-'));
                        if (value) newUtilities.push(value);
                        onChange(newUtilities);
                    }
                }
            )
        );
    }
})();
