/**
 * Studio Text Block
 * Enhanced text block with Studio typography presets and design token integration
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, RichText, BlockControls, AlignmentToolbar } = wp.blockEditor;
    const { PanelBody, SelectControl, ToolbarGroup, ToolbarButton } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useState } = wp.element;

    // Typography presets
    const TYPOGRAPHY_PRESETS = {
        'hero-title': {
            label: __('Hero Title', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--xxxl)',
            fontWeight: 'var(--wp--preset--font-weight--bold)',
            lineHeight: 'var(--wp--preset--line-height--tight)',
            tagName: 'h1'
        },
        'section-title': {
            label: __('Section Title', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--xxl)',
            fontWeight: 'var(--wp--preset--font-weight--semibold)',
            lineHeight: 'var(--wp--preset--line-height--normal)',
            tagName: 'h2'
        },
        'card-title': {
            label: __('Card Title', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--lg)',
            fontWeight: 'var(--wp--preset--font-weight--semibold)',
            lineHeight: 'var(--wp--preset--line-height--normal)',
            tagName: 'h3'
        },
        'body-text': {
            label: __('Body Text', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--md)',
            fontWeight: 'var(--wp--preset--font-weight--regular)',
            lineHeight: 'var(--wp--preset--line-height--relaxed)',
            tagName: 'p'
        },
        'caption': {
            label: __('Caption', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--sm)',
            fontWeight: 'var(--wp--preset--font-weight--regular)',
            lineHeight: 'var(--wp--preset--line-height--normal)',
            tagName: 'p'
        },
        'small-text': {
            label: __('Small Text', 'ds-studio'),
            fontSize: 'var(--wp--preset--font-size--xs)',
            fontWeight: 'var(--wp--preset--font-weight--regular)',
            lineHeight: 'var(--wp--preset--line-height--normal)',
            tagName: 'small'
        }
    };

    // Get Studio tokens from localized data
    const getStudioTokens = () => {
        return window.studioTokens || {
            colors: {},
            typography: {},
            spacing: {}
        };
    };

    // Generate CSS variables from attributes
    const generateCSSVariables = (attributes) => {
        const studioTokens = getStudioTokens();
        const preset = TYPOGRAPHY_PRESETS[attributes.studioTypographyPreset] || TYPOGRAPHY_PRESETS['body-text'];
        
        let styles = {
            fontSize: attributes.customFontSize || preset.fontSize,
            fontWeight: attributes.customFontWeight || preset.fontWeight,
            lineHeight: attributes.customLineHeight || preset.lineHeight,
            fontFamily: 'var(--wp--preset--font-family--primary)'
        };

        // Apply Studio text color
        if (attributes.studioTextColor && studioTokens.colors[attributes.studioTextColor]) {
            styles.color = `var(--wp--preset--color--${attributes.studioTextColor})`;
        }

        // Apply Studio background color
        if (attributes.studioBackgroundColor && studioTokens.colors[attributes.studioBackgroundColor]) {
            styles.backgroundColor = `var(--wp--preset--color--${attributes.studioBackgroundColor})`;
        }

        // Apply Studio spacing
        if (attributes.studioSpacing) {
            const spacing = attributes.studioSpacing;
            if (spacing.margin) {
                if (spacing.margin.top) styles.marginTop = `var(--wp--preset--spacing--${spacing.margin.top})`;
                if (spacing.margin.right) styles.marginRight = `var(--wp--preset--spacing--${spacing.margin.right})`;
                if (spacing.margin.bottom) styles.marginBottom = `var(--wp--preset--spacing--${spacing.margin.bottom})`;
                if (spacing.margin.left) styles.marginLeft = `var(--wp--preset--spacing--${spacing.margin.left})`;
            }
            if (spacing.padding) {
                if (spacing.padding.top) styles.paddingTop = `var(--wp--preset--spacing--${spacing.padding.top})`;
                if (spacing.padding.right) styles.paddingRight = `var(--wp--preset--spacing--${spacing.padding.right})`;
                if (spacing.padding.bottom) styles.paddingBottom = `var(--wp--preset--spacing--${spacing.padding.bottom})`;
                if (spacing.padding.left) styles.paddingLeft = `var(--wp--preset--spacing--${spacing.padding.left})`;
            }
        }

        return styles;
    };

    // Studio Color Picker Component
    const StudioColorPicker = ({ label, value, onChange, tokens }) => {
        const colors = tokens.colors || {};
        
        return el('div', { className: 'studio-color-picker' },
            el('label', { className: 'studio-control-label' }, label),
            el('div', { className: 'studio-color-grid' },
                el('button', {
                    className: `studio-color-swatch ${!value ? 'is-selected' : ''}`,
                    onClick: () => onChange(''),
                    title: __('Default', 'ds-studio')
                }, __('Default', 'ds-studio')),
                Object.keys(colors).map(colorKey => {
                    const color = colors[colorKey];
                    return el('button', {
                        key: colorKey,
                        className: `studio-color-swatch ${value === colorKey ? 'is-selected' : ''}`,
                        style: { backgroundColor: color.value || color },
                        onClick: () => onChange(colorKey),
                        title: color.name || colorKey
                    });
                })
            )
        );
    };

    // Studio Spacing Picker Component
    const StudioSpacingPicker = ({ label, value, onChange, tokens, side }) => {
        const spacing = tokens.spacing || {};
        
        return el('div', { className: 'studio-spacing-picker' },
            el('label', { className: 'studio-control-label' }, label),
            el('select', {
                value: value || '',
                onChange: (e) => onChange(e.target.value),
                className: 'studio-spacing-select'
            },
                el('option', { value: '' }, __('Default', 'ds-studio')),
                Object.keys(spacing).map(spacingKey => {
                    const spacingValue = spacing[spacingKey];
                    return el('option', {
                        key: spacingKey,
                        value: spacingKey
                    }, `${spacingKey.toUpperCase()} (${spacingValue.value || spacingValue})`);
                })
            )
        );
    };

    // Edit component
    const StudioTextEdit = (props) => {
        const { attributes, setAttributes, isSelected } = props;
        const { 
            content, 
            tagName, 
            studioTypographyPreset, 
            studioTextColor, 
            studioBackgroundColor,
            studioSpacing,
            customFontSize,
            customFontWeight,
            customLineHeight
        } = attributes;

        const studioTokens = getStudioTokens();
        const preset = TYPOGRAPHY_PRESETS[studioTypographyPreset] || TYPOGRAPHY_PRESETS['body-text'];
        const styles = generateCSSVariables(attributes);

        // Update tag name when preset changes
        const handlePresetChange = (newPreset) => {
            const newPresetData = TYPOGRAPHY_PRESETS[newPreset];
            setAttributes({
                studioTypographyPreset: newPreset,
                tagName: newPresetData ? newPresetData.tagName : 'p'
            });
        };

        // Handle spacing changes
        const handleSpacingChange = (type, side, value) => {
            const newSpacing = { ...studioSpacing };
            if (!newSpacing[type]) newSpacing[type] = {};
            newSpacing[type][side] = value;
            setAttributes({ studioSpacing: newSpacing });
        };

        return el(Fragment, {},
            // Block Controls
            el(BlockControls, {},
                el(ToolbarGroup, {},
                    Object.keys(TYPOGRAPHY_PRESETS).map(presetKey => {
                        const presetData = TYPOGRAPHY_PRESETS[presetKey];
                        return el(ToolbarButton, {
                            key: presetKey,
                            icon: 'editor-textcolor',
                            title: presetData.label,
                            isPressed: studioTypographyPreset === presetKey,
                            onClick: () => handlePresetChange(presetKey)
                        });
                    })
                )
            ),

            // Inspector Controls
            el(InspectorControls, {},
                // Typography Panel
                el(PanelBody, {
                    title: __('Studio Typography', 'ds-studio'),
                    initialOpen: true
                },
                    el(SelectControl, {
                        label: __('Typography Preset', 'ds-studio'),
                        value: studioTypographyPreset,
                        options: Object.keys(TYPOGRAPHY_PRESETS).map(key => ({
                            label: TYPOGRAPHY_PRESETS[key].label,
                            value: key
                        })),
                        onChange: handlePresetChange
                    }),
                    el('div', { className: 'studio-typography-preview', style: styles },
                        __('Preview: ', 'ds-studio') + preset.label
                    )
                ),

                // Colors Panel
                el(PanelBody, {
                    title: __('Studio Colors', 'ds-studio'),
                    initialOpen: false
                },
                    el(StudioColorPicker, {
                        label: __('Text Color', 'ds-studio'),
                        value: studioTextColor,
                        onChange: (color) => setAttributes({ studioTextColor: color }),
                        tokens: studioTokens
                    }),
                    el(StudioColorPicker, {
                        label: __('Background Color', 'ds-studio'),
                        value: studioBackgroundColor,
                        onChange: (color) => setAttributes({ studioBackgroundColor: color }),
                        tokens: studioTokens
                    })
                ),

                // Spacing Panel
                el(PanelBody, {
                    title: __('Studio Spacing', 'ds-studio'),
                    initialOpen: false
                },
                    el('h4', {}, __('Margin', 'ds-studio')),
                    el('div', { className: 'studio-spacing-grid' },
                        el(StudioSpacingPicker, {
                            label: __('Top', 'ds-studio'),
                            value: studioSpacing?.margin?.top || '',
                            onChange: (value) => handleSpacingChange('margin', 'top', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Right', 'ds-studio'),
                            value: studioSpacing?.margin?.right || '',
                            onChange: (value) => handleSpacingChange('margin', 'right', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Bottom', 'ds-studio'),
                            value: studioSpacing?.margin?.bottom || '',
                            onChange: (value) => handleSpacingChange('margin', 'bottom', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Left', 'ds-studio'),
                            value: studioSpacing?.margin?.left || '',
                            onChange: (value) => handleSpacingChange('margin', 'left', value),
                            tokens: studioTokens
                        })
                    ),
                    el('h4', {}, __('Padding', 'ds-studio')),
                    el('div', { className: 'studio-spacing-grid' },
                        el(StudioSpacingPicker, {
                            label: __('Top', 'ds-studio'),
                            value: studioSpacing?.padding?.top || '',
                            onChange: (value) => handleSpacingChange('padding', 'top', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Right', 'ds-studio'),
                            value: studioSpacing?.padding?.right || '',
                            onChange: (value) => handleSpacingChange('padding', 'right', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Bottom', 'ds-studio'),
                            value: studioSpacing?.padding?.bottom || '',
                            onChange: (value) => handleSpacingChange('padding', 'bottom', value),
                            tokens: studioTokens
                        }),
                        el(StudioSpacingPicker, {
                            label: __('Left', 'ds-studio'),
                            value: studioSpacing?.padding?.left || '',
                            onChange: (value) => handleSpacingChange('padding', 'left', value),
                            tokens: studioTokens
                        })
                    )
                )
            ),

            // Block Content
            el('div', {
                className: 'studio-text-block',
                style: styles
            },
                el(RichText, {
                    tagName: tagName,
                    className: 'studio-text-content',
                    value: content,
                    onChange: (newContent) => setAttributes({ content: newContent }),
                    placeholder: __('Start writing or type / to choose a block', 'ds-studio'),
                    allowedFormats: ['core/bold', 'core/italic', 'core/link', 'core/strikethrough'],
                    style: styles
                })
            )
        );
    };

    // Save component
    const StudioTextSave = (props) => {
        const { attributes } = props;
        const { content, tagName } = attributes;
        const styles = generateCSSVariables(attributes);

        return el('div', {
            className: 'studio-text-block',
            style: styles
        },
            el(RichText.Content, {
                tagName: tagName,
                className: 'studio-text-content',
                value: content,
                style: styles
            })
        );
    };

    // Register the block
    registerBlockType('studio/text', {
        edit: StudioTextEdit,
        save: StudioTextSave
    });

})();
