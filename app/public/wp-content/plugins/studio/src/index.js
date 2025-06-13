/**
 * Design System Studio - Main Entry Point
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import ColorManager from './components/ColorManager';
import SpacingManager from './components/SpacingManager';
import TypographyManager from './components/TypographyManager';
import LayoutManager from './components/LayoutManager';
import ExportImport from './components/ExportImport';

/**
 * Main Studio Plugin Component
 */
const StudioPlugin = () => {
    return (
        <>
            <PluginSidebarMoreMenuItem
                target="studio"
                icon="admin-customizer"
            >
                {__('Studio', 'studio')}
            </PluginSidebarMoreMenuItem>
            <PluginSidebar
                name="studio"
                title={__('Design System Studio', 'studio')}
                icon="admin-customizer"
            >
                <div className="studio-panel">
                    <PanelBody
                        title={__('Colors', 'studio')}
                        initialOpen={false}
                    >
                        <ColorManager />
                    </PanelBody>
                    
                    <PanelBody
                        title={__('Spacing', 'studio')}
                        initialOpen={false}
                    >
                        <SpacingManager />
                    </PanelBody>
                    
                    <PanelBody
                        title={__('Typography', 'studio')}
                        initialOpen={false}
                    >
                        <TypographyManager />
                    </PanelBody>
                    
                    <PanelBody
                        title={__('Layout', 'studio')}
                        initialOpen={false}
                    >
                        <LayoutManager />
                    </PanelBody>
                    
                    <PanelBody
                        title={__('Export / Import', 'studio')}
                        initialOpen={false}
                    >
                        <ExportImport />
                    </PanelBody>
                </div>
            </PluginSidebar>
        </>
    );
};

// Register the plugin
registerPlugin('studio', {
    render: StudioPlugin,
    icon: 'admin-customizer',
});
