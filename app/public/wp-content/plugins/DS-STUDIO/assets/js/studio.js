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
        const [openSections, setOpenSections] = useState({ theme: true, semantic: false });
        const [editingColor, setEditingColor] = useState(null);
        const [showAddNew, setShowAddNew] = useState(false);
        const [addingToCategory, setAddingToCategory] = useState(null);
        const [newColor, setNewColor] = useState({ name: '', slug: '', color: '#000000' });
        const [showAddCategory, setShowAddCategory] = useState(false);
        const [newCategory, setNewCategory] = useState({ key: '', name: '', icon: '🎨' });
        const [editingCategory, setEditingCategory] = useState(null);

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

        const updateColorCategory = (colorSlug, newCategory) => {
            if (!tokens || !tokens.colors) return;
            
            const updatedTokens = { ...tokens };
            
            // Update the category for the specific color
            if (updatedTokens.colors[colorSlug]) {
                updatedTokens.colors[colorSlug] = {
                    ...updatedTokens.colors[colorSlug],
                    category: newCategory
                };
                saveTokens(updatedTokens);
            }
        };

        const deleteColor = (colorSlug) => {
            if (!tokens || !tokens.colors) return;
            
            const updatedTokens = { ...tokens };
            updatedTokens.colors = updatedTokens.colors.filter(color => color.slug !== colorSlug);
            
            saveTokens(updatedTokens);
            setEditingColor(null);
        };

        const addNewColor = () => {
            if (!tokens || !addingToCategory) return;
            
            const updatedTokens = { ...tokens };
            if (!updatedTokens.colors) updatedTokens.colors = {};
            
            // Generate a unique slug
            let baseSlug = newColor.slug || newColor.name.toLowerCase().replace(/\s+/g, '-');
            let finalSlug = baseSlug;
            let counter = 1;
            
            // Ensure slug is unique
            while (updatedTokens.colors[finalSlug]) {
                finalSlug = `${baseSlug}-${counter}`;
                counter++;
            }
            
            // Get the next order number for this category
            const colorsInCategory = Object.values(updatedTokens.colors).filter(c => c.category === addingToCategory);
            const nextOrder = colorsInCategory.length > 0 ? Math.max(...colorsInCategory.map(c => c.order || 0)) + 1 : 1;
            
            updatedTokens.colors[finalSlug] = {
                name: newColor.name,
                value: newColor.color,
                category: addingToCategory,
                order: nextOrder
            };
            
            saveTokens(updatedTokens);
            setNewColor({ name: '', slug: '', color: '#000000' });
            setShowAddNew(false);
            setAddingToCategory(null);
        };

        const getColorsByCategory = (category) => {
            if (!tokens || !tokens.colors) return [];
            
            // Convert metadata structure to array format
            const allColors = [];
            
            // Process each color with metadata structure
            Object.keys(tokens.colors).forEach(slug => {
                const colorData = tokens.colors[slug];
                
                // Handle both old and new formats for backward compatibility
                if (typeof colorData === 'string') {
                    // Old format: direct hex value
                    allColors.push({
                        slug: slug,
                        name: slug.split('-').map(word => 
                            word.charAt(0).toUpperCase() + word.slice(1)
                        ).join(' '),
                        color: colorData,
                        category: 'theme', // default category
                        order: 999
                    });
                } else if (colorData && typeof colorData === 'object') {
                    // New metadata format
                    allColors.push({
                        slug: slug,
                        name: colorData.name || slug.split('-').map(word => 
                            word.charAt(0).toUpperCase() + word.slice(1)
                        ).join(' '),
                        color: colorData.value || colorData.color,
                        category: colorData.category || 'theme',
                        order: colorData.order || 999
                    });
                }
            });
            
            // Filter by requested category and sort by order
            const filteredColors = allColors.filter(color => {
                if (category === 'blocksy') {
                    // Map 'blocksy' to 'theme' for backward compatibility
                    return color.category === 'theme';
                }
                return color.category === category;
            });
            
            // Sort by order, then by name
            return filteredColors.sort((a, b) => {
                if (a.order !== b.order) {
                    return a.order - b.order;
                }
                return a.name.localeCompare(b.name);
            });
        };

        const getAvailableCategories = () => {
            if (!tokens) return [];
            
            // Use categories from studio.json if available
            if (tokens.categories) {
                return Object.keys(tokens.categories)
                    .map(key => ({
                        key: key,
                        name: tokens.categories[key].name || `${key.charAt(0).toUpperCase() + key.slice(1)} Colors`,
                        icon: tokens.categories[key].icon || '🎨',
                        order: tokens.categories[key].order || 999
                    }))
                    .sort((a, b) => a.order - b.order);
            }
            
            // Fallback: get categories from colors (old method)
            if (!tokens.colors) return [];
            
            const categories = new Set();
            Object.values(tokens.colors).forEach(color => {
                if (color.category) {
                    categories.add(color.category);
                }
            });
            
            // Define category order and display names
            const categoryConfig = {
                'theme': { name: 'Theme Colors', icon: '🎨', order: 1 },
                'brand': { name: 'Brand Colors', icon: '🏷️', order: 2 },
                'semantic': { name: 'Semantic Colors', icon: '⚡', order: 3 },
                'neutral': { name: 'Neutral Colors', icon: '⚪', order: 4 },
                'custom': { name: 'Custom Colors', icon: '✨', order: 5 }
            };
            
            return Array.from(categories)
                .map(cat => ({
                    key: cat,
                    name: categoryConfig[cat]?.name || `${cat.charAt(0).toUpperCase() + cat.slice(1)} Colors`,
                    icon: categoryConfig[cat]?.icon || '🎨',
                    order: categoryConfig[cat]?.order || 999
                }))
                .sort((a, b) => a.order - b.order);
        };

        const saveCategory = async (category) => {
            if (!tokens) return;
            
            const updatedTokens = { ...tokens };
            
            // Ensure categories section exists
            if (!updatedTokens.categories) {
                updatedTokens.categories = {};
            }
            
            // Add/update the category
            updatedTokens.categories[category.key] = {
                name: category.name,
                icon: category.icon,
                order: category.order || Object.keys(updatedTokens.categories).length + 1
            };
            
            // Save the updated tokens
            saveTokens(updatedTokens);
            
            // Reset form
            setNewCategory({ key: '', name: '', icon: '🎨' });
            setShowAddCategory(false);
        };

        const deleteCategory = async (categoryKey) => {
            if (!tokens || !tokens.categories) return;
            
            const updatedTokens = { ...tokens };
            
            // Remove the category
            delete updatedTokens.categories[categoryKey];
            
            // Move any colors in this category to 'theme' as default
            if (updatedTokens.colors) {
                Object.keys(updatedTokens.colors).forEach(colorKey => {
                    if (updatedTokens.colors[colorKey].category === categoryKey) {
                        updatedTokens.colors[colorKey].category = 'theme';
                    }
                });
            }
            
            // Save the updated tokens
            saveTokens(updatedTokens);
        };

        const updateCategory = (categoryKey, field, value) => {
            if (!tokens || !tokens.categories) return;
            
            const updatedTokens = { ...tokens };
            
            if (field === 'key' && value !== categoryKey) {
                // Changing the key - need to rename the category
                const categoryData = updatedTokens.categories[categoryKey];
                delete updatedTokens.categories[categoryKey];
                updatedTokens.categories[value] = categoryData;
                
                // Update any colors using this category
                if (updatedTokens.colors) {
                    Object.keys(updatedTokens.colors).forEach(colorKey => {
                        if (updatedTokens.colors[colorKey].category === categoryKey) {
                            updatedTokens.colors[colorKey].category = value;
                        }
                    });
                }
                
                setEditingCategory(value); // Update editing state to new key
            } else {
                // Just updating name or icon
                updatedTokens.categories[categoryKey][field] = value;
            }
            
            saveTokens(updatedTokens);
        };

        if (loading) {
            return el('div', { className: 'studio-loading' }, 'Loading design tokens...');
        }

        const availableCategories = getAvailableCategories();

        return el('div', { className: 'studio-design-tokens' },
            // Accordion Sections - Dynamic based on available categories
            el('div', { className: 'studio-accordion' },
                availableCategories.map(category => {
                    const categoryColors = getColorsByCategory(category.key);
                    const isOpen = openSections[category.key];
                    
                    return el('div', { className: 'studio-accordion-section', key: category.key },
                        el('div', { className: 'studio-accordion-header', onClick: () => setOpenSections(prev => ({
                            ...prev,
                            [category.key]: !prev[category.key]
                        })) },
                            el('span', { className: 'studio-accordion-title' }, 
                                `${category.icon} ${category.name} (${categoryColors.length})`
                            ),
                            el('div', { className: 'studio-accordion-controls' },
                                el('button', {
                                    className: 'studio-add-color-btn',
                                    onClick: () => {
                                        setAddingToCategory(category.key);
                                        setShowAddNew(true);
                                    },
                                    title: 'Add Color to this category'
                                }, '+ Add Color'),
                                el('span', { className: 'studio-accordion-arrow' }, 
                                    openSections[category.key] ? '▼' : '▶'
                                )
                            )
                        ),
                        
                        isOpen && el('div', { className: 'studio-accordion-content' },
                            el('div', { className: 'studio-colors-grid' },
                                categoryColors.map(color => 
                                    el('div', { 
                                        className: 'studio-color-item',
                                        key: color.slug,
                                        onClick: () => setEditingColor(editingColor === color.slug ? null : color.slug)
                                    },
                                        el('div', { 
                                            className: 'studio-color-swatch',
                                            style: { backgroundColor: color.color }
                                        }),
                                        el('div', { className: 'studio-color-info' },
                                            el('div', { className: 'studio-color-name' }, color.name),
                                            el('div', { className: 'studio-color-value' }, color.color)
                                        ),
                                        el('select', {
                                            className: 'studio-category-selector',
                                            value: color.category || 'theme',
                                            onClick: (e) => e.stopPropagation(),
                                            onChange: (e) => {
                                                const newCategory = e.target.value;
                                                updateColorCategory(color.slug, newCategory);
                                            }
                                        },
                                            // Dynamically generate options from available categories
                                            availableCategories.map(cat => 
                                                el('option', { 
                                                    key: cat.key, 
                                                    value: cat.key 
                                                }, cat.name)
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    );
                }),
                
                // Add new category form
                el('div', { className: 'studio-add-category-form' },
                    el('button', {
                        className: 'studio-add-category-btn',
                        onClick: () => setShowAddCategory(true)
                    }, 'Add New Category'),
                    
                    showAddCategory && el('div', { className: 'studio-add-category-inputs' },
                        el(TextControl, {
                            label: 'Category Key',
                            value: newCategory.key,
                            onChange: (value) => setNewCategory(prev => ({ ...prev, key: value })),
                            placeholder: 'e.g., custom-category'
                        }),
                        el(TextControl, {
                            label: 'Category Name',
                            value: newCategory.name,
                            onChange: (value) => setNewCategory(prev => ({ ...prev, name: value })),
                            placeholder: 'e.g., Custom Category'
                        }),
                        el(TextControl, {
                            label: 'Category Icon',
                            value: newCategory.icon,
                            onChange: (value) => setNewCategory(prev => ({ ...prev, icon: value })),
                            placeholder: 'e.g., 🎨'
                        }),
                        el('button', {
                            className: 'studio-add-category-submit',
                            onClick: () => {
                                saveCategory(newCategory);
                                setShowAddCategory(false);
                            }
                        }, 'Add Category')
                    )
                ),
                
                // Category management
                el('div', { className: 'studio-category-management' },
                    el('h3', null, 'Category Management'),
                    el('ul', null,
                        availableCategories.map(category => 
                            el('li', { key: category.key },
                                el('span', null, category.name),
                                el('button', {
                                    className: 'studio-edit-category-btn',
                                    onClick: () => setEditingCategory(category.key)
                                }, 'Edit'),
                                el('button', {
                                    className: 'studio-delete-category-btn',
                                    onClick: () => deleteCategory(category.key)
                                }, 'Delete')
                            )
                        )
                    ),
                    
                    editingCategory && el('div', { className: 'studio-edit-category-form' },
                        el(TextControl, {
                            label: 'Category Key',
                            value: availableCategories.find(cat => cat.key === editingCategory).key,
                            onChange: (value) => updateCategory(editingCategory, 'key', value),
                            placeholder: 'e.g., custom-category'
                        }),
                        el(TextControl, {
                            label: 'Category Name',
                            value: availableCategories.find(cat => cat.key === editingCategory).name,
                            onChange: (value) => updateCategory(editingCategory, 'name', value),
                            placeholder: 'e.g., Custom Category'
                        }),
                        el(TextControl, {
                            label: 'Category Icon',
                            value: availableCategories.find(cat => cat.key === editingCategory).icon,
                            onChange: (value) => updateCategory(editingCategory, 'icon', value),
                            placeholder: 'e.g., 🎨'
                        }),
                        el('button', {
                            className: 'studio-cancel-edit-category-btn',
                            onClick: () => setEditingCategory(null)
                        }, 'Cancel')
                    )
                ),
                
                // Add new color form
                showAddNew && el('div', { className: 'studio-add-color-form' },
                    el(TextControl, {
                        label: 'Color Name',
                        value: newColor.name,
                        onChange: (value) => setNewColor(prev => ({ ...prev, name: value })),
                        placeholder: 'e.g., Primary Color'
                    }),
                    el(TextControl, {
                        label: 'Color Slug',
                        value: newColor.slug,
                        onChange: (value) => setNewColor(prev => ({ ...prev, slug: value })),
                        placeholder: 'e.g., primary-color'
                    }),
                    el(ColorPicker, {
                        label: 'Color Value',
                        value: newColor.color,
                        onChange: (value) => setNewColor(prev => ({ ...prev, color: value })),
                        placeholder: 'e.g., #000000'
                    }),
                    el('button', {
                        className: 'studio-add-color-submit',
                        onClick: addNewColor
                    }, 'Add Color')
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
