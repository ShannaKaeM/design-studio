(function() {
    'use strict';

    const { Component, Fragment } = wp.element;
    const { PanelBody, Button } = wp.components;
    const { InspectorControls } = wp.blockEditor;
    const { createHigherOrderComponent } = wp.compose;
    const { addFilter } = wp.hooks;

    console.log('Style Builder Test: Starting...');

    // Simple test component
    const SimpleStyleBuilder = () => {
        return (
            <PanelBody title="Style Builder Test" initialOpen={false}>
                <p>Style Builder is working!</p>
                <Button isPrimary>Test Button</Button>
            </PanelBody>
        );
    };

    // Higher Order Component
    const withStyleBuilderTest = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            const { name: blockName, isSelected } = props;
            
            console.log('Style Builder Test: Block selected:', blockName, isSelected);
            
            // Only show for core blocks and when block is selected
            if (!blockName.startsWith('core/') || !isSelected) {
                return <BlockEdit {...props} />;
            }

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    <InspectorControls>
                        <SimpleStyleBuilder />
                    </InspectorControls>
                </Fragment>
            );
        };
    }, 'withStyleBuilderTest');

    // Register the filter
    console.log('Style Builder Test: Registering filter...');
    addFilter(
        'editor.BlockEdit',
        'ds-studio/style-builder-test',
        withStyleBuilderTest
    );
    
    console.log('Style Builder Test: Filter registered successfully!');

})();
