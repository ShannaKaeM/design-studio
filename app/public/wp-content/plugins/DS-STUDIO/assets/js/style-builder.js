/**
 * DS-Studio Style Builder
 * Block editor interface for creating custom block styles
 */

(function() {
    console.log('DS-Studio Style Builder script loaded');
    
    const { __ } = wp.i18n;
    const { Component, Fragment } = wp.element;
    const { 
        Button, 
        Modal, 
        TextControl, 
        PanelBody, 
        PanelRow,
        Notice,
        Spinner,
        DropdownMenu,
        MenuGroup,
        MenuItem
    } = wp.components;
    const { withSelect, withDispatch } = wp.data;
    const { compose } = wp.compose;
    const { InspectorControls } = wp.blockEditor;
    const { createHigherOrderComponent } = wp.compose;
    const { addFilter } = wp.hooks;

    // Style Builder Component
    class StyleBuilder extends Component {
        constructor(props) {
            super(props);
            this.state = {
                isModalOpen: false,
                styleName: '',
                styleLabel: '',
                isSaving: false,
                notice: null,
                customStyles: dsStudioStyleBuilder.customStyles || {}
            };
        }

        openModal = () => {
            this.setState({ isModalOpen: true });
        }

        closeModal = () => {
            this.setState({ 
                isModalOpen: false,
                styleName: '',
                styleLabel: '',
                notice: null
            });
        }

        saveStyle = () => {
            const { styleName, styleLabel } = this.state;
            const { blockName, attributes } = this.props;

            if (!styleName || !styleLabel) {
                this.setState({
                    notice: {
                        type: 'error',
                        message: __('Please enter both style name and label', 'ds-studio')
                    }
                });
                return;
            }

            this.setState({ isSaving: true });

            // Clean style name (remove spaces, special chars)
            const cleanStyleName = styleName.toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');

            const data = {
                action: 'ds_studio_save_custom_style',
                nonce: dsStudioStyleBuilder.nonce,
                style_name: cleanStyleName,
                style_label: styleLabel,
                block_type: blockName,
                style_attributes: JSON.stringify(attributes)
            };

            jQuery.post(dsStudioStyleBuilder.ajaxUrl, data)
                .done((response) => {
                    if (response.success) {
                        this.setState({
                            notice: {
                                type: 'success',
                                message: __('Style saved successfully!', 'ds-studio')
                            },
                            customStyles: {
                                ...this.state.customStyles,
                                [blockName]: {
                                    ...this.state.customStyles[blockName],
                                    [cleanStyleName]: {
                                        label: styleLabel,
                                        attributes: attributes
                                    }
                                }
                            }
                        });

                        // Refresh the block styles
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.setState({
                            notice: {
                                type: 'error',
                                message: response.data || __('Failed to save style', 'ds-studio')
                            }
                        });
                    }
                })
                .fail(() => {
                    this.setState({
                        notice: {
                            type: 'error',
                            message: __('Network error occurred', 'ds-studio')
                        }
                    });
                })
                .always(() => {
                    this.setState({ isSaving: false });
                });
        }

        deleteStyle = (styleName) => {
            const { blockName } = this.props;

            if (!confirm(__('Are you sure you want to delete this style?', 'ds-studio'))) {
                return;
            }

            const data = {
                action: 'ds_studio_delete_custom_style',
                nonce: dsStudioStyleBuilder.nonce,
                style_name: styleName,
                block_type: blockName
            };

            jQuery.post(dsStudioStyleBuilder.ajaxUrl, data)
                .done((response) => {
                    if (response.success) {
                        // Remove from local state
                        const newCustomStyles = { ...this.state.customStyles };
                        if (newCustomStyles[blockName]) {
                            delete newCustomStyles[blockName][styleName];
                        }
                        this.setState({ customStyles: newCustomStyles });

                        // Refresh the block styles
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                });
        }

        render() {
            const { blockName } = this.props;
            const { isModalOpen, styleName, styleLabel, isSaving, notice, customStyles } = this.state;
            
            const blockStyles = customStyles[blockName] || {};
            const hasCustomStyles = Object.keys(blockStyles).length > 0;

            return (
                <Fragment>
                    <PanelBody title={__('Style Builder', 'ds-studio')} initialOpen={false}>
                        <PanelRow>
                            <p>{__('Save the current block styling as a reusable style.', 'ds-studio')}</p>
                        </PanelRow>
                        
                        <PanelRow>
                            <Button 
                                isPrimary 
                                onClick={this.openModal}
                                icon="saved"
                            >
                                {__('Save Current Style', 'ds-studio')}
                            </Button>
                        </PanelRow>

                        {hasCustomStyles && (
                            <Fragment>
                                <PanelRow>
                                    <strong>{__('Custom Styles:', 'ds-studio')}</strong>
                                </PanelRow>
                                {Object.entries(blockStyles).map(([name, style]) => (
                                    <PanelRow key={name}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', width: '100%', alignItems: 'center' }}>
                                            <span>{style.label}</span>
                                            <Button 
                                                isDestructive 
                                                isSmall
                                                onClick={() => this.deleteStyle(name)}
                                                icon="trash"
                                            >
                                                {__('Delete', 'ds-studio')}
                                            </Button>
                                        </div>
                                    </PanelRow>
                                ))}
                            </Fragment>
                        )}
                    </PanelBody>

                    {isModalOpen && (
                        <Modal
                            title={__('Save Block Style', 'ds-studio')}
                            onRequestClose={this.closeModal}
                            className="ds-studio-style-builder-modal"
                        >
                            {notice && (
                                <Notice 
                                    status={notice.type} 
                                    isDismissible={false}
                                    style={{ marginBottom: '16px' }}
                                >
                                    {notice.message}
                                </Notice>
                            )}

                            <div style={{ padding: '16px 0' }}>
                                <TextControl
                                    label={__('Style Name', 'ds-studio')}
                                    help={__('Internal name for the style (will be cleaned automatically)', 'ds-studio')}
                                    value={styleName}
                                    onChange={(value) => this.setState({ styleName: value })}
                                    placeholder="card-title"
                                />

                                <TextControl
                                    label={__('Style Label', 'ds-studio')}
                                    help={__('Display name that users will see', 'ds-studio')}
                                    value={styleLabel}
                                    onChange={(value) => this.setState({ styleLabel: value })}
                                    placeholder="Card Title"
                                />

                                <div style={{ 
                                    background: '#f0f0f0', 
                                    padding: '12px', 
                                    borderRadius: '4px',
                                    marginTop: '16px'
                                }}>
                                    <strong>{__('Current Block Settings:', 'ds-studio')}</strong>
                                    <pre style={{ 
                                        fontSize: '11px', 
                                        margin: '8px 0 0 0',
                                        maxHeight: '150px',
                                        overflow: 'auto'
                                    }}>
                                        {JSON.stringify(this.props.attributes, null, 2)}
                                    </pre>
                                </div>
                            </div>

                            <div style={{ 
                                display: 'flex', 
                                justifyContent: 'flex-end', 
                                gap: '8px',
                                paddingTop: '16px',
                                borderTop: '1px solid #ddd'
                            }}>
                                <Button onClick={this.closeModal}>
                                    {__('Cancel', 'ds-studio')}
                                </Button>
                                <Button 
                                    isPrimary 
                                    onClick={this.saveStyle}
                                    disabled={isSaving}
                                >
                                    {isSaving ? (
                                        <Fragment>
                                            <Spinner />
                                            {__('Saving...', 'ds-studio')}
                                        </Fragment>
                                    ) : (
                                        __('Save Style', 'ds-studio')
                                    )}
                                </Button>
                            </div>
                        </Modal>
                    )}
                </Fragment>
            );
        }
    }

    // Higher Order Component to add Style Builder to blocks
    const withStyleBuilder = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { name: blockName, attributes, isSelected } = props;
            
            // Only show for core blocks and when block is selected
            if (!blockName.startsWith('core/') || !isSelected) {
                return <BlockEdit {...props} />;
            }

            // Skip for certain blocks that don't need styling
            const skipBlocks = [
                'core/html',
                'core/code',
                'core/preformatted',
                'core/verse',
                'core/shortcode'
            ];

            if (skipBlocks.includes(blockName)) {
                return <BlockEdit {...props} />;
            }

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <StyleBuilder 
                            blockName={blockName}
                            attributes={attributes}
                        />
                    </InspectorControls>
                </Fragment>
            );
        };
    }, 'withStyleBuilder');

    // Add the Style Builder to all blocks
    addFilter(
        'editor.BlockEdit',
        'ds-studio/style-builder',
        withStyleBuilder
    );

})();
