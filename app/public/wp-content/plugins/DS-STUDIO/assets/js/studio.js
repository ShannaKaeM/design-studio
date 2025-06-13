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
        
        const [showTypographyAddFormsState, setShowTypographyAddFormsState] = useState({});
        const [newTypographyItemsState, setNewTypographyItemsState] = useState({});

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
                        nonce: window.dsStudio?.nonce
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setTokens(data.data);
                    
                    // Initialize categories from the loaded data
                    if (data.data.categories) {
                        setCategories(data.data.categories);
                    }
                    
                    console.log('Loaded tokens:', data.data);
                } else {
                    console.error('Failed to load tokens:', data.data);
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

        const getColorsByCategory = (categoryKey) => {
            if (!tokens || !tokens.colors) return [];
            
            // If requesting 'colors', return all colors
            if (categoryKey === 'colors') {
                return Object.keys(tokens.colors).map(slug => ({
                    slug,
                    ...tokens.colors[slug]
                }));
            }
            
            // Otherwise filter by specific category
            return Object.keys(tokens.colors)
                .map(slug => ({
                    slug,
                    ...tokens.colors[slug]
                }))
                .filter(color => color.category === categoryKey);
        };

        const getGradientsByCategory = (categoryKey) => {
            if (!tokens || !tokens.gradients) return [];
            
            // Convert metadata structure to array format
            const allGradients = [];
            
            // Process each gradient with metadata structure
            Object.keys(tokens.gradients).forEach(slug => {
                const gradientData = tokens.gradients[slug];
                
                // Handle both old and new formats for backward compatibility
                if (typeof gradientData === 'string') {
                    // Old format: direct gradient value
                    allGradients.push({
                        slug: slug,
                        name: slug.split('-').map(word => 
                            word.charAt(0).toUpperCase() + word.slice(1)
                        ).join(' '),
                        gradient: gradientData,
                        category: 'theme', // default category
                        order: 999
                    });
                } else if (gradientData && typeof gradientData === 'object') {
                    // New metadata format
                    allGradients.push({
                        slug: slug,
                        name: gradientData.name || slug.split('-').map(word => 
                            word.charAt(0).toUpperCase() + word.slice(1)
                        ).join(' '),
                        gradient: gradientData.value || gradientData.gradient,
                        category: gradientData.category || 'theme',
                        order: gradientData.order || 999
                    });
                }
            });
            
            // Filter by requested category and sort by order
            const filteredGradients = allGradients.filter(gradient => {
                if (categoryKey === 'blocksy') {
                    // Map 'blocksy' to 'theme' for backward compatibility
                    return gradient.category === 'theme';
                }
                return gradient.category === categoryKey;
            });
            
            // Sort by order, then by name
            return filteredGradients.sort((a, b) => {
                if (a.order !== b.order) {
                    return a.order - b.order;
                }
                return a.name.localeCompare(b.name);
            });
        };

        const getAvailableCategories = () => {
            if (!tokens || !tokens.categories) return [];
            
            // Create top-level token type categories
            const topLevelCategories = [
                {
                    key: 'colors',
                    name: 'Colors',
                    icon: '🎨',
                    order: 1,
                    hasSubCategories: true
                },
                {
                    key: 'gradients', 
                    name: 'Gradients',
                    icon: '🌈',
                    order: 2,
                    hasSubCategories: false
                },
                {
                    key: 'typography',
                    name: 'Typography', 
                    icon: '📝',
                    order: 3,
                    hasSubCategories: false
                },
                {
                    key: 'layout',
                    name: 'Layout',
                    icon: '📐', 
                    order: 4,
                    hasSubCategories: false
                },
                {
                    key: 'spacing',
                    name: 'Spacing',
                    icon: '📏',
                    order: 5,
                    hasSubCategories: false
                }
            ];
            
            return topLevelCategories.sort((a, b) => a.order - b.order);
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

        const handleSyncAll = () => {
            if (!tokens) return;
            
            // Add visual feedback
            const button = document.querySelector('.ds-sync-all-button');
            if (button) {
                button.textContent = '⏳ Syncing...';
                button.disabled = true;
            }
            
            // Just trigger a save with current tokens to force sync
            saveTokens(tokens);
            
            // Reset button after a delay
            setTimeout(() => {
                if (button) {
                    button.textContent = '🔄 Sync All to theme.json';
                    button.disabled = false;
                }
            }, 1500);
        };

        const renderColors = (category) => {
            const colors = getColorsByCategory(category.key);
            const totalItems = colors.length;
            
            return el('div', { className: 'studio-colors-grid' },
                colors.map(color => 
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
            );
        };

        const renderGradients = (category) => {
            const gradients = getGradientsByCategory(category.key);
            const totalItems = gradients.length;
            
            return el('div', { className: 'studio-gradients-grid' },
                gradients.map(gradient => 
                    el('div', { 
                        className: 'studio-gradient-item',
                        key: gradient.slug,
                        onClick: () => setEditingColor(editingColor === gradient.slug ? null : gradient.slug)
                    },
                        el('div', { 
                            className: 'studio-gradient-swatch',
                            style: { background: gradient.gradient }
                        }),
                        el('div', { className: 'studio-gradient-info' },
                            el('div', { className: 'studio-gradient-name' }, gradient.name),
                            el('div', { className: 'studio-gradient-value' }, gradient.gradient)
                        ),
                        el('select', {
                            className: 'studio-category-selector',
                            value: gradient.category || 'theme',
                            onClick: (e) => e.stopPropagation(),
                            onChange: (e) => {
                                const newCategory = e.target.value;
                                updateColorCategory(gradient.slug, newCategory);
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
            );
        };

        const renderLayoutSettings = (category) => {
            const layoutSettings = tokens.layout;
            if (!layoutSettings) return el('div', {}, 'No layout settings found');
            
            const updateLayoutSetting = (key, value) => {
                const updatedTokens = {
                    ...tokens,
                    layout: {
                        ...tokens.layout,
                        [key]: value
                    }
                };
                saveTokens(updatedTokens);
            };
            
            const updateRootPadding = (side, value) => {
                const updatedTokens = {
                    ...tokens,
                    layout: {
                        ...tokens.layout,
                        rootPadding: {
                            ...tokens.layout.rootPadding,
                            [side]: value
                        }
                    }
                };
                saveTokens(updatedTokens);
            };
            
            return el('div', { className: 'studio-layout-settings-grid' },
                // Content Width
                el('div', { className: 'studio-layout-setting-item' },
                    el('div', { className: 'studio-layout-setting-info' },
                        el('div', { className: 'studio-layout-setting-name' }, 'Content Width'),
                        el('input', {
                            type: 'text',
                            className: 'studio-layout-setting-input',
                            value: layoutSettings.contentWidth || '1280px',
                            onChange: (e) => updateLayoutSetting('contentWidth', e.target.value),
                            placeholder: 'e.g., 1280px'
                        })
                    )
                ),
                
                // Wide Width
                el('div', { className: 'studio-layout-setting-item' },
                    el('div', { className: 'studio-layout-setting-info' },
                        el('div', { className: 'studio-layout-setting-name' }, 'Wide Width'),
                        el('input', {
                            type: 'text',
                            className: 'studio-layout-setting-input',
                            value: layoutSettings.wideWidth || '1400px',
                            onChange: (e) => updateLayoutSetting('wideWidth', e.target.value),
                            placeholder: 'e.g., 1400px'
                        })
                    )
                ),
                
                // Full Width
                el('div', { className: 'studio-layout-setting-item' },
                    el('div', { className: 'studio-layout-setting-info' },
                        el('div', { className: 'studio-layout-setting-name' }, 'Full Width'),
                        el('input', {
                            type: 'text',
                            className: 'studio-layout-setting-input',
                            value: layoutSettings.fullWidth || '100vw',
                            onChange: (e) => updateLayoutSetting('fullWidth', e.target.value),
                            placeholder: 'e.g., 100vw'
                        })
                    )
                ),
                
                // Root Padding Aware Alignments
                el('div', { className: 'studio-layout-setting-item' },
                    el('div', { className: 'studio-layout-setting-info' },
                        el('div', { className: 'studio-layout-setting-name' }, 'Root Padding Aware'),
                        el('select', {
                            className: 'studio-layout-setting-select',
                            value: layoutSettings.useRootPaddingAwareAlignments ? 'true' : 'false',
                            onChange: (e) => updateLayoutSetting('useRootPaddingAwareAlignments', e.target.value === 'true')
                        },
                            el('option', { value: 'false' }, 'Disabled'),
                            el('option', { value: 'true' }, 'Enabled')
                        )
                    )
                ),
                
                // Appearance Tools
                el('div', { className: 'studio-layout-setting-item' },
                    el('div', { className: 'studio-layout-setting-info' },
                        el('div', { className: 'studio-layout-setting-name' }, 'Appearance Tools'),
                        el('select', {
                            className: 'studio-layout-setting-select',
                            value: layoutSettings.appearanceTools ? 'true' : 'false',
                            onChange: (e) => updateLayoutSetting('appearanceTools', e.target.value === 'true')
                        },
                            el('option', { value: 'true' }, 'Enabled'),
                            el('option', { value: 'false' }, 'Disabled')
                        )
                    )
                ),
                
                // Root Padding Section
                el('div', { className: 'studio-layout-padding-section' },
                    el('h4', { className: 'studio-layout-section-title' }, 'Root Padding'),
                    el('div', { className: 'studio-layout-padding-grid' },
                        // Top
                        el('div', { className: 'studio-layout-padding-item' },
                            el('label', { className: 'studio-layout-padding-label' }, 'Top'),
                            el('input', {
                                type: 'text',
                                className: 'studio-layout-padding-input',
                                value: layoutSettings.rootPadding?.top || '0px',
                                onChange: (e) => updateRootPadding('top', e.target.value),
                                placeholder: '0px'
                            })
                        ),
                        // Right
                        el('div', { className: 'studio-layout-padding-item' },
                            el('label', { className: 'studio-layout-padding-label' }, 'Right'),
                            el('input', {
                                type: 'text',
                                className: 'studio-layout-padding-input',
                                value: layoutSettings.rootPadding?.right || '0px',
                                onChange: (e) => updateRootPadding('right', e.target.value),
                                placeholder: '0px'
                            })
                        ),
                        // Bottom
                        el('div', { className: 'studio-layout-padding-item' },
                            el('label', { className: 'studio-layout-padding-label' }, 'Bottom'),
                            el('input', {
                                type: 'text',
                                className: 'studio-layout-padding-input',
                                value: layoutSettings.rootPadding?.bottom || '0px',
                                onChange: (e) => updateRootPadding('bottom', e.target.value),
                                placeholder: '0px'
                            })
                        ),
                        // Left
                        el('div', { className: 'studio-layout-padding-item' },
                            el('label', { className: 'studio-layout-padding-label' }, 'Left'),
                            el('input', {
                                type: 'text',
                                className: 'studio-layout-padding-input',
                                value: layoutSettings.rootPadding?.left || '0px',
                                onChange: (e) => updateRootPadding('left', e.target.value),
                                placeholder: '0px'
                            })
                        )
                    )
                )
            );
        };

        const renderSpacingSettings = (category) => {
            const spacingSettings = tokens.spacing;
            if (!spacingSettings) return el('div', {}, 'No spacing settings found');
            
            const updateSpacingSetting = (key, value) => {
                const updatedTokens = {
                    ...tokens,
                    spacing: {
                        ...tokens.spacing,
                        [key]: value
                    }
                };
                saveTokens(updatedTokens);
            };
            
            return el('div', { className: 'studio-spacing-settings-container' },
                // Spacing Scale Section
                el('div', { className: 'studio-spacing-scale-section' },
                    el('h4', { className: 'studio-spacing-section-title' }, 'Spacing Scale'),
                    el('div', { className: 'studio-spacing-scale-grid' },
                        Object.keys(spacingSettings).map(spacingKey => {
                            const spacingValue = spacingSettings[spacingKey];
                            
                            // Skip non-scale items
                            if (typeof spacingValue !== 'string') return null;
                            
                            return el('div', { 
                                className: 'studio-spacing-scale-item',
                                key: spacingKey
                            },
                                el('div', { className: 'studio-spacing-scale-info' },
                                    el('div', { className: 'studio-spacing-scale-name' }, spacingKey.toUpperCase()),
                                    el('input', {
                                        type: 'text',
                                        className: 'studio-spacing-scale-input',
                                        value: spacingValue,
                                        onChange: (e) => updateSpacingSetting(spacingKey, e.target.value),
                                        placeholder: 'e.g., 16px'
                                    }),
                                    el('div', { 
                                        className: 'studio-spacing-scale-preview',
                                        style: { 
                                            width: spacingValue,
                                            height: '4px',
                                            backgroundColor: '#0073aa',
                                            marginTop: '4px'
                                        }
                                    })
                                )
                            );
                        }).filter(Boolean)
                    )
                )
            );
        };

        const renderTypographySettings = () => {
            const typographySettings = tokens.typography;
            if (!typographySettings) return el('div', {}, 'No typography settings found');
            
            const updateTypographySetting = (section, key, value) => {
                const updatedTokens = {
                    ...tokens,
                    typography: {
                        ...tokens.typography,
                        [section]: {
                            ...tokens.typography[section],
                            [key]: value
                        }
                    }
                };
                saveTokens(updatedTokens);
            };
            
            const addTypographyItem = (section, newKey, newValue) => {
                if (!newKey || !newValue) return;
                
                const updatedTokens = {
                    ...tokens,
                    typography: {
                        ...tokens.typography,
                        [section]: {
                            ...tokens.typography[section],
                            [newKey]: typeof newValue === 'string' ? newValue : {
                                name: newValue.name || newKey,
                                value: newValue.value || '',
                                category: newValue.category || 'custom'
                            }
                        }
                    }
                };
                saveTokens(updatedTokens);
            };
            
            const deleteTypographyItem = (section, key) => {
                if (!confirm(`Delete ${key} from ${section}?`)) return;
                
                const updatedSection = { ...tokens.typography[section] };
                delete updatedSection[key];
                
                const updatedTokens = {
                    ...tokens,
                    typography: {
                        ...tokens.typography,
                        [section]: updatedSection
                    }
                };
                saveTokens(updatedTokens);
            };
            
            const toggleAddForm = (sectionKey) => {
                setShowTypographyAddFormsState(prev => ({
                    ...prev,
                    [sectionKey]: !prev[sectionKey]
                }));
                
                // Reset form when closing
                if (showTypographyAddFormsState[sectionKey]) {
                    setNewTypographyItemsState(prev => ({
                        ...prev,
                        [sectionKey]: { key: '', name: '', value: '', category: 'custom' }
                    }));
                }
            };
            
            const updateNewItem = (sectionKey, field, value) => {
                setNewTypographyItemsState(prev => ({
                    ...prev,
                    [sectionKey]: {
                        ...prev[sectionKey],
                        [field]: value
                    }
                }));
            };
            
            const submitNewItem = (sectionKey) => {
                const newItem = newTypographyItemsState[sectionKey] || {};
                
                if (sectionKey === 'fontFamilies') {
                    addTypographyItem(sectionKey, newItem.key, {
                        name: newItem.name,
                        value: newItem.value,
                        category: newItem.category
                    });
                } else {
                    addTypographyItem(sectionKey, newItem.key, newItem.value);
                }
                
                // Reset form
                setNewTypographyItemsState(prev => ({
                    ...prev,
                    [sectionKey]: { key: '', name: '', value: '', category: 'custom' }
                }));
                setShowTypographyAddFormsState(prev => ({
                    ...prev,
                    [sectionKey]: false
                }));
            };
            
            const renderTypographySection = (sectionKey, sectionTitle, items) => {
                const showAddForm = showTypographyAddFormsState[sectionKey] || false;
                const newItem = newTypographyItemsState[sectionKey] || { key: '', name: '', value: '', category: 'custom' };
                
                return el('div', { className: 'studio-typography-section' },
                    el('div', { className: 'studio-typography-section-header' },
                        el('h4', { className: 'studio-typography-section-title' }, sectionTitle),
                        el('button', {
                            className: 'studio-add-typography-btn',
                            onClick: () => toggleAddForm(sectionKey)
                        }, showAddForm ? 'Cancel' : `+ Add ${sectionTitle.slice(0, -1)}`)
                    ),
                    
                    // Add form
                    showAddForm && el('div', { className: 'studio-add-typography-form' },
                        el('input', {
                            type: 'text',
                            placeholder: 'Key (e.g., large)',
                            value: newItem.key,
                            onChange: (e) => updateNewItem(sectionKey, 'key', e.target.value)
                        }),
                        sectionKey === 'fontFamilies' && el('input', {
                            type: 'text',
                            placeholder: 'Name (e.g., Montserrat)',
                            value: newItem.name,
                            onChange: (e) => updateNewItem(sectionKey, 'name', e.target.value)
                        }),
                        el('input', {
                            type: 'text',
                            placeholder: sectionKey === 'fontFamilies' ? 'CSS Value (e.g., Montserrat, sans-serif)' : 'Value (e.g., 18px)',
                            value: newItem.value,
                            onChange: (e) => updateNewItem(sectionKey, 'value', e.target.value)
                        }),
                        el('button', {
                            className: 'studio-add-typography-submit',
                            onClick: () => submitNewItem(sectionKey)
                        }, 'Add')
                    ),
                    
                    // Items grid
                    el('div', { className: 'studio-typography-items-grid' },
                        Object.keys(items).map(itemKey => {
                            const item = items[itemKey];
                            const isObject = typeof item === 'object';
                            
                            return el('div', { 
                                className: 'studio-typography-item',
                                key: itemKey
                            },
                                el('div', { className: 'studio-typography-item-header' },
                                    el('span', { className: 'studio-typography-item-key' }, itemKey.toUpperCase()),
                                    el('button', {
                                        className: 'studio-delete-typography-btn',
                                        onClick: () => deleteTypographyItem(sectionKey, itemKey),
                                        title: `Delete ${itemKey}`
                                    }, '×')
                                ),
                                
                                // Font Family editing
                                isObject && sectionKey === 'fontFamilies' && el('div', { className: 'studio-typography-item-fields' },
                                    el('label', {}, 'Name:'),
                                    el('input', {
                                        type: 'text',
                                        value: item.name || '',
                                        onChange: (e) => updateTypographySetting(sectionKey, itemKey, {
                                            ...item,
                                            name: e.target.value
                                        }),
                                        placeholder: 'Font name'
                                    }),
                                    el('label', {}, 'CSS Value:'),
                                    el('input', {
                                        type: 'text',
                                        value: item.value || '',
                                        onChange: (e) => updateTypographySetting(sectionKey, itemKey, {
                                            ...item,
                                            value: e.target.value
                                        }),
                                        placeholder: 'CSS font-family value'
                                    }),
                                    el('div', { 
                                        className: 'studio-typography-preview',
                                        style: { fontFamily: item.value }
                                    }, `Preview: ${item.name || itemKey}`)
                                ),
                                
                                // Simple value editing (sizes, weights, line heights)
                                !isObject && el('div', { className: 'studio-typography-item-fields' },
                                    el('input', {
                                        type: 'text',
                                        value: item,
                                        onChange: (e) => updateTypographySetting(sectionKey, itemKey, e.target.value),
                                        placeholder: sectionKey === 'fontSizes' ? 'e.g., 16px' : 
                                                   sectionKey === 'fontWeights' ? 'e.g., 400' : 'e.g., 1.5'
                                    }),
                                    sectionKey === 'fontSizes' && el('div', { 
                                        className: 'studio-typography-preview',
                                        style: { fontSize: item }
                                    }, 'Preview Text'),
                                    sectionKey === 'fontWeights' && el('div', { 
                                        className: 'studio-typography-preview',
                                        style: { fontWeight: item }
                                    }, 'Preview Text'),
                                    sectionKey === 'lineHeights' && el('div', { 
                                        className: 'studio-typography-preview',
                                        style: { lineHeight: item }
                                    }, 'Preview Text\nMultiple Lines\nTo Show Line Height')
                                )
                            );
                        })
                    )
                );
            };
            
            return el('div', { className: 'studio-typography-settings-container' },
                typographySettings.fontFamilies && renderTypographySection('fontFamilies', 'Font Families', typographySettings.fontFamilies),
                typographySettings.fontSizes && renderTypographySection('fontSizes', 'Font Sizes', typographySettings.fontSizes),
                typographySettings.fontWeights && renderTypographySection('fontWeights', 'Font Weights', typographySettings.fontWeights),
                typographySettings.lineHeights && renderTypographySection('lineHeights', 'Line Heights', typographySettings.lineHeights)
            );
        };

        const renderColorsWithSubCategories = () => {
            const colors = getColorsByCategory('colors');
            
            // Get sub-categories from studio.json categories, excluding non-color categories
            const colorCategories = tokens.categories ? 
                Object.keys(tokens.categories).filter(key => 
                    !['layout', 'spacing', 'typography', 'gradients'].includes(key)
                ) : [];
            
            return el('div', { className: 'studio-colors-with-subcategories' },
                colorCategories.map(subCategoryKey => {
                    const subCategoryConfig = tokens.categories[subCategoryKey];
                    const subCategoryColors = colors.filter(color => color.category === subCategoryKey);
                    
                    if (subCategoryColors.length === 0) return null;
                    
                    return el('div', { 
                        className: 'studio-color-subcategory',
                        key: subCategoryKey
                    },
                        el('h4', { className: 'studio-color-subcategory-title' }, 
                            `${subCategoryConfig.icon || '🎨'} ${subCategoryConfig.name || subCategoryKey} (${subCategoryColors.length})`
                        ),
                        el('div', { className: 'studio-colors-grid' },
                            subCategoryColors.map(color => 
                                el('div', { 
                                    className: 'studio-color-item',
                                    key: color.slug,
                                    onClick: () => setEditingColor(editingColor === color.slug ? null : color.slug)
                                },
                                    el('div', { 
                                        className: 'studio-color-swatch',
                                        style: { backgroundColor: color.color || color.value }
                                    }),
                                    el('div', { className: 'studio-color-info' },
                                        el('div', { className: 'studio-color-name' }, color.name),
                                        el('div', { className: 'studio-color-value' }, color.color || color.value)
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
                                        // Generate options from color categories only
                                        colorCategories.map(catKey => 
                                            el('option', { 
                                                key: catKey, 
                                                value: catKey 
                                            }, tokens.categories[catKey].name || catKey)
                                        )
                                    )
                                )
                            )
                        )
                    );
                }).filter(Boolean) // Remove null entries
            );
        };

        if (loading) {
            return el('div', { className: 'studio-loading' }, 'Loading design tokens...');
        }

        const availableCategories = getAvailableCategories();

        const syncAllButton = el('button', {
            className: 'ds-sync-all-button',
            onClick: handleSyncAll,
            style: {
                marginBottom: '20px',
                padding: '10px 20px',
                backgroundColor: '#0073aa',
                color: 'white',
                border: 'none',
                borderRadius: '4px',
                cursor: 'pointer',
                fontSize: '14px',
                fontWeight: '500'
            }
        }, '🔄 Sync All to theme.json');

        return el('div', { className: 'studio-design-tokens' },
            syncAllButton,
            // Top-level Token Type Accordions
            el('div', { className: 'studio-accordion' },
                availableCategories.map(tokenType => {
                    const isOpen = openSections[tokenType.key];
                    
                    return el('div', { 
                        className: 'studio-accordion-item',
                        key: tokenType.key
                    },
                        el('div', { 
                            className: `studio-accordion-header ${isOpen ? 'open' : ''}`,
                            onClick: () => setOpenSections(prev => ({
                                ...prev,
                                [tokenType.key]: !prev[tokenType.key]
                            }))
                        },
                            el('span', { className: 'studio-accordion-icon' }, tokenType.icon),
                            el('span', { className: 'studio-accordion-title' }, tokenType.name),
                            el('span', { className: 'studio-accordion-arrow' }, isOpen ? '▼' : '▶')
                        ),
                        
                        isOpen && el('div', { className: 'studio-accordion-content' },
                            // Handle different token types
                            tokenType.key === 'colors' ? renderColorsWithSubCategories() :
                            tokenType.key === 'gradients' ? renderGradients(tokenType) :
                            tokenType.key === 'layout' ? renderLayoutSettings(tokenType) :
                            tokenType.key === 'spacing' ? renderSpacingSettings(tokenType) :
                            tokenType.key === 'typography' ? renderTypographySettings() :
                            el('div', {}, 'Token type not implemented yet')
                        )
                    );
                })
            )
        );
    }

    /**
     * Block Styles Panel
     * Create and manage custom block styles
     */
    function BlockStylesPanel() {
        const [blockStyles, setBlockStyles] = useState([]);
        const [loading, setLoading] = useState(true);
        const [newStyle, setNewStyle] = useState({
            name: '',
            label: '',
            blockType: 'core/paragraph',
            css: ''
        });
        const [showAddForm, setShowAddForm] = useState(false);

        // Common WordPress blocks
        const blockTypes = [
            { value: 'core/paragraph', label: 'Paragraph' },
            { value: 'core/heading', label: 'Heading' },
            { value: 'core/button', label: 'Button' },
            { value: 'core/group', label: 'Group' },
            { value: 'core/columns', label: 'Columns' },
            { value: 'core/image', label: 'Image' },
            { value: 'core/cover', label: 'Cover' },
            { value: 'core/quote', label: 'Quote' }
        ];

        const addBlockStyle = () => {
            if (!newStyle.name || !newStyle.label) return;
            
            const style = {
                id: Date.now(),
                name: newStyle.name,
                label: newStyle.label,
                blockType: newStyle.blockType,
                css: newStyle.css || `/* Custom styles for ${newStyle.label} */\n.wp-block-${newStyle.blockType.replace('core/', '')}.is-style-${newStyle.name} {\n  /* Add your styles here */\n}`
            };
            
            setBlockStyles(prev => [...prev, style]);
            setNewStyle({ name: '', label: '', blockType: 'core/paragraph', css: '' });
            setShowAddForm(false);
        };

        const deleteBlockStyle = (id) => {
            if (confirm('Delete this block style?')) {
                setBlockStyles(prev => prev.filter(style => style.id !== id));
            }
        };

        return el('div', { className: 'studio-panel studio-block-styles-panel' },
            // Header with Add Button
            el('div', { className: 'studio-panel-header' },
                el('h2', {}, 'Block Styles'),
                el('button', {
                    className: 'studio-add-style-btn',
                    onClick: () => setShowAddForm(!showAddForm)
                }, showAddForm ? 'Cancel' : '+ Add Block Style')
            ),

            // Add Form
            showAddForm && el('div', { className: 'studio-add-style-form' },
                el('div', { className: 'studio-form-row' },
                    el('div', { className: 'studio-form-field' },
                        el('label', {}, 'Style Name'),
                        el('input', {
                            type: 'text',
                            value: newStyle.name,
                            onChange: (e) => setNewStyle(prev => ({ ...prev, name: e.target.value })),
                            placeholder: 'e.g., primary, large, rounded'
                        })
                    ),
                    el('div', { className: 'studio-form-field' },
                        el('label', {}, 'Display Label'),
                        el('input', {
                            type: 'text',
                            value: newStyle.label,
                            onChange: (e) => setNewStyle(prev => ({ ...prev, label: e.target.value })),
                            placeholder: 'e.g., Primary Button, Large Text'
                        })
                    )
                ),
                el('div', { className: 'studio-form-row' },
                    el('div', { className: 'studio-form-field' },
                        el('label', {}, 'Block Type'),
                        el('select', {
                            value: newStyle.blockType,
                            onChange: (e) => setNewStyle(prev => ({ ...prev, blockType: e.target.value }))
                        },
                            blockTypes.map(block => 
                                el('option', { key: block.value, value: block.value }, block.label)
                            )
                        )
                    )
                ),
                el('div', { className: 'studio-form-field' },
                    el('label', {}, 'CSS (Optional)'),
                    el('textarea', {
                        value: newStyle.css,
                        onChange: (e) => setNewStyle(prev => ({ ...prev, css: e.target.value })),
                        placeholder: 'Custom CSS for this style...',
                        rows: 6
                    })
                ),
                el('button', {
                    className: 'studio-add-style-submit',
                    onClick: addBlockStyle
                }, 'Add Style')
            ),

            // Block Styles List
            el('div', { className: 'studio-block-styles-list' },
                blockStyles.length === 0 ? 
                    el('div', { className: 'studio-empty-state' },
                        el('p', {}, '🎨 No block styles yet'),
                        el('p', {}, 'Create custom styles for WordPress blocks')
                    ) :
                    blockStyles.map(style => 
                        el('div', { 
                            className: 'studio-block-style-item',
                            key: style.id
                        },
                            el('div', { className: 'studio-style-header' },
                                el('div', { className: 'studio-style-info' },
                                    el('h4', {}, style.label),
                                    el('span', { className: 'studio-style-meta' }, 
                                        `${blockTypes.find(b => b.value === style.blockType)?.label} • .is-style-${style.name}`
                                    )
                                ),
                                el('button', {
                                    className: 'studio-delete-style-btn',
                                    onClick: () => deleteBlockStyle(style.id)
                                }, '×')
                            ),
                            style.css && el('div', { className: 'studio-style-css' },
                                el('pre', {}, style.css)
                            )
                        )
                    )
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
