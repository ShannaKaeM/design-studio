/**
 * Studio Text Block - Phase 2A Enhanced
 * Enhanced text block with tag selector, block transformation, and build-time CSS generation
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, RichText, BlockControls } = wp.blockEditor;
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

    // Enhanced Typography presets with complete definitions
    const TYPOGRAPHY_PRESETS = {
        'hero-title': {
            label: 'Hero Title',
            tag: 'h1',
            fontSize: 'xxxl',
            fontWeight: 'bold',
            lineHeight: 'tight',
            description: 'Large, bold headlines for hero sections'
        },
        'section-title': {
            label: 'Section Title',
            tag: 'h2',
            fontSize: 'xxl',
            fontWeight: 'semibold',
            lineHeight: 'normal',
            description: 'Section headings and major content divisions'
        },
        'card-title': {
            label: 'Card Title',
            tag: 'h3',
            fontSize: 'lg',
            fontWeight: 'semibold',
            lineHeight: 'normal',
            description: 'Card headings and component titles'
        },
        'body-text': {
            label: 'Body Text',
            tag: 'p',
            fontSize: 'md',
            fontWeight: 'regular',
            lineHeight: 'relaxed',
            description: 'Standard paragraph text and content'
        },
        'caption': {
            label: 'Caption',
            tag: 'p',
            fontSize: 'sm',
            fontWeight: 'regular',
            lineHeight: 'normal',
            description: 'Image captions and supplementary text'
        },
        'small-text': {
            label: 'Small Text',
            tag: 'small',
            fontSize: 'xs',
            fontWeight: 'regular',
            lineHeight: 'normal',
            description: 'Fine print and legal text'
        }
    };

    // Build-time CSS generation using WordPress + Studio attributes
    const generateBuildTimeCSS = (attributes) => {
        const studioTokens = getStudioTokens();
        const cssRules = [];

        // Typography preset styles (Studio tokens applied via build-time CSS)
        if (attributes.typographyPreset && TYPOGRAPHY_PRESETS[attributes.typographyPreset]) {
            const preset = TYPOGRAPHY_PRESETS[attributes.typographyPreset];

            if (preset.fontSize) {
                const fontSize = studioTokens.typography && studioTokens.typography.fontSizes && studioTokens.typography.fontSizes[preset.fontSize] 
                    ? (studioTokens.typography.fontSizes[preset.fontSize].value || studioTokens.typography.fontSizes[preset.fontSize])
                    : preset.fontSize;
                cssRules.push(`font-size: ${fontSize}`);
            }

            if (preset.fontWeight) {
                cssRules.push(`font-weight: ${preset.fontWeight}`);
            }

            if (preset.lineHeight) {
                cssRules.push(`line-height: ${preset.lineHeight}`);
            }

            if (preset.letterSpacing) {
                cssRules.push(`letter-spacing: ${preset.letterSpacing}`);
            }
        }

        // WordPress color controls (populated by Studio tokens via theme.json)
        if (attributes.textColor) {
            cssRules.push(`color: var(--wp--preset--color--${attributes.textColor})`);
        }

        if (attributes.backgroundColor) {
            cssRules.push(`background-color: var(--wp--preset--color--${attributes.backgroundColor})`);
        }

        // WordPress typography controls (populated by Studio tokens via theme.json)
        if (attributes.fontSize) {
            cssRules.push(`font-size: var(--wp--preset--font-size--${attributes.fontSize})`);
        }

        // WordPress spacing controls (populated by Studio tokens via theme.json)
        if (attributes.style && attributes.style.spacing) {
            const spacing = attributes.style.spacing;

            // Margin
            if (spacing.margin) {
                Object.keys(spacing.margin).forEach(side => {
                    if (spacing.margin[side]) {
                        cssRules.push(`margin-${side}: ${spacing.margin[side]}`);
                    }
                });
            }

            // Padding
            if (spacing.padding) {
                Object.keys(spacing.padding).forEach(side => {
                    if (spacing.padding[side]) {
                        cssRules.push(`padding-${side}: ${spacing.padding[side]}`);
                    }
                });
            }
        }

        // Custom overrides (for fine-tuning)
        if (attributes.customFontSize) {
            cssRules.push(`font-size: ${attributes.customFontSize}px`);
        }

        if (attributes.customLineHeight) {
            cssRules.push(`line-height: ${attributes.customLineHeight}`);
        }

        return cssRules.join('; ');
    };

    // Edit component
    const StudioTextEdit = (props) => {
        const { attributes, setAttributes, isSelected } = props;
        const { 
            content, 
            typographyPreset = 'body-text',
            textColor,
            backgroundColor,
            fontSize,
            customFontSize,
            customLineHeight
        } = attributes;

        const studioTokens = getStudioTokens();
        const buildTimeCSS = generateBuildTimeCSS(attributes);
        const styles = {};

        // Parse CSS string into style object for editor preview
        buildTimeCSS.split('; ').forEach(rule => {
            if (rule.trim()) {
                const [property, value] = rule.split(': ');
                if (property && value) {
                    styles[property.trim()] = value.trim();
                }
            }
        });

        return el(Fragment, {},
            // Inspector Controls
            el(InspectorControls, {},
                // Typography Panel
                el(PanelBody, {
                    title: __('Typography', 'studio'),
                    initialOpen: true
                },
                    el(SelectControl, {
                        label: __('Typography Preset', 'studio'),
                        value: typographyPreset,
                        options: Object.keys(TYPOGRAPHY_PRESETS).map(key => ({
                            label: TYPOGRAPHY_PRESETS[key].label,
                            value: key
                        })),
                        onChange: (newPreset) => setAttributes({ typographyPreset: newPreset }),
                        help: __('Choose the typography preset for this text', 'studio')
                    })
                )
            ),

            // Block Content
            el('div', {
                className: 'studio-text-block',
                style: styles,
                'data-preset': typographyPreset
            },
                el(RichText, {
                    tagName: TYPOGRAPHY_PRESETS[typographyPreset].tag,
                    className: 'studio-text-content',
                    placeholder: __('Start writing...', 'studio'),
                    value: content,
                    onChange: (newContent) => setAttributes({ content: newContent }),
                    allowedFormats: ['core/bold', 'core/italic', 'core/link']
                })
            )
        );
    };

    // Save component with build-time CSS
    const StudioTextSave = (props) => {
        const { attributes } = props;
        const { content, typographyPreset } = attributes;

        // Generate build-time CSS for frontend
        const buildTimeCSS = generateBuildTimeCSS(attributes);

        // Parse CSS string into style object for inline styles
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

        return el('div', {
            className: 'studio-text-block',
            style: styles,
            'data-preset': typographyPreset
        },
            el(RichText.Content, {
                tagName: TYPOGRAPHY_PRESETS[typographyPreset].tag,
                className: 'studio-text-content',
                value: content
            })
        );
    };

    // Register the enhanced block
    registerBlockType('studio/text', {
        edit: StudioTextEdit,
        save: StudioTextSave
    });

})();
