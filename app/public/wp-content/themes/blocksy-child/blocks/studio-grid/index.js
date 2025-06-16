import { registerBlockType } from '@wordpress/blocks';
import { 
    InnerBlocks, 
    InspectorControls, 
    useBlockProps,
    useInnerBlocksProps
} from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    SelectControl,
    TextControl,
    __experimentalUnitControl as UnitControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Gap presets
const gapPresets = [
    { label: 'None', value: 'none' },
    { label: 'Small', value: 'small' },
    { label: 'Medium', value: 'medium' },
    { label: 'Large', value: 'large' },
    { label: 'X-Large', value: 'xlarge' }
];

// Alignment options
const alignOptions = [
    { label: 'Stretch', value: 'stretch' },
    { label: 'Start', value: 'start' },
    { label: 'Center', value: 'center' },
    { label: 'End', value: 'end' }
];

// Auto flow options
const flowOptions = [
    { label: 'Row', value: 'row' },
    { label: 'Column', value: 'column' },
    { label: 'Dense', value: 'dense' }
];

registerBlockType('studio/grid', {
    edit: function Edit({ attributes, setAttributes }) {
        const { 
            columns, 
            columnsMobile, 
            columnsTablet, 
            gap, 
            alignItems, 
            justifyItems,
            minHeight,
            autoFlow,
            autoRows
        } = attributes;

        const blockProps = useBlockProps({
            className: `studio-grid studio-grid--gap-${gap}`,
            style: {
                '--studio-grid-columns': columns,
                '--studio-grid-columns-tablet': columnsTablet,
                '--studio-grid-columns-mobile': columnsMobile,
                '--studio-grid-align': alignItems,
                '--studio-grid-justify': justifyItems,
                '--studio-grid-min-height': minHeight || undefined,
                '--studio-grid-auto-flow': autoFlow,
                '--studio-grid-auto-rows': autoRows || 'auto'
            }
        });

        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            allowedBlocks: true,
            template: [
                ['core/group', { className: 'studio-grid__item' }],
                ['core/group', { className: 'studio-grid__item' }]
            ],
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Grid Settings', 'studio')}>
                        <RangeControl
                            label={__('Columns (Desktop)', 'studio')}
                            value={columns}
                            onChange={(value) => setAttributes({ columns: value })}
                            min={1}
                            max={12}
                        />
                        <RangeControl
                            label={__('Columns (Tablet)', 'studio')}
                            value={columnsTablet}
                            onChange={(value) => setAttributes({ columnsTablet: value })}
                            min={1}
                            max={6}
                        />
                        <RangeControl
                            label={__('Columns (Mobile)', 'studio')}
                            value={columnsMobile}
                            onChange={(value) => setAttributes({ columnsMobile: value })}
                            min={1}
                            max={3}
                        />
                        <SelectControl
                            label={__('Gap', 'studio')}
                            value={gap}
                            options={gapPresets}
                            onChange={(value) => setAttributes({ gap: value })}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Alignment', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('Align Items', 'studio')}
                            value={alignItems}
                            options={alignOptions}
                            onChange={(value) => setAttributes({ alignItems: value })}
                            help={__('Vertical alignment of grid items', 'studio')}
                        />
                        <SelectControl
                            label={__('Justify Items', 'studio')}
                            value={justifyItems}
                            options={alignOptions}
                            onChange={(value) => setAttributes({ justifyItems: value })}
                            help={__('Horizontal alignment of grid items', 'studio')}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Advanced', 'studio')} initialOpen={false}>
                        <UnitControl
                            label={__('Minimum Height', 'studio')}
                            value={minHeight}
                            onChange={(value) => setAttributes({ minHeight: value })}
                        />
                        <SelectControl
                            label={__('Auto Flow', 'studio')}
                            value={autoFlow}
                            options={flowOptions}
                            onChange={(value) => setAttributes({ autoFlow: value })}
                            help={__('How auto-placed items flow in the grid', 'studio')}
                        />
                        <TextControl
                            label={__('Auto Rows', 'studio')}
                            value={autoRows}
                            onChange={(value) => setAttributes({ autoRows: value })}
                            help={__('Size of implicitly-created rows (e.g., "minmax(100px, auto)")', 'studio')}
                        />
                    </PanelBody>
                </InspectorControls>
                
                <div {...innerBlocksProps} />
            </>
        );
    },

    save: function Save({ attributes }) {
        const { 
            columns, 
            columnsMobile, 
            columnsTablet, 
            gap, 
            alignItems, 
            justifyItems,
            minHeight,
            autoFlow,
            autoRows
        } = attributes;

        const blockProps = useBlockProps.save({
            className: `studio-grid studio-grid--gap-${gap}`,
            style: {
                '--studio-grid-columns': columns,
                '--studio-grid-columns-tablet': columnsTablet,
                '--studio-grid-columns-mobile': columnsMobile,
                '--studio-grid-align': alignItems,
                '--studio-grid-justify': justifyItems,
                '--studio-grid-min-height': minHeight || undefined,
                '--studio-grid-auto-flow': autoFlow,
                '--studio-grid-auto-rows': autoRows || 'auto'
            }
        });

        return (
            <div {...blockProps}>
                <InnerBlocks.Content />
            </div>
        );
    }
});
