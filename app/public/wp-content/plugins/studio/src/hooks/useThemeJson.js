/**
 * useThemeJson Hook
 * Manages theme.json state and API calls
 */

import { useState, useEffect } from '@wordpress/element';
import { apiFetch } from '@wordpress/api-fetch';

export const useThemeJson = () => {
    const [themeJson, setThemeJson] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);

    /**
     * Load theme.json on mount
     */
    useEffect(() => {
        loadThemeJson();
    }, []);

    /**
     * Load theme.json from server
     */
    const loadThemeJson = async () => {
        setIsLoading(true);
        setError(null);

        try {
            // Use the global dsStudio object passed from PHP
            if (window.studio && window.studio.currentThemeJson) {
                setThemeJson(window.studio.currentThemeJson);
            } else {
                // Fallback to default structure
                setThemeJson(getDefaultThemeJson());
            }
        } catch (err) {
            setError(err.message);
            setThemeJson(getDefaultThemeJson());
        } finally {
            setIsLoading(false);
        }
    };

    /**
     * Update theme.json in state (local only)
     */
    const updateThemeJson = (newThemeJson) => {
        setThemeJson(newThemeJson);
        
        // Apply CSS variables to editor immediately for live preview
        applyLivePreview(newThemeJson);
    };

    /**
     * Save theme.json to server
     */
    const saveThemeJson = async () => {
        if (!themeJson) return;

        setIsLoading(true);
        setError(null);

        try {
            const formData = new FormData();
            formData.append('action', 'studio_save_theme_json');
            formData.append('nonce', window.studio.nonce);
            formData.append('themeJson', JSON.stringify(themeJson));

            const response = await fetch(window.studio.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.data || 'Failed to save theme.json');
            }

            // Optionally show success message
            console.log('Theme.json saved successfully!');
            
        } catch (err) {
            setError(err.message);
            console.error('Error saving theme.json:', err);
        } finally {
            setIsLoading(false);
        }
    };

    /**
     * Apply live preview by injecting CSS variables
     */
    const applyLivePreview = (themeJsonData) => {
        const cssVariables = generateCSSVariables(themeJsonData);
        
        // Remove existing Studio styles
        const existingStyle = document.getElementById('studio-live-preview');
        if (existingStyle) {
            existingStyle.remove();
        }

        // Inject new styles
        const styleElement = document.createElement('style');
        styleElement.id = 'studio-live-preview';
        styleElement.textContent = `:root { ${cssVariables} }`;
        document.head.appendChild(styleElement);
    };

    /**
     * Generate CSS variables from theme.json
     */
    const generateCSSVariables = (themeJsonData) => {
        let cssVars = '';

        // Colors
        if (themeJsonData?.settings?.color?.palette) {
            themeJsonData.settings.color.palette.forEach(color => {
                cssVars += `--wp--preset--color--${color.slug}: ${color.color}; `;
            });
        }

        // Spacing
        if (themeJsonData?.settings?.spacing?.spacingSizes) {
            themeJsonData.settings.spacing.spacingSizes.forEach(spacing => {
                cssVars += `--wp--preset--spacing--${spacing.slug}: ${spacing.size}; `;
            });
        }

        // Typography
        if (themeJsonData?.settings?.typography?.fontSizes) {
            themeJsonData.settings.typography.fontSizes.forEach(fontSize => {
                cssVars += `--wp--preset--font-size--${fontSize.slug}: ${fontSize.size}; `;
            });
        }

        return cssVars;
    };

    /**
     * Get default theme.json structure
     */
    const getDefaultThemeJson = () => {
        return {
            '$schema': 'https://schemas.wp.org/trunk/theme.json',
            'version': 3,
            'settings': {
                'color': {
                    'palette': []
                },
                'spacing': {
                    'spacingSizes': []
                },
                'typography': {
                    'fontSizes': [],
                    'fontFamilies': []
                },
                'layout': {
                    'contentSize': '1200px',
                    'wideSize': '1400px'
                }
            },
            'styles': {
                'elements': {}
            }
        };
    };

    return {
        themeJson,
        isLoading,
        error,
        updateThemeJson,
        saveThemeJson,
        loadThemeJson
    };
};
