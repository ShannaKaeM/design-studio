/**
 * Studio Text Block
 * Text block with typography presets and design token integration
 */

(function() {
    'use strict';

    const { registerBlockType } = wp.blocks;
    const { InspectorControls, RichText, BlockControls, useBlockProps } = wp.blockEditor;
    const { PanelBody, SelectControl, ToggleControl, __experimentalDivider: Divider, Button, TextControl, TextareaControl, Modal, Notice } = wp.components;
    const { __ } = wp.i18n;
    const { createElement: el, Fragment, useState, useEffect } = wp.element;
    const { useSelect } = wp.data;
    const apiFetch = wp.apiFetch;

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

        // State for Save Block Style modal
        const [isStyleModalOpen, setIsStyleModalOpen] = useState(false);
        const [styleName, setStyleName] = useState('');
        const [styleLabel, setStyleLabel] = useState('');
        const [styleDescription, setStyleDescription] = useState('');
        const [isSaving, setIsSaving] = useState(false);
        const [notice, setNotice] = useState(null);

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

        // Handle saving block style
        const handleSaveStyle = async () => {
            if (!styleName || !styleLabel) {
                setNotice({ type: 'error', message: __('Please provide both a name and label for the style.', 'studio') });
                return;
            }

            setIsSaving(true);
            setNotice(null);

            try {
                // Prepare the block style data
                const styleData = {
                    name: styleName,
                    label: styleLabel,
                    blockType: 'studio/text',
                    description: styleDescription,
                    attributes: attributes,
                    classes: `is-style-${styleName}`,
                    tagName: tagName,
                    customCSS: buildTimeCSS,
                    type: 'css'
                };

                // Send to server via AJAX
                const formData = new FormData();
                formData.append('action', 'studio_save_block_style');
                formData.append('nonce', window.studioAdmin?.nonce || '');
                formData.append('styleKey', `studio-text-${styleName}`);
                formData.append('styleData', JSON.stringify(styleData));

                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    setNotice({ type: 'success', message: __('Block style saved successfully!', 'studio') });
                    setTimeout(() => {
                        setIsStyleModalOpen(false);
                        setStyleName('');
                        setStyleLabel('');
                        setStyleDescription('');
                        setNotice(null);
                    }, 2000);
                } else {
                    throw new Error(result.data?.message || __('Failed to save block style', 'studio'));
                }
            } catch (error) {
                setNotice({ type: 'error', message: error.message });
            } finally {
                setIsSaving(false);
            }
        };

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
                ),

                el(PanelBody, {
                    title: __('Save as Style', 'studio'),
                    initialOpen: false
                },
                    el(Button, {
                        variant: 'secondary',
                        onClick: () => setIsStyleModalOpen(true),
                        style: { width: '100%' }
                    }, __('Save as Block Style', 'studio'))
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
            ),

            isStyleModalOpen && el(Modal, {
                title: __('Save Block Style', 'studio'),
                onRequestClose: () => setIsStyleModalOpen(false),
                style: { maxWidth: '500px' }
            },
                notice && el(Notice, {
                    status: notice.type,
                    isDismissible: false
                }, notice.message),

                el(TextControl, {
                    label: __('Style Name (lowercase, no spaces)', 'studio'),
                    value: styleName,
                    onChange: setStyleName,
                    help: __('Used internally, e.g., "hero-title"', 'studio'),
                    pattern: '[a-z0-9-]+'
                }),

                el(TextControl, {
                    label: __('Style Label', 'studio'),
                    value: styleLabel,
                    onChange: setStyleLabel,
                    help: __('Display name, e.g., "Hero Title"', 'studio')
                }),

                el(TextareaControl, {
                    label: __('Description (optional)', 'studio'),
                    value: styleDescription,
                    onChange: setStyleDescription,
                    help: __('Describe when to use this style', 'studio'),
                    rows: 3
                }),

                el('div', { style: { marginTop: '20px', display: 'flex', gap: '10px', justifyContent: 'flex-end' } },
                    el(Button, {
                        variant: 'tertiary',
                        onClick: () => setIsStyleModalOpen(false),
                        disabled: isSaving
                    }, __('Cancel', 'studio')),
                    
                    el(Button, {
                        variant: 'primary',
                        onClick: handleSaveStyle,
                        isBusy: isSaving,
                        disabled: isSaving || !styleName || !styleLabel
                    }, __('Save Style', 'studio'))
                )
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
