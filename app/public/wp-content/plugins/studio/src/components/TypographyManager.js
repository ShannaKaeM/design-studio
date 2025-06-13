/**
 * Typography Manager Component
 */

import { useState, useEffect } from '@wordpress/element';
import { 
    TextControl, 
    Button, 
    SelectControl,
    RangeControl,
    Flex, 
    FlexItem,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

const TypographyManager = () => {
    const { themeJson, updateThemeJson } = useThemeJson();
    const [fontSizes, setFontSizes] = useState([]);
    const [fontFamilies, setFontFamilies] = useState([]);

    // Font size presets
    const fontSizePresets = {
        'modern': [
            { slug: 'xs', size: '0.75rem', name: 'Extra Small' },
            { slug: 'sm', size: '0.875rem', name: 'Small' },
            { slug: 'base', size: '1rem', name: 'Base' },
            { slug: 'lg', size: '1.125rem', name: 'Large' },
            { slug: 'xl', size: '1.25rem', name: 'Extra Large' },
            { slug: '2xl', size: '1.5rem', name: '2X Large' },
            { slug: '3xl', size: '1.875rem', name: '3X Large' },
            { slug: '4xl', size: '2.25rem', name: '4X Large' }
        ]
    };

    // Initialize from theme.json
    useEffect(() => {
        if (themeJson?.settings?.typography?.fontSizes) {
            setFontSizes(themeJson.settings.typography.fontSizes);
        }
        if (themeJson?.settings?.typography?.fontFamilies) {
            setFontFamilies(themeJson.settings.typography.fontFamilies);
        }
    }, [themeJson]);

    const loadFontSizePreset = (presetName) => {
        const preset = fontSizePresets[presetName];
        if (preset) {
            setFontSizes(preset);
            updateTypography({ fontSizes: preset, fontFamilies });
        }
    };

    const updateTypography = (updates) => {
        const updatedThemeJson = {
            ...themeJson,
            settings: {
                ...themeJson.settings,
                typography: {
                    ...themeJson.settings.typography,
                    ...updates
                }
            }
        };
        updateThemeJson(updatedThemeJson);
    };

    return (
        <div className="ds-typography-manager">
            <div className="ds-font-size-presets">
                <h4>{__('Font Size Presets', 'studio')}</h4>
                <Button
                    isSecondary
                    onClick={() => loadFontSizePreset('modern')}
                >
                    {__('Modern Scale', 'studio')}
                </Button>
            </div>

            <div className="ds-font-sizes">
                <h4>{__('Font Sizes', 'studio')}</h4>
                {fontSizes.length === 0 && (
                    <Notice status="info" isDismissible={false}>
                        {__('No font sizes defined. Load a preset to get started.', 'studio')}
                    </Notice>
                )}
                
                {fontSizes.map((fontSize, index) => (
                    <div key={index} className="ds-font-size-item">
                        <div 
                            style={{ 
                                fontSize: fontSize.size,
                                marginBottom: '8px',
                                fontWeight: '500'
                            }}
                        >
                            {fontSize.name} - {fontSize.size}
                        </div>
                        <code>--wp--preset--font-size--{fontSize.slug}</code>
                    </div>
                ))}
            </div>

            {fontSizes.length > 0 && (
                <div className="ds-css-preview">
                    <h4>{__('Generated CSS Variables', 'studio')}</h4>
                    <pre className="ds-css-code">
                        {fontSizes.map(fontSize => 
                            `--wp--preset--font-size--${fontSize.slug}: ${fontSize.size};`
                        ).join('\n')}
                    </pre>
                </div>
            )}
        </div>
    );
};

export default TypographyManager;
