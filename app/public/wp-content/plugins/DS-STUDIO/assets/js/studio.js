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
    const { PanelBody, TextControl, TextareaControl, Notice, ColorPicker } = wp.components;
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
        const [tokens, setTokens] = useState(null);
        const [loading, setLoading] = useState(true);
        const [openSections, setOpenSections] = useState({ blocksy: true, semantic: false });
        const [editingColor, setEditingColor] = useState(null);
        const [showAddNew, setShowAddNew] = useState(false);
        const [newColor, setNewColor] = useState({ name: '', slug: '', color: '#000000' });

        useEffect(() => {
            loadTokens();
        }, []);

        const loadTokens = async () => {
            setLoading(true);
            try {
                const response = await fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_get_design_tokens',
                        nonce: window.dsStudio?.nonce || ''
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setTokens(data.data);
                }
            } catch (error) {
                console.error('Error loading tokens:', error);
            } finally {
                setLoading(false);
            }
        };

        const saveTokens = (tokens) => {
            fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_save_design_tokens',
                    nonce: window.dsStudio?.nonce || '',
                    tokens: JSON.stringify(tokens)
                })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTokens(tokens);
                    console.log('Tokens saved successfully');
                } else {
                    console.error('Failed to save tokens:', data.data);
                }
            });
        };

        const syncToThemeJson = () => {
            fetch(window.dsStudio?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_manual_sync_to_theme_json',
                    nonce: window.dsStudio?.nonce
                })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully synced to theme.json!');
                    console.log('Synced to theme.json successfully');
                } else {
                    alert('Failed to sync to theme.json: ' + data.data);
                    console.error('Failed to sync to theme.json:', data.data);
                }
            });
        };

        const updateColor = (colorSlug, newColorValue, newName, newSlug) => {
            if (!tokens || !tokens.colors) return;
            
            const updatedTokens = { ...tokens };
            const colorIndex = updatedTokens.colors.findIndex(c => c.slug === colorSlug);
            
            if (colorIndex !== -1) {
                updatedTokens.colors[colorIndex] = {
                    ...updatedTokens.colors[colorIndex],
                    value: newColorValue,
                    name: newName || updatedTokens.colors[colorIndex].name,
                    slug: newSlug || updatedTokens.colors[colorIndex].slug
                };
                saveTokens(updatedTokens);
            }
            setEditingColor(null);
        };

        const deleteColor = (colorSlug) => {
            if (!tokens || !tokens.colors) return;
            
            const updatedTokens = { ...tokens };
            updatedTokens.colors = updatedTokens.colors.filter(color => color.slug !== colorSlug);
            
            saveTokens(updatedTokens);
            setEditingColor(null);
        };

        const addNewColor = (category) => {
            if (!tokens) return;
            
            const updatedTokens = { ...tokens };
            if (!updatedTokens.colors) updatedTokens.colors = [];
            
            const categoryPrefix = category === 'blocksy' ? 'blocksy-' : 'wp-';
            const finalSlug = newColor.slug.startsWith(categoryPrefix) ? newColor.slug : categoryPrefix + newColor.slug;
            
            updatedTokens.colors.push({
                name: newColor.name,
                slug: finalSlug,
                value: newColor.color,
                category: category
            });
            
            saveTokens(updatedTokens);
            setNewColor({ name: '', slug: '', color: '#000000' });
            setShowAddNew(false);
        };

        const getColorsByCategory = (category) => {
            if (!tokens || !tokens.colors) return [];
            
            // Create a map to track unique colors by slug to prevent duplicates
            const uniqueColors = new Map();
            
            // Process all colors and deduplicate by slug
            tokens.colors.forEach(color => {
                if (!uniqueColors.has(color.slug)) {
                    uniqueColors.set(color.slug, color);
                }
            });
            
            const allUniqueColors = Array.from(uniqueColors.values());
            
            if (category === 'blocksy') {
                // Blocksy colors: anything with blocksy- prefix or blocksy in name
                // Also include custom Blocksy customizer colors
                return allUniqueColors.filter(color => {
                    const slug = color.slug.toLowerCase();
                    const name = color.name.toLowerCase();
                    
                    return slug.startsWith('blocksy-') || 
                           name.includes('blocksy') ||
                           slug.includes('blocksy') ||
                           // Include common Blocksy theme color names
                           ['primary', 'secondary', 'accent'].includes(slug) ||
                           // Include colors that are likely from Blocksy customizer
                           (color.source && color.source === 'blocksy-customizer');
                });
            } else if (category === 'semantic') {
                // Semantic colors: standard WP colors but exclude Blocksy ones
                return allUniqueColors.filter(color => {
                    const slug = color.slug.toLowerCase();
                    const name = color.name.toLowerCase();
                    
                    // Exclude Blocksy colors
                    if (slug.startsWith('blocksy-') || 
                        name.includes('blocksy') || 
                        slug.includes('blocksy') ||
                        (color.source && color.source === 'blocksy-customizer')) {
                        return false;
                    }
                    
                    // Include standard WordPress colors
                    const wpCoreColors = [
                        'black', 'white', 'cyan-bluish-gray', 'warm-gray', 'very-light-gray',
                        'very-dark-gray', 'vivid-red', 'luminous-vivid-orange', 'luminous-vivid-amber',
                        'light-green-cyan', 'vivid-green-cyan', 'pale-cyan-blue', 'vivid-cyan-blue',
                        'vivid-purple', 'base', 'contrast', 'primary', 'secondary', 'tertiary'
                    ];
                    
                    return wpCoreColors.includes(slug) || 
                           name.includes('wp') ||
                           name.includes('wordpress') ||
                           // Include if it doesn't match Blocksy pattern and looks like WP core
                           (!slug.includes('blocksy') && !name.includes('blocksy'));
                });
            }
            
            return [];
        };

        if (loading) {
            return el('div', { className: 'studio-loading' }, 'Loading design tokens...');
        }

        const blocksyColors = getColorsByCategory('blocksy');
        const semanticColors = getColorsByCategory('semantic');

        return el('div', { className: 'studio-design-tokens' },
            // Accordion Sections
            el('div', { className: 'studio-accordion' },
                // Theme Colors Section
                el('div', { className: 'studio-accordion-section' },
                    el('button', {
                        className: `studio-accordion-header ${openSections.blocksy ? 'active' : ''}`,
                        onClick: () => setOpenSections({ ...openSections, blocksy: !openSections.blocksy })
                    }, `Theme Colors (${blocksyColors.length})`),
                    openSections.blocksy && el('div', { className: 'studio-accordion-content' },
                        el('div', { className: 'studio-colors-grid' },
                            blocksyColors.map(color => 
                                el('div', { 
                                    key: color.slug, 
                                    className: 'studio-color-group' 
                                },
                                    editingColor === color.slug ? 
                                        el('div', { className: 'studio-color-edit' },
                                            el('input', {
                                                type: 'text',
                                                defaultValue: color.name,
                                                id: `edit-name-${color.slug}`,
                                                placeholder: 'Color name'
                                            }),
                                            el('input', {
                                                type: 'text',
                                                defaultValue: color.slug,
                                                id: `edit-slug-${color.slug}`,
                                                placeholder: 'color-slug'
                                            }),
                                            el('input', {
                                                type: 'color',
                                                defaultValue: color.value,
                                                id: `edit-color-${color.slug}`
                                            }),
                                            el('div', { className: 'studio-edit-buttons' },
                                                el('button', {
                                                    className: 'studio-save-btn',
                                                    onClick: () => {
                                                        const newName = document.getElementById(`edit-name-${color.slug}`).value;
                                                        const newSlug = document.getElementById(`edit-slug-${color.slug}`).value;
                                                        const newColor = document.getElementById(`edit-color-${color.slug}`).value;
                                                        updateColor(color.slug, newColor, newName, newSlug);
                                                    }
                                                }, 'Save'),
                                                el('button', {
                                                    className: 'studio-cancel-btn',
                                                    onClick: () => setEditingColor(null)
                                                }, 'Cancel'),
                                                el('button', {
                                                    className: 'studio-delete-btn',
                                                    onClick: () => {
                                                        if (confirm(`Delete "${color.name}"? This cannot be undone.`)) {
                                                            deleteColor(color.slug);
                                                        }
                                                    }
                                                }, 'Delete')
                                            )
                                        ) :
                                        el('div', { 
                                            className: 'studio-color-display',
                                            onClick: () => setEditingColor(color.slug)
                                        },
                                            el('div', { 
                                                className: 'studio-color-swatch',
                                                style: { backgroundColor: color.value }
                                            }),
                                            el('div', { className: 'studio-color-info' },
                                                el('div', { className: 'studio-color-name' }, color.name),
                                                el('div', { className: 'studio-color-value' }, color.value)
                                            )
                                        )
                                )
                            )
                        ),
                        el('button', {
                            className: 'studio-add-color-bottom',
                            onClick: () => setShowAddNew('blocksy')
                        }, '+ Add Theme Color'),
                        el('button', {
                            className: 'studio-sync-button',
                            onClick: syncToThemeJson
                        }, 'Save to theme.json')
                    )
                ),

                // Semantic Colors Section
                el('div', { className: 'studio-accordion-section' },
                    el('button', {
                        className: `studio-accordion-header ${openSections.semantic ? 'active' : ''}`,
                        onClick: () => setOpenSections({ ...openSections, semantic: !openSections.semantic })
                    }, `Semantic Colors (${semanticColors.length})`),
                    openSections.semantic && el('div', { className: 'studio-accordion-content' },
                        el('div', { className: 'studio-colors-grid' },
                            semanticColors.map(color => 
                                el('div', { 
                                    key: color.slug, 
                                    className: 'studio-color-group' 
                                },
                                    editingColor === color.slug ? 
                                        el('div', { className: 'studio-color-edit' },
                                            el('input', {
                                                type: 'text',
                                                defaultValue: color.name,
                                                id: `edit-name-${color.slug}`,
                                                placeholder: 'Color name'
                                            }),
                                            el('input', {
                                                type: 'text',
                                                defaultValue: color.slug,
                                                id: `edit-slug-${color.slug}`,
                                                placeholder: 'color-slug'
                                            }),
                                            el('input', {
                                                type: 'color',
                                                defaultValue: color.value,
                                                id: `edit-color-${color.slug}`
                                            }),
                                            el('div', { className: 'studio-edit-buttons' },
                                                el('button', {
                                                    className: 'studio-save-btn',
                                                    onClick: () => {
                                                        const newName = document.getElementById(`edit-name-${color.slug}`).value;
                                                        const newSlug = document.getElementById(`edit-slug-${color.slug}`).value;
                                                        const newColor = document.getElementById(`edit-color-${color.slug}`).value;
                                                        updateColor(color.slug, newColor, newName, newSlug);
                                                    }
                                                }, 'Save'),
                                                el('button', {
                                                    className: 'studio-cancel-btn',
                                                    onClick: () => setEditingColor(null)
                                                }, 'Cancel'),
                                                el('button', {
                                                    className: 'studio-delete-btn',
                                                    onClick: () => {
                                                        if (confirm(`Delete "${color.name}"? This cannot be undone.`)) {
                                                            deleteColor(color.slug);
                                                        }
                                                    }
                                                }, 'Delete')
                                            )
                                        ) :
                                        el('div', { 
                                            className: 'studio-color-display',
                                            onClick: () => setEditingColor(color.slug)
                                        },
                                            el('div', { 
                                                className: 'studio-color-swatch',
                                                style: { backgroundColor: color.value }
                                            }),
                                            el('div', { className: 'studio-color-info' },
                                                el('div', { className: 'studio-color-name' }, color.name),
                                                el('div', { className: 'studio-color-value' }, color.value)
                                            )
                                        )
                                )
                            )
                        ),
                        el('button', {
                            className: 'studio-add-color-bottom',
                            onClick: () => setShowAddNew('semantic')
                        }, '+ Add Semantic Color'),
                        el('button', {
                            className: 'studio-sync-button',
                            onClick: syncToThemeJson
                        }, 'Save to theme.json')
                    )
                ),

                // Add New Color Modal
                showAddNew && el('div', { className: 'studio-modal-overlay' },
                    el('div', { className: 'studio-modal' },
                        el('h3', null, `Add New ${showAddNew === 'blocksy' ? 'Theme' : 'Semantic'} Color`),
                        el('div', { className: 'studio-form-group' },
                            el('label', null, 'Color Name:'),
                            el('input', {
                                type: 'text',
                                value: newColor.name,
                                onChange: (e) => setNewColor({ ...newColor, name: e.target.value }),
                                placeholder: 'e.g., Primary Blue'
                            })
                        ),
                        el('div', { className: 'studio-form-group' },
                            el('label', null, 'Slug:'),
                            el('input', {
                                type: 'text',
                                value: newColor.slug,
                                onChange: (e) => setNewColor({ ...newColor, slug: e.target.value }),
                                placeholder: 'e.g., primary-blue'
                            })
                        ),
                        el('div', { className: 'studio-form-group' },
                            el('label', null, 'Color:'),
                            el('input', {
                                type: 'color',
                                value: newColor.color,
                                onChange: (e) => setNewColor({ ...newColor, color: e.target.value })
                            })
                        ),
                        el('div', { className: 'studio-modal-actions' },
                            el('button', {
                                onClick: () => addNewColor(showAddNew),
                                disabled: !newColor.name || !newColor.slug
                            }, 'Add Color'),
                            el('button', {
                                onClick: () => {
                                    setShowAddNew(false);
                                    setNewColor({ name: '', slug: '', color: '#000000' });
                                }
                            }, 'Cancel')
                        )
                    )
                )
            )
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
                el('button', {
                    className: 'studio-create-button',
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
                el('button', {
                    className: 'studio-convert-button',
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
                    el('button', {
                        className: 'studio-copy-button',
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
