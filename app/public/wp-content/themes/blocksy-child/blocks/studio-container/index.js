import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps, InspectorControls, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Button, Modal, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';

// Helper function to get theme settings
const useThemeSettings = () => {
    return useSelect((select) => {
        const settings = select('core/block-editor').getSettings();
        return settings.__experimentalFeatures?.custom || {};
    }, []);
};

registerBlockType('studio/container', {
    edit: ({ attributes, setAttributes }) => {
        const { widthPreset, paddingPreset, heightPreset, tagName, minHeight } = attributes;
        
        // State for preset saving
        const [isPresetModalOpen, setIsPresetModalOpen] = useState(false);
        const [presetName, setPresetName] = useState('');
        const [saveNotice, setSaveNotice] = useState(null);
        
        // Get theme settings
        const themeSettings = useThemeSettings();
        const containerSettings = themeSettings.container || {};
        const paddingScale = themeSettings.paddingScale || {};
        
        // Build width presets from theme
        const widthPresets = Object.entries(containerSettings.widthPresets || {}).map(([key, preset]) => ({
            label: preset.name,
            value: preset.value
        }));
        
        // Build height presets from theme
        const heightPresets = Object.entries(containerSettings.heightPresets || {}).map(([key, preset]) => ({
            label: preset.name,
            value: preset.value
        }));
        
        // Build padding presets from theme paddingScale
        const paddingPresets = [
            { label: 'None', value: 'none' },
            ...Object.entries(paddingScale).map(([key, value]) => ({
                label: key.charAt(0).toUpperCase() + key.slice(1),
                value: key
            }))
        ];
        
        // Build HTML tag options from theme
        const tagOptions = Object.entries(containerSettings.htmlTags || {}).map(([key, tag]) => ({
            label: tag.name,
            value: tag.value
        }));
        
        // Fallback arrays if theme settings aren't available
        const fallbackWidthPresets = [
            { label: 'Content Width', value: 'content' },
            { label: 'Wide Width', value: 'wide' },
            { label: 'Full Width', value: 'full' },
            { label: 'Custom', value: 'custom' }
        ];
        
        const fallbackHeightPresets = [
            { label: 'Auto', value: 'auto' },
            { label: '25% Viewport', value: '25vh' },
            { label: '50% Viewport', value: '50vh' },
            { label: '75% Viewport', value: '75vh' },
            { label: 'Full Viewport', value: '100vh' }
        ];
        
        const fallbackPaddingPresets = [
            { label: 'None', value: 'none' },
            { label: 'Small', value: 'small' },
            { label: 'Medium', value: 'medium' },
            { label: 'Large', value: 'large' },
            { label: 'Extra Large', value: 'xlarge' }
        ];
        
        const fallbackTagOptions = [
            { label: 'Div', value: 'div' },
            { label: 'Section', value: 'section' },
            { label: 'Article', value: 'article' },
            { label: 'Aside', value: 'aside' },
            { label: 'Main', value: 'main' },
            { label: 'Header', value: 'header' },
            { label: 'Footer', value: 'footer' }
        ];
        
        // Use theme presets or fallbacks
        const finalWidthPresets = widthPresets.length > 0 ? widthPresets : fallbackWidthPresets;
        const finalHeightPresets = heightPresets.length > 0 ? heightPresets : fallbackHeightPresets;
        const finalPaddingPresets = paddingPresets.length > 1 ? paddingPresets : fallbackPaddingPresets; // > 1 because we always add 'None'
        const finalTagOptions = tagOptions.length > 0 ? tagOptions : fallbackTagOptions;
        
        // Save preset function
        const saveAsPreset = () => {
            if (!presetName.trim()) return;
            
            const presetData = {
                name: presetName.trim(),
                attributes: {
                    widthPreset: widthPreset || 'content',
                    paddingPreset: paddingPreset || 'medium', 
                    heightPreset: heightPreset || 'auto',
                    tagName: tagName || 'div',
                    minHeight: minHeight || ''
                },
                description: `Saved from editor on ${new Date().toLocaleDateString()}`
            };
            
            // AJAX call to save preset
            wp.ajax.post('studio_save_preset', {
                action: 'studio_save_preset',
                nonce: window.studioAdmin?.nonce || '',
                block_type: 'container',
                preset_data: presetData
            }).done((response) => {
                setSaveNotice({
                    type: 'success',
                    message: `Preset "${presetName}" saved successfully!`
                });
                setIsPresetModalOpen(false);
                setPresetName('');
                
                // Clear notice after 3 seconds
                setTimeout(() => setSaveNotice(null), 3000);
            }).fail((error) => {
                setSaveNotice({
                    type: 'error', 
                    message: `Failed to save preset: ${error.responseText || 'Unknown error'}`
                });
            });
        };
        
        const blockProps = useBlockProps({
            className: `studio-container width-${widthPreset} padding-${paddingPreset} height-${heightPreset || 'auto'}`,
            style: {
                ...(minHeight && minHeight !== 'auto' ? { minHeight } : {}),
                ...(heightPreset && heightPreset !== 'auto' ? { minHeight: heightPreset } : {})
            }
        });

        const innerBlocksProps = useInnerBlocksProps(blockProps, {
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender
        });

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Layout Settings', 'studio')} initialOpen={true}>
                        <SelectControl
                            label={__('Width', 'studio')}
                            value={widthPreset || 'content'}
                            options={finalWidthPresets}
                            onChange={(value) => setAttributes({ widthPreset: value })}
                            help={__('Choose container width preset', 'studio')}
                        />
                        
                        <SelectControl
                            label={__('Height', 'studio')}
                            value={heightPreset || 'auto'}
                            options={finalHeightPresets}
                            onChange={(value) => setAttributes({ heightPreset: value })}
                            help={__('Choose container height preset', 'studio')}
                        />
                        
                        <SelectControl
                            label={__('Padding', 'studio')}
                            value={paddingPreset || 'medium'}
                            options={finalPaddingPresets}
                            onChange={(value) => setAttributes({ paddingPreset: value })}
                            help={__('Choose padding preset', 'studio')}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Container Settings', 'studio')} initialOpen={false}>
                        <SelectControl
                            label={__('HTML Tag', 'studio')}
                            value={tagName || 'div'}
                            options={finalTagOptions}
                            onChange={(value) => setAttributes({ tagName: value })}
                            help={__('Choose semantic HTML tag', 'studio')}
                        />
                        
                        {heightPreset === 'custom' && (
                            <TextControl
                                label={__('Custom Height', 'studio')}
                                value={minHeight || ''}
                                onChange={(value) => setAttributes({ minHeight: value })}
                                help={__('Enter custom height (e.g., 300px, 50vh)', 'studio')}
                            />
                        )}
                    </PanelBody>
                    
                    <PanelBody title={__('Block Presets', 'studio')} initialOpen={false}>
                        {saveNotice && (
                            <Notice 
                                status={saveNotice.type} 
                                isDismissible={false}
                                style={{ marginBottom: '12px' }}
                            >
                                {saveNotice.message}
                            </Notice>
                        )}
                        
                        <Button
                            variant="secondary"
                            onClick={() => setIsPresetModalOpen(true)}
                            style={{ width: '100%', marginBottom: '8px' }}
                        >
                            {__('Save Current as Preset', 'studio')}
                        </Button>
                        
                        <p style={{ fontSize: '12px', color: '#666', margin: '4px 0 0 0' }}>
                            {__('Save your current settings as a reusable preset', 'studio')}
                        </p>
                    </PanelBody>
                </InspectorControls>
                
                {isPresetModalOpen && (
                    <Modal
                        title={__('Save Container Preset', 'studio')}
                        onRequestClose={() => {
                            setIsPresetModalOpen(false);
                            setPresetName('');
                        }}
                        size="medium"
                    >
                        <div style={{ padding: '16px 0' }}>
                            <TextControl
                                label={__('Preset Name', 'studio')}
                                value={presetName}
                                onChange={setPresetName}
                                placeholder={__('e.g., Hero Section, Content Block, etc.', 'studio')}
                                help={__('Give your preset a descriptive name', 'studio')}
                            />
                            
                            <div style={{ 
                                background: '#f8f9fa', 
                                padding: '12px', 
                                borderRadius: '4px', 
                                margin: '16px 0',
                                fontSize: '13px'
                            }}>
                                <strong>{__('Current Settings:', 'studio')}</strong><br/>
                                Width: {widthPreset || 'content'}<br/>
                                Height: {heightPreset || 'auto'}<br/>
                                Padding: {paddingPreset || 'medium'}<br/>
                                HTML Tag: {tagName || 'div'}
                                {minHeight && <><br/>Custom Height: {minHeight}</>}
                            </div>
                            
                            <div style={{ display: 'flex', gap: '12px', justifyContent: 'flex-end' }}>
                                <Button
                                    variant="tertiary"
                                    onClick={() => {
                                        setIsPresetModalOpen(false);
                                        setPresetName('');
                                    }}
                                >
                                    {__('Cancel', 'studio')}
                                </Button>
                                <Button
                                    variant="primary"
                                    onClick={saveAsPreset}
                                    disabled={!presetName.trim()}
                                >
                                    {__('Save Preset', 'studio')}
                                </Button>
                            </div>
                        </div>
                    </Modal>
                )}
                
                <div {...innerBlocksProps} />
            </>
        );
    },

    save: ({ attributes }) => {
        const { widthPreset, paddingPreset, heightPreset, tagName, minHeight } = attributes;
        
        const blockProps = useBlockProps.save({
            className: `studio-container width-${widthPreset || 'content'} padding-${paddingPreset || 'medium'} height-${heightPreset || 'auto'}`,
            style: {
                ...(minHeight && minHeight !== 'auto' ? { minHeight } : {}),
                ...(heightPreset && heightPreset !== 'auto' && heightPreset !== 'custom' ? { minHeight: heightPreset } : {})
            }
        });

        const innerBlocksProps = useInnerBlocksProps.save(blockProps);
        const TagName = tagName || 'div';

        return <TagName {...innerBlocksProps} />;
    }
});
