import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps, InspectorControls, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Width presets
const widthPresets = [
    { label: 'Content Width', value: 'content' },
    { label: 'Wide Width', value: 'wide' },
    { label: 'Full Width', value: 'full' },
    { label: 'Custom', value: 'custom' }
];

// Padding presets
const paddingPresets = [
    { label: 'None', value: 'none' },
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' },
    { label: 'Extra Large', value: 'xlarge' }
];

// Tag options
const tagOptions = [
    { label: 'Div', value: 'div' },
    { label: 'Section', value: 'section' },
    { label: 'Article', value: 'article' },
    { label: 'Aside', value: 'aside' },
    { label: 'Main', value: 'main' },
    { label: 'Header', value: 'header' },
    { label: 'Footer', value: 'footer' }
];

registerBlockType('studio/container', {
    edit: ({ attributes, setAttributes }) => {
        const { widthPreset, paddingPreset, tagName, minHeight } = attributes;
        
        const blockProps = useBlockProps({
            className: `studio-container width-${widthPreset} padding-${paddingPreset}`,
            style: minHeight ? { minHeight } : undefined
        });

        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Container Settings', 'studio')}>
                        <SelectControl
                            label={__('Width', 'studio')}
                            value={widthPreset}
                            options={widthPresets}
                            onChange={(value) => setAttributes({ widthPreset: value })}
                        />
                        
                        <SelectControl
                            label={__('Padding', 'studio')}
                            value={paddingPreset}
                            options={paddingPresets}
                            onChange={(value) => setAttributes({ paddingPreset: value })}
                        />
                        
                        <SelectControl
                            label={__('HTML Tag', 'studio')}
                            value={tagName}
                            options={tagOptions}
                            onChange={(value) => setAttributes({ tagName: value })}
                        />
                        
                        <TextControl
                            label={__('Minimum Height', 'studio')}
                            value={minHeight}
                            onChange={(value) => setAttributes({ minHeight: value })}
                            placeholder="e.g., 400px, 50vh"
                            help={__('Leave empty for auto height', 'studio')}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div {...innerBlocksProps} />
            </>
        );
    },

    save: ({ attributes }) => {
        const { widthPreset, paddingPreset, tagName, minHeight } = attributes;
        const Tag = tagName;
        
        const blockProps = useBlockProps.save({
            className: `studio-container width-${widthPreset} padding-${paddingPreset}`,
            style: minHeight ? { minHeight } : undefined
        });

        return (
            <Tag {...blockProps}>
                <InnerBlocks.Content />
            </Tag>
        );
    }
});
