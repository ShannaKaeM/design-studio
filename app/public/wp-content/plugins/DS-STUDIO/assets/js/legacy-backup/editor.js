(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editor;
    const { PanelBody, Button, TextControl, Notice, ColorPicker, Popover, TabPanel } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    // Enhanced Color Picker with CMYK support (no OKLCH)
    const EnhancedColorPicker = ({ color, onChange, onClose }) => {
        const [currentColor, setCurrentColor] = useState(color);
        const [activeTab, setActiveTab] = useState('hex');
        const [cmyk, setCmyk] = useState({ c: 0, m: 0, y: 0, k: 0 });
        const [hsl, setHsl] = useState({ h: 0, s: 0, l: 0 });

        // Color conversion functions
        const hexToHsl = (hex) => {
            const r = parseInt(hex.slice(1, 3), 16) / 255;
            const g = parseInt(hex.slice(3, 5), 16) / 255;
            const b = parseInt(hex.slice(5, 7), 16) / 255;

            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            let h, s, l = (max + min) / 2;

            if (max === min) {
                h = s = 0;
            } else {
                const d = max - min;
                s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }

            return {
                h: Math.round(h * 360),
                s: Math.round(s * 100),
                l: Math.round(l * 100)
            };
        };

        const hslToHex = (h, s, l) => {
            h /= 360;
            s /= 100;
            l /= 100;

            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };

            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            const r = hue2rgb(p, q, h + 1/3);
            const g = hue2rgb(p, q, h);
            const b = hue2rgb(p, q, h - 1/3);

            const toHex = (c) => {
                const hex = Math.round(c * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };

            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        };

        const hexToCmyk = (hex) => {
            const r = parseInt(hex.slice(1, 3), 16) / 255;
            const g = parseInt(hex.slice(3, 5), 16) / 255;
            const b = parseInt(hex.slice(5, 7), 16) / 255;

            const k = 1 - Math.max(r, g, b);
            const c = k === 1 ? 0 : (1 - r - k) / (1 - k);
            const m = k === 1 ? 0 : (1 - g - k) / (1 - k);
            const y = k === 1 ? 0 : (1 - b - k) / (1 - k);

            return {
                c: Math.round(c * 100),
                m: Math.round(m * 100),
                y: Math.round(y * 100),
                k: Math.round(k * 100)
            };
        };

        const cmykToHex = (c, m, y, k) => {
            c /= 100; m /= 100; y /= 100; k /= 100;
            const r = Math.round(255 * (1 - c) * (1 - k));
            const g = Math.round(255 * (1 - m) * (1 - k));
            const b = Math.round(255 * (1 - y) * (1 - k));
            
            return '#' + [r, g, b].map(x => {
                const hex = x.toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        };

        useEffect(() => {
            setCurrentColor(color);
            setHsl(hexToHsl(color));
            setCmyk(hexToCmyk(color));
        }, [color]);

        const handleColorChange = (newColor) => {
            setCurrentColor(newColor);
            setHsl(hexToHsl(newColor));
            setCmyk(hexToCmyk(newColor));
            onChange(newColor);
        };

        const handleCmykChange = (type, value) => {
            const newCmyk = { ...cmyk, [type]: value };
            setCmyk(newCmyk);
            const newHex = cmykToHex(newCmyk.c, newCmyk.m, newCmyk.y, newCmyk.k);
            setCurrentColor(newHex);
            setHsl(hexToHsl(newHex));
            onChange(newHex);
        };

        const handleHslChange = (type, value) => {
            const newHsl = { ...hsl, [type]: value };
            setHsl(newHsl);
            const newHex = hslToHex(newHsl.h, newHsl.s, newHsl.l);
            setCurrentColor(newHex);
            setCmyk(hexToCmyk(newHex));
            onChange(newHex);
        };

        return el('div', {
            style: {
                background: 'white',
                border: '1px solid #ddd',
                borderRadius: '4px',
                padding: '15px',
                boxShadow: '0 2px 10px rgba(0,0,0,0.1)',
                minWidth: '300px'
            }
        },
            el(TabPanel, {
                tabs: [
                    { name: 'hex', title: 'HEX' },
                    { name: 'hsl', title: 'HSL' },
                    { name: 'cmyk', title: 'CMYK' }
                ],
                onSelect: setActiveTab
            }, (tab) => {
                if (tab.name === 'hex') {
                    return el('div', {},
                        el(ColorPicker, {
                            color: currentColor,
                            onChange: handleColorChange,
                            disableAlpha: true
                        }),
                        el(TextControl, {
                            label: 'HEX Value',
                            value: currentColor,
                            onChange: handleColorChange,
                            style: { marginTop: '10px' }
                        })
                    );
                } else if (tab.name === 'hsl') {
                    return el('div', {},
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Hue (0-360)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 360,
                                value: hsl.h,
                                onChange: (e) => handleHslChange('h', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, hsl.h)
                        ),
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Saturation (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: hsl.s,
                                onChange: (e) => handleHslChange('s', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, hsl.s + '%')
                        ),
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Lightness (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: hsl.l,
                                onChange: (e) => handleHslChange('l', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, hsl.l + '%')
                        )
                    );
                } else if (tab.name === 'cmyk') {
                    return el('div', {},
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Cyan (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: cmyk.c,
                                onChange: (e) => handleCmykChange('c', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, cmyk.c + '%')
                        ),
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Magenta (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: cmyk.m,
                                onChange: (e) => handleCmykChange('m', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, cmyk.m + '%')
                        ),
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Yellow (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: cmyk.y,
                                onChange: (e) => handleCmykChange('y', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, cmyk.y + '%')
                        ),
                        el('div', { style: { marginBottom: '10px' } },
                            el('label', {}, 'Black (0-100)'),
                            el('input', {
                                type: 'range',
                                min: 0,
                                max: 100,
                                value: cmyk.k,
                                onChange: (e) => handleCmykChange('k', parseInt(e.target.value)),
                                style: { width: '100%', marginLeft: '10px' }
                            }),
                            el('span', { style: { marginLeft: '10px' } }, cmyk.k + '%')
                        )
                    );
                }
            }),
            el('div', {
                style: {
                    marginTop: '15px',
                    display: 'flex',
                    justifyContent: 'space-between'
                }
            },
                el(Button, {
                    variant: 'primary',
                    onClick: onClose
                }, 'Done'),
                el(Button, {
                    variant: 'secondary',
                    onClick: onClose
                }, 'Cancel')
            )
        );
    };

    // Color item component for displaying individual colors
    const ColorItem = ({ color, onEdit, onDelete }) => {
        const [showPicker, setShowPicker] = useState(false);
        const [isEditing, setIsEditing] = useState(false);
        const [editedColor, setEditedColor] = useState(color);

        const handleSave = () => {
            onEdit(editedColor);
            setIsEditing(false);
            setShowPicker(false);
        };

        const handleCancel = () => {
            setEditedColor(color);
            setIsEditing(false);
            setShowPicker(false);
        };

        return el('div', { 
            style: { 
                display: 'flex', 
                alignItems: 'center', 
                marginBottom: '8px',
                padding: '8px',
                border: '1px solid #e0e0e0',
                borderRadius: '4px',
                backgroundColor: '#f9f9f9'
            } 
        },
            el('div', {
                style: {
                    width: '30px',
                    height: '30px',
                    backgroundColor: color.color,
                    border: '1px solid #ccc',
                    borderRadius: '3px',
                    marginRight: '10px',
                    cursor: 'pointer'
                },
                onClick: () => setShowPicker(true)
            }),
            el('div', { style: { flex: 1 } },
                el('div', { style: { fontWeight: 'bold' } }, color.name),
                el('div', { style: { fontSize: '12px', color: '#666' } }, color.color),
                el('div', { style: { fontSize: '11px', color: '#999' } }, `Slug: ${color.slug}`)
            ),
            el('div', { style: { display: 'flex', gap: '5px' } },
                el(Button, {
                    size: 'small',
                    variant: 'secondary',
                    onClick: () => setIsEditing(true)
                }, 'Edit'),
                el(Button, {
                    size: 'small',
                    variant: 'secondary',
                    isDestructive: true,
                    onClick: () => onDelete(color.slug)
                }, 'Delete')
            ),
            showPicker && el(Popover, {
                position: 'middle center',
                onClose: handleCancel
            },
                el(EnhancedColorPicker, {
                    color: editedColor.color,
                    onChange: (newColor) => setEditedColor({ ...editedColor, color: newColor }),
                    onClose: () => {
                        if (isEditing) {
                            handleSave();
                        } else {
                            handleCancel();
                        }
                    }
                })
            )
        );
    };

    // Main DS-Studio Panel Component
    const DSStudioPanel = () => {
        // Get initial colors from pre-loaded theme.json data
        const getInitialColors = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson && 
                window.dsStudio.currentThemeJson.settings && 
                window.dsStudio.currentThemeJson.settings.color && 
                window.dsStudio.currentThemeJson.settings.color.palette) {
                return window.dsStudio.currentThemeJson.settings.color.palette;
            }
            return [];
        };

        const initialColors = getInitialColors();
        const [colors, setColors] = useState(initialColors);
        const [newColorName, setNewColorName] = useState('');
        const [newColorValue, setNewColorValue] = useState('#3a5a59');
        const [isLoading, setIsLoading] = useState(false);
        const [message, setMessage] = useState('');

        // Typography state
        const [fontFamilies, setFontFamilies] = useState([]);
        const [fontSizes, setFontSizes] = useState([]);
        const [newFontFamily, setNewFontFamily] = useState({ name: '', fontFamily: '' });
        const [newFontSize, setNewFontSize] = useState({ name: '', size: '' });
        const [typographyLoading, setTypographyLoading] = useState(false);
        const [typographyMessage, setTypographyMessage] = useState('');

        // Load existing colors from theme.json on component mount
        useEffect(() => {
            // If we already have colors from the pre-loaded data, show success message
            if (initialColors.length > 0) {
                setMessage(`Loaded ${initialColors.length} colors from theme.json`);
                setTimeout(() => setMessage(''), 3000);
            } else {
                // Fallback to AJAX if no pre-loaded data
                loadExistingColors();
            }
            // Also load typography
            loadExistingTypography();
        }, []);

        const loadExistingColors = () => {
            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_get_theme_json');
            formData.append('nonce', window.dsStudio.nonce);

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success && result.data) {
                    const themeJson = JSON.parse(result.data);
                    if (themeJson.settings && themeJson.settings.color && themeJson.settings.color.palette) {
                        setColors(themeJson.settings.color.palette);
                        setMessage(`Loaded ${themeJson.settings.color.palette.length} colors from theme.json`);
                    } else {
                        setMessage('No colors found in theme.json');
                    }
                } else {
                    setMessage('Error loading colors: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const loadExistingTypography = () => {
            // First try to load from pre-loaded theme.json data
            if (window.dsStudio && window.dsStudio.currentThemeJson && 
                window.dsStudio.currentThemeJson.settings && 
                window.dsStudio.currentThemeJson.settings.typography) {
                
                const typography = window.dsStudio.currentThemeJson.settings.typography;
                
                // Load font families
                const families = typography.fontFamilies || [];
                setFontFamilies(families);
                
                // Load font sizes  
                const sizes = typography.fontSizes || [];
                setFontSizes(sizes);
                
                setTypographyMessage(`Loaded ${families.length} font families and ${sizes.length} font sizes from theme.json`);
                setTimeout(() => setTypographyMessage(''), 3000);
                return;
            }

            // Fallback to AJAX if no pre-loaded data
            setTypographyLoading(true);
            setTypographyMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_get_typography');
            formData.append('nonce', window.dsStudio.nonce);

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setTypographyLoading(false);
                if (result.success && result.data) {
                    const typographyData = JSON.parse(result.data);
                    if (typographyData.fontFamilies) {
                        setFontFamilies(typographyData.fontFamilies);
                    }
                    if (typographyData.fontSizes) {
                        setFontSizes(typographyData.fontSizes);
                    }
                    setTypographyMessage('Typography loaded successfully!');
                } else {
                    setTypographyMessage('Error loading typography: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setTypographyMessage(''), 3000);
            })
            .catch(error => {
                setTypographyLoading(false);
                setTypographyMessage('Error: ' + error.message);
                setTimeout(() => setTypographyMessage(''), 3000);
            });
        };

        const addColor = () => {
            if (!newColorName.trim()) {
                setMessage('Please enter a color name');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            const slug = newColorName.toLowerCase().replace(/[^a-z0-9]/g, '-');
            const newColor = {
                name: newColorName.trim(),
                slug: slug,
                color: newColorValue
            };

            const updatedColors = [...colors.filter(c => c.slug !== slug), newColor];
            saveColorsToThemeJson(updatedColors);
        };

        const editColor = (editedColor) => {
            const updatedColors = colors.map(color => 
                color.slug === editedColor.slug ? editedColor : color
            );
            saveColorsToThemeJson(updatedColors);
        };

        const deleteColor = (slug) => {
            const updatedColors = colors.filter(color => color.slug !== slug);
            saveColorsToThemeJson(updatedColors);
        };

        const saveColorsToThemeJson = (colorsToSave) => {
            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('colors', JSON.stringify(colorsToSave));

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setColors(colorsToSave);
                    setMessage('Colors saved to theme.json successfully!');
                    setNewColorName('');
                    setNewColorValue('#3a5a59');
                    
                    // Apply live preview
                    applyColorLivePreview(colorsToSave);
                    
                    // Force editor refresh
                    if (window.wp && window.wp.data) {
                        window.wp.data.dispatch('core/editor').refreshPost();
                    }
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

        const applyColorLivePreview = (colorsToPreview) => {
            let styleElement = document.getElementById('ds-studio-live-preview');
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = 'ds-studio-live-preview';
                document.head.appendChild(styleElement);
            }

            const cssVariables = colorsToPreview.map(color => 
                `--wp--preset--color--${color.slug}: ${color.color};`
            ).join('\n');

            styleElement.textContent = `:root { ${cssVariables} }`;
        };

        const addFontFamily = () => {
            if (newFontFamily.name.trim() && newFontFamily.fontFamily.trim()) {
                const slug = newFontFamily.name.toLowerCase().replace(/[^a-z0-9]/g, '-');
                const updatedFamilies = [...fontFamilies, {
                    name: newFontFamily.name,
                    slug: slug,
                    fontFamily: newFontFamily.fontFamily
                }];
                saveTypographyToThemeJson(updatedFamilies, fontSizes);
                setNewFontFamily({ name: '', fontFamily: '' });
            }
        };

        const addFontSize = () => {
            if (newFontSize.name.trim() && newFontSize.size.trim()) {
                const slug = newFontSize.name.toLowerCase().replace(/[^a-z0-9]/g, '-');
                const updatedSizes = [...fontSizes, {
                    name: newFontSize.name,
                    slug: slug,
                    size: newFontSize.size
                }];
                saveTypographyToThemeJson(fontFamilies, updatedSizes);
                setNewFontSize({ name: '', size: '' });
            }
        };

        const saveTypographyToThemeJson = (familiesToSave, sizesToSave) => {
            setTypographyLoading(true);
            setTypographyMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_typography');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('fontFamilies', JSON.stringify(familiesToSave));
            formData.append('fontSizes', JSON.stringify(sizesToSave));

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setTypographyLoading(false);
                if (result.success) {
                    setFontFamilies(familiesToSave);
                    setFontSizes(sizesToSave);
                    setTypographyMessage('Typography saved to theme.json successfully!');
                    
                    // Apply live preview
                    applyTypographyLivePreview(familiesToSave, sizesToSave);
                } else {
                    setTypographyMessage('Error saving: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setTypographyMessage(''), 3000);
            })
            .catch(error => {
                setTypographyLoading(false);
                setTypographyMessage('Error: ' + error.message);
                setTimeout(() => setTypographyMessage(''), 3000);
            });
        };

        const applyTypographyLivePreview = (familiesToPreview, sizesToPreview) => {
            let styleElement = document.getElementById('ds-studio-typography-preview');
            if (!styleElement) {
                styleElement = document.createElement('style');
                styleElement.id = 'ds-studio-typography-preview';
                document.head.appendChild(styleElement);
            }

            const fontFamilyVariables = familiesToPreview.map(family => 
                `--wp--preset--font-family--${family.slug}: ${family.fontFamily};`
            ).join('\n');

            const fontSizeVariables = sizesToPreview.map(size => 
                `--wp--preset--font-size--${size.slug}: ${size.size};`
            ).join('\n');

            styleElement.textContent = `:root { ${fontFamilyVariables}\n${fontSizeVariables} }`;
        };

        return el('div', { className: 'ds-studio-panel' },
            el('h3', {}, 'Design System Studio'),
            el('p', {}, 'Manage your theme.json colors'),
            
            // Show message if any
            message && el(Notice, {
                status: message.includes('Error') ? 'error' : 'success',
                isDismissible: false
            }, message),

            // Colors Module
            el(PanelBody, { title: 'Colors', initialOpen: true },
                // Add new color section
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', {}, 'Add New Color'),
                    el(TextControl, {
                        label: 'Color Name',
                        value: newColorName,
                        onChange: setNewColorName,
                        placeholder: 'e.g., Primary Blue'
                    }),
                    el('div', { style: { marginBottom: '10px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px' } }, 'Color Value'),
                        el('div', { style: { display: 'flex', alignItems: 'center', gap: '10px' } },
                            el('div', {
                                style: {
                                    width: '30px',
                                    height: '30px',
                                    backgroundColor: newColorValue,
                                    border: '1px solid #ccc',
                                    borderRadius: '3px'
                                }
                            }),
                            el(TextControl, {
                                value: newColorValue,
                                onChange: setNewColorValue,
                                style: { flex: 1 }
                            })
                        )
                    ),
                    el(Button, {
                        variant: 'primary',
                        onClick: addColor,
                        disabled: isLoading
                    }, isLoading ? 'Adding...' : 'Add Color')
                ),

                // Control buttons
                el('div', { style: { marginBottom: '15px' } },
                    el(Button, {
                        variant: 'secondary',
                        onClick: loadExistingColors,
                        disabled: isLoading
                    }, 'Reload Colors from theme.json')
                ),

                // Current Colors
                el('div', {},
                    el('h4', {}, `Current Colors (${colors.length})`),
                    colors.length === 0 
                        ? el('p', { style: { fontStyle: 'italic', color: '#666' } }, 'No colors found. Add some colors above.')
                        : colors.map((color, index) => 
                            el(ColorItem, {
                                key: color.slug,
                                color: color,
                                onEdit: editColor,
                                onDelete: deleteColor
                            })
                        )
                )
            ),

            // Typography Module
            el(PanelBody, { title: 'Typography', initialOpen: false },
                // Show typography message if any
                typographyMessage && el(Notice, {
                    status: typographyMessage.includes('Error') ? 'error' : 'success',
                    isDismissible: false
                }, typographyMessage),
                
                // Typography control buttons
                el('div', { style: { marginBottom: '15px' } },
                    el(Button, {
                        variant: 'secondary',
                        onClick: loadExistingTypography,
                        disabled: typographyLoading
                    }, 'Reload Typography from theme.json')
                ),
                
                // Add Font Family Section
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Add Font Family'),
                    el(TextControl, {
                        label: 'Font Name',
                        value: newFontFamily.name,
                        onChange: (value) => setNewFontFamily({ ...newFontFamily, name: value }),
                        placeholder: 'e.g., Heading Font'
                    }),
                    el(TextControl, {
                        label: 'Font Family CSS',
                        value: newFontFamily.fontFamily,
                        onChange: (value) => setNewFontFamily({ ...newFontFamily, fontFamily: value }),
                        placeholder: 'e.g., "Inter", sans-serif'
                    }),
                    el(Button, {
                        variant: 'primary',
                        onClick: addFontFamily,
                        disabled: !newFontFamily.name.trim() || !newFontFamily.fontFamily.trim() || typographyLoading
                    }, typographyLoading ? 'Saving...' : 'Add Font Family')
                ),
                
                // Add Font Size Section
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Add Font Size'),
                    el(TextControl, {
                        label: 'Size Name',
                        value: newFontSize.name,
                        onChange: (value) => setNewFontSize({ ...newFontSize, name: value }),
                        placeholder: 'e.g., Heading Large'
                    }),
                    el(TextControl, {
                        label: 'Size Value',
                        value: newFontSize.size,
                        onChange: (value) => setNewFontSize({ ...newFontSize, size: value }),
                        placeholder: 'e.g., 2rem or clamp(1.5rem, 4vw, 2.5rem)'
                    }),
                    el(Button, {
                        variant: 'primary',
                        onClick: addFontSize,
                        disabled: !newFontSize.name.trim() || !newFontSize.size.trim() || typographyLoading
                    }, typographyLoading ? 'Saving...' : 'Add Font Size')
                ),
                
                // Current Font Families
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', {}, `Current Font Families (${fontFamilies.length})`),
                    fontFamilies.length === 0 
                        ? el('p', { style: { fontStyle: 'italic', color: '#666' } }, 'No font families found.')
                        : fontFamilies.map((family, index) => 
                            el('div', { 
                                key: family.slug,
                                style: { 
                                    padding: '10px', 
                                    border: '1px solid #ddd', 
                                    borderRadius: '4px',
                                    marginBottom: '8px',
                                    fontFamily: family.fontFamily
                                }
                            },
                                el('strong', {}, family.name),
                                el('br'),
                                el('code', { style: { fontSize: '12px', color: '#666' } }, family.fontFamily)
                            )
                        )
                ),
                
                // Current Font Sizes
                el('div', {},
                    el('h4', {}, `Current Font Sizes (${fontSizes.length})`),
                    fontSizes.length === 0 
                        ? el('p', { style: { fontStyle: 'italic', color: '#666' } }, 'No font sizes found.')
                        : fontSizes.map((size, index) => 
                            el('div', { 
                                key: size.slug,
                                style: { 
                                    padding: '10px', 
                                    border: '1px solid #ddd', 
                                    borderRadius: '4px',
                                    marginBottom: '8px'
                                }
                            },
                                el('strong', {}, size.name),
                                el('br'),
                                el('span', { 
                                    style: { 
                                        fontSize: size.size.includes('rem') || size.size.includes('em') ? size.size : '16px',
                                        color: '#666' 
                                    } 
                                }, `Sample text (${size.size})`)
                            )
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
