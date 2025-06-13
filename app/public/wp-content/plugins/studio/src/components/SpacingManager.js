/**
 * Spacing Manager Component
 * Handles spacing scale management
 */

import { useState, useEffect } from '@wordpress/element';
import { 
    RangeControl, 
    Button, 
    TextControl, 
    Flex, 
    FlexItem,
    Notice,
    SelectControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

const SpacingManager = () => {
    const { themeJson, updateThemeJson } = useThemeJson();
    const [spacingSizes, setSpacingSizes] = useState([]);
    const [newSpacingName, setNewSpacingName] = useState('');
    const [newSpacingSize, setNewSpacingSize] = useState(16);
    const [newSpacingUnit, setNewSpacingUnit] = useState('px');

    // Predefined spacing scales
    const spacingPresets = {
        'tailwind': [
            { slug: 'xs', size: '0.25rem', name: 'Extra Small' },
            { slug: 'sm', size: '0.5rem', name: 'Small' },
            { slug: 'md', size: '1rem', name: 'Medium' },
            { slug: 'lg', size: '1.5rem', name: 'Large' },
            { slug: 'xl', size: '2rem', name: 'Extra Large' },
            { slug: '2xl', size: '3rem', name: '2X Large' },
            { slug: '3xl', size: '4rem', name: '3X Large' }
        ],
        'bootstrap': [
            { slug: '1', size: '0.25rem', name: 'Spacing 1' },
            { slug: '2', size: '0.5rem', name: 'Spacing 2' },
            { slug: '3', size: '1rem', name: 'Spacing 3' },
            { slug: '4', size: '1.5rem', name: 'Spacing 4' },
            { slug: '5', size: '3rem', name: 'Spacing 5' }
        ],
        'custom': []
    };

    // Initialize spacing from theme.json
    useEffect(() => {
        if (themeJson?.settings?.spacing?.spacingSizes) {
            setSpacingSizes(themeJson.settings.spacing.spacingSizes);
        }
    }, [themeJson]);

    /**
     * Add new spacing size
     */
    const addSpacing = () => {
        if (!newSpacingName.trim()) return;

        const newSpacing = {
            slug: newSpacingName.toLowerCase().replace(/\s+/g, '-'),
            size: `${newSpacingSize}${newSpacingUnit}`,
            name: newSpacingName
        };

        const updatedSpacing = [...spacingSizes, newSpacing];
        setSpacingSizes(updatedSpacing);
        updateSpacingScale(updatedSpacing);
        
        // Reset form
        setNewSpacingName('');
        setNewSpacingSize(16);
        setNewSpacingUnit('px');
    };

    /**
     * Remove spacing size
     */
    const removeSpacing = (index) => {
        const updatedSpacing = spacingSizes.filter((_, i) => i !== index);
        setSpacingSizes(updatedSpacing);
        updateSpacingScale(updatedSpacing);
    };

    /**
     * Update spacing value
     */
    const updateSpacing = (index, field, value) => {
        const updatedSpacing = [...spacingSizes];
        updatedSpacing[index] = {
            ...updatedSpacing[index],
            [field]: value
        };
        
        // Update slug when name changes
        if (field === 'name') {
            updatedSpacing[index].slug = value.toLowerCase().replace(/\s+/g, '-');
        }
        
        setSpacingSizes(updatedSpacing);
        updateSpacingScale(updatedSpacing);
    };

    /**
     * Load spacing preset
     */
    const loadPreset = (presetName) => {
        const preset = spacingPresets[presetName];
        if (preset) {
            setSpacingSizes(preset);
            updateSpacingScale(preset);
        }
    };

    /**
     * Update theme.json spacing scale
     */
    const updateSpacingScale = (spacingScale) => {
        const updatedThemeJson = {
            ...themeJson,
            settings: {
                ...themeJson.settings,
                spacing: {
                    ...themeJson.settings.spacing,
                    spacingSizes: spacingScale
                }
            }
        };
        
        updateThemeJson(updatedThemeJson);
    };

    /**
     * Generate CSS variables preview
     */
    const generateCSSVariables = () => {
        return spacingSizes.map(spacing => 
            `--wp--preset--spacing--${spacing.slug}: ${spacing.size};`
        ).join('\n');
    };

    /**
     * Convert size to pixels for visual preview
     */
    const getSizeInPixels = (size) => {
        if (size.includes('rem')) {
            return parseFloat(size) * 16; // Assuming 1rem = 16px
        } else if (size.includes('em')) {
            return parseFloat(size) * 16;
        } else if (size.includes('px')) {
            return parseFloat(size);
        }
        return 16; // fallback
    };

    return (
        <div className="ds-spacing-manager">
            {/* Spacing Presets */}
            <div className="ds-spacing-presets">
                <h4>{__('Quick Start', 'studio')}</h4>
                <Flex>
                    <FlexItem>
                        <Button
                            isSecondary
                            onClick={() => loadPreset('tailwind')}
                        >
                            {__('Tailwind Scale', 'studio')}
                        </Button>
                    </FlexItem>
                    <FlexItem>
                        <Button
                            isSecondary
                            onClick={() => loadPreset('bootstrap')}
                        >
                            {__('Bootstrap Scale', 'studio')}
                        </Button>
                    </FlexItem>
                </Flex>
            </div>

            {/* Add New Spacing */}
            <div className="ds-add-spacing">
                <h4>{__('Add New Spacing', 'studio')}</h4>
                
                <TextControl
                    label={__('Spacing Name', 'studio')}
                    value={newSpacingName}
                    onChange={setNewSpacingName}
                    placeholder="Medium"
                />
                
                <Flex>
                    <FlexItem isBlock>
                        <RangeControl
                            label={__('Size', 'studio')}
                            value={newSpacingSize}
                            onChange={setNewSpacingSize}
                            min={0}
                            max={200}
                            step={newSpacingUnit === 'rem' ? 0.25 : 1}
                        />
                    </FlexItem>
                    <FlexItem>
                        <SelectControl
                            label={__('Unit', 'studio')}
                            value={newSpacingUnit}
                            onChange={setNewSpacingUnit}
                            options={[
                                { label: 'px', value: 'px' },
                                { label: 'rem', value: 'rem' },
                                { label: 'em', value: 'em' },
                                { label: '%', value: '%' }
                            ]}
                        />
                    </FlexItem>
                </Flex>
                
                <Button
                    isPrimary
                    onClick={addSpacing}
                    disabled={!newSpacingName.trim()}
                >
                    {__('Add Spacing', 'studio')}
                </Button>
            </div>

            {/* Spacing Scale */}
            <div className="ds-spacing-scale">
                <h4>{__('Spacing Scale', 'studio')}</h4>
                
                {spacingSizes.length === 0 && (
                    <Notice status="info" isDismissible={false}>
                        {__('No spacing sizes defined. Add your first spacing above or load a preset.', 'studio')}
                    </Notice>
                )}

                {spacingSizes.map((spacing, index) => (
                    <div key={index} className="ds-spacing-item">
                        <Flex>
                            <FlexItem>
                                <div 
                                    className="ds-spacing-visual"
                                    style={{ 
                                        width: `${Math.min(getSizeInPixels(spacing.size), 100)}px`,
                                        height: '20px',
                                        backgroundColor: '#3a5a59',
                                        borderRadius: '2px'
                                    }}
                                />
                            </FlexItem>
                            
                            <FlexItem isBlock>
                                <TextControl
                                    label={__('Name', 'studio')}
                                    value={spacing.name}
                                    onChange={(value) => updateSpacing(index, 'name', value)}
                                />
                                
                                <TextControl
                                    label={__('Size', 'studio')}
                                    value={spacing.size}
                                    onChange={(value) => updateSpacing(index, 'size', value)}
                                />
                                
                                <div className="ds-spacing-slug">
                                    <strong>Slug:</strong> {spacing.slug}
                                </div>
                                
                                <div className="ds-css-variable">
                                    <code>--wp--preset--spacing--{spacing.slug}</code>
                                </div>
                            </FlexItem>
                            
                            <FlexItem>
                                <Button
                                    isDestructive
                                    isSmall
                                    onClick={() => removeSpacing(index)}
                                >
                                    {__('Remove', 'studio')}
                                </Button>
                            </FlexItem>
                        </Flex>
                    </div>
                ))}
            </div>

            {/* CSS Variables Preview */}
            {spacingSizes.length > 0 && (
                <div className="ds-css-preview">
                    <h4>{__('Generated CSS Variables', 'studio')}</h4>
                    <pre className="ds-css-code">
                        {generateCSSVariables()}
                    </pre>
                </div>
            )}
        </div>
    );
};

export default SpacingManager;
