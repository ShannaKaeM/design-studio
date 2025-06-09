(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
    const { PanelBody, Button, TextControl, Notice, ColorPicker, Popover, TabPanel } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    // Enhanced Color Picker Component
    const EnhancedColorPicker = ({ color, onChange, onClose, colorName, onNameChange }) => {
        const [activeTab, setActiveTab] = useState('oklch');
        
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
        
        // Convert CMYK to hex
        const cmykToHex = (c, m, y, k) => {
            c = c / 100;
            m = m / 100;
            y = y / 100;
            k = k / 100;
            
            const r = 255 * (1 - c) * (1 - k);
            const g = 255 * (1 - m) * (1 - k);
            const b = 255 * (1 - y) * (1 - k);
            
            const toHex = (val) => {
                const hex = Math.round(val).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
        };
        
        // Convert hex to OKLCH
        const hexToOklch = (hex) => {
            // First convert hex to linear RGB
            const hexToLinearRgb = (hex) => {
                const r = parseInt(hex.slice(1, 3), 16) / 255;
                const g = parseInt(hex.slice(3, 5), 16) / 255;
                const b = parseInt(hex.slice(5, 7), 16) / 255;
                
                const toLinear = (c) => c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
                return [toLinear(r), toLinear(g), toLinear(b)];
            };
            
            // Convert linear RGB to XYZ
            const linearRgbToXyz = ([r, g, b]) => {
                const x = 0.4124564 * r + 0.3575761 * g + 0.1804375 * b;
                const y = 0.2126729 * r + 0.7151522 * g + 0.0721750 * b;
                const z = 0.0193339 * r + 0.1191920 * g + 0.9503041 * b;
                return [x, y, z];
            };
            
            // Convert XYZ to OKLAB
            const xyzToOklab = ([x, y, z]) => {
                const l = Math.cbrt(0.8189330101 * x + 0.3618667424 * y - 0.1288597137 * z);
                const m = Math.cbrt(0.0329845436 * x + 0.9293118715 * y + 0.0361456387 * z);
                const s = Math.cbrt(0.0482003018 * x + 0.2643662691 * y + 0.6338517070 * z);
                
                return [
                    0.2104542553 * l + 0.7936177850 * m - 0.0040720468 * s,
                    1.9779984951 * l - 2.4285922050 * m + 0.4505937099 * s,
                    0.0259040371 * l + 0.7827717662 * m - 0.8086757660 * s
                ];
            };
            
            // Convert OKLAB to OKLCH
            const oklabToOklch = ([l, a, b]) => {
                const c = Math.sqrt(a * a + b * b);
                let h = Math.atan2(b, a) * 180 / Math.PI;
                if (h < 0) h += 360;
                return [l, c, h];
            };
            
            const [r, g, b] = hexToLinearRgb(hex);
            const xyz = linearRgbToXyz([r, g, b]);
            const oklab = xyzToOklab(xyz);
            const [l, c, h] = oklabToOklch(oklab);
            
            return {
                l: Math.round(l * 100),
                c: Math.round(c * 100),
                h: Math.round(h)
            };
        };
        
        // Convert OKLCH to hex
        const oklchToHex = (l, c, h) => {
            l = l / 100;
            c = c / 100;
            h = h * Math.PI / 180;
            
            // Convert OKLCH to OKLAB
            const a = c * Math.cos(h);
            const b = c * Math.sin(h);
            
            // Convert OKLAB to XYZ
            const oklabToXyz = ([l, a, b]) => {
                const l_ = l + 0.3963377774 * a + 0.2158037573 * b;
                const m_ = l - 0.1055613458 * a - 0.0638541728 * b;
                const s_ = l - 0.0894841775 * a - 1.2914855480 * b;
                
                const l3 = l_ * l_ * l_;
                const m3 = m_ * m_ * m_;
                const s3 = s_ * s_ * s_;
                
                return [
                    +1.2268798733 * l3 - 0.5578149965 * m3 + 0.2813910456 * s3,
                    -0.0405801784 * l3 + 1.1122568696 * m3 - 0.0716766787 * s3,
                    -0.0763812845 * l3 - 0.4214819784 * m3 + 1.5861632204 * s3
                ];
            };
            
            // Convert XYZ to linear RGB
            const xyzToLinearRgb = ([x, y, z]) => {
                const r = +3.2404542 * x - 1.5371385 * y - 0.4985314 * z;
                const g = -0.9692660 * x + 1.8760108 * y + 0.0415560 * z;
                const b = +0.0556434 * x - 0.2040259 * y + 1.0572252 * z;
                return [r, g, b];
            };
            
            // Convert linear RGB to sRGB
            const linearRgbToSrgb = ([r, g, b]) => {
                const toSrgb = (c) => {
                    c = Math.max(0, Math.min(1, c));
                    return c <= 0.0031308 ? 12.92 * c : 1.055 * Math.pow(c, 1/2.4) - 0.055;
                };
                return [toSrgb(r), toSrgb(g), toSrgb(b)];
            };
            
            const xyz = oklabToXyz([l, a, b]);
            const linearRgb = xyzToLinearRgb(xyz);
            const [r, g, b_val] = linearRgbToSrgb(linearRgb);
            
            const toHex = (c) => {
                const hex = Math.round(c * 255).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            };
            
            return `#${toHex(r)}${toHex(g)}${toHex(b_val)}`;
        };
        
        const hsl = hexToHsl(color);
        const cmyk = hexToCmyk(color);
        const oklch = hexToOklch(color);
        
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
            } else if (activeTab === 'oklch') {
                const newOklch = { ...oklch };
                newOklch[type] = parseInt(value);
                const newHex = oklchToHex(newOklch.l, newOklch.c, newOklch.h);
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
            
            // Tabs for HSLA/CMYK/OKLCH (moved up closer to color)
            el('div', { style: { marginBottom: '12px' } },
                el('div', {
                    style: {
                        display: 'flex',
                        borderBottom: '1px solid #e0e0e0',
                        marginBottom: '16px',
                        gap: '4px'
                    }
                },
                    el('button', {
                        onClick: () => setActiveTab('oklch'),
                        style: {
                            flex: 1,
                            padding: '8px 16px',
                            border: 'none',
                            backgroundColor: activeTab === 'oklch' ? '#000' : '#f5f5f5',
                            color: activeTab === 'oklch' ? '#fff' : '#666',
                            cursor: 'pointer',
                            fontSize: '14px',
                            fontWeight: activeTab === 'oklch' ? 'bold' : 'normal',
                            borderRadius: '6px'
                        }
                    }, 'OKLCH'),
                    el('button', {
                        onClick: () => setActiveTab('hsla'),
                        style: {
                            flex: 1,
                            padding: '8px 16px',
                            border: 'none',
                            backgroundColor: activeTab === 'hsla' ? '#000' : '#f5f5f5',
                            color: activeTab === 'hsla' ? '#fff' : '#666',
                            cursor: 'pointer',
                            fontSize: '14px',
                            fontWeight: activeTab === 'hsla' ? 'bold' : 'normal',
                            borderRadius: '6px'
                        }
                    }, 'HSLA'),
                    el('button', {
                        onClick: () => setActiveTab('cmyk'),
                        style: {
                            flex: 1,
                            padding: '8px 16px',
                            border: 'none',
                            backgroundColor: activeTab === 'cmyk' ? '#000' : '#f5f5f5',
                            color: activeTab === 'cmyk' ? '#fff' : '#666',
                            cursor: 'pointer',
                            fontSize: '14px',
                            fontWeight: activeTab === 'cmyk' ? 'bold' : 'normal',
                            borderRadius: '6px'
                        }
                    }, 'CMYK')
                )
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
            
            // OKLCH Sliders
            activeTab === 'oklch' && el('div', { style: { marginBottom: '12px' } },
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
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${oklch.l}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: oklch.l,
                        onChange: (e) => handleSliderChange('l', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #000000, #ffffff)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
                // Chroma Slider
                el('div', { style: { marginBottom: '8px' } },
                    el('div', { 
                        style: { 
                            display: 'flex', 
                            justifyContent: 'space-between', 
                            marginBottom: '4px' 
                        } 
                    },
                        el('label', { style: { fontSize: '12px', color: '#666' } }, 'Chroma'),
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${oklch.c}%`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '100',
                        value: oklch.c,
                        onChange: (e) => handleSliderChange('c', e.target.value),
                        style: {
                            width: '100%',
                            height: '6px',
                            borderRadius: '3px',
                            background: 'linear-gradient(to right, #000000, #ffffff)',
                            outline: 'none',
                            cursor: 'pointer'
                        }
                    })
                ),
                
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
                        el('span', { style: { fontSize: '12px', color: '#666' } }, `${oklch.h}°`)
                    ),
                    el('input', {
                        type: 'range',
                        min: '0',
                        max: '360',
                        value: oklch.h,
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
                )
            ),
            
            // Color Name and Hex Inputs (moved to bottom)
            el('div', { style: { marginTop: '16px', borderTop: '1px solid #e0e0e0', paddingTop: '12px' } },
                // Color Name Input
                el('div', { style: { marginBottom: '12px' } },
                    el('label', { 
                        style: { 
                            display: 'block', 
                            fontSize: '12px', 
                            fontWeight: '600', 
                            marginBottom: '4px',
                            color: '#1e1e1e'
                        } 
                    }, 'Color Name'),
                    el('input', {
                        type: 'text',
                        value: colorName,
                        onChange: (e) => onNameChange(e.target.value),
                        style: {
                            width: '100%',
                            padding: '6px 10px',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            fontSize: '14px'
                        }
                    })
                ),
                
                // Hex Color Input
                el('div', {},
                    el('label', { 
                        style: { 
                            display: 'block', 
                            fontSize: '12px', 
                            fontWeight: '600', 
                            marginBottom: '4px',
                            color: '#1e1e1e'
                        } 
                    }, 'Hex Color'),
                    el('input', {
                        type: 'text',
                        value: color,
                        onChange: (e) => onChange(e.target.value),
                        style: {
                            width: '100%',
                            padding: '6px 10px',
                            border: '1px solid #ddd',
                            borderRadius: '4px',
                            fontSize: '14px',
                            fontFamily: 'monospace'
                        }
                    })
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
                onClick: isEditing ? null : () => setIsEditing(true)
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
                position: 'bottom left',
                onClose: () => setShowColorPicker(false)
            },
                el(EnhancedColorPicker, {
                    color: editColor,
                    onChange: handleColorChange,
                    onClose: () => setShowColorPicker(false),
                    colorName: editName,
                    onNameChange: setEditName
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
