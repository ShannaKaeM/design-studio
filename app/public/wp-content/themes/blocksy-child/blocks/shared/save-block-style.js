/**
 * Save Block Style Component
 * Adds a "Save as Block Style" button to block inspector controls
 */

import { __ } from '@wordpress/i18n';
import { Button, TextControl, TextareaControl, Modal, Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

export function SaveBlockStyle({ blockName, attributes, blockType }) {
    const [isOpen, setIsOpen] = useState(false);
    const [styleName, setStyleName] = useState('');
    const [styleLabel, setStyleLabel] = useState('');
    const [styleDescription, setStyleDescription] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [notice, setNotice] = useState(null);

    // Get current block's computed styles
    const computedStyles = useSelect((select) => {
        const { getSelectedBlock } = select('core/block-editor');
        const selectedBlock = getSelectedBlock();
        if (!selectedBlock) return null;

        // Get the block's DOM element
        const blockElement = document.querySelector(`[data-block="${selectedBlock.clientId}"]`);
        if (!blockElement) return null;

        // Get computed styles
        return window.getComputedStyle(blockElement);
    }, []);

    const handleSave = async () => {
        if (!styleName || !styleLabel) {
            setNotice({ type: 'error', message: __('Please provide both a name and label for the style.', 'studio') });
            return;
        }

        setIsSaving(true);
        setNotice(null);

        try {
            // Prepare the block style data
            const styleData = {
                name: styleName,
                label: styleLabel,
                blockType: blockType,
                description: styleDescription,
                attributes: attributes,
                classes: `is-style-${styleName}`,
                type: 'attributes', // Can be 'css' or 'attributes'
            };

            // Extract relevant CSS properties based on block type
            if (computedStyles) {
                const cssProperties = extractCSSProperties(blockType, computedStyles);
                if (cssProperties) {
                    styleData.customCSS = cssProperties;
                    styleData.type = 'css';
                }
            }

            // Send to server
            const response = await apiFetch({
                path: '/studio/v1/save-block-style',
                method: 'POST',
                data: {
                    action: 'studio_save_block_style',
                    nonce: window.studioAdmin?.nonce,
                    styleKey: `${blockType}-${styleName}`,
                    styleData: styleData
                }
            });

            if (response.success) {
                setNotice({ type: 'success', message: __('Block style saved successfully!', 'studio') });
                setTimeout(() => {
                    setIsOpen(false);
                    setStyleName('');
                    setStyleLabel('');
                    setStyleDescription('');
                    setNotice(null);
                }, 2000);
            } else {
                throw new Error(response.data?.message || __('Failed to save block style', 'studio'));
            }
        } catch (error) {
            setNotice({ type: 'error', message: error.message });
        } finally {
            setIsSaving(false);
        }
    };

    // Extract CSS properties based on block type
    const extractCSSProperties = (blockType, styles) => {
        const properties = [];
        
        switch (blockType) {
            case 'studio/text':
                // Typography properties
                if (styles.fontSize) properties.push(`font-size: ${styles.fontSize}`);
                if (styles.fontWeight) properties.push(`font-weight: ${styles.fontWeight}`);
                if (styles.lineHeight) properties.push(`line-height: ${styles.lineHeight}`);
                if (styles.color) properties.push(`color: ${styles.color}`);
                if (styles.fontFamily) properties.push(`font-family: ${styles.fontFamily}`);
                if (styles.textTransform) properties.push(`text-transform: ${styles.textTransform}`);
                if (styles.letterSpacing) properties.push(`letter-spacing: ${styles.letterSpacing}`);
                break;

            case 'studio/button':
                // Button properties
                if (styles.backgroundColor) properties.push(`background-color: ${styles.backgroundColor}`);
                if (styles.color) properties.push(`color: ${styles.color}`);
                if (styles.borderRadius) properties.push(`border-radius: ${styles.borderRadius}`);
                if (styles.padding) properties.push(`padding: ${styles.padding}`);
                if (styles.fontSize) properties.push(`font-size: ${styles.fontSize}`);
                if (styles.fontWeight) properties.push(`font-weight: ${styles.fontWeight}`);
                if (styles.border) properties.push(`border: ${styles.border}`);
                break;

            case 'studio/container':
                // Container properties
                if (styles.padding) properties.push(`padding: ${styles.padding}`);
                if (styles.margin) properties.push(`margin: ${styles.margin}`);
                if (styles.backgroundColor) properties.push(`background-color: ${styles.backgroundColor}`);
                if (styles.borderRadius) properties.push(`border-radius: ${styles.borderRadius}`);
                if (styles.boxShadow) properties.push(`box-shadow: ${styles.boxShadow}`);
                break;

            case 'studio/image':
                // Image properties
                if (styles.borderRadius) properties.push(`border-radius: ${styles.borderRadius}`);
                if (styles.boxShadow) properties.push(`box-shadow: ${styles.boxShadow}`);
                if (styles.opacity) properties.push(`opacity: ${styles.opacity}`);
                if (styles.filter) properties.push(`filter: ${styles.filter}`);
                break;

            case 'studio/grid':
                // Grid properties
                if (styles.gap) properties.push(`gap: ${styles.gap}`);
                if (styles.padding) properties.push(`padding: ${styles.padding}`);
                break;
        }

        return properties.length > 0 ? properties.join('; ') + ';' : null;
    };

    return (
        <>
            <Button
                variant="secondary"
                onClick={() => setIsOpen(true)}
                style={{ width: '100%', marginTop: '16px' }}
            >
                {__('Save as Block Style', 'studio')}
            </Button>

            {isOpen && (
                <Modal
                    title={__('Save Block Style', 'studio')}
                    onRequestClose={() => setIsOpen(false)}
                    style={{ maxWidth: '500px' }}
                >
                    {notice && (
                        <Notice
                            status={notice.type}
                            isDismissible={false}
                        >
                            {notice.message}
                        </Notice>
                    )}

                    <TextControl
                        label={__('Style Name (lowercase, no spaces)', 'studio')}
                        value={styleName}
                        onChange={setStyleName}
                        help={__('Used internally, e.g., "hero-title"', 'studio')}
                        pattern="[a-z0-9-]+"
                    />

                    <TextControl
                        label={__('Style Label', 'studio')}
                        value={styleLabel}
                        onChange={setStyleLabel}
                        help={__('Display name, e.g., "Hero Title"', 'studio')}
                    />

                    <TextareaControl
                        label={__('Description (optional)', 'studio')}
                        value={styleDescription}
                        onChange={setStyleDescription}
                        help={__('Describe when to use this style', 'studio')}
                        rows={3}
                    />

                    <div style={{ marginTop: '20px', display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
                        <Button
                            variant="tertiary"
                            onClick={() => setIsOpen(false)}
                            disabled={isSaving}
                        >
                            {__('Cancel', 'studio')}
                        </Button>
                        <Button
                            variant="primary"
                            onClick={handleSave}
                            isBusy={isSaving}
                            disabled={isSaving || !styleName || !styleLabel}
                        >
                            {__('Save Style', 'studio')}
                        </Button>
                    </div>
                </Modal>
            )}
        </>
    );
}
