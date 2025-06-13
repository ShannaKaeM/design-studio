/**
 * Layout Manager Component
 */

import { useState, useEffect } from '@wordpress/element';
import { 
    TextControl, 
    RangeControl,
    Flex, 
    FlexItem
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

const LayoutManager = () => {
    const { themeJson, updateThemeJson } = useThemeJson();
    const [contentSize, setContentSize] = useState('1200px');
    const [wideSize, setWideSize] = useState('1400px');

    // Initialize from theme.json
    useEffect(() => {
        if (themeJson?.settings?.layout) {
            setContentSize(themeJson.settings.layout.contentSize || '1200px');
            setWideSize(themeJson.settings.layout.wideSize || '1400px');
        }
    }, [themeJson]);

    const updateLayout = (updates) => {
        const updatedThemeJson = {
            ...themeJson,
            settings: {
                ...themeJson.settings,
                layout: {
                    ...themeJson.settings.layout,
                    ...updates
                }
            }
        };
        updateThemeJson(updatedThemeJson);
    };

    const handleContentSizeChange = (value) => {
        setContentSize(value);
        updateLayout({ contentSize: value });
    };

    const handleWideSizeChange = (value) => {
        setWideSize(value);
        updateLayout({ wideSize: value });
    };

    return (
        <div className="ds-layout-manager">
            <h4>{__('Layout Settings', 'studio')}</h4>
            
            <TextControl
                label={__('Content Size', 'studio')}
                value={contentSize}
                onChange={handleContentSizeChange}
                help={__('Maximum width for regular content blocks', 'studio')}
            />
            
            <TextControl
                label={__('Wide Size', 'studio')}
                value={wideSize}
                onChange={handleWideSizeChange}
                help={__('Maximum width for wide-aligned blocks', 'studio')}
            />

            <div className="ds-layout-preview">
                <h5>{__('Layout Preview', 'studio')}</h5>
                <div style={{ 
                    border: '2px dashed #ccc', 
                    padding: '20px',
                    textAlign: 'center',
                    marginBottom: '10px',
                    maxWidth: contentSize,
                    margin: '0 auto'
                }}>
                    Content Size: {contentSize}
                </div>
                <div style={{ 
                    border: '2px dashed #999', 
                    padding: '20px',
                    textAlign: 'center',
                    maxWidth: wideSize,
                    margin: '0 auto'
                }}>
                    Wide Size: {wideSize}
                </div>
            </div>
        </div>
    );
};

export default LayoutManager;
