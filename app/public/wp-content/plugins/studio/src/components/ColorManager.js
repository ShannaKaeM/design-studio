/**
 * Color Manager Component
 * Handles color palette management and theme.json generation
 */

import { useState, useEffect } from '@wordpress/element';
import { 
    ColorPicker, 
    Button, 
    TextControl, 
    Flex, 
    FlexItem,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

const ColorManager = () => {
    const { themeJson, updateThemeJson, saveThemeJson, isLoading } = useThemeJson();
    const [colors, setColors] = useState([]);
    const [newColorName, setNewColorName] = useState('');
    const [newColorValue, setNewColorValue] = useState('#3a5a59');

    // Initialize colors from theme.json
    useEffect(() => {
        if (themeJson?.settings?.color?.palette) {
            setColors(themeJson.settings.color.palette);
        }
    }, [themeJson]);

    /**
     * Add new color to palette
     */
    const addColor = () => {
        if (!newColorName.trim()) return;

        const newColor = {
            slug: newColorName.toLowerCase().replace(/\s+/g, '-'),
            color: newColorValue,
            name: newColorName
        };

        const updatedColors = [...colors, newColor];
        setColors(updatedColors);
        updateColorPalette(updatedColors);
        
        // Reset form
        setNewColorName('');
        setNewColorValue('#3a5a59');
    };

    /**
     * Remove color from palette
     */
    const removeColor = (index) => {
        const updatedColors = colors.filter((_, i) => i !== index);
        setColors(updatedColors);
        updateColorPalette(updatedColors);
    };

    /**
     * Update color value
     */
    const updateColor = (index, field, value) => {
        const updatedColors = [...colors];
        updatedColors[index] = {
            ...updatedColors[index],
            [field]: value
        };
        
        // Update slug when name changes
        if (field === 'name') {
            updatedColors[index].slug = value.toLowerCase().replace(/\s+/g, '-');
        }
        
        setColors(updatedColors);
        updateColorPalette(updatedColors);
    };

    /**
     * Update theme.json color palette
     */
    const updateColorPalette = (colorPalette) => {
        const updatedThemeJson = {
            ...themeJson,
            settings: {
                ...themeJson.settings,
                color: {
                    ...themeJson.settings.color,
                    palette: colorPalette
                }
            }
        };
        
        updateThemeJson(updatedThemeJson);
    };

    /**
     * Generate CSS variables preview
     */
    const generateCSSVariables = () => {
        return colors.map(color => 
            `--wp--preset--color--${color.slug}: ${color.color};`
        ).join('\n');
    };

    return (
        <div className="ds-color-manager">
            {/* Add New Color */}
            <div className="ds-add-color">
                <h4>{__('Add New Color', 'studio')}</h4>
                
                <TextControl
                    label={__('Color Name', 'studio')}
                    value={newColorName}
                    onChange={setNewColorName}
                    placeholder="Primary"
                />
                
                <div className="ds-color-picker-wrapper">
                    <label>{__('Color Value', 'studio')}</label>
                    <ColorPicker
                        color={newColorValue}
                        onChange={setNewColorValue}
                        enableAlpha
                    />
                </div>
                
                <Button
                    isPrimary
                    onClick={addColor}
                    disabled={!newColorName.trim()}
                >
                    {__('Add Color', 'studio')}
                </Button>
            </div>

            {/* Color Palette */}
            <div className="ds-color-palette">
                <h4>{__('Color Palette', 'studio')}</h4>
                
                {colors.length === 0 && (
                    <Notice status="info" isDismissible={false}>
                        {__('No colors in palette. Add your first color above.', 'studio')}
                    </Notice>
                )}

                {colors.map((color, index) => (
                    <div key={index} className="ds-color-item">
                        <Flex>
                            <FlexItem>
                                <div 
                                    className="ds-color-swatch"
                                    style={{ backgroundColor: color.color }}
                                />
                            </FlexItem>
                            
                            <FlexItem isBlock>
                                <TextControl
                                    label={__('Name', 'studio')}
                                    value={color.name}
                                    onChange={(value) => updateColor(index, 'name', value)}
                                />
                                
                                <TextControl
                                    label={__('Color', 'studio')}
                                    value={color.color}
                                    onChange={(value) => updateColor(index, 'color', value)}
                                />
                                
                                <div className="ds-color-slug">
                                    <strong>Slug:</strong> {color.slug}
                                </div>
                                
                                <div className="ds-css-variable">
                                    <code>--wp--preset--color--{color.slug}</code>
                                </div>
                            </FlexItem>
                            
                            <FlexItem>
                                <Button
                                    isDestructive
                                    isSmall
                                    onClick={() => removeColor(index)}
                                >
                                    {__('Remove', 'studio')}
                                </Button>
                            </FlexItem>
                        </Flex>
                    </div>
                ))}
            </div>

            {/* CSS Variables Preview */}
            {colors.length > 0 && (
                <div className="ds-css-preview">
                    <h4>{__('Generated CSS Variables', 'studio')}</h4>
                    <pre className="ds-css-code">
                        {generateCSSVariables()}
                    </pre>
                </div>
            )}

            {/* Save Button */}
            <div className="ds-save-section">
                <Button
                    isPrimary
                    onClick={saveThemeJson}
                    disabled={isLoading}
                    isBusy={isLoading}
                >
                    {isLoading ? __('Saving...', 'studio') : __('Save to theme.json', 'studio')}
                </Button>
            </div>
        </div>
    );
};

export default ColorManager;
