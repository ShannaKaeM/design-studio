/**
 * Design Studio - Unified Block Editor Panel
 * Combines Design Tokens, Block Styles, and Patterns into one organized interface
 */

(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { 
        PanelBody, 
        Button, 
        TextControl, 
        TextareaControl,
        Notice, 
        ColorPicker, 
        TabPanel,
        SelectControl,
        Card,
        CardBody,
        CardHeader,
        Flex,
        FlexItem
    } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;
    const { __ } = wp.i18n;

    // Main Design Studio Panel Component
    const DesignStudioPanel = () => {
        const [activeSection, setActiveSection] = useState('design-tokens');
        const [isLoading, setIsLoading] = useState(false);
        const [message, setMessage] = useState('');

        // Design Tokens State
        const [designTokens, setDesignTokens] = useState({});
        const [colors, setColors] = useState([]);
        const [typography, setTypography] = useState({});
        const [spacing, setSpacing] = useState({});

        // Block Styles State
        const [styleName, setStyleName] = useState('');
        const [utilityClasses, setUtilityClasses] = useState('');
        const [customCSS, setCustomCSS] = useState('');
        const [styleType, setStyleType] = useState('utility');
        const [description, setDescription] = useState('');
        const [savedStyles, setSavedStyles] = useState({});

        // Patterns State
        const [htmlInput, setHtmlInput] = useState('');
        const [convertedBlocks, setConvertedBlocks] = useState('');
        const [isConverting, setIsConverting] = useState(false);

        // Load data on mount
        useEffect(() => {
            loadDesignTokens();
            loadSavedStyles();
        }, []);

        // Load design tokens
        const loadDesignTokens = async () => {
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_studio_get_design_tokens',
                        nonce: dsStudio.nonce
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setDesignTokens(data.data);
                    setColors(Object.entries(data.data.colors || {}).map(([name, value]) => ({ name, value })));
                    setTypography(data.data.typography || {});
                    setSpacing(data.data.spacing || {});
                }
            } catch (error) {
                console.error('Error loading design tokens:', error);
            }
        };

        // Load saved block styles
        const loadSavedStyles = async () => {
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'get_block_styles',
                        nonce: dsBlockStyles?.nonce || dsStudio.nonce
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setSavedStyles(data.data || {});
                }
            } catch (error) {
                console.error('Error loading block styles:', error);
            }
        };

        // Save design tokens
        const saveDesignTokens = async () => {
            setIsLoading(true);
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_studio_save_design_tokens',
                        nonce: dsStudio.nonce,
                        tokens: JSON.stringify(designTokens)
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setMessage({ type: 'success', text: 'Design tokens saved and synced to theme.json!' });
                } else {
                    setMessage({ type: 'error', text: data.data || 'Failed to save design tokens' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Error saving design tokens' });
            }
            setIsLoading(false);
        };

        // Save block style
        const saveBlockStyle = async () => {
            if (!styleName.trim()) {
                setMessage({ type: 'error', text: 'Style name is required' });
                return;
            }

            setIsLoading(true);
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'save_block_style',
                        nonce: dsBlockStyles?.nonce || dsStudio.nonce,
                        style_name: styleName,
                        utility_classes: utilityClasses,
                        custom_css: customCSS,
                        style_type: styleType,
                        description: description
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setMessage({ type: 'success', text: 'Block style saved successfully!' });
                    loadSavedStyles();
                    // Clear form
                    setStyleName('');
                    setUtilityClasses('');
                    setCustomCSS('');
                    setDescription('');
                } else {
                    setMessage({ type: 'error', text: data.data || 'Failed to save block style' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Error saving block style' });
            }
            setIsLoading(false);
        };

        // Convert HTML to blocks
        const convertHtmlToBlocks = async () => {
            if (!htmlInput.trim()) {
                setMessage({ type: 'error', text: 'HTML input is required' });
                return;
            }

            setIsConverting(true);
            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'convert_html_to_blocks',
                        nonce: dsStudio.nonce,
                        html: htmlInput
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setConvertedBlocks(data.data);
                    setMessage({ type: 'success', text: 'HTML converted to blocks successfully!' });
                } else {
                    setMessage({ type: 'error', text: data.data || 'Failed to convert HTML' });
                }
            } catch (error) {
                setMessage({ type: 'error', text: 'Error converting HTML' });
            }
            setIsConverting(false);
        };

        // Section navigation with 4 separate panels
        const sections = [
            { name: 'design-tokens', title: 'Design Tokens', icon: '🎨', description: 'Colors, typography, spacing' },
            { name: 'styles', title: 'Block Styles', icon: '✨', description: 'Create and manage block styles' },
            { name: 'patterns', title: 'Patterns', icon: '📄', description: 'Pattern library and management' },
            { name: 'html-converter', title: 'HTML Converter', icon: '🔄', description: 'Convert HTML to blocks' }
        ];

        // Render Design Tokens Section
        const renderDesignTokensSection = () => (
            el('div', { className: 'ds-section ds-design-tokens' },
                // Colors Panel
                el(PanelBody, { 
                    title: 'Colors', 
                    initialOpen: true,
                    className: 'ds-panel-colors'
                },
                    colors.map((color, index) => 
                        el('div', { 
                            key: index, 
                            className: 'ds-color-item',
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '12px',
                                padding: '8px',
                                border: '1px solid #ddd',
                                borderRadius: '4px'
                            }
                        },
                            el('div', {
                                style: {
                                    width: '24px',
                                    height: '24px',
                                    backgroundColor: typeof color.value === 'string' ? color.value : color.value['500'] || '#ccc',
                                    borderRadius: '4px',
                                    marginRight: '8px',
                                    border: '1px solid #ccc'
                                }
                            }),
                            el('span', { style: { flex: 1, fontWeight: '500' } }, color.name),
                            el('code', { 
                                style: { 
                                    fontSize: '12px', 
                                    color: '#666',
                                    backgroundColor: '#f5f5f5',
                                    padding: '2px 6px',
                                    borderRadius: '3px'
                                } 
                            }, typeof color.value === 'string' ? color.value : JSON.stringify(color.value))
                        )
                    ),
                    el(Button, {
                        variant: 'secondary',
                        onClick: saveDesignTokens,
                        isBusy: isLoading,
                        style: { marginTop: '16px' }
                    }, 'Save & Sync to theme.json')
                ),

                // Typography Panel
                el(PanelBody, { 
                    title: 'Typography', 
                    initialOpen: false,
                    className: 'ds-panel-typography'
                },
                    el('p', { style: { color: '#666', fontSize: '14px' } }, 
                        'Typography tokens will be displayed here when available.'
                    )
                ),

                // Spacing Panel
                el(PanelBody, { 
                    title: 'Spacing', 
                    initialOpen: false,
                    className: 'ds-panel-spacing'
                },
                    el('p', { style: { color: '#666', fontSize: '14px' } }, 
                        'Spacing tokens will be displayed here when available.'
                    )
                )
            )
        );

        // Render Block Styles Section
        const renderStylesSection = () => (
            el('div', { className: 'ds-section ds-styles' },
                // Create Block Style Panel
                el(PanelBody, { 
                    title: 'Create Block Style', 
                    initialOpen: true,
                    className: 'ds-panel-create-style'
                },
                    el(TextControl, {
                        label: 'Style Name',
                        value: styleName,
                        onChange: setStyleName,
                        placeholder: 'e.g., card-primary, hero-large',
                        help: 'This will be the CSS class name (use lowercase and hyphens)'
                    }),
                    
                    el(SelectControl, {
                        label: 'Style Type',
                        value: styleType,
                        options: [
                            { label: 'Utility Classes', value: 'utility' },
                            { label: 'Custom CSS', value: 'css' },
                            { label: 'Combined', value: 'combined' }
                        ],
                        onChange: setStyleType
                    }),

                    (styleType === 'utility' || styleType === 'combined') && 
                    el(TextareaControl, {
                        label: 'Utility Classes',
                        value: utilityClasses,
                        onChange: setUtilityClasses,
                        placeholder: 'e.g., bg-primary text-white p-lg rounded-lg shadow-md',
                        help: 'Space-separated utility class names',
                        rows: 3
                    }),

                    (styleType === 'css' || styleType === 'combined') &&
                    el(TextareaControl, {
                        label: 'Custom CSS',
                        value: customCSS,
                        onChange: setCustomCSS,
                        placeholder: 'background: linear-gradient(45deg, #ff6b6b, #4ecdc4);',
                        help: 'Custom CSS rules (without selector)',
                        rows: 4
                    }),

                    el(TextControl, {
                        label: 'Description (Optional)',
                        value: description,
                        onChange: setDescription,
                        placeholder: 'Brief description of this style'
                    }),

                    el(Button, {
                        variant: 'primary',
                        onClick: saveBlockStyle,
                        isBusy: isLoading,
                        style: { marginTop: '12px' }
                    }, 'Create Block Style')
                ),

                // Saved Block Styles Panel
                el(PanelBody, { 
                    title: `Saved Block Styles (${Object.keys(savedStyles).length})`, 
                    initialOpen: false,
                    className: 'ds-panel-saved-styles'
                },
                    Object.keys(savedStyles).length === 0 ? 
                        el('p', { style: { color: '#666', fontSize: '14px' } }, 
                            'No saved block styles yet. Create your first style above!'
                        ) :
                        Object.entries(savedStyles).map(([name, style]) =>
                            el('div', { 
                                key: name,
                                className: 'ds-saved-style-item',
                                style: {
                                    padding: '12px',
                                    border: '1px solid #ddd',
                                    borderRadius: '4px',
                                    marginBottom: '8px'
                                }
                            },
                                el('div', { style: { fontWeight: '500', marginBottom: '4px' } }, name),
                                style.description && 
                                    el('div', { style: { fontSize: '12px', color: '#666', marginBottom: '4px' } }, style.description),
                                style.classes && 
                                    el('code', { 
                                        style: { 
                                            fontSize: '11px', 
                                            backgroundColor: '#f5f5f5',
                                            padding: '2px 4px',
                                            borderRadius: '2px',
                                            display: 'block',
                                            marginTop: '4px'
                                        } 
                                    }, style.classes)
                            )
                        )
                )
            )
        );

        // Render Patterns Section
        const renderPatternsSection = () => (
            el('div', { className: 'ds-section ds-patterns' },
                // Pattern Library Panel
                el(PanelBody, { 
                    title: 'Pattern Library', 
                    initialOpen: true,
                    className: 'ds-panel-pattern-library'
                },
                    el('p', { style: { color: '#666', fontSize: '14px', marginBottom: '16px' } }, 
                        'Browse and manage your design patterns.'
                    ),
                    el('div', { 
                        style: { 
                            padding: '20px', 
                            border: '2px dashed #ddd', 
                            borderRadius: '8px',
                            textAlign: 'center',
                            color: '#666'
                        } 
                    },
                        el('div', { style: { fontSize: '24px', marginBottom: '8px' } }, '📄'),
                        el('div', { style: { fontSize: '14px' } }, 'Pattern library coming soon'),
                        el('div', { style: { fontSize: '12px', marginTop: '4px' } }, 'Create, organize, and reuse design patterns')
                    )
                )
            )
        );

        // Render HTML Converter Section
        const renderHtmlConverterSection = () => (
            el('div', { className: 'ds-section ds-html-converter' },
                // HTML to Blocks Converter Panel
                el(PanelBody, { 
                    title: 'HTML to Blocks Converter', 
                    initialOpen: true,
                    className: 'ds-panel-html-converter'
                },
                    el(TextareaControl, {
                        label: 'HTML Input',
                        value: htmlInput,
                        onChange: setHtmlInput,
                        placeholder: 'Paste your HTML code here...',
                        rows: 8,
                        help: 'Enter HTML that you want to convert to WordPress blocks'
                    }),

                    el(Button, {
                        variant: 'primary',
                        onClick: convertHtmlToBlocks,
                        isBusy: isConverting,
                        style: { marginTop: '12px' }
                    }, 'Convert to Blocks'),

                    convertedBlocks && el('div', { style: { marginTop: '16px' } },
                        el('h4', { style: { margin: '0 0 8px 0' } }, 'Converted Blocks:'),
                        el(TextareaControl, {
                            value: convertedBlocks,
                            readOnly: true,
                            rows: 8,
                            help: 'Copy this JSON and paste it into your block editor'
                        })
                    )
                )
            )
        );

        // Main render
        return el('div', { className: 'ds-studio-unified-panel' },
            // Message Notice
            message && el(Notice, {
                status: message.type,
                onRemove: () => setMessage(''),
                style: { margin: '0 0 16px 0' }
            }, message.text),

            // Icon-based Section Navigation - Single Row
            el('div', { 
                className: 'ds-section-nav ds-icon-nav-row',
                style: {
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginBottom: '16px',
                    padding: '8px 12px',
                    backgroundColor: '#f6f7f7',
                    borderRadius: '6px',
                    border: '1px solid #ddd'
                }
            },
                sections.map(section => 
                    el('div', {
                        key: section.name,
                        className: `ds-nav-icon-item ${activeSection === section.name ? 'active' : ''}`,
                        onClick: () => setActiveSection(section.name),
                        title: `${section.title} - ${section.description}`,
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            width: '36px',
                            height: '36px',
                            borderRadius: '4px',
                            cursor: 'pointer',
                            transition: 'all 0.2s ease',
                            backgroundColor: activeSection === section.name ? '#007cba' : 'transparent',
                            color: activeSection === section.name ? 'white' : '#666',
                            border: '1px solid',
                            borderColor: activeSection === section.name ? '#007cba' : 'transparent',
                            position: 'relative'
                        }
                    },
                        el('span', { 
                            style: { 
                                fontSize: '16px',
                                lineHeight: '1'
                            } 
                        }, section.icon)
                    )
                )
            ),

            // Active Section Content
            el('div', { className: 'ds-section-content' },
                activeSection === 'design-tokens' && renderDesignTokensSection(),
                activeSection === 'styles' && renderStylesSection(),
                activeSection === 'patterns' && renderPatternsSection(),
                activeSection === 'html-converter' && renderHtmlConverterSection()
            )
        );
    };

    // Register the unified plugin
    registerPlugin('design-studio-unified', {
        render: () => {
            return el('div', {},
                el(PluginSidebarMoreMenuItem, {
                    target: 'design-studio-unified',
                    icon: 'art'
                }, 'Design Studio'),
                
                el(PluginSidebar, {
                    name: 'design-studio-unified',
                    title: 'Design Studio',
                    icon: 'art'
                },
                    el(DesignStudioPanel)
                )
            );
        }
    });

})();
