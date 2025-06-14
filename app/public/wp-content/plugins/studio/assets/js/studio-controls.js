/**
 * Studio Controls - Global Components
 * 
 * Makes Studio control components available globally for use in blocks
 */

(function() {
    'use strict';

    const { BaseControl, Button, ButtonGroup, SelectControl, TextControl } = wp.components;
    const { __ } = wp.i18n;
    const { useState, useEffect, createElement: el } = wp.element;

    /**
     * Studio Typography Presets
     * Shows typography presets from blockStyles and allows applying them
     */
    const StudioTypographyPresets = ({ label, value, blockType = 'studio/text', onChange }) => {
        const [presets, setPresets] = useState([]);
        const [loading, setLoading] = useState(true);
        
        useEffect(() => {
            loadTypographyPresets();
        }, [blockType]);
        
        const loadTypographyPresets = async () => {
            setLoading(true);
            try {
                const response = await fetch(window.ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'studio_get_block_styles',
                        blockType: blockType,
                        nonce: window.studioData?.nonce || window.studioNonce
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    setPresets(data.data || []);
                }
            } catch (error) {
                console.error('Failed to load typography presets:', error);
            } finally {
                setLoading(false);
            }
        };
        
        const applyPreset = (preset) => {
            if (onChange) {
                onChange(preset.name);
            }
        };
        
        const removePreset = () => {
            if (onChange) {
                onChange('');
            }
        };
        
        if (loading) {
            return el(BaseControl, { 
                label: label, 
                className: 'studio-typography-presets' 
            },
                el('div', { className: 'studio-presets-loading' },
                    __('Loading presets...', 'studio')
                )
            );
        }
        
        return el(BaseControl, { 
            label: label, 
            className: 'studio-typography-presets' 
        },
            el('div', { className: 'studio-presets-grid' },
                // Preset buttons
                ...presets.map((preset) => {
                    const isSelected = value === preset.name;
                    
                    return el('div', { 
                        key: preset.name, 
                        className: 'studio-preset-item' 
                    },
                        el(Button, {
                            variant: isSelected ? 'primary' : 'secondary',
                            onClick: () => applyPreset(preset),
                            className: 'studio-preset-button'
                        }, preset.label),
                        
                        preset.description && el('div', { 
                            className: 'studio-preset-description' 
                        }, preset.description)
                    );
                }),
                
                // Clear button
                el(Button, {
                    variant: !value ? 'primary' : 'secondary',
                    onClick: removePreset,
                    className: 'studio-preset-clear'
                }, __('Clear Style', 'studio'))
            )
        );
    };

    // Make components available globally
    window.studioControls = {
        StudioTypographyPresets
    };

})();
