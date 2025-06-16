/**
 * Studio Text Block
 * Text block with typography presets and design token integration
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, RichText, BlockControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, ToggleControl, __experimentalDivider: Divider } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useState, useEffect } = wp.element;

    // Get Studio tokens from localized data
    const getStudioTokens = () => {
        return window.studioTokens || {
            colors: {},
            typography: {},
            spacing: {}
        };
    };

    // Dynamic typography presets
    let TYPOGRAPHY_PRESETS = {};
    let PRESET_OPTIONS = [
        { label: __('Select a preset...', 'studio'), value: '' }
    ];

    // Load typography presets from theme.json
    const loadTypographyPresets = () => {
        // Get presets from localized data
        if (window.studioPresets && window.studioPresets.typography) {
            TYPOGRAPHY_PRESETS = window.studioPresets.typography;
            
            PRESET_OPTIONS = [
                { label: __('Select a preset...', 'studio'), value: '' }
            ];

            Object.keys(TYPOGRAPHY_PRESETS).forEach(key => {
                PRESET_OPTIONS.push({
                    label: TYPOGRAPHY_PRESETS[key].label || key,
                    value: key
                });
            });
        }
    };

    // Load presets when script loads
    loadTypographyPresets();

    // Build CSS from attributes
    const generateBuildTimeCSS = (attributes) => {
        const studioTokens = getStudioTokens();
        const cssRules = [];

        // Typography preset styles
        if (attributes.typographyPreset && TYPOGRAPHY_PRESETS[attributes.typographyPreset]) {
            const preset = TYPOGRAPHY_PRESETS[attributes.typographyPreset];
            
            if (preset.fontSize) cssRules.push(`font-size: ${preset.fontSize}`);
            if (preset.fontWeight) cssRules.push(`font-weight: ${preset.fontWeight}`);
            if (preset.lineHeight) cssRules.push(`line-height: ${preset.lineHeight}`);
            if (preset.letterSpacing) cssRules.push(`letter-spacing: ${preset.letterSpacing}`);
            if (preset.textTransform) cssRules.push(`text-transform: ${preset.textTransform}`);
        }

        // Color controls
        if (attributes.textColor) {
            const colorValue = studioTokens.colors[attributes.textColor]?.value || attributes.textColor;
            cssRules.push(`color: ${colorValue}`);
        }

        if (attributes.backgroundColor) {
            const colorValue = studioTokens.colors[attributes.backgroundColor]?.value || attributes.backgroundColor;
            cssRules.push(`background-color: ${colorValue}`);
        }

        // Custom font size
        if (attributes.customFontSize) {
            cssRules.push(`font-size: ${attributes.customFontSize}px`);
        }

        // Custom line height
        if (attributes.customLineHeight) {
            cssRules.push(`line-height: ${attributes.customLineHeight}`);
        }

        return cssRules.join('; ');
    };

    // Edit component
    const StudioTextEdit = (props) => {
        const { attributes, setAttributes } = props;
        const { content, typographyPreset, tagName = 'p', textColor, backgroundColor } = attributes;

        // Generate inline styles
        const buildTimeCSS = generateBuildTimeCSS(attributes);
        const styles = {};
        
        buildTimeCSS.split('; ').forEach(rule => {
            if (rule.trim()) {
                const [property, value] = rule.split(': ');
                if (property && value) {
                    const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                    styles[camelProperty] = value;
                }
            }
        });

        const blockProps = useBlockProps({
            className: `studio-text ${typographyPreset ? `has-preset-${typographyPreset}` : ''}`.trim(),
            style: styles
        });

        return el(Fragment, {},
            el(InspectorControls, {},
                el(PanelBody, { 
                    title: __('Typography Preset', 'studio'),
                    initialOpen: true
                },
                    el(SelectControl, {
                        label: __('Typography Preset', 'studio'),
                        value: typographyPreset || '',
                        options: PRESET_OPTIONS,
                        onChange: (newPreset) => setAttributes({ typographyPreset: newPreset }),
                        help: __('Select a typography preset to apply consistent styling', 'studio')
                    })
                ),
                
                el(PanelBody, { 
                    title: __('HTML Tag', 'studio'),
                    initialOpen: false
                },
                    el(SelectControl, {
                        label: __('HTML Tag', 'studio'),
                        value: tagName,
                        options: [
                            { label: 'Paragraph (p)', value: 'p' },
                            { label: 'Heading 1 (h1)', value: 'h1' },
                            { label: 'Heading 2 (h2)', value: 'h2' },
                            { label: 'Heading 3 (h3)', value: 'h3' },
                            { label: 'Heading 4 (h4)', value: 'h4' },
                            { label: 'Heading 5 (h5)', value: 'h5' },
                            { label: 'Heading 6 (h6)', value: 'h6' },
                            { label: 'Span', value: 'span' },
                            { label: 'Div', value: 'div' },
                            { label: 'Small', value: 'small' }
                        ],
                        onChange: (newTag) => setAttributes({ tagName: newTag }),
                        help: __('Choose HTML tag for semantic markup', 'studio')
                    })
                )
            ),
            
            el('div', blockProps,
                el(RichText, {
                    tagName: tagName,
                    placeholder: __('Start writing...', 'studio'),
                    value: content,
                    onChange: (newContent) => setAttributes({ content: newContent }),
                    allowedFormats: ['core/bold', 'core/italic', 'core/link', 'core/strikethrough', 'core/underline']
                })
            )
        );
    };

    // Save component
    const StudioTextSave = (props) => {
        const { attributes } = props;
        const { content, typographyPreset, tagName = 'p' } = attributes;

        // Generate build-time CSS for frontend
        const buildTimeCSS = generateBuildTimeCSS(attributes);

        // Parse CSS string into style object
        const styles = {};
        
        buildTimeCSS.split('; ').forEach(rule => {
            if (rule.trim()) {
                const [property, value] = rule.split(': ');
                if (property && value) {
                    const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                    styles[camelProperty] = value;
                }
            }
        });

        const blockProps = useBlockProps.save({
            className: `studio-text ${typographyPreset ? `has-preset-${typographyPreset}` : ''}`.trim(),
            style: styles
        });

        return el('div', blockProps,
            el(RichText.Content, {
                tagName: tagName,
                value: content
            })
        );
    };

    // Register the block
    registerBlockType('studio/text', {
        edit: StudioTextEdit,
        save: StudioTextSave
    });

})();
