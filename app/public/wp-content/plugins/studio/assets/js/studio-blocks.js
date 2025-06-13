/**
 * Studio Blocks - Main JavaScript File
 * 
 * Registers all Studio blocks and provides shared utilities
 */

(function() {
    'use strict';
    
    const { registerBlockType } = wp.blocks;
    const { InnerBlocks, InspectorControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, BaseControl, Button, ButtonGroup, TextControl } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el } = wp.element;

    // Studio Control Components
    const StudioColorPicker = function({ label, value, tokens, onChange }) {
        const colors = tokens?.themeColors || tokens?.semanticColors || {};
        
        return el(BaseControl, { label: label, className: 'studio-color-picker' },
            el('div', { className: 'studio-color-grid' },
                Object.entries(colors).map(function([key, colorData]) {
                    const colorValue = colorData.value || colorData;
                    const isSelected = value === `var(--wp--preset--color--${key})`;
                    
                    return el('button', {
                        key: key,
                        className: `studio-color-swatch ${isSelected ? 'is-selected' : ''}`,
                        style: { backgroundColor: colorValue },
                        onClick: () => onChange(`var(--wp--preset--color--${key})`),
                        title: colorData.name || key
                    });
                }).concat([
                    el('button', {
                        key: 'clear',
                        className: `studio-color-swatch studio-color-clear ${!value ? 'is-selected' : ''}`,
                        onClick: () => onChange(''),
                        title: __('Clear', 'studio')
                    }, '×')
                ])
            )
        );
    };

    const StudioSpacingPicker = function({ label, value, tokens, onChange, single = false }) {
        const spacing = tokens || {};
        
        if (single) {
            return el(BaseControl, { label: label, className: 'studio-spacing-picker studio-spacing-single' },
                el('div', { className: 'studio-spacing-options' },
                    Object.entries(spacing).map(function([key, spacingValue]) {
                        const cssVar = `var(--wp--preset--spacing--${key})`;
                        const isSelected = value === cssVar;
                        
                        return el(Button, {
                            key: key,
                            variant: isSelected ? 'primary' : 'secondary',
                            size: 'small',
                            onClick: () => onChange(cssVar),
                            title: `${key}: ${spacingValue}`
                        }, key.toUpperCase());
                    }).concat([
                        el(Button, {
                            key: 'none',
                            variant: !value ? 'primary' : 'secondary',
                            size: 'small',
                            onClick: () => onChange('')
                        }, __('None', 'studio'))
                    ])
                )
            );
        }
        
        // Multi-side spacing (simplified for now)
        return el(BaseControl, { label: label, className: 'studio-spacing-picker' },
            el('p', {}, __('Multi-side spacing coming soon', 'studio'))
        );
    };

    // Studio Container Block Edit
    const StudioContainerEdit = function(props) {
        const { attributes, setAttributes } = props;
        const {
            studioBackgroundColor,
            studioPadding,
            studioDisplay,
            studioFlexDirection,
            studioJustifyContent,
            studioAlignItems,
            studioGap
        } = attributes;

        // Get Studio tokens from global
        const studioTokens = window.studioBlocks?.tokens || {};

        // Generate CSS variables
        const generateCSSVariables = function() {
            const cssVars = {};
            
            if (studioBackgroundColor) {
                cssVars['--studio-bg-color'] = studioBackgroundColor;
            }
            
            if (studioGap) {
                cssVars['--studio-gap'] = studioGap;
            }
            
            return cssVars;
        };

        // Generate CSS classes
        const generateClasses = function() {
            let classes = 'studio-container';
            
            if (studioDisplay) classes += ` studio-container--${studioDisplay}`;
            if (studioDisplay === 'flex' && studioFlexDirection) classes += ` studio-container--flex-${studioFlexDirection}`;
            if (studioDisplay === 'flex' && studioJustifyContent) classes += ` studio-container--justify-${studioJustifyContent}`;
            if (studioDisplay === 'flex' && studioAlignItems) classes += ` studio-container--align-${studioAlignItems}`;
            
            return classes;
        };

        const blockProps = useBlockProps({
            className: generateClasses(),
            style: generateCSSVariables()
        });

        return el('div', {},
            // Inspector Controls
            el(InspectorControls, {},
                el(PanelBody, { title: __('Background', 'studio'), initialOpen: true },
                    el(StudioColorPicker, {
                        label: __('Background Color', 'studio'),
                        value: studioBackgroundColor,
                        tokens: studioTokens.colors,
                        onChange: function(color) { setAttributes({ studioBackgroundColor: color }); }
                    })
                ),
                
                el(PanelBody, { title: __('Layout', 'studio'), initialOpen: false },
                    el(SelectControl, {
                        label: __('Display', 'studio'),
                        value: studioDisplay,
                        options: [
                            { label: __('Block', 'studio'), value: 'block' },
                            { label: __('Flex', 'studio'), value: 'flex' },
                            { label: __('Grid', 'studio'), value: 'grid' }
                        ],
                        onChange: function(display) { setAttributes({ studioDisplay: display }); }
                    }),
                    
                    studioDisplay === 'flex' && el(SelectControl, {
                        label: __('Flex Direction', 'studio'),
                        value: studioFlexDirection,
                        options: [
                            { label: __('Column', 'studio'), value: 'column' },
                            { label: __('Row', 'studio'), value: 'row' }
                        ],
                        onChange: function(direction) { setAttributes({ studioFlexDirection: direction }); }
                    }),
                    
                    (studioDisplay === 'flex' || studioDisplay === 'grid') && el(StudioSpacingPicker, {
                        label: __('Gap', 'studio'),
                        value: studioGap,
                        tokens: studioTokens.spacing,
                        single: true,
                        onChange: function(gap) { setAttributes({ studioGap: gap }); }
                    })
                )
            ),
            
            // Block Content
            el('div', blockProps,
                el(InnerBlocks)
            )
        );
    };

    // Studio Container Block Save
    const StudioContainerSave = function(props) {
        const { attributes } = props;
        const {
            studioBackgroundColor,
            studioDisplay,
            studioFlexDirection,
            studioJustifyContent,
            studioAlignItems,
            studioGap
        } = attributes;

        // Generate CSS variables (same as Edit)
        const generateCSSVariables = function() {
            const cssVars = {};
            
            if (studioBackgroundColor) {
                cssVars['--studio-bg-color'] = studioBackgroundColor;
            }
            
            if (studioGap) {
                cssVars['--studio-gap'] = studioGap;
            }
            
            return cssVars;
        };

        // Generate CSS classes (same as Edit)
        const generateClasses = function() {
            let classes = 'studio-container';
            
            if (studioDisplay) classes += ` studio-container--${studioDisplay}`;
            if (studioDisplay === 'flex' && studioFlexDirection) classes += ` studio-container--flex-${studioFlexDirection}`;
            if (studioDisplay === 'flex' && studioJustifyContent) classes += ` studio-container--justify-${studioJustifyContent}`;
            if (studioDisplay === 'flex' && studioAlignItems) classes += ` studio-container--align-${studioAlignItems}`;
            
            return classes;
        };

        const blockProps = useBlockProps.save({
            className: generateClasses(),
            style: generateCSSVariables()
        });

        return el('div', blockProps,
            el(InnerBlocks.Content)
        );
    };

    // Register Studio Container Block
    registerBlockType('studio/container', {
        edit: StudioContainerEdit,
        save: StudioContainerSave
    });

    // Studio Text Block is registered in its own file: /blocks/studio-text/index.js
    // Studio Button Block will be added in Phase 2

    // Studio Blocks utilities
    window.StudioBlocks = {
        getTokens: function() {
            return window.studioBlocks?.tokens || {};
        },
        
        tokenToCSS: function(tokenType, tokenKey) {
            return `var(--wp--preset--${tokenType}--${tokenKey})`;
        }
    };

    // Console log for debugging
    console.log('🎨 Studio Blocks loaded!', {
        tokens: window.studioBlocks?.tokens,
        utilities: window.StudioBlocks
    });

})();
