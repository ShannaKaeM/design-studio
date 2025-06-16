import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps, InspectorControls, BlockControls, URLInput } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, TextControl, ToolbarGroup, ToolbarButton, Popover, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { link, linkOff } from '@wordpress/icons';
import { useState } from '@wordpress/element';

// Button presets
const buttonPresets = [
    { label: 'Primary', value: 'primary' },
    { label: 'Secondary', value: 'secondary' },
    { label: 'Outline', value: 'outline' },
    { label: 'Ghost', value: 'ghost' },
    { label: 'Link', value: 'link' }
];

// Size options
const sizeOptions = [
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' }
];

// Width options
const widthOptions = [
    { label: 'Auto', value: 'auto' },
    { label: 'Full Width', value: 'full' },
    { label: 'Fixed', value: 'fixed' }
];

// Icon position options
const iconPositions = [
    { label: 'None', value: 'none' },
    { label: 'Before Text', value: 'before' },
    { label: 'After Text', value: 'after' }
];

// Common icons (simplified for now)
const iconOptions = [
    { label: 'None', value: '' },
    { label: 'Arrow Right →', value: 'arrow-right' },
    { label: 'Arrow Left ←', value: 'arrow-left' },
    { label: 'Download ↓', value: 'download' },
    { label: 'External Link ↗', value: 'external' },
    { label: 'Plus +', value: 'plus' },
    { label: 'Check ✓', value: 'check' }
];

registerBlockType('studio/button', {
    edit: ({ attributes, setAttributes, isSelected }) => {
        const { text, url, linkTarget, rel, buttonPreset, size, width, iconPosition, icon } = attributes;
        const [isLinkPopoverOpen, setIsLinkPopoverOpen] = useState(false);
        
        const blockProps = useBlockProps({
            className: `studio-button-wrapper align-${attributes.align || 'none'} width-${width}`
        });

        const buttonClasses = `studio-button preset-${buttonPreset} size-${size}${icon && iconPosition !== 'none' ? ` has-icon icon-${iconPosition}` : ''}`;

        const renderIcon = () => {
            if (!icon || iconPosition === 'none') return null;
            return <span className="studio-button-icon">{getIconSymbol(icon)}</span>;
        };

        return (
            <>
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={url ? link : linkOff}
                            label={__('Link', 'studio')}
                            onClick={() => setIsLinkPopoverOpen(!isLinkPopoverOpen)}
                            isActive={!!url}
                        />
                    </ToolbarGroup>
                </BlockControls>

                <InspectorControls>
                    <PanelBody title={__('Button Settings', 'studio')}>
                        <SelectControl
                            label={__('Style Preset', 'studio')}
                            value={buttonPreset}
                            options={buttonPresets}
                            onChange={(value) => setAttributes({ buttonPreset: value })}
                        />
                        
                        <SelectControl
                            label={__('Size', 'studio')}
                            value={size}
                            options={sizeOptions}
                            onChange={(value) => setAttributes({ size: value })}
                        />
                        
                        <SelectControl
                            label={__('Width', 'studio')}
                            value={width}
                            options={widthOptions}
                            onChange={(value) => setAttributes({ width: value })}
                        />
                    </PanelBody>

                    <PanelBody title={__('Icon', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('Icon Position', 'studio')}
                            value={iconPosition}
                            options={iconPositions}
                            onChange={(value) => setAttributes({ iconPosition: value })}
                        />
                        
                        {iconPosition !== 'none' && (
                            <SelectControl
                                label={__('Icon', 'studio')}
                                value={icon}
                                options={iconOptions}
                                onChange={(value) => setAttributes({ icon: value })}
                            />
                        )}
                    </PanelBody>

                    <PanelBody title={__('Link Settings', 'studio')} initialOpen={false}>
                        <TextControl
                            label={__('URL', 'studio')}
                            value={url}
                            onChange={(value) => setAttributes({ url: value })}
                        />
                        
                        <ToggleControl
                            label={__('Open in new tab', 'studio')}
                            checked={linkTarget === '_blank'}
                            onChange={(value) => {
                                setAttributes({ 
                                    linkTarget: value ? '_blank' : '',
                                    rel: value ? 'noopener' : ''
                                });
                            }}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    <div className={buttonClasses}>
                        {iconPosition === 'before' && renderIcon()}
                        <RichText
                            tagName="span"
                            className="studio-button-text"
                            value={text}
                            onChange={(value) => setAttributes({ text: value })}
                            placeholder={__('Button text...', 'studio')}
                            allowedFormats={[]}
                            disableLineBreaks
                        />
                        {iconPosition === 'after' && renderIcon()}
                    </div>
                </div>

                {isLinkPopoverOpen && (
                    <Popover
                        position="bottom center"
                        onClose={() => setIsLinkPopoverOpen(false)}
                    >
                        <div style={{ padding: '16px', minWidth: '260px' }}>
                            <URLInput
                                value={url}
                                onChange={(value) => setAttributes({ url: value })}
                                placeholder={__('Paste URL or type to search', 'studio')}
                            />
                            <div style={{ marginTop: '8px' }}>
                                <Button
                                    variant="primary"
                                    onClick={() => setIsLinkPopoverOpen(false)}
                                    style={{ marginRight: '8px' }}
                                >
                                    {__('Apply', 'studio')}
                                </Button>
                                <Button
                                    variant="tertiary"
                                    onClick={() => {
                                        setAttributes({ url: '' });
                                        setIsLinkPopoverOpen(false);
                                    }}
                                >
                                    {__('Remove', 'studio')}
                                </Button>
                            </div>
                        </div>
                    </Popover>
                )}
            </>
        );
    },

    save: ({ attributes }) => {
        const { text, url, linkTarget, rel, buttonPreset, size, width, iconPosition, icon } = attributes;
        
        const blockProps = useBlockProps.save({
            className: `studio-button-wrapper align-${attributes.align || 'none'} width-${width}`
        });

        const buttonClasses = `studio-button preset-${buttonPreset} size-${size}${icon && iconPosition !== 'none' ? ` has-icon icon-${iconPosition}` : ''}`;

        const renderIcon = () => {
            if (!icon || iconPosition === 'none') return null;
            return <span className="studio-button-icon">{getIconSymbol(icon)}</span>;
        };

        const buttonContent = (
            <>
                {iconPosition === 'before' && renderIcon()}
                <RichText.Content
                    tagName="span"
                    className="studio-button-text"
                    value={text}
                />
                {iconPosition === 'after' && renderIcon()}
            </>
        );

        return (
            <div {...blockProps}>
                {url ? (
                    <a
                        className={buttonClasses}
                        href={url}
                        target={linkTarget}
                        rel={rel}
                    >
                        {buttonContent}
                    </a>
                ) : (
                    <span className={buttonClasses}>
                        {buttonContent}
                    </span>
                )}
            </div>
        );
    }
});

// Helper function to get icon symbol
function getIconSymbol(iconName) {
    const icons = {
        'arrow-right': '→',
        'arrow-left': '←',
        'download': '↓',
        'external': '↗',
        'plus': '+',
        'check': '✓'
    };
    return icons[iconName] || '';
}
