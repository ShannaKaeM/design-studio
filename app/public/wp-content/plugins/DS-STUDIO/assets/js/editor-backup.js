(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editor;
    const { PanelBody, Button, TextControl, Notice, ColorPicker, Popover, TabPanel } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    // Enhanced Color Picker Component
    const EnhancedColorPicker = ({ color, onChange, onClose, colorName, onNameChange, onSave, onCancel }) => {
        const [activeTab, setActiveTab] = useState('hsla');
        
        // Convert hex to HSL for display
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
        
        // Convert HSL to hex
        const hslToHex = (h, s, l) => {
            h = h / 360;
            s = s / 100;
            l = l / 100;
            
            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };
            
            let r, g, b;
            if (s === 0) {
                r = g = b = l;
            } else {
                const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                const p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
            }
            
            const toHex = (c) => {
                const hex = Math.round(c * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        };
        
        // Convert hex to CMYK
        const hexToCmyk = (hex) => {
            const r = parseInt(hex.slice(1, 3), 16) / 255;
            const g = parseInt(hex.slice(3, 5), 16) / 255;
            const b = parseInt(hex.slice(5, 7), 16) / 255;
            
            const k = 1 - Math.max(r, Math.max(g, b));
            const c = (1 - r - k) / (1 - k) || 0;
            const m = (1 - g - k) / (1 - k) || 0;
            const y = (1 - b - k) / (1 - k) || 0;
            
            return {
                c: Math.round(c * 100),
                m: Math.round(m * 100),
                y: Math.round(y * 100),
                k: Math.round(k * 100)
            };
        };

        // Convert CMYK to hex
        const cmykToHex = (c, m, y, k) => {
            const r = Math.round(255 * (1 - c / 100) * (1 - k / 100));
            const g = Math.round(255 * (1 - m / 100) * (1 - k / 100));
            const b = Math.round(255 * (1 - y / 100) * (1 - k / 100));
            
            return '#' + [r, g, b].map(x => {
                const hex = x.toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        };

        // Color values for sliders
        const hsl = hexToHsl(color);
        const cmyk = hexToCmyk(color);

        const handleSliderChange = (type, value) => {
            if (activeTab === 'hsla') {
                const newHsl = { ...hsl };
                newHsl[type] = parseInt(value);
                const newHex = hslToHex(newHsl.h, newHsl.s, newHsl.l);
                onChange(newHex);
            } else if (activeTab === 'cmyk') {
                const newCmyk = { ...cmyk };
                newCmyk[type] = parseInt(value);
                const newHex = cmykToHex(newCmyk.c, newCmyk.m, newCmyk.y, newCmyk.k);
                onChange(newHex);
            }
        };
        
        return el('div', {
            className: 'ds-studio-enhanced-color-picker',
            style: {
                width: '400px',
                padding: '16px',
                backgroundColor: '#fff',
                borderRadius: '8px',
                boxShadow: '0 4px 20px rgba(0,0,0,0.15)',
                maxHeight: '600px'
            }
        },
            // Close X button in top right
            el('button', {
                onClick: onClose,
                style: {
                    position: 'absolute',
                    top: '8px',
                    right: '8px',
                    background: 'none',
                    border: 'none',
                    fontSize: '18px',
                    cursor: 'pointer',
                    color: '#666',
                    width: '24px',
                    height: '24px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                }
            }, '×'),
            
            // Large Color Preview (expanded to fill top area)
            el('div', {
                style: {
                    width: '100%',
                    height: '100px',
                    backgroundColor: color,
                    borderRadius: '8px',
                    marginBottom: '16px',
                    border: '1px solid #e0e0e0'
                }
            }),
            
            // Tabs for HSLA/CMYK (moved up closer to color)
            el('div', { style: { display: 'flex', marginBottom: '8px', borderBottom: '1px solid #ddd' } },
                el('button', {
                        style: {
                            padding: '8px 16px',
                            border: 'none',
                            cursor: 'pointer',
                            backgroundColor: activeTab === 'hsla' ? '#000' : '#f5f5f5',
                            color: activeTab === 'hsla' ? '#fff' : '#666',
                            fontSize: '12px',
                            fontWeight: activeTab === 'hsla' ? 'bold' : 'normal',
                            borderRadius: activeTab === 'hsla' ? '6px 6px 0 0' : '6px 6px 0 0'
                        },
                        onClick: () => setActiveTab('hsla')
                    }, 'HSLA'),
                    
                el('button', {
                        style: {
                            padding: '8px 16px',
                            border: 'none',
                            cursor: 'pointer',
                            backgroundColor: activeTab === 'cmyk' ? '#000' : '#f5f5f5',
                            color: activeTab === 'cmyk' ? '#fff' : '#666',
                            fontSize: '12px',
                            fontWeight: activeTab === 'cmyk' ? 'bold' : 'normal',
                            borderRadius: activeTab === 'cmyk' ? '6px 6px 0 0' : '6px 6px 0 0'
                        },
                        onClick: () => setActiveTab('cmyk')
                    }, 'CMYK')
            ),
            
            // HSLA Sliders (tightened spacing)
            activeTab === 'hsla' && el('div', { style: { marginBottom: '12px' } },
                // Hue Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Hue'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${hsl.h}°`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '360',
                        value: hsl.h,
                        onChange: (e) => handleSliderChange('h', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Saturation Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Saturation'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${hsl.s}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: hsl.s,
                        onChange: (e) => handleSliderChange('s', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: `linear-gradient(to right, hsl(${hsl.h}, 0%, ${hsl.l}%), hsl(${hsl.h}, 100%, ${hsl.l}%))`,
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Lightness Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Lightness'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${hsl.l}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: hsl.l,
                        onChange: (e) => handleSliderChange('l', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: `linear-gradient(to right, hsl(${hsl.h}, ${hsl.s}%, 0%), hsl(${hsl.h}, ${hsl.s}%, 50%), hsl(${hsl.h}, ${hsl.s}%, 100%))`,
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Alpha Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Alpha'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, '100%')
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: '100',
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: `linear-gradient(to right, transparent, ${color})`,
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                )
            ),
            
            // CMYK Sliders
            activeTab === 'cmyk' && el('div', { style: { marginBottom: '12px' } },
                // Cyan Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Cyan'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${cmyk.c}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: cmyk.c,
                        onChange: (e) => handleSliderChange('c', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #ffffff, #00ffff)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Magenta Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Magenta'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${cmyk.m}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: cmyk.m,
                        onChange: (e) => handleSliderChange('m', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #ffffff, #ff00ff)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Yellow Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Yellow'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${cmyk.y}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: cmyk.y,
                        onChange: (e) => handleSliderChange('y', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #ffffff, #ffff00)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Black Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Black'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${cmyk.k}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: cmyk.k,
                        onChange: (e) => handleSliderChange('k', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #ffffff, #000000)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                )
            ),
            
            // Color Name and Hex Inputs (moved to bottom)
            el('div', { style: { marginTop: '16px', borderTop: '1px solid #e0e0e0', paddingTop: '12px' } },
                // Color Name Input
                el('div', { style: { marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' } },
                    el('label', { 
                        style: { 
                            fontSize: '12px', 
                            fontWeight: '600', 
                            color: '#1e1e1e',
                            minWidth: '80px'
                        } 
                    }, 'Color Name'),
                    el('input', {
                        type: 'text',
                        value: colorName,
                        onChange: (e) => onNameChange(e.target.value),
                        style: {
                            flex: 1,
                            padding: '6px 10px',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            fontSize: '14px'
                        }
                    })
                ),
                
                // Hex Color Input
                el('div', { style: { marginBottom: '16px', display: 'flex', alignItems: 'center', gap: '8px' } },
                    el('label', { 
                        style: { 
                            fontSize: '12px', 
                            fontWeight: '600', 
                            color: '#1e1e1e',
                            minWidth: '80px'
                        } 
                    }, 'Hex Color'),
                    el('input', {
                        type: 'text',
                        value: color,
                        onChange: (e) => onChange(e.target.value),
                        style: {
                            flex: 1,
                            padding: '6px 10px',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            fontSize: '14px',
                            fontFamily: 'monospace'
                        }
                    })
                ),
                
                // Save and Cancel buttons
                el('div', { style: { display: 'flex', gap: '8px' } },
                    el('button', {
                        onClick: onSave,
                        style: {
                            padding: '8px 16px',
                            backgroundColor: '#000',
                            color: '#fff',
                            border: 'none',
                            borderRadius: '4px',
                            fontSize: '14px',
                            fontWeight: '500',
                            cursor: 'pointer'
                        }
                    }, 'Save'),
                    el('button', {
                        onClick: onCancel,
                        style: {
                            padding: '8px 16px',
                            backgroundColor: 'transparent',
                            color: '#000',
                            border: '1px solid #000',
                            borderRadius: '4px',
                            fontSize: '14px',
                            fontWeight: '500',
                            cursor: 'pointer'
                        }
                    }, 'Cancel')
                )
            )
        );
    };

    // Color item component with advanced editing
    const ColorItem = ({ colorKey, colorData, onUpdate, onDelete }) => {
        const [isEditing, setIsEditing] = useState(false);
        const [editName, setEditName] = useState(colorData.name || colorKey);
        const [editColor, setEditColor] = useState(colorData.color);
        const [showColorPicker, setShowColorPicker] = useState(false);

        const handleSave = () => {
            const updatedColor = {
                name: editName,
                slug: editName.toLowerCase().replace(/\s+/g, '-'),
                color: editColor
            };
            onUpdate(colorKey, updatedColor);
            setIsEditing(false);
            setShowColorPicker(false);
        };

        const handleCancel = () => {
            setEditName(colorData.name || colorKey);
            setEditColor(colorData.color);
            setIsEditing(false);
            setShowColorPicker(false);
        };

        const handleColorChange = (color) => {
            // Convert color object to hex string
            const hexColor = color.hex || color;
            setEditColor(hexColor);
        };

        return el('div', { 
            className: 'ds-studio-color-item',
            style: { 
                marginBottom: '12px', 
                padding: '12px', 
                border: '1px solid #ddd', 
                borderRadius: '4px',
                backgroundColor: '#f9f9f9'
            } 
        },
            // Color preview and name
            el('div', { 
                style: { 
                    display: 'flex', 
                    alignItems: 'center', 
                    marginBottom: isEditing ? '8px' : '0',
                    cursor: isEditing ? 'default' : 'pointer'
                },
                onClick: () => setShowColorPicker(!showColorPicker)
            },
                el('div', {
                    className: 'ds-studio-color-preview',
                    style: {
                        width: '32px',
                        height: '32px',
                        backgroundColor: editColor,
                        border: '2px solid #fff',
                        borderRadius: '6px',
                        marginRight: '12px',
                        cursor: 'pointer',
                        boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                    },
                    onClick: isEditing ? (e) => {
                        e.stopPropagation();
                        setShowColorPicker(!showColorPicker);
                    } : null
                }),
                el('div', { 
                    className: 'ds-studio-color-info',
                    style: { flex: 1 }
                },
                    el('div', { 
                        className: 'ds-studio-color-name',
                        style: { 
                            fontWeight: '600',
                            fontSize: '14px',
                            marginBottom: '4px'
                        } 
                    }, colorData.name || colorKey),
                    el('div', { 
                        className: 'ds-studio-color-slug',
                        style: { 
                            fontSize: '11px', 
                            color: '#666',
                            fontFamily: 'monospace',
                            background: '#f0f0f0',
                            padding: '2px 6px',
                            borderRadius: '3px',
                            display: 'inline-block',
                            marginBottom: '2px'
                        } 
                    }, `--wp--preset--color--${colorData.slug || colorKey}`),
                    el('div', { 
                        className: 'ds-studio-color-hex',
                        style: { 
                            fontSize: '12px', 
                            color: '#888',
                            fontFamily: 'monospace',
                            fontWeight: '500'
                        } 
                    }, editColor.toUpperCase())
                )
            ),

            // Color Picker Popover
            showColorPicker && el(Popover, {
                className: 'ds-studio-color-picker-popover',
                position: 'top right',
                onClose: () => setShowColorPicker(false)
            },
                el(EnhancedColorPicker, {
                    color: editColor,
                    onChange: handleColorChange,
                    onClose: () => setShowColorPicker(false),
                    colorName: editName,
                    onNameChange: setEditName,
                    onSave: handleSave,
                    onCancel: handleCancel
                })
            ),

            // Edit mode controls
            isEditing && el('div', { 
                className: 'ds-studio-edit-controls',
                style: { marginTop: '12px' } 
            },
                el(TextControl, {
                    label: 'Color Name',
                    value: editName,
                    onChange: setEditName,
                    style: { marginBottom: '8px' }
                }),
                el(TextControl, {
                    label: 'Hex Color',
                    value: editColor,
                    onChange: setEditColor,
                    placeholder: '#000000',
                    style: { marginBottom: '12px' }
                }),
                el('div', { style: { display: 'flex', gap: '8px' } },
                    el(Button, {
                        isPrimary: true,
                        onClick: handleSave,
                        text: 'Save'
                    }),
                    el(Button, {
                        isSecondary: true,
                        onClick: handleCancel,
                        text: 'Cancel'
                    }),
                    el(Button, {
                        isDestructive: true,
                        onClick: () => {
                            if (confirm('Are you sure you want to delete this color?')) {
                                onDelete(colorKey);
                            }
                        },
                        text: 'Delete',
                        style: { marginLeft: 'auto' }
                    })
                )
            )
        );
    };

    // Simple DS-Studio Panel Component
    const DSStudioPanel = () => {
        const [colors, setColors] = useState([]);
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
            setTypographyLoading(true);
            setTypographyMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_get_theme_json');
            formData.append('nonce', window.dsStudio.nonce);

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setTypographyLoading(false);
                if (result.success && result.data) {
                    const themeJson = result.data;
                    window.dsStudio.currentThemeJson = themeJson;
                    
                    // Load font families
                    const families = themeJson.settings?.typography?.fontFamilies || [];
                    setFontFamilies(families);
                    
                    // Load font sizes
                    const sizes = themeJson.settings?.typography?.fontSizes || [];
                    setFontSizes(sizes);
                    
                    setTypographyMessage(`Loaded ${families.length} font families and ${sizes.length} font sizes from theme.json`);
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

        const addFontFamily = () => {
            if (newFontFamily.name.trim() && newFontFamily.fontFamily.trim()) {
                const slug = newFontFamily.name.toLowerCase().replace(/[^a-z0-9]/g, '-');
                const updatedFamilies = [...fontFamilies, {
                    name: newFontFamily.name,
                    slug: slug,
                    fontFamily: newFontFamily.fontFamily
                }];
                
                setFontFamilies(updatedFamilies);
                setNewFontFamily({ name: '', fontFamily: '' });
                
                // Save to theme.json
                saveTypographyToThemeJson(updatedFamilies, fontSizes);
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
                
                setFontSizes(updatedSizes);
                setNewFontSize({ name: '', size: '' });
                
                // Save to theme.json
                saveTypographyToThemeJson(fontFamilies, updatedSizes);
            }
        };

        const saveTypographyToThemeJson = (familiesToSave, sizesToSave) => {
            setTypographyLoading(true);
            setTypographyMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            
            // Create updated theme.json structure
            const currentThemeJson = window.dsStudio.currentThemeJson || {};
            const updatedThemeJson = {
                ...currentThemeJson,
                settings: {
                    ...currentThemeJson.settings,
                    typography: {
                        ...currentThemeJson.settings?.typography,
                        fontFamilies: familiesToSave,
                        fontSizes: sizesToSave
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
                setTypographyLoading(false);
                if (result.success) {
                    setTypographyMessage('Typography saved to theme.json successfully!');
                    // Update the global theme.json reference
                    window.dsStudio.currentThemeJson = updatedThemeJson;
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
            // Remove existing typography preview styles
            const existingStyle = document.getElementById('ds-studio-typography-preview');
            if (existingStyle) {
                existingStyle.remove();
            }

            // Generate CSS variables for typography
            let cssVariables = '';
            
            // Font families
            familiesToPreview.forEach(family => {
                cssVariables += `--wp--preset--font-family--${family.slug}: ${family.fontFamily}; `;
            });
            
            // Font sizes
            sizesToPreview.forEach(size => {
                cssVariables += `--wp--preset--font-size--${size.slug}: ${size.size}; `;
            });

            // Inject new styles
            const styleElement = document.createElement('style');
            styleElement.id = 'ds-studio-typography-preview';
            styleElement.textContent = `:root { ${cssVariables} }`;
            document.head.appendChild(styleElement);
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
                        el(ColorItem, {
                            key: index,
                            colorKey: index,
                            colorData: color,
                            onUpdate: (key, data) => {
                                const updatedColors = [...colors];
                                updatedColors[key] = data;
                                setColors(updatedColors);
                                saveColorsToThemeJson(updatedColors);
                            },
                            onDelete: (key) => {
                                const updatedColors = colors.filter((_, i) => i !== key);
                                setColors(updatedColors);
                                saveColorsToThemeJson(updatedColors);
                            }
                        })
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
                el('div', { style: { marginBottom: '15px', display: 'flex', gap: '10px', flexWrap: 'wrap' } },
                    el(Button, {
                        isSecondary: true,
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
                        isPrimary: true,
                        onClick: addFontFamily,
                        disabled: !newFontFamily.name.trim() || !newFontFamily.fontFamily.trim() || typographyLoading,
                        isBusy: typographyLoading
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
                        isPrimary: true,
                        onClick: addFontSize,
                        disabled: !newFontSize.name.trim() || !newFontSize.size.trim() || typographyLoading,
                        isBusy: typographyLoading
                    }, typographyLoading ? 'Saving...' : 'Add Font Size')
                ),
                
                // Current Font Families
                el('div', { style: { marginBottom: '15px' } },
                    el('h4', {}, `Font Families (${fontFamilies.length})`),
                    fontFamilies.length === 0 
                        ? el('p', { style: { fontStyle: 'italic', color: '#666' } }, 'No custom font families found.')
                        : fontFamilies.map((family, index) => 
                            el('div', {
                                key: index,
                                style: {
                                    padding: '10px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    marginBottom: '8px',
                                    backgroundColor: '#f9f9f9'
                                }
                            },
                                el('div', { style: { fontWeight: 'bold' } }, family.name),
                                el('div', { style: { fontSize: '12px', color: '#666', fontFamily: family.fontFamily } }, family.fontFamily),
                                el('div', { style: { fontSize: '11px', color: '#999' } }, `Slug: ${family.slug}`)
                            )
                        )
                ),
                
                // Current Font Sizes
                el('div', {},
                    el('h4', {}, `Font Sizes (${fontSizes.length})`),
                    fontSizes.length === 0 
                        ? el('p', { style: { fontStyle: 'italic', color: '#666' } }, 'No custom font sizes found.')
                        : fontSizes.map((size, index) => 
                            el('div', {
                                key: index,
                                style: {
                                    padding: '10px',
                                    border: '1px solid #e0e0e0',
                                    borderRadius: '4px',
                                    marginBottom: '8px',
                                    backgroundColor: '#f9f9f9'
                                }
                            },
                                el('div', { style: { fontWeight: 'bold' } }, size.name),
                                el('div', { style: { fontSize: '12px', color: '#666' } }, size.size),
                                el('div', { style: { fontSize: '11px', color: '#999' } }, `Slug: ${size.slug}`)
                            )
                        )
                )
            )
        );

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
