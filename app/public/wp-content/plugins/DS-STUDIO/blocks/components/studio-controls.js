/**
 * Studio Control Components
 * 
 * Reusable components for Studio design token integration
 */

import { BaseControl, Button, ButtonGroup, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Studio Color Picker
 * Shows colors from Studio design tokens
 */
export const StudioColorPicker = ({ label, value, tokens, onChange }) => {
    const colors = tokens?.themeColors || tokens?.semanticColors || {};
    
    return (
        <BaseControl label={label} className="studio-color-picker">
            <div className="studio-color-grid">
                {Object.entries(colors).map(([key, colorData]) => {
                    const colorValue = colorData.value || colorData;
                    const isSelected = value === `var(--wp--preset--color--${key})`;
                    
                    return (
                        <button
                            key={key}
                            className={`studio-color-swatch ${isSelected ? 'is-selected' : ''}`}
                            style={{ backgroundColor: colorValue }}
                            onClick={() => onChange(`var(--wp--preset--color--${key})`)}
                            title={colorData.name || key}
                        />
                    );
                })}
                
                {/* Clear button */}
                <button
                    className={`studio-color-swatch studio-color-clear ${!value ? 'is-selected' : ''}`}
                    onClick={() => onChange('')}
                    title={__('Clear', 'ds-studio')}
                >
                    ×
                </button>
            </div>
        </BaseControl>
    );
};

/**
 * Studio Gradient Picker
 * Shows gradients from Studio design tokens
 */
export const StudioGradientPicker = ({ label, value, tokens, onChange }) => {
    const gradients = tokens || {};
    
    return (
        <BaseControl label={label} className="studio-gradient-picker">
            <div className="studio-gradient-grid">
                {Object.entries(gradients).map(([key, gradientData]) => {
                    const gradientValue = gradientData.value || gradientData;
                    const isSelected = value === `var(--wp--preset--gradient--${key})`;
                    
                    return (
                        <button
                            key={key}
                            className={`studio-gradient-swatch ${isSelected ? 'is-selected' : ''}`}
                            style={{ background: gradientValue }}
                            onClick={() => onChange(`var(--wp--preset--gradient--${key})`)}
                            title={gradientData.name || key}
                        />
                    );
                })}
                
                {/* Clear button */}
                <button
                    className={`studio-gradient-swatch studio-gradient-clear ${!value ? 'is-selected' : ''}`}
                    onClick={() => onChange('')}
                    title={__('Clear', 'ds-studio')}
                >
                    ×
                </button>
            </div>
        </BaseControl>
    );
};

/**
 * Studio Spacing Picker
 * Shows spacing values from Studio design tokens
 */
export const StudioSpacingPicker = ({ 
    label, 
    value, 
    tokens, 
    onChange, 
    sides = ['top', 'right', 'bottom', 'left'],
    single = false,
    allowCustom = false 
}) => {
    const spacing = tokens || {};
    
    // For single value (gap, border-radius, etc.)
    if (single) {
        return (
            <BaseControl label={label} className="studio-spacing-picker studio-spacing-single">
                <div className="studio-spacing-options">
                    {Object.entries(spacing).map(([key, spacingValue]) => {
                        const cssVar = `var(--wp--preset--spacing--${key})`;
                        const isSelected = value === cssVar;
                        
                        return (
                            <Button
                                key={key}
                                variant={isSelected ? 'primary' : 'secondary'}
                                size="small"
                                onClick={() => onChange(cssVar)}
                                title={`${key}: ${spacingValue}`}
                            >
                                {key.toUpperCase()}
                            </Button>
                        );
                    })}
                    
                    <Button
                        variant={!value ? 'primary' : 'secondary'}
                        size="small"
                        onClick={() => onChange('')}
                    >
                        {__('None', 'ds-studio')}
                    </Button>
                </div>
                
                {allowCustom && (
                    <TextControl
                        label={__('Custom Value', 'ds-studio')}
                        value={value && !value.startsWith('var(') ? value : ''}
                        onChange={(custom) => onChange(custom)}
                        placeholder="e.g. 20px, 2rem, 5%"
                    />
                )}
            </BaseControl>
        );
    }
    
    // For multi-side values (padding, margin)
    const currentValue = value || {};
    
    const updateSide = (side, newValue) => {
        const updated = { ...currentValue };
        if (newValue) {
            updated[side] = newValue;
        } else {
            delete updated[side];
        }
        onChange(updated);
    };
    
    return (
        <BaseControl label={label} className="studio-spacing-picker studio-spacing-multi">
            {sides.map(side => (
                <div key={side} className="studio-spacing-side">
                    <label className="studio-spacing-side-label">
                        {side.charAt(0).toUpperCase() + side.slice(1)}
                    </label>
                    
                    <div className="studio-spacing-options">
                        {Object.entries(spacing).map(([key, spacingValue]) => {
                            const cssVar = `var(--wp--preset--spacing--${key})`;
                            const isSelected = currentValue[side] === cssVar;
                            
                            return (
                                <Button
                                    key={key}
                                    variant={isSelected ? 'primary' : 'secondary'}
                                    size="small"
                                    onClick={() => updateSide(side, cssVar)}
                                    title={`${key}: ${spacingValue}`}
                                >
                                    {key.toUpperCase()}
                                </Button>
                            );
                        })}
                        
                        <Button
                            variant={!currentValue[side] ? 'primary' : 'secondary'}
                            size="small"
                            onClick={() => updateSide(side, '')}
                        >
                            {__('None', 'ds-studio')}
                        </Button>
                    </div>
                </div>
            ))}
        </BaseControl>
    );
};

/**
 * Studio Typography Picker
 * Shows typography presets from Studio design tokens
 */
export const StudioTypographyPicker = ({ label, value, tokens, onChange }) => {
    const typography = tokens || {};
    const fontFamilies = typography.fontFamilies || {};
    const fontSizes = typography.fontSizes || {};
    const fontWeights = typography.fontWeights || {};
    const lineHeights = typography.lineHeights || {};
    
    const currentValue = value || {};
    
    const updateTypography = (property, newValue) => {
        const updated = { ...currentValue };
        if (newValue) {
            updated[property] = newValue;
        } else {
            delete updated[property];
        }
        onChange(updated);
    };
    
    return (
        <BaseControl label={label} className="studio-typography-picker">
            {/* Font Family */}
            <div className="studio-typography-control">
                <SelectControl
                    label={__('Font Family', 'ds-studio')}
                    value={currentValue.fontFamily || ''}
                    options={[
                        { label: __('Default', 'ds-studio'), value: '' },
                        ...Object.entries(fontFamilies).map(([key, fontData]) => ({
                            label: fontData.name || key,
                            value: `var(--wp--preset--font-family--${key})`
                        }))
                    ]}
                    onChange={(fontFamily) => updateTypography('fontFamily', fontFamily)}
                />
            </div>
            
            {/* Font Size */}
            <div className="studio-typography-control">
                <label className="studio-typography-label">{__('Font Size', 'ds-studio')}</label>
                <ButtonGroup>
                    {Object.entries(fontSizes).map(([key, fontSize]) => {
                        const cssVar = `var(--wp--preset--font-size--${key})`;
                        const isSelected = currentValue.fontSize === cssVar;
                        
                        return (
                            <Button
                                key={key}
                                variant={isSelected ? 'primary' : 'secondary'}
                                size="small"
                                onClick={() => updateTypography('fontSize', cssVar)}
                            >
                                {key.toUpperCase()}
                            </Button>
                        );
                    })}
                </ButtonGroup>
            </div>
            
            {/* Font Weight */}
            <div className="studio-typography-control">
                <label className="studio-typography-label">{__('Font Weight', 'ds-studio')}</label>
                <ButtonGroup>
                    {Object.entries(fontWeights).map(([key, fontWeight]) => {
                        const cssVar = `var(--wp--preset--font-weight--${key})`;
                        const isSelected = currentValue.fontWeight === cssVar;
                        
                        return (
                            <Button
                                key={key}
                                variant={isSelected ? 'primary' : 'secondary'}
                                size="small"
                                onClick={() => updateTypography('fontWeight', cssVar)}
                            >
                                {key}
                            </Button>
                        );
                    })}
                </ButtonGroup>
            </div>
            
            {/* Line Height */}
            <div className="studio-typography-control">
                <label className="studio-typography-label">{__('Line Height', 'ds-studio')}</label>
                <ButtonGroup>
                    {Object.entries(lineHeights).map(([key, lineHeight]) => {
                        const cssVar = `var(--wp--preset--line-height--${key})`;
                        const isSelected = currentValue.lineHeight === cssVar;
                        
                        return (
                            <Button
                                key={key}
                                variant={isSelected ? 'primary' : 'secondary'}
                                size="small"
                                onClick={() => updateTypography('lineHeight', cssVar)}
                            >
                                {key}
                            </Button>
                        );
                    })}
                </ButtonGroup>
            </div>
        </BaseControl>
    );
};
