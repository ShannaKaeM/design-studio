/**
 * The Studio - Design System Management Panel
 * A clean, unified interface for managing design tokens, styles, patterns, and tools
 */

(function() {
    'use strict';

    // Wait for WordPress to be ready
    if (!window.wp || !window.wp.plugins) {
        console.error('WordPress plugins API not available');
        return;
    }

    const { __ } = wp.i18n;
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, TextControl, TextareaControl, Notice, ColorPicker } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    /**
     * Studio Navigation Component
     * Clean icon-based navigation between panels
     */
    function StudioNavigation({ activePanel, onPanelChange }) {
        const panels = [
            { id: 'tokens', icon: '🎨', title: 'Design Tokens', description: 'Colors, typography, spacing' },
            { id: 'styles', icon: '✨', title: 'Block Styles', description: 'Create and manage block styles' },
            { id: 'patterns', icon: '📋', title: 'Patterns', description: 'Pattern library and management' },
            { id: 'converter', icon: '🔄', title: 'HTML Converter', description: 'Convert HTML to blocks' }
        ];

        return el('div', { className: 'studio-navigation' },
            panels.map(panel => 
                el('button', {
                    key: panel.id,
                    className: `studio-nav-item ${activePanel === panel.id ? 'active' : ''}`,
                    onClick: () => onPanelChange(panel.id),
                    title: `${panel.title} - ${panel.description}`,
                    'aria-label': panel.title
                }, panel.icon)
            )
        );
    }

    /**
     * Design Tokens Panel
     * Manage colors, typography, and spacing tokens
     */
    function DesignTokensPanel() {
        const [tokens, setTokens] = useState({
            colors: {
                primary: { '50': '#eff6ff', '500': '#3b82f6', '900': '#1e3a8a' },
                neutral: { '50': '#f9fafb', '500': '#6b7280', '900': '#111827' },
                semantic: { success: '#10b981', warning: '#f59e0b', error: '#ef4444' }
            }
        });
        const [isLoading, setIsLoading] = useState(false);

        const saveTokens = async () => {
            setIsLoading(true);
            try {
                const response = await fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_studio_save_tokens',
                        nonce: window.dsStudio?.nonce || '',
                        tokens: JSON.stringify(tokens)
                    })
                });
                const data = await response.json();
                if (data.success) {
                    console.log('Tokens saved successfully');
                }
            } catch (error) {
                console.error('Failed to save tokens:', error);
            } finally {
                setIsLoading(false);
            }
        };

        return el('div', { className: 'studio-panel studio-tokens-panel' },
            el(PanelBody, { title: 'Colors', initialOpen: true },
                Object.entries(tokens.colors).map(([colorName, shades]) =>
                    el('div', { key: colorName, className: 'studio-color-group' },
                        el('h4', { className: 'studio-color-name' }, colorName),
                        el('div', { className: 'studio-color-swatches' },
                            typeof shades === 'object' && shades !== null ?
                                Object.entries(shades).map(([shade, value]) =>
                                    el('div', { 
                                        key: shade, 
                                        className: 'studio-color-swatch',
                                        style: { backgroundColor: value },
                                        title: `${colorName}-${shade}: ${value}`
                                    })
                                ) :
                                el('div', { 
                                    className: 'studio-color-swatch',
                                    style: { backgroundColor: shades },
                                    title: `${colorName}: ${shades}`
                                })
                        )
                    )
                )
            ),
            el(PanelBody, { title: 'Typography', initialOpen: false },
                el('p', { className: 'studio-placeholder' }, 'Typography tokens coming soon...')
            ),
            el(PanelBody, { title: 'Spacing', initialOpen: false },
                el('p', { className: 'studio-placeholder' }, 'Spacing tokens coming soon...')
            ),
            el(Button, {
                isPrimary: true,
                isBusy: isLoading,
                onClick: saveTokens,
                className: 'studio-save-button'
            }, isLoading ? 'Saving...' : 'Save & Sync to theme.json')
        );
    }

    /**
     * Block Styles Panel
     * Create and manage custom block styles
     */
    function BlockStylesPanel() {
        const [styleName, setStyleName] = useState('');
        const [utilityClasses, setUtilityClasses] = useState('');
        const [description, setDescription] = useState('');
        const [isLoading, setIsLoading] = useState(false);

        const createStyle = async () => {
            if (!styleName.trim()) return;
            
            setIsLoading(true);
            try {
                const response = await fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'save_block_style',
                        nonce: window.dsBlockStyles?.nonce || '',
                        styleName,
                        utilityClasses,
                        description,
                        styleType: 'utility'
                    })
                });
                const data = await response.json();
                if (data.success) {
                    setStyleName('');
                    setUtilityClasses('');
                    setDescription('');
                    console.log('Block style created successfully');
                }
            } catch (error) {
                console.error('Failed to create block style:', error);
            } finally {
                setIsLoading(false);
            }
        };

        return el('div', { className: 'studio-panel studio-styles-panel' },
            el(PanelBody, { title: 'Create Block Style', initialOpen: true },
                el(TextControl, {
                    label: 'Style Name',
                    value: styleName,
                    onChange: setStyleName,
                    placeholder: 'e.g., card-primary, hero-large',
                    help: 'This will be the CSS class name (use lowercase and hyphens)'
                }),
                el(TextareaControl, {
                    label: 'Utility Classes',
                    value: utilityClasses,
                    onChange: setUtilityClasses,
                    placeholder: 'e.g., bg-primary text-white p-lg rounded-lg shadow-md',
                    rows: 3,
                    help: 'Space-separated utility class names'
                }),
                el(TextControl, {
                    label: 'Description (Optional)',
                    value: description,
                    onChange: setDescription,
                    placeholder: 'Brief description of this style'
                }),
                el(Button, {
                    isPrimary: true,
                    isBusy: isLoading,
                    onClick: createStyle,
                    disabled: !styleName.trim()
                }, isLoading ? 'Creating...' : 'Create Block Style')
            ),
            el(PanelBody, { title: 'Saved Block Styles', initialOpen: false },
                el('p', { className: 'studio-placeholder' }, 'Saved styles will appear here...')
            )
        );
    }

    /**
     * Patterns Panel
     * Manage design patterns and components
     */
    function PatternsPanel() {
        return el('div', { className: 'studio-panel studio-patterns-panel' },
            el(PanelBody, { title: 'Pattern Library', initialOpen: true },
                el('div', { className: 'studio-coming-soon' },
                    el('div', { className: 'studio-icon' }, '📋'),
                    el('h3', null, 'Pattern library coming soon'),
                    el('p', null, 'Create, organize, and reuse design patterns')
                )
            )
        );
    }

    /**
     * HTML Converter Panel
     * Convert HTML to WordPress blocks
     */
    function ConverterPanel() {
        const [htmlInput, setHtmlInput] = useState('');
        const [convertedBlocks, setConvertedBlocks] = useState('');
        const [isConverting, setIsConverting] = useState(false);

        const convertHtml = async () => {
            if (!htmlInput.trim()) return;
            
            setIsConverting(true);
            try {
                const response = await fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_studio_convert_html',
                        nonce: window.dsStudio?.nonce || '',
                        html: htmlInput
                    })
                });
                const data = await response.json();
                if (data.success) {
                    setConvertedBlocks(data.data.blocks);
                }
            } catch (error) {
                console.error('Failed to convert HTML:', error);
            } finally {
                setIsConverting(false);
            }
        };

        return el('div', { className: 'studio-panel studio-converter-panel' },
            el(PanelBody, { title: 'HTML to Blocks Converter', initialOpen: true },
                el(TextareaControl, {
                    label: 'HTML Input',
                    value: htmlInput,
                    onChange: setHtmlInput,
                    placeholder: 'Paste your HTML code here...',
                    rows: 8,
                    help: 'Enter HTML that you want to convert to WordPress blocks'
                }),
                el(Button, {
                    isPrimary: true,
                    isBusy: isConverting,
                    onClick: convertHtml,
                    disabled: !htmlInput.trim()
                }, isConverting ? 'Converting...' : 'Convert to Blocks'),
                convertedBlocks && el('div', { className: 'studio-conversion-result' },
                    el('label', { className: 'studio-result-label' }, 'Generated Blocks JSON:'),
                    el('textarea', {
                        value: convertedBlocks,
                        readOnly: true,
                        rows: 12,
                        className: 'studio-result-textarea'
                    }),
                    el(Button, {
                        isSecondary: true,
                        onClick: () => navigator.clipboard?.writeText(convertedBlocks)
                    }, 'Copy to Clipboard')
                )
            )
        );
    }

    /**
     * Main Studio Component
     * Orchestrates the entire studio interface
     */
    function Studio() {
        const [activePanel, setActivePanel] = useState('tokens');

        const renderPanel = () => {
            switch (activePanel) {
                case 'tokens': return el(DesignTokensPanel);
                case 'styles': return el(BlockStylesPanel);
                case 'patterns': return el(PatternsPanel);
                case 'converter': return el(ConverterPanel);
                default: return el(DesignTokensPanel);
            }
        };

        return el('div', { className: 'studio-container' },
            el(StudioNavigation, {
                activePanel,
                onPanelChange: setActivePanel
            }),
            renderPanel()
        );
    }

    /**
     * Register The Studio Plugin
     */
    registerPlugin('the-studio', {
        render: () => el(wp.element.Fragment, null,
            el(PluginSidebarMoreMenuItem, {
                target: 'the-studio',
                icon: 'admin-customizer'
            }, __('The Studio', 'ds-studio')),
            
            el(PluginSidebar, {
                name: 'the-studio',
                title: __('The Studio', 'ds-studio'),
                icon: 'admin-customizer',
                className: 'studio-sidebar'
            }, el(Studio))
        )
    });

    console.log('The Studio registered successfully');

})();
