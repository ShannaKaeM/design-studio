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

    // Import Studio controls
    const StudioTypographyPresets = (window.studioControls && window.studioControls.StudioTypographyPresets) || null;

    // Get Studio tokens from localized data
    const getStudioTokens = () => {
        return window.studioTokens || {
            colors: {},
            typography: {},
            spacing: {}
        };
    };

    // Dynamic typography presets loaded from Studio Block Styles
    let TYPOGRAPHY_PRESETS = {};
    let PRESET_OPTIONS = [
        { label: 'Loading presets...', value: '' }
    ];

    // Load typography presets from Studio Block Styles
    const loadTypographyPresets = () => {
        if (!window.studioData) {
            console.warn('Studio data not available');
            return;
        }

        fetch(window.studioData.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'studio_get_block_styles',
                nonce: window.studioData.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.styles) {
                // Convert to preset format
                TYPOGRAPHY_PRESETS = {};
                PRESET_OPTIONS = [
                    { label: 'Select a preset...', value: '' }
                ];

                // Filter typography presets for studio/text block
                const textPresets = data.data.styles.filter(style => 
                    style.blockType === 'studio/text'
                );

                textPresets.forEach(preset => {
                    if (preset.name && preset.label) {
                        TYPOGRAPHY_PRESETS[preset.name] = {
                            label: preset.label,
                            description: preset.description || `Typography preset: ${preset.label}`,
                            css: preset.customCSS || preset.css
                        };

                        PRESET_OPTIONS.push({
                            label: preset.label,
                            value: preset.name
                        });
                    }
                });

                console.log('Loaded typography presets:', Object.keys(TYPOGRAPHY_PRESETS));
            }
        })
        .catch(error => {
            console.error('Error loading typography presets:', error);
        });
    };

    // Load presets when script loads
    loadTypographyPresets();

    // Build-time CSS generation using WordPress + Studio attributes
    const generateBuildTimeCSS = (attributes) => {
        const studioTokens = getStudioTokens();
        const cssRules = [];

        // Typography preset styles (from Studio Block Styles)
        if (attributes.typographyPreset && TYPOGRAPHY_PRESETS[attributes.typographyPreset]) {
            const preset = TYPOGRAPHY_PRESETS[attributes.typographyPreset];
            
            // Apply the preset's CSS directly
            if (preset.css) {
                // Parse CSS rules from preset
                const cssDeclarations = preset.css.split(';').filter(rule => rule.trim());
                cssDeclarations.forEach(declaration => {
                    if (declaration.trim()) {
                        cssRules.push(declaration.trim());
                    }
                });
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
            typographyPreset = '',
            tagName = 'p',
            textColor,
            backgroundColor,
            fontSize,
            customFontSize,
            customLineHeight
        } = attributes;

        // State for managing preset loading
        const [presetsLoaded, setPresetsLoaded] = useState(false);
        const [currentPresetOptions, setCurrentPresetOptions] = useState(PRESET_OPTIONS);

        // Load presets on component mount
        useEffect(() => {
            if (!presetsLoaded && window.studioData) {
                console.log('Loading typography presets...', window.studioData);
                
                fetch(window.studioData.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'studio_get_block_styles',
                        nonce: window.studioData.nonce
                    })
                })
                .then(response => {
                    console.log('AJAX Response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('AJAX Data:', data);
                    if (data.success && data.data.styles) {
                        console.log('All block styles:', data.data.styles);
                        
                        // Filter typography presets for studio/text block
                        const textPresets = data.data.styles.filter(style => 
                            style.blockType === 'studio/text'
                        );
                        
                        console.log('Filtered text presets:', textPresets);

                        // Convert to preset format
                        TYPOGRAPHY_PRESETS = {};
                        const newPresetOptions = [
                            { label: 'Select a preset...', value: '' }
                        ];

                        textPresets.forEach(preset => {
                            TYPOGRAPHY_PRESETS[preset.name] = {
                                label: preset.label,
                                description: preset.description || `Typography preset: ${preset.label}`,
                                css: preset.customCSS || preset.css
                            };

                            newPresetOptions.push({
                                label: preset.label,
                                value: preset.name
                            });
                        });

                        PRESET_OPTIONS = newPresetOptions;
                        setCurrentPresetOptions(newPresetOptions);
                        setPresetsLoaded(true);

                        console.log('Final TYPOGRAPHY_PRESETS:', TYPOGRAPHY_PRESETS);
                        console.log('Final PRESET_OPTIONS:', newPresetOptions);
                    } else {
                        console.error('AJAX call failed or no styles returned:', data);
                        setPresetsLoaded(true);
                    }
                })
                .catch(error => {
                    console.error('Error loading typography presets:', error);
                    setPresetsLoaded(true); // Set to true to prevent infinite loading
                });
            } else if (!window.studioData) {
                console.error('studioData not available');
                setPresetsLoaded(true);
            }
        }, [presetsLoaded]);

        const studioTokens = getStudioTokens();
        const buildTimeCSS = generateBuildTimeCSS(attributes);

        // Parse CSS string into style object for inline styles
        const styles = {
            // Reset default tag styles to prevent conflicts
            margin: 0,
            padding: 0,
            fontSize: 'inherit',
            fontWeight: 'inherit',
            lineHeight: 'inherit',
            color: 'inherit'
        };
        
        buildTimeCSS.split('; ').forEach(rule => {
            if (rule.trim()) {
                const [property, value] = rule.split(': ');
                if (property && value) {
                    const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                    styles[camelProperty] = value;
                }
            }
        });

        return el(Fragment, {},
            el(InspectorControls, {},
                el(PanelBody, {
                    title: __('Typography', 'studio'),
                    initialOpen: true
                },
                    // Typography Presets Component
                    StudioTypographyPresets && el(StudioTypographyPresets, {
                        selectedPreset: typographyPreset,
                        setAttributes: setAttributes,
                        onChange: (newPreset) => setAttributes({ typographyPreset: newPreset })
                    }),
                    
                    Divider && el(Divider),
                    
                    // HTML Tag Selector (for semantic markup)
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
                        help: __('Choose HTML tag for semantic markup (separate from visual styling)', 'studio')
                    }),
                    
                    Divider && el(Divider)
                    
                )
            ),
            
            // Apply styles directly to RichText element
            el(RichText, {
                tagName: tagName,
                className: `studio-text-content ${typographyPreset ? `is-style-${typographyPreset}` : ''}`.trim(),
                placeholder: __('Start writing...', 'studio'),
                value: content,
                onChange: (newContent) => setAttributes({ content: newContent }),
                allowedFormats: ['core/bold', 'core/italic', 'core/link'],
                style: styles
            })
        );
    };

    // Save component with build-time CSS
    const StudioTextSave = (props) => {
        const { attributes } = props;
        const { content, typographyPreset, tagName } = attributes;

        // Generate build-time CSS for frontend
        const buildTimeCSS = generateBuildTimeCSS(attributes);

        // Parse CSS string into style object for inline styles
        const styles = {
            // Reset default tag styles to prevent conflicts
            margin: 0,
            padding: 0,
            fontSize: 'inherit',
            fontWeight: 'inherit',
            lineHeight: 'inherit',
            color: 'inherit'
        };
        
        buildTimeCSS.split('; ').forEach(rule => {
            if (rule.trim()) {
                const [property, value] = rule.split(': ');
                if (property && value) {
                    const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                    styles[camelProperty] = value;
                }
            }
        });

        // Apply styles directly to the content element, not a wrapper
        return el(RichText.Content, {
            tagName: tagName,
            className: `studio-text-content ${typographyPreset ? `is-style-${typographyPreset}` : ''}`.trim(),
            value: content,
            style: styles
        });
    };

    // Register the enhanced block
    registerBlockType('studio/text', {
        title: __('Studio Text', 'studio'),
        description: __('Enhanced text block with typography presets', 'studio'),
        category: 'studio-blocks',
        icon: 'editor-textcolor',
        supports: {
            html: false,
            className: true,
            customClassName: true
        },
        attributes: {
            content: {
                type: 'string',
                source: 'html',
                selector: 'h1,h2,h3,h4,h5,h6,p,span,div,small',
                default: ''
            },
            typographyPreset: {
                type: 'string',
                default: 'body-text'
            },
            tagName: {
                type: 'string',
                default: 'p'
            },
            textColor: {
                type: 'string'
            },
            backgroundColor: {
                type: 'string'
            },
            fontSize: {
                type: 'string'
            },
            style: {
                type: 'object',
                default: {
                    spacing: {
                        margin: {},
                        padding: {}
                    }
                }
            },
            customFontSize: {
                type: 'number'
            },
            customLineHeight: {
                type: 'number'
            }
        },
        edit: StudioTextEdit,
        save: StudioTextSave
    });

})();
