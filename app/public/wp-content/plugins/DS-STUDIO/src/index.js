/**
 * Design System Studio - Main Entry Point
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/edit-post';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import DesignSystemPanel from './components/DesignSystemPanel';
import ColorManager from './components/ColorManager';
import SpacingManager from './components/SpacingManager';
import TypographyManager from './components/TypographyManager';
import LayoutManager from './components/LayoutManager';
import ExportImport from './components/ExportImport';

/**
 * Main DS Studio Plugin Component
 */
const DSStudioPlugin = () => {
    return (
        <PluginSidebar
            name="ds-studio"
            title={__('Design System Studio', 'ds-studio')}
            icon="admin-customizer"
        >
            <div className="ds-studio-panel">
                <PanelBody
                    title={__('Colors', 'ds-studio')}
                    initialOpen={true}
                >
                    <ColorManager />
                </PanelBody>

                <PanelBody
                    title={__('Spacing', 'ds-studio')}
                    initialOpen={false}
                >
                    <SpacingManager />
                </PanelBody>

                <PanelBody
                    title={__('Typography', 'ds-studio')}
                    initialOpen={false}
                >
                    <TypographyManager />
                </PanelBody>

                <PanelBody
                    title={__('Layout', 'ds-studio')}
                    initialOpen={false}
                >
                    <LayoutManager />
                </PanelBody>

                <PanelBody
                    title={__('Export / Import', 'ds-studio')}
                    initialOpen={false}
                >
                    <ExportImport />
                </PanelBody>
            </div>
        </PluginSidebar>
    );
};

// Register the plugin
registerPlugin('ds-studio', {
    render: DSStudioPlugin,
    icon: 'admin-customizer',
});
