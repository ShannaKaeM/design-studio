/**
 * Design System Panel Component
 * Main container for all design system controls
 */

import { useState } from '@wordpress/element';
import { 
    Panel,
    PanelBody,
    Button,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

import ColorManager from './ColorManager';
import SpacingManager from './SpacingManager';
import TypographyManager from './TypographyManager';
import LayoutManager from './LayoutManager';
import ExportImport from './ExportImport';

const DesignSystemPanel = () => {
    const { themeJson, saveThemeJson, isLoading, error } = useThemeJson();
    const [activePanel, setActivePanel] = useState('colors');

    const panels = [
        { key: 'colors', title: __('Colors', 'ds-studio'), component: ColorManager },
        { key: 'spacing', title: __('Spacing', 'ds-studio'), component: SpacingManager },
        { key: 'typography', title: __('Typography', 'ds-studio'), component: TypographyManager },
        { key: 'layout', title: __('Layout', 'ds-studio'), component: LayoutManager },
        { key: 'export', title: __('Export/Import', 'ds-studio'), component: ExportImport }
    ];

    const renderActivePanel = () => {
        const panel = panels.find(p => p.key === activePanel);
        if (panel) {
            const Component = panel.component;
            return <Component />;
        }
        return null;
    };

    return (
        <div className="ds-design-system-panel">
            <div className="ds-panel-header">
                <h3>{__('Design System Studio', 'ds-studio')}</h3>
                <p>{__('Visual theme.json management with live preview', 'ds-studio')}</p>
            </div>

            {error && (
                <Notice status="error" isDismissible={false}>
                    {error}
                </Notice>
            )}

            {/* Panel Navigation */}
            <div className="ds-panel-nav">
                {panels.map(panel => (
                    <Button
                        key={panel.key}
                        isPressed={activePanel === panel.key}
                        onClick={() => setActivePanel(panel.key)}
                        variant={activePanel === panel.key ? 'primary' : 'secondary'}
                        size="small"
                    >
                        {panel.title}
                    </Button>
                ))}
            </div>

            {/* Active Panel Content */}
            <div className="ds-panel-content">
                {renderActivePanel()}
            </div>

            {/* Global Save Button */}
            <div className="ds-global-actions">
                <Button
                    isPrimary
                    onClick={saveThemeJson}
                    disabled={isLoading}
                    isBusy={isLoading}
                    style={{ width: '100%' }}
                >
                    {isLoading ? __('Saving...', 'ds-studio') : __('Save All Changes', 'ds-studio')}
                </Button>
            </div>

            {/* Live Preview Status */}
            <div className="ds-preview-status">
                <Notice status="info" isDismissible={false}>
                    {__('Changes are previewed live in the editor. Click "Save All Changes" to write to theme.json file.', 'ds-studio')}
                </Notice>
            </div>
        </div>
    );
};

export default DesignSystemPanel;
