(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, TextControl, Notice } = wp.components;
    const { createElement: el, useState, useEffect } = wp.element;

    // Simple DS-Studio Panel Component
    const DSStudioPanel = () => {
        const [colors, setColors] = useState([]);
        const [newColorName, setNewColorName] = useState('');
        const [newColorValue, setNewColorValue] = useState('#3a5a59');
        const [isLoading, setIsLoading] = useState(false);
        const [message, setMessage] = useState('');
        const [editingIndex, setEditingIndex] = useState(-1);
        const [editingName, setEditingName] = useState('');
        const [editingColor, setEditingColor] = useState('');
        const [isRefreshing, setIsRefreshing] = useState(false);

        // Load existing colors from theme.json on component mount
        useEffect(() => {
            loadExistingColors();
        }, []);

        const loadExistingColors = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                if (themeJson.settings && themeJson.settings.color && themeJson.settings.color.palette) {
                    setColors(themeJson.settings.color.palette);
                }
            }
        };

        const addColor = () => {
            if (newColorName.trim()) {
                const newColor = {
                    name: newColorName,
                    slug: newColorName.toLowerCase().replace(/\s+/g, '-'),
                    color: newColorValue
                };
                const updatedColors = [...colors, newColor];
                setColors(updatedColors);
                setNewColorName('');
                setNewColorValue('#3a5a59');
                
                // Save to theme.json
                saveColorsToThemeJson(updatedColors);
            }
        };

        const removeColor = (index) => {
            const updatedColors = colors.filter((_, i) => i !== index);
            setColors(updatedColors);
            saveColorsToThemeJson(updatedColors);
        };

        const startEditing = (index) => {
            setEditingIndex(index);
            setEditingName(colors[index].name);
            setEditingColor(colors[index].color);
        };

        const cancelEditing = () => {
            setEditingIndex(-1);
            setEditingName('');
            setEditingColor('');
        };

        const saveEdit = () => {
            if (editingName.trim()) {
                const updatedColors = [...colors];
                updatedColors[editingIndex] = {
                    name: editingName,
                    slug: editingName.toLowerCase().replace(/\s+/g, '-'),
                    color: editingColor
                };
                setColors(updatedColors);
                saveColorsToThemeJson(updatedColors);
                cancelEditing();
            }
        };

        const forceEditorRefresh = (autoTriggered = false) => {
            setIsRefreshing(true);
            if (!autoTriggered) {
                setMessage('Forcing editor refresh...');
            }
            
            // Apply current colors with aggressive refresh
            applyLivePreview(colors);
            
            // Additional aggressive refresh techniques
            setTimeout(() => {
                // Force iframe refresh if in iframe
                if (window.parent && window.parent !== window) {
                    window.parent.postMessage({ type: 'refreshEditor' }, '*');
                }
                
                // Force WordPress to re-evaluate theme settings
                if (wp.data && wp.data.dispatch) {
                    const { dispatch } = wp.data;
                    
                    // Clear all editor caches
                    if (dispatch('core')) {
                        dispatch('core').invalidateResolutionForStore();
                    }
                    
                    // Force re-initialization of editor
                    if (dispatch('core/editor')) {
                        const currentPost = wp.data.select('core/editor').getCurrentPost();
                        dispatch('core/editor').resetPost(currentPost);
                    }
                }
                
                setIsRefreshing(false);
                if (autoTriggered) {
                    setMessage('✅ Colors saved and editor refreshed! Check your color picker.');
                } else {
                    setMessage('Editor refresh complete! Color palette should now be updated.');
                }
                setTimeout(() => setMessage(''), 4000);
            }, 1000);
        };

        const saveColorsToThemeJson = (colorsToSave) => {
            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            
            // Create updated theme.json structure
            const currentThemeJson = window.dsStudio.currentThemeJson || {};
            const updatedThemeJson = {
                ...currentThemeJson,
                settings: {
                    ...currentThemeJson.settings,
                    color: {
                        ...currentThemeJson.settings?.color,
                        palette: colorsToSave
                    }
                }
            };
            
            formData.append('themeJson', JSON.stringify(updatedThemeJson));

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setMessage('Colors saved to theme.json successfully!');
                    // Update the global theme.json reference
                    window.dsStudio.currentThemeJson = updatedThemeJson;
                    // Apply live preview
                    applyLivePreview(colorsToSave);
                } else {
                    setMessage('Error saving: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const applyLivePreview = (colorsToPreview) => {
            // Remove existing preview styles
            const existingStyle = document.getElementById('ds-studio-live-preview');
            if (existingStyle) {
                existingStyle.remove();
            }

            // Generate CSS variables for colors
            let cssVariables = '';
            colorsToPreview.forEach(color => {
                cssVariables += `--wp--preset--color--${color.slug}: ${color.color}; `;
            });

            // Inject new styles
            const styleElement = document.createElement('style');
            styleElement.id = 'ds-studio-live-preview';
            styleElement.textContent = `:root { ${cssVariables} }`;
            document.head.appendChild(styleElement);
        };

        return el('div', { className: 'ds-studio-panel' },
            el('h3', {}, 'Design System Studio'),
            el('p', {}, 'Manage your theme.json colors'),
            
            // Show message if any
            message && el(Notice, {
                status: message.includes('Error') ? 'error' : 'success',
                isDismissible: false
            }, message),
            
            // Control buttons
            el('div', { style: { marginBottom: '15px', display: 'flex', gap: '10px', flexWrap: 'wrap' } },
                el(Button, {
                    isSecondary: true,
                    onClick: loadExistingColors,
                    disabled: isLoading
                }, 'Reload Colors from theme.json')
            ),
            
            el(PanelBody, { title: 'Add New Color', initialOpen: true },
                el(TextControl, {
                    label: 'Color Name',
                    value: newColorName,
                    onChange: setNewColorName,
                    placeholder: 'Enter color name (e.g., Primary Blue)'
                }),
                el('div', { style: { margin: '10px 0' } },
                    el('label', {}, 'Color Value:'),
                    el('input', {
                        type: 'color',
                        value: newColorValue,
                        onChange: (e) => setNewColorValue(e.target.value),
                        style: { width: '50px', height: '30px', marginLeft: '10px' }
                    })
                ),
                el(Button, {
                    isPrimary: true,
                    onClick: addColor,
                    disabled: !newColorName.trim() || isLoading,
                    isBusy: isLoading
                }, isLoading ? 'Saving...' : 'Add Color')
            ),

            el(PanelBody, { title: `Current Colors (${colors.length})`, initialOpen: true },
                colors.length === 0 
                    ? el('p', {}, 'No colors found in theme.json. Add some above!')
                    : colors.map((color, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '8px',
                                padding: '8px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                backgroundColor: '#f9f9f9'
                            }
                        },
                            el('div', {
                                style: {
                                    width: '20px',
                                    height: '20px',
                                    backgroundColor: color.color,
                                    borderRadius: '3px',
                                    marginRight: '8px',
                                    border: '1px solid #ccc',
                                    cursor: editingIndex === index ? 'default' : 'pointer'
                                },
                                onClick: editingIndex === index ? null : () => startEditing(index),
                                title: editingIndex === index ? '' : 'Click to edit this color'
                            }),
                            editingIndex === index 
                                ? el('div', { style: { flex: 1 } },
                                    el(TextControl, {
                                        label: 'Color Name',
                                        value: editingName,
                                        onChange: setEditingName,
                                        placeholder: 'Enter color name (e.g., Primary Blue)'
                                    }),
                                    el('div', { style: { margin: '10px 0' } },
                                        el('label', {}, 'Color Value:'),
                                        el('input', {
                                            type: 'color',
                                            value: editingColor,
                                            onChange: (e) => setEditingColor(e.target.value),
                                            style: { width: '50px', height: '30px', marginLeft: '10px' }
                                        })
                                    ),
                                    el(Button, {
                                        isPrimary: true,
                                        onClick: saveEdit,
                                        disabled: !editingName.trim() || isLoading,
                                        isBusy: isLoading
                                    }, isLoading ? 'Saving...' : 'Save Edit'),
                                    el(Button, {
                                        isSecondary: true,
                                        onClick: cancelEditing,
                                        disabled: isLoading
                                    }, 'Cancel')
                                )
                                : el('div', { style: { flex: 1 } },
                                    el('strong', {}, color.name),
                                    el('br'),
                                    el('code', { style: { fontSize: '11px', color: '#666' } }, 
                                        `--wp--preset--color--${color.slug}`
                                    ),
                                    el('br'),
                                    el('span', { style: { fontSize: '12px', color: '#888' } }, color.color)
                                ),
                            el(Button, {
                                isDestructive: true,
                                isSmall: true,
                                onClick: editingIndex === index ? cancelEditing : () => removeColor(index),
                                disabled: isLoading
                            }, 'Remove'),
                            editingIndex !== index && el(Button, {
                                isSecondary: true,
                                isSmall: true,
                                onClick: () => startEditing(index),
                                disabled: isLoading,
                                style: { marginLeft: '5px' }
                            }, 'Edit')
                        )
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
