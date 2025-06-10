(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editor;
    const { PanelBody, Button, TextControl, Notice, ColorPicker, Popover, TabPanel } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    // Simple DS-Studio Panel Component
    const DSStudioPanel = () => {
        const [colors, setColors] = useState([]);
        const [newColorName, setNewColorName] = useState('');
        const [newColorValue, setNewColorValue] = useState('#3a5a59');
        const [isLoading, setIsLoading] = useState(false);
        const [message, setMessage] = useState('');

        // Typography state
        const [typography, setTypography] = useState({
            fontFamilies: [],
            fontSizes: [],
            fontWeights: [],
            lineHeights: [],
            letterSpacing: [],
            textTransforms: []
        });
        const [newTypographyItem, setNewTypographyItem] = useState({
            type: 'fontSizes',
            name: '',
            value: ''
        });

        // Load existing colors and typography from theme.json on component mount
        useEffect(() => {
            loadExistingColors();
            loadExistingTypography();
        }, []);

        const loadExistingColors = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                if (themeJson.settings && themeJson.settings.color && themeJson.settings.color.palette) {
                    setColors(themeJson.settings.color.palette);
                }
            }
        };

        const loadExistingTypography = () => {
            const formData = new FormData();
            formData.append('action', 'ds_studio_get_theme_json');
            formData.append('nonce', window.dsStudio.nonce);

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data) {
                    const themeJson = result.data;
                    const typographyData = {
                        fontFamilies: themeJson.settings?.typography?.fontFamilies || [],
                        fontSizes: themeJson.settings?.typography?.fontSizes || [],
                        fontWeights: themeJson.settings?.custom?.typography?.fontWeight || [],
                        lineHeights: themeJson.settings?.custom?.typography?.lineHeight || [],
                        letterSpacing: themeJson.settings?.custom?.typography?.letterSpacing || [],
                        textTransforms: themeJson.settings?.custom?.typography?.textTransform || []
                    };
                    setTypography(typographyData);
                }
            })
            .catch(error => {
                console.error('Error loading typography:', error);
            });
        };

        const saveColor = () => {
            if (!newColorName.trim() || !newColorValue.trim()) {
                setMessage('Please enter both color name and value');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('color_name', newColorName.trim());
            formData.append('color_value', newColorValue.trim());

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setMessage('Color saved successfully!');
                    setNewColorName('');
                    setNewColorValue('#3a5a59');
                    loadExistingColors();
                    
                    // Force editor to refresh color palette
                    if (wp.data && wp.data.dispatch) {
                        const { dispatch } = wp.data;
                        if (dispatch('core/editor')) {
                            dispatch('core/editor').editPost({});
                        }
                    }
                } else {
                    setMessage('Error: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const saveTypographyItem = () => {
            if (!newTypographyItem.name.trim() || !newTypographyItem.value.trim()) {
                setMessage('Please enter both name and value');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('typography_type', newTypographyItem.type);
            formData.append('typography_name', newTypographyItem.name.trim());
            formData.append('typography_value', newTypographyItem.value.trim());

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setMessage('Typography item saved successfully!');
                    setNewTypographyItem({
                        type: 'fontSizes',
                        name: '',
                        value: ''
                    });
                    loadExistingTypography();
                    
                    // Force editor to refresh
                    if (wp.data && wp.data.dispatch) {
                        const { dispatch } = wp.data;
                        if (dispatch('core/editor')) {
                            dispatch('core/editor').editPost({});
                        }
                    }
                } else {
                    setMessage('Error: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const renderTypographySection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '6px',
                                padding: '6px',
                                border: '1px solid #ddd',
                                borderRadius: '3px',
                                fontSize: '12px'
                            } 
                        },
                            el('div', { style: { flex: 1 } },
                                el('div', { style: { fontWeight: 'bold' } }, item.name || item.slug),
                                el('div', { style: { color: '#666' } }, 
                                    type === 'fontFamilies' ? item.fontFamily : 
                                    type === 'fontSizes' ? item.size :
                                    item.value || item.slug
                                )
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        return el('div', { style: { padding: '16px' } },
            el('h3', { style: { marginTop: '0' } }, 'Design System Studio'),
            
            // Show message if any
            message && el(Notice, {
                status: message.includes('Error') ? 'error' : 'success',
                isDismissible: false
            }, message),
            
            // Colors Module
            el(PanelBody, { title: 'Colors', initialOpen: true },
                // Existing colors
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Current Colors'),
                    colors.length > 0 ? 
                        colors.map((color, index) => 
                            el('div', { 
                                key: index,
                                style: { 
                                    display: 'flex', 
                                    alignItems: 'center', 
                                    marginBottom: '8px',
                                    padding: '8px',
                                    border: '1px solid #ddd',
                                    borderRadius: '4px'
                                } 
                            },
                                el('div', {
                                    style: {
                                        width: '24px',
                                        height: '24px',
                                        backgroundColor: color.color,
                                        borderRadius: '4px',
                                        marginRight: '12px',
                                        border: '1px solid #ccc'
                                    }
                                }),
                                el('div', { style: { flex: 1 } },
                                    el('div', { style: { fontWeight: 'bold' } }, color.name),
                                    el('div', { style: { fontSize: '12px', color: '#666' } }, color.color)
                                )
                            )
                        ) :
                        el('p', { style: { color: '#666', fontStyle: 'italic' } }, 'No colors found in theme.json')
                ),
                
                // Add new color
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Add New Color'),
                    el(TextControl, {
                        label: 'Color Name',
                        value: newColorName,
                        onChange: setNewColorName,
                        placeholder: 'e.g., Primary Blue'
                    }),
                    el('div', { style: { marginBottom: '10px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Color Value'),
                        el('input', {
                            type: 'color',
                            value: newColorValue,
                            onChange: (e) => setNewColorValue(e.target.value),
                            style: { width: '100%', height: '40px', border: 'none', borderRadius: '4px' }
                        })
                    ),
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading,
                        onClick: saveColor
                    }, isLoading ? 'Saving...' : 'Save Color')
                )
            ),

            // Typography Module
            el(PanelBody, { title: 'Typography', initialOpen: false },
                // Current Typography
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Current Typography'),
                    renderTypographySection('Font Families', typography.fontFamilies, 'fontFamilies'),
                    renderTypographySection('Font Sizes', typography.fontSizes, 'fontSizes'),
                    renderTypographySection('Font Weights', typography.fontWeights, 'fontWeights'),
                    renderTypographySection('Line Heights', typography.lineHeights, 'lineHeights'),
                    renderTypographySection('Letter Spacing', typography.letterSpacing, 'letterSpacing'),
                    renderTypographySection('Text Transforms', typography.textTransforms, 'textTransforms')
                ),

                // Add new typography item
                el('div', { style: { padding: '15px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Add Typography Token'),
                    el('div', { style: { marginBottom: '10px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Type'),
                        el('select', {
                            value: newTypographyItem.type,
                            onChange: (e) => setNewTypographyItem({...newTypographyItem, type: e.target.value}),
                            style: { width: '100%', padding: '8px', border: '1px solid #ddd', borderRadius: '4px' }
                        },
                            el('option', { value: 'fontSizes' }, 'Font Size'),
                            el('option', { value: 'fontFamilies' }, 'Font Family'),
                            el('option', { value: 'fontWeights' }, 'Font Weight'),
                            el('option', { value: 'lineHeights' }, 'Line Height'),
                            el('option', { value: 'letterSpacing' }, 'Letter Spacing'),
                            el('option', { value: 'textTransforms' }, 'Text Transform')
                        )
                    ),
                    el(TextControl, {
                        label: 'Name',
                        value: newTypographyItem.name,
                        onChange: (value) => setNewTypographyItem({...newTypographyItem, name: value}),
                        placeholder: 'e.g., Heading Large, Body Text, etc.'
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newTypographyItem.value,
                        onChange: (value) => setNewTypographyItem({...newTypographyItem, value: value}),
                        placeholder: newTypographyItem.type === 'fontSizes' ? 'e.g., 2rem, 24px' :
                                   newTypographyItem.type === 'fontFamilies' ? 'e.g., "Inter", sans-serif' :
                                   newTypographyItem.type === 'fontWeights' ? 'e.g., 400, 600, bold' :
                                   newTypographyItem.type === 'lineHeights' ? 'e.g., 1.5, 1.2' :
                                   newTypographyItem.type === 'letterSpacing' ? 'e.g., 0.05em, 1px' :
                                   'e.g., uppercase, lowercase, capitalize'
                    }),
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading,
                        onClick: saveTypographyItem
                    }, isLoading ? 'Saving...' : 'Save Typography Token')
                )
            )
        );
    };

    // Register the plugin
    registerPlugin('ds-studio', {
        render: () => {
            return [
                el(PluginSidebarMoreMenuItem, {
                    target: 'ds-studio-sidebar',
                    icon: 'art'
                }, 'DS-Studio'),
                
                el(PluginSidebar, {
                    name: 'ds-studio-sidebar',
                    title: 'Design System Studio',
                    icon: 'art'
                }, el(DSStudioPanel))
            ];
        }
    });

})();
