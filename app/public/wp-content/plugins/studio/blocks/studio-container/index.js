/**
 * Studio Container Block
 * 
 * Advanced layout container with Studio design token integration
 * Phase 2A: Build-time CSS generation + GB2.0 performance patterns
 */

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ColorPicker, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Build-time CSS generation (Phase 2A - GB2.0 Pattern)
 * Resolves Studio tokens to actual values for optimal performance
 */
const generateBuildTimeCSS = (attributes) => {
    let cssRules = [];

    // Background Color - use direct color value
    if (attributes.backgroundColor) {
        cssRules.push(`background-color: ${attributes.backgroundColor}`);
    }

    // Padding - convert to pixel values
    if (attributes.padding) {
        cssRules.push(`padding: ${attributes.padding}`);
    }

    // Gap - convert to pixel values
    if (attributes.gap) {
        cssRules.push(`gap: ${attributes.gap}`);
    }

    // Layout properties
    if (attributes.display) {
        cssRules.push(`display: ${attributes.display}`);
    }

    if (attributes.display === 'flex') {
        // Set default flex-direction if not specified
        const flexDirection = attributes.flexDirection || 'row';
        cssRules.push(`flex-direction: ${flexDirection}`);
        
        if (attributes.justifyContent) {
            cssRules.push(`justify-content: ${attributes.justifyContent}`);
        }
        if (attributes.alignItems) {
            cssRules.push(`align-items: ${attributes.alignItems}`);
        }
    }

    return cssRules.join('; ');
};

/**
 * Block Edit Component
 */
const Edit = ({ attributes, setAttributes }) => {
    const {
        display,
        flexDirection,
        justifyContent,
        alignItems,
        gap,
        padding,
        backgroundColor
    } = attributes;

    // Generate build-time CSS for editor preview
    const buildTimeCSS = generateBuildTimeCSS(attributes);
    
    // Parse CSS string into style object for inline styles
    const styles = {};
    buildTimeCSS.split('; ').forEach(rule => {
        if (rule.trim()) {
            const [property, value] = rule.split(': ');
            if (property && value) {
                const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                styles[camelProperty] = value;
            }
        }
    });

    const blockProps = useBlockProps({
        className: 'studio-container',
        style: styles,
        'data-display': display,
        'data-studio-container': true
    });

    return (
        <>
            <InspectorControls>
                {/* Layout Panel */}
                <PanelBody title={__('Layout', 'studio')} initialOpen={true}>
                    <SelectControl
                        label={__('Display', 'studio')}
                        value={display || 'flex'}
                        options={[
                            { label: __('Flex', 'studio'), value: 'flex' },
                            { label: __('Block', 'studio'), value: 'block' },
                            { label: __('Grid', 'studio'), value: 'grid' }
                        ]}
                        onChange={(value) => setAttributes({ display: value })}
                    />
                    
                    {display === 'flex' && (
                        <>
                            <SelectControl
                                label={__('Direction', 'studio')}
                                value={flexDirection || 'row'}
                                options={[
                                    { label: __('Row', 'studio'), value: 'row' },
                                    { label: __('Column', 'studio'), value: 'column' }
                                ]}
                                onChange={(value) => setAttributes({ flexDirection: value })}
                            />
                            
                            <SelectControl
                                label={__('Justify Content', 'studio')}
                                value={justifyContent || 'flex-start'}
                                options={[
                                    { label: __('Start', 'studio'), value: 'flex-start' },
                                    { label: __('Center', 'studio'), value: 'center' },
                                    { label: __('End', 'studio'), value: 'flex-end' },
                                    { label: __('Space Between', 'studio'), value: 'space-between' },
                                    { label: __('Space Around', 'studio'), value: 'space-around' }
                                ]}
                                onChange={(value) => setAttributes({ justifyContent: value })}
                            />
                            
                            <SelectControl
                                label={__('Align Items', 'studio')}
                                value={alignItems || 'stretch'}
                                options={[
                                    { label: __('Stretch', 'studio'), value: 'stretch' },
                                    { label: __('Start', 'studio'), value: 'flex-start' },
                                    { label: __('Center', 'studio'), value: 'center' },
                                    { label: __('End', 'studio'), value: 'flex-end' }
                                ]}
                                onChange={(value) => setAttributes({ alignItems: value })}
                            />
                        </>
                    )}
                </PanelBody>

                {/* Spacing Panel */}
                <PanelBody title={__('Spacing', 'studio')} initialOpen={false}>
                    <RangeControl
                        label={__('Gap', 'studio')}
                        value={parseInt(gap) || 0}
                        onChange={(value) => setAttributes({ gap: value + 'px' })}
                        min={0}
                        max={100}
                        step={4}
                    />
                    
                    <RangeControl
                        label={__('Padding', 'studio')}
                        value={parseInt(padding) || 0}
                        onChange={(value) => setAttributes({ padding: value + 'px' })}
                        min={0}
                        max={100}
                        step={4}
                    />
                </PanelBody>

                {/* Background Panel */}
                <PanelBody title={__('Background', 'studio')} initialOpen={false}>
                    <ColorPicker
                        color={backgroundColor || '#ffffff'}
                        onChange={(color) => setAttributes({ backgroundColor: color.hex })}
                        disableAlpha={false}
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
 * Block Save Component with Build-time CSS (Phase 2A)
 */
const Save = ({ attributes }) => {
    // Generate build-time CSS for frontend
    const buildTimeCSS = generateBuildTimeCSS(attributes);
    
    // Parse CSS string into style object for inline styles
    const styles = {};
    buildTimeCSS.split('; ').forEach(rule => {
        if (rule.trim()) {
            const [property, value] = rule.split(': ');
            if (property && value) {
                const camelProperty = property.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
                styles[camelProperty] = value;
            }
        }
    });

    const blockProps = useBlockProps.save({
        className: 'studio-container',
        style: styles,
        'data-display': attributes.display,
        'data-studio-container': true
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
    apiVersion: 2,
    title: __('Studio Container', 'studio'),
    description: __('Advanced layout container with Studio design token integration', 'studio'),
    icon: 'layout',
    category: 'layout',
    keywords: ['studio', 'container', 'layout'],
    edit: Edit,
    save: Save
});
