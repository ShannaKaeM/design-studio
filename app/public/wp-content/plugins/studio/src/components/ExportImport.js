/**
 * Export/Import Component
 */

import { useState } from '@wordpress/element';
import { 
    Button, 
    TextareaControl,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useThemeJson } from '../hooks/useThemeJson';

const ExportImport = () => {
    const { themeJson, updateThemeJson, saveThemeJson } = useThemeJson();
    const [importData, setImportData] = useState('');
    const [exportData, setExportData] = useState('');
    const [message, setMessage] = useState('');

    const handleExport = () => {
        const exportJson = JSON.stringify(themeJson, null, 2);
        setExportData(exportJson);
        setMessage(__('Design system exported! Copy the JSON below.', 'studio'));
    };

    const handleImport = () => {
        try {
            const importedJson = JSON.parse(importData);
            updateThemeJson(importedJson);
            setMessage(__('Design system imported successfully!', 'studio'));
            setImportData('');
        } catch (error) {
            setMessage(__('Invalid JSON format. Please check your data.', 'studio'));
        }
    };

    const copyToClipboard = () => {
        navigator.clipboard.writeText(exportData);
        setMessage(__('Copied to clipboard!', 'studio'));
    };

    return (
        <div className="ds-export-import">
            {message && (
                <Notice 
                    status="success" 
                    isDismissible={true}
                    onRemove={() => setMessage('')}
                >
                    {message}
                </Notice>
            )}

            <div className="ds-export-section">
                <h4>{__('Export Design System', 'studio')}</h4>
                <Button isPrimary onClick={handleExport}>
                    {__('Export Current Settings', 'studio')}
                </Button>
                
                {exportData && (
                    <div style={{ marginTop: '16px' }}>
                        <Button isSecondary onClick={copyToClipboard}>
                            {__('Copy to Clipboard', 'studio')}
                        </Button>
                        <TextareaControl
                            value={exportData}
                            readOnly
                            rows={10}
                            style={{ marginTop: '8px' }}
                        />
                    </div>
                )}
            </div>

            <div className="ds-import-section">
                <h4>{__('Import Design System', 'studio')}</h4>
                <TextareaControl
                    label={__('Paste theme.json data', 'studio')}
                    value={importData}
                    onChange={setImportData}
                    rows={6}
                    placeholder={__('Paste your theme.json data here...', 'studio')}
                />
                <Button 
                    isPrimary 
                    onClick={handleImport}
                    disabled={!importData.trim()}
                >
                    {__('Import Settings', 'studio')}
                </Button>
            </div>

            <div className="ds-save-section">
                <h4>{__('Save to File', 'studio')}</h4>
                <Button isPrimary onClick={saveThemeJson}>
                    {__('Save to theme.json', 'studio')}
                </Button>
            </div>
        </div>
    );
};

export default ExportImport;
