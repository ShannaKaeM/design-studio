/**
 * Studio Container Block
 * 
 * Advanced layout container with Studio design token integration
 */

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, __experimentalBoxControl as BoxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Studio Components
import { StudioColorPicker, StudioSpacingPicker, StudioGradientPicker } from '../components/studio-controls';

/**
 * Block Edit Component
 */
const Edit = ({ attributes, setAttributes }) => {
    const {
        studioBackgroundColor,
        studioBackgroundGradient,
        studioPadding,
        studioMargin,
        studioBorderRadius,
        studioMinHeight,
        studioDisplay,
        studioFlexDirection,
        studioJustifyContent,
        studioAlignItems,
        studioGap,
        studioGridColumns,
        studioGridRows
    } = attributes;

    // Get Studio tokens from global
    const studioTokens = window.studioBlocks?.tokens || {};

    // Generate CSS variables from Studio tokens
    const generateCSSVariables = () => {
        const cssVars = {};
        
        if (studioBackgroundColor) {
            cssVars['--studio-bg-color'] = studioBackgroundColor;
        }
        
        if (studioBackgroundGradient) {
            cssVars['--studio-bg-gradient'] = studioBackgroundGradient;
        }
        
        if (studioPadding) {
            if (studioPadding.top) cssVars['--studio-padding-top'] = studioPadding.top;
            if (studioPadding.right) cssVars['--studio-padding-right'] = studioPadding.right;
            if (studioPadding.bottom) cssVars['--studio-padding-bottom'] = studioPadding.bottom;
            if (studioPadding.left) cssVars['--studio-padding-left'] = studioPadding.left;
        }
        
        if (studioMargin) {
            if (studioMargin.top) cssVars['--studio-margin-top'] = studioMargin.top;
            if (studioMargin.bottom) cssVars['--studio-margin-bottom'] = studioMargin.bottom;
        }
        
        if (studioBorderRadius) cssVars['--studio-border-radius'] = studioBorderRadius;
        if (studioMinHeight) cssVars['--studio-min-height'] = studioMinHeight;
        if (studioGap) cssVars['--studio-gap'] = studioGap;
        if (studioGridColumns) cssVars['--studio-grid-columns'] = studioGridColumns;
        if (studioGridRows) cssVars['--studio-grid-rows'] = studioGridRows;
        
        return cssVars;
    };

    // Generate CSS classes
    const generateClasses = () => {
        let classes = 'studio-container';
        
        if (studioDisplay) classes += ` studio-container--${studioDisplay}`;
        if (studioDisplay === 'flex' && studioFlexDirection) classes += ` studio-container--flex-${studioFlexDirection}`;
        if (studioDisplay === 'flex' && studioJustifyContent) classes += ` studio-container--justify-${studioJustifyContent}`;
        if (studioDisplay === 'flex' && studioAlignItems) classes += ` studio-container--align-${studioAlignItems}`;
        
        return classes;
    };

    const blockProps = useBlockProps({
        className: generateClasses(),
        style: generateCSSVariables()
    });

    return (
        <>
            <InspectorControls>
                {/* Background Panel */}
                <PanelBody title={__('Background', 'ds-studio')} initialOpen={true}>
                    <StudioColorPicker
                        label={__('Background Color', 'ds-studio')}
                        value={studioBackgroundColor}
                        tokens={studioTokens.colors}
                        onChange={(color) => setAttributes({ studioBackgroundColor: color })}
                    />
                    
                    <StudioGradientPicker
                        label={__('Background Gradient', 'ds-studio')}
                        value={studioBackgroundGradient}
                        tokens={studioTokens.gradients}
                        onChange={(gradient) => setAttributes({ studioBackgroundGradient: gradient })}
                    />
                </PanelBody>

                {/* Spacing Panel */}
                <PanelBody title={__('Spacing', 'ds-studio')} initialOpen={false}>
                    <StudioSpacingPicker
                        label={__('Padding', 'ds-studio')}
                        value={studioPadding}
                        tokens={studioTokens.spacing}
                        sides={['top', 'right', 'bottom', 'left']}
                        onChange={(padding) => setAttributes({ studioPadding: padding })}
                    />
                    
                    <StudioSpacingPicker
                        label={__('Margin', 'ds-studio')}
                        value={studioMargin}
                        tokens={studioTokens.spacing}
                        sides={['top', 'bottom']}
                        onChange={(margin) => setAttributes({ studioMargin: margin })}
                    />
                    
                    <StudioSpacingPicker
                        label={__('Border Radius', 'ds-studio')}
                        value={studioBorderRadius}
                        tokens={studioTokens.spacing}
                        single={true}
                        onChange={(radius) => setAttributes({ studioBorderRadius: radius })}
                    />
                </PanelBody>

                {/* Layout Panel */}
                <PanelBody title={__('Layout', 'ds-studio')} initialOpen={false}>
                    <SelectControl
                        label={__('Display', 'ds-studio')}
                        value={studioDisplay}
                        options={[
                            { label: __('Block', 'ds-studio'), value: 'block' },
                            { label: __('Flex', 'ds-studio'), value: 'flex' },
                            { label: __('Grid', 'ds-studio'), value: 'grid' }
                        ]}
                        onChange={(display) => setAttributes({ studioDisplay: display })}
                    />
                    
                    {studioDisplay === 'flex' && (
                        <>
                            <SelectControl
                                label={__('Flex Direction', 'ds-studio')}
                                value={studioFlexDirection}
                                options={[
                                    { label: __('Column', 'ds-studio'), value: 'column' },
                                    { label: __('Row', 'ds-studio'), value: 'row' }
                                ]}
                                onChange={(direction) => setAttributes({ studioFlexDirection: direction })}
                            />
                            
                            <SelectControl
                                label={__('Justify Content', 'ds-studio')}
                                value={studioJustifyContent}
                                options={[
                                    { label: __('Start', 'ds-studio'), value: 'flex-start' },
                                    { label: __('Center', 'ds-studio'), value: 'center' },
                                    { label: __('End', 'ds-studio'), value: 'flex-end' },
                                    { label: __('Space Between', 'ds-studio'), value: 'space-between' },
                                    { label: __('Space Around', 'ds-studio'), value: 'space-around' },
                                    { label: __('Space Evenly', 'ds-studio'), value: 'space-evenly' }
                                ]}
                                onChange={(justify) => setAttributes({ studioJustifyContent: justify })}
                            />
                            
                            <SelectControl
                                label={__('Align Items', 'ds-studio')}
                                value={studioAlignItems}
                                options={[
                                    { label: __('Stretch', 'ds-studio'), value: 'stretch' },
                                    { label: __('Start', 'ds-studio'), value: 'flex-start' },
                                    { label: __('Center', 'ds-studio'), value: 'center' },
                                    { label: __('End', 'ds-studio'), value: 'flex-end' }
                                ]}
                                onChange={(align) => setAttributes({ studioAlignItems: align })}
                            />
                        </>
                    )}
                    
                    {(studioDisplay === 'flex' || studioDisplay === 'grid') && (
                        <StudioSpacingPicker
                            label={__('Gap', 'ds-studio')}
                            value={studioGap}
                            tokens={studioTokens.spacing}
                            single={true}
                            onChange={(gap) => setAttributes({ studioGap: gap })}
                        />
                    )}
                    
                    {studioDisplay === 'grid' && (
                        <>
                            <SelectControl
                                label={__('Grid Columns', 'ds-studio')}
                                value={studioGridColumns}
                                options={[
                                    { label: __('Auto', 'ds-studio'), value: '' },
                                    { label: __('1 Column', 'ds-studio'), value: '1fr' },
                                    { label: __('2 Columns', 'ds-studio'), value: '1fr 1fr' },
                                    { label: __('3 Columns', 'ds-studio'), value: '1fr 1fr 1fr' },
                                    { label: __('4 Columns', 'ds-studio'), value: '1fr 1fr 1fr 1fr' },
                                    { label: __('Auto Fit', 'ds-studio'), value: 'repeat(auto-fit, minmax(250px, 1fr))' }
                                ]}
                                onChange={(columns) => setAttributes({ studioGridColumns: columns })}
                            />
                        </>
                    )}
                </PanelBody>

                {/* Dimensions Panel */}
                <PanelBody title={__('Dimensions', 'ds-studio')} initialOpen={false}>
                    <StudioSpacingPicker
                        label={__('Min Height', 'ds-studio')}
                        value={studioMinHeight}
                        tokens={studioTokens.spacing}
                        single={true}
                        allowCustom={true}
                        onChange={(height) => setAttributes({ studioMinHeight: height })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <InnerBlocks />
            </div>
        </>
    );
};

/**
 * Block Save Component
 */
const Save = ({ attributes }) => {
    const {
        studioBackgroundColor,
        studioBackgroundGradient,
        studioPadding,
        studioMargin,
        studioBorderRadius,
        studioMinHeight,
        studioDisplay,
        studioFlexDirection,
        studioJustifyContent,
        studioAlignItems,
        studioGap,
        studioGridColumns,
        studioGridRows
    } = attributes;

    // Generate CSS variables (same as Edit)
    const generateCSSVariables = () => {
        const cssVars = {};
        
        if (studioBackgroundColor) cssVars['--studio-bg-color'] = studioBackgroundColor;
        if (studioBackgroundGradient) cssVars['--studio-bg-gradient'] = studioBackgroundGradient;
        
        if (studioPadding) {
            if (studioPadding.top) cssVars['--studio-padding-top'] = studioPadding.top;
            if (studioPadding.right) cssVars['--studio-padding-right'] = studioPadding.right;
            if (studioPadding.bottom) cssVars['--studio-padding-bottom'] = studioPadding.bottom;
            if (studioPadding.left) cssVars['--studio-padding-left'] = studioPadding.left;
        }
        
        if (studioMargin) {
            if (studioMargin.top) cssVars['--studio-margin-top'] = studioMargin.top;
            if (studioMargin.bottom) cssVars['--studio-margin-bottom'] = studioMargin.bottom;
        }
        
        if (studioBorderRadius) cssVars['--studio-border-radius'] = studioBorderRadius;
        if (studioMinHeight) cssVars['--studio-min-height'] = studioMinHeight;
        if (studioGap) cssVars['--studio-gap'] = studioGap;
        if (studioGridColumns) cssVars['--studio-grid-columns'] = studioGridColumns;
        if (studioGridRows) cssVars['--studio-grid-rows'] = studioGridRows;
        
        return cssVars;
    };

    // Generate CSS classes (same as Edit)
    const generateClasses = () => {
        let classes = 'studio-container';
        
        if (studioDisplay) classes += ` studio-container--${studioDisplay}`;
        if (studioDisplay === 'flex' && studioFlexDirection) classes += ` studio-container--flex-${studioFlexDirection}`;
        if (studioDisplay === 'flex' && studioJustifyContent) classes += ` studio-container--justify-${studioJustifyContent}`;
        if (studioDisplay === 'flex' && studioAlignItems) classes += ` studio-container--align-${studioAlignItems}`;
        
        return classes;
    };

    const blockProps = useBlockProps.save({
        className: generateClasses(),
        style: generateCSSVariables()
    });

    return (
        <div {...blockProps}>
            <InnerBlocks.Content />
        </div>
    );
};

/**
 * Register the Studio Container block
 */
registerBlockType('studio/container', {
    edit: Edit,
    save: Save
});
