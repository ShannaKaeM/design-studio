/**
 * Design Studio Block Styles Tab
 * Add block styles management to the Design Studio sidebar
 */

// Wait for WordPress to be ready
document.addEventListener('DOMContentLoaded', function() {
    if (!window.wp || !window.wp.plugins) {
        console.error('WordPress plugins API not available');
        return;
    }

    const { __ } = wp.i18n;
    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, TextControl, TextareaControl, Button, Notice } = wp.components;
    const { useState, useEffect } = wp.element;
    const { useSelect, useDispatch } = wp.data;

    // Block Styles Manager Component
    function BlockStylesManager() {
        const [styleName, setStyleName] = useState('');
        const [utilityClasses, setUtilityClasses] = useState('');
        const [description, setDescription] = useState('');
        const [savedStyles, setSavedStyles] = useState({});
        const [isLoading, setIsLoading] = useState(false);
        const [notice, setNotice] = useState(null);
        const [availableClasses, setAvailableClasses] = useState([]);
        const [suggestions, setSuggestions] = useState([]);
        const [showSuggestions, setShowSuggestions] = useState(false);
        const [cursorPosition, setCursorPosition] = useState(0);
        const [editingStyle, setEditingStyle] = useState(null);
        
        // Get selected block data
        const { getSelectedBlock } = useSelect('core/block-editor');
        const { updateBlockAttributes } = useDispatch('core/block-editor');
        
        // Load saved styles and utility classes on mount
        useEffect(() => {
            loadSavedStyles();
            loadUtilityClasses();
        }, []);
        
        // Load saved styles from server (always fresh from theme.json)
        const loadSavedStyles = async () => {
            try {
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'get_block_styles',
                        nonce: window.dsBlockStyles.nonce
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setSavedStyles(data.data.styles);
                    console.log('Loaded fresh styles from theme.json:', Object.keys(data.data.styles));
                }
            } catch (error) {
                console.error('Failed to load saved styles:', error);
            }
        };
        
        // Load utility classes from server
        const loadUtilityClasses = async () => {
            try {
                console.log('Loading utility classes...');
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'get_utility_classes',
                        nonce: window.dsBlockStyles.nonce
                    })
                });
                
                const data = await response.json();
                console.log('Utility classes response:', data);
                if (data.success) {
                    setAvailableClasses(data.data.classes);
                    console.log('Loaded', data.data.classes.length, 'utility classes');
                    console.log('First 10 classes:', data.data.classes.slice(0, 10));
                } else {
                    console.error('Failed to load utility classes:', data);
                }
            } catch (error) {
                console.error('Failed to load utility classes:', error);
            }
        };
        
        // Regenerate utility classes from server
        const regenerateUtilities = async () => {
            try {
                console.log('Regenerating utility classes...');
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'regenerate_utility_classes',
                        nonce: window.dsBlockStyles.nonce
                    })
                });
                
                const data = await response.json();
                console.log('Regenerate utility classes response:', data);
                if (data.success) {
                    setAvailableClasses(data.data.classes);
                    console.log('Regenerated', data.data.classes.length, 'utility classes');
                    console.log('First 10 classes:', data.data.classes.slice(0, 10));
                    showNotice(__('Utility classes regenerated successfully!', 'ds-studio'));
                } else {
                    console.error('Failed to regenerate utility classes:', data);
                    showNotice(__('Failed to regenerate utility classes', 'ds-studio'), 'error');
                }
            } catch (error) {
                console.error('Failed to regenerate utility classes:', error);
                showNotice(__('Error regenerating utility classes', 'ds-studio'), 'error');
            }
        };
        
        // Handle utility classes input change with autocomplete
        const handleUtilityClassesChange = (value) => {
            setUtilityClasses(value);
            
            // Get current word being typed
            const words = value.split(' ');
            const currentWord = words[words.length - 1];
            
            console.log('Autocomplete triggered:', { currentWord, availableClassesCount: availableClasses.length });
            
            if (currentWord.length > 0) {
                // Filter available classes based on current word
                const filtered = availableClasses.filter(className => 
                    className.toLowerCase().startsWith(currentWord.toLowerCase())
                );
                
                console.log('Filtered suggestions:', filtered.slice(0, 5));
                setSuggestions(filtered.slice(0, 10)); // Limit to 10 suggestions
                setShowSuggestions(filtered.length > 0);
            } else {
                setShowSuggestions(false);
            }
        };
        
        // Insert suggestion into utility classes
        const insertSuggestion = (suggestion) => {
            const words = utilityClasses.split(' ');
            words[words.length - 1] = suggestion;
            const newValue = words.join(' ') + ' ';
            setUtilityClasses(newValue);
            setShowSuggestions(false);
        };
        
        // Show notice temporarily
        const showNotice = (message, type = 'success') => {
            setNotice({ message, type });
            setTimeout(() => setNotice(null), 3000);
        };
        
        // Save new block style
        const saveBlockStyle = async () => {
            if (!styleName.trim() || !utilityClasses.trim()) {
                showNotice(__('Style name and utility classes are required', 'ds-studio'), 'error');
                return;
            }
            
            setIsLoading(true);
            
            try {
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'save_block_style',
                        nonce: window.dsBlockStyles.nonce,
                        style_name: styleName,
                        utility_classes: utilityClasses,
                        description: description
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    setSavedStyles(prev => ({
                        ...prev,
                        [styleName]: data.data.style
                    }));
                    
                    // Clear form
                    setStyleName('');
                    setUtilityClasses('');
                    setDescription('');
                    
                    showNotice(__('Block style saved successfully!', 'ds-studio'));
                } else {
                    showNotice(data.data || __('Failed to save block style', 'ds-studio'), 'error');
                }
            } catch (error) {
                showNotice(__('Error saving block style', 'ds-studio'), 'error');
            }
            
            setIsLoading(false);
        };
        
        // Delete block style
        const deleteBlockStyle = async (styleNameToDelete) => {
            if (!confirm(__('Are you sure you want to delete this block style?', 'ds-studio'))) {
                return;
            }
            
            try {
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'delete_block_style',
                        nonce: window.dsBlockStyles.nonce,
                        style_name: styleNameToDelete
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    setSavedStyles(prev => {
                        const newStyles = { ...prev };
                        delete newStyles[styleNameToDelete];
                        return newStyles;
                    });
                    
                    showNotice(__('Block style deleted successfully!', 'ds-studio'));
                } else {
                    showNotice(data.data || __('Failed to delete block style', 'ds-studio'), 'error');
                }
            } catch (error) {
                showNotice(__('Error deleting block style', 'ds-studio'), 'error');
            }
        };
        
        // Apply block style to currently selected block
        const applyBlockStyle = (styleKey, styleData) => {
            const selectedBlock = getSelectedBlock();
            if (!selectedBlock) {
                showNotice(__('Please select a block first', 'ds-studio'), 'error');
                return;
            }
            
            const currentClassName = selectedBlock.attributes.className || '';
            const utilityClasses = styleData.classes;
            
            // Combine existing classes with new utility classes
            const existingClasses = currentClassName.split(' ').filter(c => c.trim());
            const newClasses = utilityClasses.split(' ').filter(c => c.trim());
            
            // Remove duplicates and combine
            const allClasses = [...new Set([...existingClasses, ...newClasses])];
            const newClassName = allClasses.join(' ').trim();
            
            updateBlockAttributes(selectedBlock.clientId, {
                className: newClassName
            });
            
            showNotice(__(`Applied "${styleKey}" style to ${selectedBlock.name.replace('core/', '')} block`, 'ds-studio'));
        };
        
        // Edit block style
        const editBlockStyle = (styleKey) => {
            const styleData = savedStyles[styleKey];
            setEditingStyle(styleKey);
            setStyleName(styleKey);
            setUtilityClasses(styleData.classes);
            setDescription(styleData.description);
        };
        
        // Update block style
        const updateBlockStyle = async () => {
            if (!styleName.trim() || !utilityClasses.trim()) {
                showNotice(__('Style name and utility classes are required', 'ds-studio'), 'error');
                return;
            }
            
            setIsLoading(true);
            
            try {
                const response = await fetch(window.dsBlockStyles.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'update_block_style',
                        nonce: window.dsBlockStyles.nonce,
                        style_name: styleName,
                        utility_classes: utilityClasses,
                        description: description
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    setSavedStyles(prev => ({
                        ...prev,
                        [styleName]: data.data.style
                    }));
                    
                    // Clear form
                    setStyleName('');
                    setUtilityClasses('');
                    setDescription('');
                    setEditingStyle(null);
                    
                    showNotice(__('Block style updated successfully!', 'ds-studio'));
                } else {
                    showNotice(data.data || __('Failed to update block style', 'ds-studio'), 'error');
                }
            } catch (error) {
                showNotice(__('Error updating block style', 'ds-studio'), 'error');
            }
            
            setIsLoading(false);
        };
        
        return wp.element.createElement('div', { style: { padding: '16px' } },
            notice && wp.element.createElement(Notice, {
                status: notice.type,
                isDismissible: false,
                style: { marginBottom: '16px' }
            }, notice.message),
            
            // Create New Block Style
            wp.element.createElement(PanelBody, {
                title: __('Create Block Style', 'ds-studio'),
                initialOpen: true
            },
                wp.element.createElement(TextControl, {
                    label: __('Style Name', 'ds-studio'),
                    value: styleName,
                    onChange: setStyleName,
                    placeholder: 'e.g. card-title, hero-heading',
                    help: __('This will be the CSS class name (use lowercase and hyphens)', 'ds-studio')
                }),
                
                wp.element.createElement('div', {
                    style: { 
                        position: 'relative',
                        marginBottom: '12px'
                    }
                },
                    wp.element.createElement(TextareaControl, {
                        label: __('Utility Classes', 'ds-studio'),
                        value: utilityClasses,
                        onChange: handleUtilityClassesChange,
                        placeholder: __('e.g., text-large font-bold text-primary p-lg', 'ds-studio'),
                        rows: 3,
                        style: { fontFamily: 'monospace', fontSize: '13px' }
                    }),
                    
                    // Autocomplete suggestions dropdown
                    showSuggestions && suggestions.length > 0 && wp.element.createElement('div', {
                        style: {
                            position: 'absolute',
                            top: '100%',
                            left: '0',
                            right: '0',
                            backgroundColor: '#fff',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
                            zIndex: 1000,
                            maxHeight: '200px',
                            overflowY: 'auto'
                        }
                    },
                        suggestions.map((suggestion, index) =>
                            wp.element.createElement('div', {
                                key: suggestion,
                                onClick: () => insertSuggestion(suggestion),
                                style: {
                                    padding: '8px 12px',
                                    cursor: 'pointer',
                                    borderBottom: index < suggestions.length - 1 ? '1px solid #eee' : 'none',
                                    fontSize: '13px',
                                    fontFamily: 'monospace'
                                },
                                onMouseEnter: (e) => e.target.style.backgroundColor = '#f0f0f0',
                                onMouseLeave: (e) => e.target.style.backgroundColor = '#fff'
                            }, suggestion)
                        )
                    ),
                    
                    // Helper text with common classes
                    wp.element.createElement('div', {
                        style: { 
                            fontSize: '11px', 
                            color: '#666', 
                            marginTop: '4px',
                            fontFamily: 'monospace'
                        }
                    }, `Available: text-small/medium/large/x-large, font-bold/medium, uppercase/lowercase, text-primary/secondary, bg-primary, p-xs/sm/md/lg, m-xs/sm/md/lg`)
                ),
                
                wp.element.createElement(TextControl, {
                    label: __('Description (Optional)', 'ds-studio'),
                    value: description,
                    onChange: setDescription,
                    placeholder: 'e.g. Large heading for card titles'
                }),
                
                wp.element.createElement(Button, {
                    variant: 'primary',
                    onClick: editingStyle ? updateBlockStyle : saveBlockStyle,
                    isBusy: isLoading,
                    disabled: !styleName.trim() || !utilityClasses.trim() || isLoading,
                    style: { width: '100%', marginTop: '12px' }
                }, editingStyle ? __('Update Block Style', 'ds-studio') : (isLoading ? __('Saving...', 'ds-studio') : __('Create Block Style', 'ds-studio')))
            ),
            
            // Saved Block Styles
            wp.element.createElement(PanelBody, {
                title: __('Saved Block Styles', 'ds-studio'),
                initialOpen: false
            },
                Object.keys(savedStyles).length === 0 ?
                    wp.element.createElement('div', {
                        style: {
                            textAlign: 'center',
                            color: '#666',
                            fontStyle: 'italic',
                            padding: '20px 0'
                        }
                    }, __('No block styles created yet.', 'ds-studio')) :
                    wp.element.createElement('div', {},
                        Object.entries(savedStyles).map(([styleKey, styleData]) =>
                            wp.element.createElement('div', {
                                key: styleKey,
                                style: {
                                    border: '1px solid #ddd',
                                    borderRadius: '4px',
                                    padding: '12px',
                                    marginBottom: '12px',
                                    background: '#f9f9f9'
                                }
                            },
                                wp.element.createElement('div', {
                                    style: {
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center',
                                        marginBottom: '8px'
                                    }
                                },
                                    wp.element.createElement('strong', {
                                        style: { fontSize: '14px' }
                                    }, styleKey),
                                    wp.element.createElement(Button, {
                                        variant: 'link',
                                        isDestructive: true,
                                        onClick: () => deleteBlockStyle(styleKey),
                                        style: { fontSize: '12px', padding: '4px' }
                                    }, __('Delete', 'ds-studio')),
                                    wp.element.createElement(Button, {
                                        variant: 'link',
                                        onClick: () => applyBlockStyle(styleKey, styleData),
                                        style: { fontSize: '12px', padding: '4px' }
                                    }, __('Apply', 'ds-studio')),
                                    wp.element.createElement(Button, {
                                        variant: 'link',
                                        onClick: () => editBlockStyle(styleKey),
                                        style: { fontSize: '12px', padding: '4px' }
                                    }, __('Edit', 'ds-studio'))
                                ),
                                
                                wp.element.createElement('div', {
                                    style: {
                                        background: '#f1f1f1',
                                        padding: '6px 8px',
                                        borderRadius: '3px',
                                        fontFamily: 'monospace',
                                        fontSize: '11px',
                                        marginBottom: '8px',
                                        wordBreak: 'break-all'
                                    }
                                }, styleData.classes),
                                
                                styleData.description && wp.element.createElement('div', {
                                    style: {
                                        fontSize: '12px',
                                        color: '#666',
                                        fontStyle: 'italic',
                                        marginBottom: '8px'
                                    }
                                }, styleData.description),
                                
                                wp.element.createElement('div', {
                                    style: { fontSize: '11px', color: '#666' }
                                },
                                    wp.element.createElement('strong', {}, __('Usage:', 'ds-studio')), ' ',
                                    wp.element.createElement('code', {
                                        style: {
                                            background: '#e1e1e1',
                                            padding: '2px 4px',
                                            borderRadius: '2px'
                                        }
                                    }, `class="${styleKey}"`)
                                )
                            )
                        )
                    )
            ),
            
            // Refresh Button
            wp.element.createElement(Button, {
                variant: 'secondary',
                onClick: loadSavedStyles,
                style: { width: '100%', marginTop: '12px' }
            }, __('Refresh Styles', 'ds-studio')),
            
            // Regenerate Utilities Button
            wp.element.createElement(Button, {
                variant: 'tertiary',
                onClick: regenerateUtilities,
                style: { width: '100%', marginTop: '8px', fontSize: '12px' }
            }, __('Regenerate Utilities', 'ds-studio'))
        );
    }

    // Register the plugin sidebar
    try {
        registerPlugin('ds-studio-block-styles', {
            render: () => wp.element.createElement(wp.element.Fragment, {},
                wp.element.createElement(PluginSidebarMoreMenuItem, {
                    target: 'ds-studio-block-styles'
                }, __('Block Styles', 'ds-studio')),
                
                wp.element.createElement(PluginSidebar, {
                    name: 'ds-studio-block-styles',
                    title: __('Design Studio - Block Styles', 'ds-studio'),
                    icon: 'admin-customizer'
                }, wp.element.createElement(BlockStylesManager))
            )
        });
        
        console.log('DS Studio Block Styles plugin registered successfully');
    } catch (error) {
        console.error('Failed to register DS Studio Block Styles plugin:', error);
    }
});
