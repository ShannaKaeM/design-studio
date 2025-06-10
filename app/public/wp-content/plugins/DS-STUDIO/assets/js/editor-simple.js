(function() {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editor;
    const { PanelBody, Button, TextControl, Notice, ColorPicker, TabPanel } = wp.components;
    const { useState, useEffect, createElement: el } = wp.element;

    // Simple DS-Studio Panel Component
    const DSStudioPanel = () => {
        const [colors, setColors] = useState([]);
        const [newColorName, setNewColorName] = useState('');
        const [newColorValue, setNewColorValue] = useState('#3a5a59');
        const [isLoading, setIsLoading] = useState(false);
        const [message, setMessage] = useState('');

        // Color editing state
        const [editingColor, setEditingColor] = useState(null);
        const [editColorValue, setEditColorValue] = useState('');
        const [showColorPicker, setShowColorPicker] = useState(false);

        // Typography state
        const [typography, setTypography] = useState({
            fontFamilies: [],
            fontSizes: [],
            fontWeights: [],
            lineHeights: [],
            letterSpacing: [],
            textTransforms: []
        });
        
        // Individual new item states for each typography type
        const [newFontFamily, setNewFontFamily] = useState({ name: '', fontFamily: '' });
        const [newFontSize, setNewFontSize] = useState({ name: '', size: '' });
        const [newFontWeight, setNewFontWeight] = useState({ name: '', value: '' });
        const [newLineHeight, setNewLineHeight] = useState({ name: '', value: '' });
        const [newLetterSpacing, setNewLetterSpacing] = useState({ name: '', value: '' });
        const [newTextTransform, setNewTextTransform] = useState({ name: '', value: '' });

        // Typography editing state
        const [editingTypography, setEditingTypography] = useState(null);
        const [editTypographyValue, setEditTypographyValue] = useState('');
        const [editTypographyName, setEditTypographyName] = useState('');
        const [showTypographyPicker, setShowTypographyPicker] = useState(false);

        // Borders state
        const [borders, setBorders] = useState({
            borderWidths: [],
            borderStyles: [],
            borderRadii: []
        });
        
        // Individual new item states for each border type
        const [newBorderWidth, setNewBorderWidth] = useState({ name: '', value: '' });
        const [newBorderStyle, setNewBorderStyle] = useState({ name: '', value: '' });
        const [newBorderRadius, setNewBorderRadius] = useState({ name: '', value: '' });

        // Borders editing state
        const [editingBorder, setEditingBorder] = useState(null);
        const [editBorderValue, setEditBorderValue] = useState('');

        // Spacing state
        const [spacing, setSpacing] = useState({
            spacingSizes: []
        });
        
        // Individual new item states for spacing
        const [newSpacingSize, setNewSpacingSize] = useState({ name: '', value: '' });

        // Spacing editing state
        const [editingSpacing, setEditingSpacing] = useState(null);
        const [editSpacingValue, setEditSpacingValue] = useState('');

        // Layout state
        const [layout, setLayout] = useState({
            containers: [],
            aspectRatios: [],
            zIndex: [],
            breakpoints: [],
            grid: []
        });
        
        // Individual new item states for layout
        const [newContainer, setNewContainer] = useState({ name: '', value: '' });
        const [newAspectRatio, setNewAspectRatio] = useState({ name: '', value: '' });
        const [newZIndex, setNewZIndex] = useState({ name: '', value: '' });
        const [newBreakpoint, setNewBreakpoint] = useState({ name: '', value: '' });
        const [newGrid, setNewGrid] = useState({ name: '', value: '' });

        // Layout editing state
        const [editingLayout, setEditingLayout] = useState(null);
        const [editLayoutValue, setEditLayoutValue] = useState('');
        const [editLayoutType, setEditLayoutType] = useState('');

        // Load existing colors and typography from theme.json on component mount
        useEffect(() => {
            loadExistingColors();
            loadExistingTypography();
            loadExistingBorders();
            loadExistingSpacing();
            loadExistingLayout();
        }, []);

        const loadExistingColors = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                if (themeJson.settings && themeJson.settings.color && themeJson.settings.color.palette) {
                    setColors(themeJson.settings.color.palette);
                }
            }
        };

        const loadExistingTypography = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                
                const typographyData = {
                    fontFamilies: themeJson.settings?.typography?.fontFamilies || [],
                    fontSizes: themeJson.settings?.typography?.fontSizes || [],
                    fontWeights: themeJson.settings?.typography?.fontWeights || [],
                    lineHeights: themeJson.settings?.typography?.lineHeights || [],
                    letterSpacing: themeJson.settings?.typography?.letterSpacings || [],
                    textTransforms: themeJson.settings?.typography?.textTransforms || []
                };
                
                setTypography(typographyData);
                console.log('Typography data loaded:', typographyData);
            }
        };

        const loadExistingBorders = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                
                // Convert custom border objects to array format
                const convertBorderWidths = (borderWidthsObj) => {
                    if (!borderWidthsObj || typeof borderWidthsObj !== 'object') return [];
                    return Object.entries(borderWidthsObj).map(([key, value]) => ({
                        name: key.charAt(0).toUpperCase() + key.slice(1),
                        slug: key,
                        value: value
                    }));
                };
                
                const convertBorderStyles = (borderStylesObj) => {
                    if (!borderStylesObj || typeof borderStylesObj !== 'object') return [];
                    return Object.entries(borderStylesObj).map(([key, value]) => ({
                        name: key.charAt(0).toUpperCase() + key.slice(1),
                        slug: key,
                        value: value
                    }));
                };
                
                const convertBorderRadii = (borderRadiiObj) => {
                    if (!borderRadiiObj || typeof borderRadiiObj !== 'object') return [];
                    return Object.entries(borderRadiiObj).map(([key, value]) => ({
                        name: key === 'xs' ? 'XS' : key === 'sm' ? 'SM' : key === 'md' ? 'MD' : 
                              key === 'lg' ? 'LG' : key === 'xl' ? 'XL' : key === '2xl' ? '2XL' :
                              key.charAt(0).toUpperCase() + key.slice(1),
                        slug: key,
                        value: value
                    }));
                };
                
                // Try to load from standard WordPress border arrays first, then fallback to custom
                let borderWidths = [];
                let borderStyles = [];
                let borderRadii = [];
                
                // Load from settings.border arrays (standard WordPress format)
                if (themeJson.settings?.border?.widths && Array.isArray(themeJson.settings.border.widths)) {
                    borderWidths = themeJson.settings.border.widths.map(item => ({
                        name: item.name,
                        slug: item.slug,
                        value: item.size
                    }));
                } else if (themeJson.custom?.borders?.widths) {
                    // Fallback to custom format
                    borderWidths = convertBorderWidths(themeJson.custom.borders.widths);
                }
                
                if (themeJson.settings?.border?.styles && Array.isArray(themeJson.settings.border.styles)) {
                    borderStyles = themeJson.settings.border.styles.map(item => ({
                        name: item.name,
                        slug: item.slug,
                        value: item.slug // For styles, the value is the same as slug
                    }));
                } else if (themeJson.custom?.borders?.styles) {
                    borderStyles = convertBorderStyles(themeJson.custom.borders.styles);
                }
                
                if (themeJson.settings?.border?.radii && Array.isArray(themeJson.settings.border.radii)) {
                    borderRadii = themeJson.settings.border.radii.map(item => ({
                        name: item.name,
                        slug: item.slug,
                        value: item.size
                    }));
                } else if (themeJson.custom?.borders?.radii) {
                    borderRadii = convertBorderRadii(themeJson.custom.borders.radii);
                }
                
                const bordersData = {
                    borderWidths: borderWidths,
                    borderStyles: borderStyles,
                    borderRadii: borderRadii
                };
                
                setBorders(bordersData);
                console.log('Borders data loaded:', bordersData);
            }
        };

        const loadExistingSpacing = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                
                // Convert custom spacing objects to array format
                const convertSpacingSizes = (spacingSizesObj) => {
                    if (!spacingSizesObj || typeof spacingSizesObj !== 'object') return [];
                    return Object.entries(spacingSizesObj).map(([key, value]) => ({
                        name: key.charAt(0).toUpperCase() + key.slice(1),
                        slug: key,
                        value: value
                    }));
                };
                
                // Try to load from standard WordPress spacing arrays first, then fallback to custom
                let spacingSizes = [];
                
                // Load from settings.spacing arrays (standard WordPress format)
                if (themeJson.settings?.spacing?.spacingSizes && Array.isArray(themeJson.settings.spacing.spacingSizes)) {
                    spacingSizes = themeJson.settings.spacing.spacingSizes.map(item => ({
                        name: item.name,
                        slug: item.slug,
                        value: item.size
                    }));
                } else if (themeJson.custom?.spacing?.sizes) {
                    // Fallback to custom format
                    spacingSizes = convertSpacingSizes(themeJson.custom.spacing.sizes);
                } else if (themeJson.custom?.spacing?.scale) {
                    spacingSizes = convertSpacingSizes(themeJson.custom.spacing.scale);
                } else if (themeJson.custom?.spacing?.padding) {
                    spacingSizes = convertSpacingSizes(themeJson.custom.spacing.padding);
                }
                
                const spacingData = {
                    spacingSizes: spacingSizes
                };
                
                setSpacing(spacingData);
                console.log('Spacing data loaded:', spacingData);
            }
        };

        const loadExistingLayout = () => {
            if (window.dsStudio && window.dsStudio.currentThemeJson) {
                const themeJson = window.dsStudio.currentThemeJson;
                
                // Convert custom layout objects to array format
                const convertToArray = (obj, type) => {
                    if (!obj || typeof obj !== 'object') return [];
                    return Object.entries(obj).map(([key, value]) => ({
                        name: key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1'),
                        slug: key,
                        value: value
                    }));
                };
                
                // Initialize arrays
                let containers = [];
                let aspectRatios = [];
                let zIndex = [];
                let breakpoints = [];
                let grid = [];
                
                // Load containers from custom.layout.containers and add WordPress standard layout
                if (themeJson.custom?.layout?.containers) {
                    containers = convertToArray(themeJson.custom.layout.containers);
                }
                
                // Add WordPress standard layout values if they exist
                if (themeJson.settings?.layout?.contentSize) {
                    const contentExists = containers.find(c => c.slug === 'content');
                    if (!contentExists) {
                        containers.unshift({
                            name: 'Content Size',
                            slug: 'content',
                            value: themeJson.settings.layout.contentSize
                        });
                    }
                }
                
                if (themeJson.settings?.layout?.wideSize) {
                    const wideExists = containers.find(c => c.slug === 'wide');
                    if (!wideExists) {
                        containers.push({
                            name: 'Wide Size',
                            slug: 'wide',
                            value: themeJson.settings.layout.wideSize
                        });
                    }
                }
                
                // Load aspect ratios
                if (themeJson.custom?.layout?.aspectRatios) {
                    aspectRatios = convertToArray(themeJson.custom.layout.aspectRatios).map(item => ({
                        ...item,
                        name: `${item.name} Aspect`,
                        type: 'aspectRatio'
                    }));
                }
                
                // Load z-index
                if (themeJson.custom?.layout?.zIndex) {
                    zIndex = convertToArray(themeJson.custom.layout.zIndex).map(item => ({
                        ...item,
                        name: `${item.name} Z-Index`,
                        type: 'zIndex'
                    }));
                }
                
                // Load breakpoints
                if (themeJson.custom?.layout?.breakpoints) {
                    breakpoints = convertToArray(themeJson.custom.layout.breakpoints).map(item => ({
                        ...item,
                        name: `${item.name} Breakpoint`,
                        type: 'breakpoint'
                    }));
                }
                
                // Load grid templates
                if (themeJson.custom?.layout?.grid) {
                    grid = convertToArray(themeJson.custom.layout.grid).map(item => ({
                        ...item,
                        name: `${item.name} Grid`
                    }));
                }
                
                const layoutData = {
                    containers: containers,
                    aspectRatios: aspectRatios,
                    zIndex: zIndex,
                    breakpoints: breakpoints,
                    grid: grid
                };
                
                setLayout(layoutData);
                console.log('Layout data loaded:', layoutData);
            }
        };

        const saveColor = () => {
            if (!newColorName.trim() || !newColorValue.trim()) {
                setMessage('Please enter both color name and value');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('color_name', newColorName.trim());
            formData.append('color_value', newColorValue.trim());

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setMessage('Color saved successfully!');
                    setNewColorName('');
                    setNewColorValue('#3a5a59');
                    loadExistingColors();
                    
                    // Force editor to refresh color palette
                    if (wp.data && wp.data.dispatch) {
                        const { dispatch } = wp.data;
                        if (dispatch('core/editor')) {
                            dispatch('core/editor').editPost({});
                        }
                    }
                } else {
                    setMessage('Error: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const saveTypographyItem = () => {
            if (!newTypographyItem.name.trim() || !newTypographyItem.value.trim()) {
                setMessage('Please enter both name and value');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            setIsLoading(true);
            setMessage('');

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('typography_type', newTypographyItem.type);
            formData.append('typography_name', newTypographyItem.name.trim());
            formData.append('typography_value', newTypographyItem.value.trim());

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setMessage('Typography item saved successfully!');
                    setNewTypographyItem({
                        type: 'fontSizes',
                        name: '',
                        value: ''
                    });
                    loadExistingTypography();
                    
                    // Force editor to refresh
                    if (wp.data && wp.data.dispatch) {
                        const { dispatch } = wp.data;
                        if (dispatch('core/editor')) {
                            dispatch('core/editor').editPost({});
                        }
                    }
                } else {
                    setMessage('Error: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        // Individual save functions for each typography type
        const saveFontFamily = async () => {
            if (!newFontFamily.name || !newFontFamily.fontFamily) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newFontFamily.name,
                    slug: newFontFamily.name.toLowerCase().replace(/\s+/g, '-'),
                    fontFamily: newFontFamily.fontFamily
                };
                
                updatedTypography.fontFamilies = [...updatedTypography.fontFamilies, newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewFontFamily({ name: '', fontFamily: '' });
                setMessage('Font family added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveFontSize = async () => {
            if (!newFontSize.name || !newFontSize.size) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newFontSize.name,
                    slug: newFontSize.name.toLowerCase().replace(/\s+/g, '-'),
                    size: newFontSize.size
                };
                
                updatedTypography.fontSizes = [...updatedTypography.fontSizes, newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewFontSize({ name: '', size: '' });
                setMessage('Font size added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveFontWeight = async () => {
            if (!newFontWeight.name || !newFontWeight.value) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newFontWeight.name,
                    slug: newFontWeight.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newFontWeight.value
                };
                
                updatedTypography.fontWeights = [...(updatedTypography.fontWeights || []), newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewFontWeight({ name: '', value: '' });
                setMessage('Font weight added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveLineHeight = async () => {
            if (!newLineHeight.name || !newLineHeight.value) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newLineHeight.name,
                    slug: newLineHeight.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newLineHeight.value
                };
                
                updatedTypography.lineHeights = [...(updatedTypography.lineHeights || []), newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewLineHeight({ name: '', value: '' });
                setMessage('Line height added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveLetterSpacing = async () => {
            if (!newLetterSpacing.name || !newLetterSpacing.value) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newLetterSpacing.name,
                    slug: newLetterSpacing.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newLetterSpacing.value
                };
                
                updatedTypography.letterSpacing = [...(updatedTypography.letterSpacing || []), newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewLetterSpacing({ name: '', value: '' });
                setMessage('Letter spacing added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveTextTransform = async () => {
            if (!newTextTransform.name || !newTextTransform.value) return;
            
            setIsLoading(true);
            try {
                const updatedTypography = { ...typography };
                const newItem = {
                    name: newTextTransform.name,
                    slug: newTextTransform.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newTextTransform.value
                };
                
                updatedTypography.textTransforms = [...(updatedTypography.textTransforms || []), newItem];
                await saveTypographyToThemeJson(updatedTypography);
                
                setTypography(updatedTypography);
                setNewTextTransform({ name: '', value: '' });
                setMessage('Text transform added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        // Border save functions
        const saveBorderWidth = async () => {
            if (!newBorderWidth.name || !newBorderWidth.value) return;
            
            setIsLoading(true);
            try {
                const updatedBorders = { ...borders };
                const newItem = {
                    name: newBorderWidth.name,
                    slug: newBorderWidth.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newBorderWidth.value
                };
                
                updatedBorders.borderWidths = [...(updatedBorders.borderWidths || []), newItem];
                await saveBordersToThemeJson(updatedBorders);
                
                setBorders(updatedBorders);
                setNewBorderWidth({ name: '', value: '' });
                setMessage('Border width added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveBorderStyle = async () => {
            if (!newBorderStyle.name || !newBorderStyle.value) return;
            
            setIsLoading(true);
            try {
                const updatedBorders = { ...borders };
                const newItem = {
                    name: newBorderStyle.name,
                    slug: newBorderStyle.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newBorderStyle.value
                };
                
                updatedBorders.borderStyles = [...(updatedBorders.borderStyles || []), newItem];
                await saveBordersToThemeJson(updatedBorders);
                
                setBorders(updatedBorders);
                setNewBorderStyle({ name: '', value: '' });
                setMessage('Border style added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveBorderRadius = async () => {
            if (!newBorderRadius.name || !newBorderRadius.value) return;
            
            setIsLoading(true);
            try {
                const updatedBorders = { ...borders };
                const newItem = {
                    name: newBorderRadius.name,
                    slug: newBorderRadius.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newBorderRadius.value
                };
                
                updatedBorders.borderRadii = [...(updatedBorders.borderRadii || []), newItem];
                await saveBordersToThemeJson(updatedBorders);
                
                setBorders(updatedBorders);
                setNewBorderRadius({ name: '', value: '' });
                setMessage('Border radius added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        // Helper function to save borders to theme.json
        const saveBordersToThemeJson = async (updatedBorders) => {
            // Convert arrays back to object format for custom.borders structure
            const borderWidthsObj = {};
            const borderStylesObj = {};
            const borderRadiiObj = {};
            
            updatedBorders.borderWidths?.forEach(item => {
                borderWidthsObj[item.slug] = item.value;
            });
            
            updatedBorders.borderStyles?.forEach(item => {
                borderStylesObj[item.slug] = item.value;
            });
            
            updatedBorders.borderRadii?.forEach(item => {
                borderRadiiObj[item.slug] = item.value;
            });

            const response = await fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_studio_save_theme_json',
                    nonce: window.dsStudio.nonce,
                    borders: JSON.stringify({
                        widths: borderWidthsObj,
                        styles: borderStylesObj,
                        radii: borderRadiiObj
                    })
                })
            });

            if (!response.ok) {
                throw new Error('Failed to save borders');
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.data || 'Unknown error');
            }

            // Refresh the theme.json data
            if (window.dsStudio.currentThemeJson) {
                if (!window.dsStudio.currentThemeJson.custom) {
                    window.dsStudio.currentThemeJson.custom = {};
                }
                window.dsStudio.currentThemeJson.custom.borders = {
                    widths: borderWidthsObj,
                    styles: borderStylesObj,
                    radii: borderRadiiObj
                };
            }
        };

        // Helper function to save typography to theme.json
        const saveTypographyToThemeJson = async (updatedTypography) => {
            const response = await fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_studio_save_theme_json',
                    nonce: window.dsStudio.nonce,
                    theme_json: JSON.stringify({
                        ...window.dsStudio.currentThemeJson,
                        settings: {
                            ...window.dsStudio.currentThemeJson.settings,
                            typography: {
                                ...window.dsStudio.currentThemeJson.settings.typography,
                                fontFamilies: updatedTypography.fontFamilies,
                                fontSizes: updatedTypography.fontSizes,
                                fontWeights: updatedTypography.fontWeights,
                                lineHeights: updatedTypography.lineHeights,
                                letterSpacing: updatedTypography.letterSpacing,
                                textTransforms: updatedTypography.textTransforms
                            }
                        }
                    })
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save typography');
            }
            
            // Update the global theme.json data
            window.dsStudio.currentThemeJson.settings.typography = {
                ...window.dsStudio.currentThemeJson.settings.typography,
                fontFamilies: updatedTypography.fontFamilies,
                fontSizes: updatedTypography.fontSizes,
                fontWeights: updatedTypography.fontWeights,
                lineHeights: updatedTypography.lineHeights,
                letterSpacing: updatedTypography.letterSpacing,
                textTransforms: updatedTypography.textTransforms
            };
        };

        // Spacing save functions
        const saveSpacingSize = async () => {
            if (!newSpacingSize.name || !newSpacingSize.value) return;
            
            setIsLoading(true);
            try {
                const updatedSpacing = { ...spacing };
                const newItem = {
                    name: newSpacingSize.name,
                    slug: newSpacingSize.name.toLowerCase().replace(/\s+/g, '-'),
                    value: newSpacingSize.value
                };
                
                updatedSpacing.spacingSizes = [...(updatedSpacing.spacingSizes || []), newItem];
                await saveSpacingToThemeJson(updatedSpacing);
                
                setSpacing(updatedSpacing);
                setNewSpacingSize({ name: '', value: '' });
                setMessage('Spacing size added successfully!');
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        // Helper function to save spacing to theme.json
        const saveSpacingToThemeJson = async (updatedSpacing) => {
            const response = await fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_studio_save_theme_json',
                    nonce: window.dsStudio.nonce,
                    theme_json: JSON.stringify({
                        ...window.dsStudio.currentThemeJson,
                        settings: {
                            ...window.dsStudio.currentThemeJson.settings,
                            spacing: {
                                ...window.dsStudio.currentThemeJson.settings.spacing,
                                spacingSizes: updatedSpacing.spacingSizes
                            }
                        }
                    })
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save spacing');
            }
            
            // Update the global theme.json data
            window.dsStudio.currentThemeJson.settings.spacing = {
                ...window.dsStudio.currentThemeJson.settings.spacing,
                spacingSizes: updatedSpacing.spacingSizes
            };
        };

        // Layout save functions
        const saveContainer = async () => {
            if (!newContainer.name.trim() || !newContainer.value.trim()) {
                setMessage('Please enter both container name and value');
                return;
            }
            
            setIsLoading(true);
            try {
                const updatedLayout = {
                    ...layout,
                    containers: [...layout.containers, {
                        name: newContainer.name,
                        slug: newContainer.name.toLowerCase().replace(/\s+/g, '-'),
                        value: newContainer.value
                    }]
                };
                
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setNewContainer({ name: '', value: '' });
                setMessage('Container added successfully!');
            } catch (error) {
                setMessage('Error adding container: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveAspectRatio = async () => {
            if (!newAspectRatio.name.trim() || !newAspectRatio.value.trim()) {
                setMessage('Please enter both aspect ratio name and value');
                return;
            }
            
            setIsLoading(true);
            try {
                const updatedLayout = {
                    ...layout,
                    aspectRatios: [...layout.aspectRatios, {
                        name: newAspectRatio.name,
                        slug: newAspectRatio.name.toLowerCase().replace(/\s+/g, '-'),
                        value: newAspectRatio.value
                    }]
                };
                
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setNewAspectRatio({ name: '', value: '' });
                setMessage('Aspect ratio added successfully!');
            } catch (error) {
                setMessage('Error adding aspect ratio: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveZIndex = async () => {
            if (!newZIndex.name.trim() || !newZIndex.value.trim()) {
                setMessage('Please enter both z-index name and value');
                return;
            }
            
            setIsLoading(true);
            try {
                const updatedLayout = {
                    ...layout,
                    zIndex: [...layout.zIndex, {
                        name: newZIndex.name,
                        slug: newZIndex.name.toLowerCase().replace(/\s+/g, '-'),
                        value: newZIndex.value
                    }]
                };
                
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setNewZIndex({ name: '', value: '' });
                setMessage('Z-index added successfully!');
            } catch (error) {
                setMessage('Error adding z-index: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveBreakpoint = async () => {
            if (!newBreakpoint.name.trim() || !newBreakpoint.value.trim()) {
                setMessage('Please enter both breakpoint name and value');
                return;
            }
            
            setIsLoading(true);
            try {
                const updatedLayout = {
                    ...layout,
                    breakpoints: [...layout.breakpoints, {
                        name: newBreakpoint.name,
                        slug: newBreakpoint.name.toLowerCase().replace(/\s+/g, '-'),
                        value: newBreakpoint.value
                    }]
                };
                
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setNewBreakpoint({ name: '', value: '' });
                setMessage('Breakpoint added successfully!');
            } catch (error) {
                setMessage('Error adding breakpoint: ' + error.message);
            }
            setIsLoading(false);
        };

        const saveGrid = async () => {
            if (!newGrid.name.trim() || !newGrid.value.trim()) {
                setMessage('Please enter both grid name and value');
                return;
            }
            
            setIsLoading(true);
            try {
                const updatedLayout = {
                    ...layout,
                    grid: [...layout.grid, {
                        name: newGrid.name,
                        slug: newGrid.name.toLowerCase().replace(/\s+/g, '-'),
                        value: newGrid.value
                    }]
                };
                
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setNewGrid({ name: '', value: '' });
                setMessage('Grid template added successfully!');
            } catch (error) {
                setMessage('Error adding grid template: ' + error.message);
            }
            setIsLoading(false);
        };

        // Helper function to save layout to theme.json
        const saveLayoutToThemeJson = async (updatedLayout) => {
            // Convert arrays back to object format for theme.json
            const containersObj = {};
            const aspectRatiosObj = {};
            const zIndexObj = {};
            const breakpointsObj = {};
            const gridObj = {};
            
            // Separate WordPress standard layout from custom containers
            let contentSize = null;
            let wideSize = null;
            
            updatedLayout.containers.forEach(container => {
                if (container.slug === 'content') {
                    contentSize = container.value;
                } else if (container.slug === 'wide') {
                    wideSize = container.value;
                } else {
                    containersObj[container.slug] = container.value;
                }
            });
            
            updatedLayout.aspectRatios.forEach(aspectRatio => {
                aspectRatiosObj[aspectRatio.slug] = aspectRatio.value;
            });
            
            updatedLayout.zIndex.forEach(zIndex => {
                zIndexObj[zIndex.slug] = zIndex.value;
            });
            
            updatedLayout.breakpoints.forEach(breakpoint => {
                breakpointsObj[breakpoint.slug] = breakpoint.value;
            });
            
            updatedLayout.grid.forEach(grid => {
                gridObj[grid.slug] = grid.value;
            });

            const response = await fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'ds_studio_save_theme_json',
                    nonce: window.dsStudio.nonce,
                    theme_json: JSON.stringify({
                        ...window.dsStudio.currentThemeJson,
                        settings: {
                            ...window.dsStudio.currentThemeJson.settings,
                            layout: {
                                ...window.dsStudio.currentThemeJson.settings.layout,
                                ...(contentSize && { contentSize }),
                                ...(wideSize && { wideSize })
                            }
                        },
                        custom: {
                            ...window.dsStudio.currentThemeJson.custom,
                            layout: {
                                ...window.dsStudio.currentThemeJson.custom.layout,
                                ...(Object.keys(containersObj).length > 0 && { containers: containersObj }),
                                ...(Object.keys(aspectRatiosObj).length > 0 && { aspectRatios: aspectRatiosObj }),
                                ...(Object.keys(zIndexObj).length > 0 && { zIndex: zIndexObj }),
                                ...(Object.keys(breakpointsObj).length > 0 && { breakpoints: breakpointsObj }),
                                ...(Object.keys(gridObj).length > 0 && { grid: gridObj })
                            }
                        }
                    })
                })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save layout');
            }
            
            // Update the global theme.json data
            if (contentSize) {
                window.dsStudio.currentThemeJson.settings.layout = {
                    ...window.dsStudio.currentThemeJson.settings.layout,
                    contentSize
                };
            }
            if (wideSize) {
                window.dsStudio.currentThemeJson.settings.layout = {
                    ...window.dsStudio.currentThemeJson.settings.layout,
                    wideSize
                };
            }
            window.dsStudio.currentThemeJson.custom.layout = {
                ...window.dsStudio.currentThemeJson.custom.layout,
                ...(Object.keys(containersObj).length > 0 && { containers: containersObj }),
                ...(Object.keys(aspectRatiosObj).length > 0 && { aspectRatios: aspectRatiosObj }),
                ...(Object.keys(zIndexObj).length > 0 && { zIndex: zIndexObj }),
                ...(Object.keys(breakpointsObj).length > 0 && { breakpoints: breakpointsObj }),
                ...(Object.keys(gridObj).length > 0 && { grid: gridObj })
            };
        };

        // Color editing functions
        const startEditingColor = (color, index) => {
            setEditingColor(index);
            setEditColorValue(color.color);
            setShowColorPicker(true);
        };

        const saveEditedColor = () => {
            if (!editColorValue.trim()) {
                setMessage('Please enter a valid color value');
                setTimeout(() => setMessage(''), 3000);
                return;
            }

            setIsLoading(true);
            const updatedColors = [...colors];
            updatedColors[editingColor] = {
                ...updatedColors[editingColor],
                color: editColorValue.trim()
            };

            const formData = new FormData();
            formData.append('action', 'ds_studio_save_theme_json');
            formData.append('nonce', window.dsStudio.nonce);
            formData.append('color_name', updatedColors[editingColor].name);
            formData.append('color_value', editColorValue.trim());

            fetch(window.dsStudio.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                setIsLoading(false);
                if (result.success) {
                    setColors(updatedColors);
                    setMessage('Color updated successfully!');
                    cancelEditingColor();
                } else {
                    setMessage('Error updating color: ' + (result.data || 'Unknown error'));
                }
                setTimeout(() => setMessage(''), 3000);
            })
            .catch(error => {
                setIsLoading(false);
                setMessage('Error: ' + error.message);
                setTimeout(() => setMessage(''), 3000);
            });
        };

        const cancelEditingColor = () => {
            setEditingColor(null);
            setEditColorValue('');
            setShowColorPicker(false);
        };

        // Typography editing functions
        const startEditingTypography = (index, type) => {
            const item = typography[type][index];
            setEditingTypography({ index, type });
            setEditTypographyName(item.name || item.slug);
            setEditTypographyValue(
                type === 'fontFamilies' ? item.fontFamily :
                type === 'fontSizes' ? item.size :
                item.value || item.slug
            );
            setShowTypographyPicker(true);
        };

        const saveEditedTypography = async () => {
            if (editingTypography === null) return;
            
            setIsLoading(true);
            try {
                const { index, type } = editingTypography;
                const updatedTypography = { ...typography };
                
                // Update the item
                const updatedItem = { ...updatedTypography[type][index] };
                updatedItem.name = editTypographyName;
                
                if (type === 'fontFamilies') {
                    updatedItem.fontFamily = editTypographyValue;
                } else if (type === 'fontSizes') {
                    updatedItem.size = editTypographyValue;
                } else {
                    updatedItem.value = editTypographyValue;
                }
                
                updatedTypography[type][index] = updatedItem;
                
                // Save to theme.json
                const response = await fetch(window.dsStudio.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ds_studio_save_theme_json',
                        nonce: window.dsStudio.nonce,
                        theme_json: JSON.stringify({
                            ...window.dsStudio.currentThemeJson,
                            settings: {
                                ...window.dsStudio.currentThemeJson.settings,
                                typography: {
                                    ...window.dsStudio.currentThemeJson.settings.typography,
                                    [type]: updatedTypography[type]
                                }
                            }
                        })
                    })
                });
                
                if (response.ok) {
                    setTypography(updatedTypography);
                    setMessage('Typography updated successfully!');
                    setShowTypographyPicker(false);
                    setEditingTypography(null);
                    
                    // Update the global theme.json data
                    window.dsStudio.currentThemeJson.settings.typography[type] = updatedTypography[type];
                } else {
                    setMessage('Error saving typography');
                }
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const cancelEditingTypography = () => {
            setShowTypographyPicker(false);
            setEditingTypography(null);
            setEditTypographyValue('');
            setEditTypographyName('');
        };

        const moveTypographyItem = (type, fromIndex, toIndex) => {
            const updatedTypography = { ...typography };
            const items = [...updatedTypography[type]];
            const [movedItem] = items.splice(fromIndex, 1);
            items.splice(toIndex, 0, movedItem);
            updatedTypography[type] = items;
            setTypography(updatedTypography);
        };

        const renderTypographySection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '6px',
                                padding: '6px',
                                border: '1px solid #ddd',
                                borderRadius: '3px',
                                fontSize: '12px'
                            } 
                        },
                            el('div', { style: { flex: 1 } },
                                el('div', { style: { fontWeight: 'bold' } }, item.name || item.slug),
                                el('div', { style: { color: '#666' } }, 
                                    type === 'fontFamilies' ? item.fontFamily :
                                    type === 'fontSizes' ? item.size :
                                    item.value || item.slug
                                )
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        const renderEditableTypographySection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '6px',
                                padding: '8px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                fontSize: '12px',
                                backgroundColor: '#fafafa'
                            } 
                        },
                            // Drag handle
                            el('div', { 
                                style: { 
                                    marginRight: '8px', 
                                    cursor: 'grab',
                                    color: '#666',
                                    fontSize: '14px'
                                },
                                title: 'Drag to reorder'
                            }, '⋮⋮'),
                            
                            // Content
                            el('div', { 
                                style: { flex: 1, cursor: 'pointer' },
                                onClick: () => startEditingTypography(index, type)
                            },
                                el('div', { style: { fontWeight: 'bold' } }, item.name || item.slug),
                                el('div', { style: { color: '#666' } }, 
                                    type === 'fontFamilies' ? item.fontFamily : 
                                    type === 'fontSizes' ? item.size :
                                    item.value || item.slug
                                )
                            ),
                            
                            // Edit button
                            el(Button, {
                                isSmall: true,
                                isSecondary: true,
                                onClick: () => startEditingTypography(index, type),
                                style: { marginLeft: '8px' }
                            }, 'Edit'),
                            
                            // Move buttons
                            el('div', { style: { marginLeft: '8px', display: 'flex', flexDirection: 'column' } },
                                index > 0 && el('button', {
                                    onClick: () => moveTypographyItem(type, index, index - 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▲'),
                                index < items.length - 1 && el('button', {
                                    onClick: () => moveTypographyItem(type, index, index + 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▼')
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        const renderEditableBorderSection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '6px',
                                padding: '8px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                fontSize: '12px',
                                backgroundColor: '#fafafa'
                            } 
                        },
                            // Visual preview for border
                            el('div', { 
                                style: { 
                                    width: '20px',
                                    height: '20px',
                                    marginRight: '8px',
                                    border: type === 'borderWidths' ? `${item.value} solid #333` :
                                           type === 'borderStyles' ? `2px ${item.value} #333` :
                                           type === 'borderRadii' ? `2px solid #333` : '1px solid #ddd',
                                    borderRadius: type === 'borderRadii' ? item.value : '2px',
                                    backgroundColor: type === 'borderRadii' ? '#f0f0f0' : 'transparent'
                                } 
                            }),
                            
                            // Content
                            el('div', { 
                                style: { flex: 1, cursor: 'pointer' },
                                onClick: () => startEditingBorder(item, index, type)
                            },
                                el('div', { style: { fontWeight: 'bold' } }, item.name),
                                el('div', { style: { color: '#666' } }, item.value)
                            ),
                            
                            // Edit button
                            el(Button, {
                                isSmall: true,
                                variant: 'secondary',
                                onClick: () => startEditingBorder(item, index, type),
                                style: { marginLeft: '8px' }
                            }, 'Edit'),
                            
                            // Move buttons
                            el('div', { style: { marginLeft: '8px', display: 'flex', flexDirection: 'column' } },
                                index > 0 && el('button', {
                                    onClick: () => moveBorderItem(type, index, index - 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▲'),
                                index < items.length - 1 && el('button', {
                                    onClick: () => moveBorderItem(type, index, index + 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▼')
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        const renderEditableSpacingSection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 8px 0', fontSize: '13px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                marginBottom: '6px',
                                padding: '8px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                fontSize: '12px',
                                backgroundColor: '#fafafa'
                            } 
                        },
                            // Visual preview for spacing
                            el('div', { 
                                style: { 
                                    width: '20px',
                                    height: '20px',
                                    marginRight: '8px',
                                    border: '1px solid #ddd',
                                    borderRadius: '2px',
                                    backgroundColor: '#f0f0f0'
                                } 
                            }),
                            
                            // Content
                            el('div', { 
                                style: { flex: 1, cursor: 'pointer' },
                                onClick: () => startEditingSpacing(item, index, type)
                            },
                                el('div', { style: { fontWeight: 'bold' } }, item.name),
                                el('div', { style: { color: '#666' } }, item.value)
                            ),
                            
                            // Edit button
                            el(Button, {
                                isSmall: true,
                                variant: 'secondary',
                                onClick: () => startEditingSpacing(item, index, type),
                                style: { marginLeft: '8px' }
                            }, 'Edit'),
                            
                            // Move buttons
                            el('div', { style: { marginLeft: '8px', display: 'flex', flexDirection: 'column' } },
                                index > 0 && el('button', {
                                    onClick: () => moveSpacingItem(type, index, index - 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▲'),
                                index < items.length - 1 && el('button', {
                                    onClick: () => moveSpacingItem(type, index, index + 1),
                                    style: { 
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px'
                                    }
                                }, '▼')
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        // Helper functions for border editing and moving
        const startEditingBorder = (item, index, type) => {
            setEditingBorder({ item, index, type });
            setEditBorderValue(item.value);
        };

        const saveBorderEdit = async () => {
            if (!editingBorder || !editBorderValue) return;
            
            setIsLoading(true);
            try {
                const updatedBorders = { ...borders };
                const { index, type } = editingBorder;
                
                // Update the specific item
                const items = [...updatedBorders[type]];
                items[index] = {
                    ...items[index],
                    value: editBorderValue
                };
                updatedBorders[type] = items;
                
                await saveBordersToThemeJson(updatedBorders);
                setBorders(updatedBorders);
                setEditingBorder(null);
                setEditBorderValue('');
                setMessage(`${type.replace('border', '').replace(/([A-Z])/g, ' $1').trim()} updated successfully!`);
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const moveBorderItem = async (type, fromIndex, toIndex) => {
            const updatedBorders = { ...borders };
            const items = [...updatedBorders[type]];
            const [movedItem] = items.splice(fromIndex, 1);
            items.splice(toIndex, 0, movedItem);
            updatedBorders[type] = items;
            
            try {
                await saveBordersToThemeJson(updatedBorders);
                setBorders(updatedBorders);
                setMessage(`${type} reordered successfully!`);
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
        };

        const startEditingSpacing = (item, index, type) => {
            setEditingSpacing({ item, index, type });
            setEditSpacingValue(item.value);
        };

        const saveSpacingEdit = async () => {
            if (!editingSpacing || !editSpacingValue) return;
            
            setIsLoading(true);
            try {
                const updatedSpacing = { ...spacing };
                const { index, type } = editingSpacing;
                
                // Update the specific item
                const items = [...updatedSpacing[type]];
                items[index] = {
                    ...items[index],
                    value: editSpacingValue
                };
                updatedSpacing[type] = items;
                
                await saveSpacingToThemeJson(updatedSpacing);
                setSpacing(updatedSpacing);
                setEditingSpacing(null);
                setEditSpacingValue('');
                setMessage(`${type.replace('spacing', '').replace(/([A-Z])/g, ' $1').trim()} updated successfully!`);
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
            setIsLoading(false);
        };

        const moveSpacingItem = async (type, fromIndex, toIndex) => {
            const updatedSpacing = { ...spacing };
            const items = [...updatedSpacing[type]];
            const [movedItem] = items.splice(fromIndex, 1);
            items.splice(toIndex, 0, movedItem);
            updatedSpacing[type] = items;
            
            try {
                await saveSpacingToThemeJson(updatedSpacing);
                setSpacing(updatedSpacing);
                setMessage(`${type} reordered successfully!`);
            } catch (error) {
                setMessage('Error: ' + error.message);
            }
        };

        // Layout editing functions
        const startEditingLayout = (item, type) => {
            setEditingLayout(item);
            setEditLayoutValue(item.value);
            setEditLayoutType(type);
        };

        const saveLayoutEdit = async () => {
            if (!editLayoutValue.trim()) {
                setMessage('Please enter a value');
                return;
            }

            setIsLoading(true);
            try {
                const updatedLayout = { ...layout };
                const categoryKey = editLayoutType;
                
                updatedLayout[categoryKey] = updatedLayout[categoryKey].map(item =>
                    item.slug === editingLayout.slug
                        ? { ...item, value: editLayoutValue }
                        : item
                );

                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setEditingLayout(null);
                setEditLayoutValue('');
                setEditLayoutType('');
                setMessage('Layout item updated successfully!');
            } catch (error) {
                setMessage('Error updating layout item: ' + error.message);
            }
            setIsLoading(false);
        };

        const moveLayoutItem = async (type, index, direction) => {
            const items = [...layout[type]];
            const newIndex = direction === 'up' ? index - 1 : index + 1;
            
            if (newIndex < 0 || newIndex >= items.length) return;
            
            [items[index], items[newIndex]] = [items[newIndex], items[index]];
            
            const updatedLayout = {
                ...layout,
                [type]: items
            };
            
            try {
                await saveLayoutToThemeJson(updatedLayout);
                setLayout(updatedLayout);
                setMessage('Layout items reordered successfully!');
            } catch (error) {
                setMessage('Error reordering layout items: ' + error.message);
            }
        };

        // Layout UI components
        const renderEditableLayoutSection = (title, items, type) => {
            return el('div', { style: { marginBottom: '15px' } },
                el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, title),
                items.length > 0 ? 
                    items.map((item, index) => 
                        el('div', { 
                            key: index,
                            style: { 
                                display: 'flex', 
                                alignItems: 'center', 
                                justifyContent: 'space-between',
                                padding: '8px 12px',
                                marginBottom: '6px',
                                backgroundColor: 'white',
                                border: '1px solid #e0e0e0',
                                borderRadius: '4px',
                                cursor: 'pointer',
                                transition: 'all 0.2s ease'
                            },
                            onClick: () => startEditingLayout(item, type),
                            onMouseEnter: (e) => {
                                e.target.style.backgroundColor = '#f0f0f0';
                                e.target.style.borderColor = '#007cba';
                            },
                            onMouseLeave: (e) => {
                                e.target.style.backgroundColor = 'white';
                                e.target.style.borderColor = '#e0e0e0';
                            }
                        },
                            el('div', { style: { flex: 1 } },
                                el('div', { style: { fontWeight: '500', fontSize: '13px', marginBottom: '2px' } }, item.name),
                                el('div', { style: { fontSize: '11px', color: '#666', fontFamily: 'monospace' } }, item.value)
                            ),
                            el('div', { style: { display: 'flex', alignItems: 'center', gap: '4px' } },
                                el('button', {
                                    onClick: (e) => {
                                        e.stopPropagation();
                                        moveLayoutItem(type, index, 'up');
                                    },
                                    disabled: index === 0,
                                    style: {
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: index === 0 ? 'not-allowed' : 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px',
                                        opacity: index === 0 ? 0.3 : 1
                                    }
                                }, '▲'),
                                el('button', {
                                    onClick: (e) => {
                                        e.stopPropagation();
                                        moveLayoutItem(type, index, 'down');
                                    },
                                    disabled: index === items.length - 1,
                                    style: {
                                        background: 'none', 
                                        border: 'none', 
                                        cursor: index === items.length - 1 ? 'not-allowed' : 'pointer',
                                        fontSize: '10px',
                                        padding: '1px 4px',
                                        opacity: index === items.length - 1 ? 0.3 : 1
                                    }
                                }, '▼')
                            )
                        )
                    ) :
                    el('p', { style: { color: '#666', fontStyle: 'italic', fontSize: '12px', margin: '0' } }, 'None found')
            );
        };

        return el('div', { style: { padding: '16px' } },
            el('h3', { style: { marginTop: '0' } }, 'Design System Studio'),
            
            // Show message if any
            message && el(Notice, {
                status: message.includes('Error') ? 'error' : 'success',
                isDismissible: false
            }, message),
            
            // Colors Module
            el(PanelBody, { 
                title: 'Colors', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                // Existing colors
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Current Colors'),
                    colors.length > 0 ? 
                        colors.map((color, index) => 
                            el('div', { 
                                key: index,
                                style: { 
                                    display: 'flex', 
                                    alignItems: 'center', 
                                    marginBottom: '8px',
                                    padding: '8px',
                                    border: '1px solid #ddd',
                                    borderRadius: '4px'
                                } 
                            },
                                el('div', {
                                    style: {
                                        width: '24px',
                                        height: '24px',
                                        backgroundColor: color.color,
                                        borderRadius: '4px',
                                        marginRight: '12px',
                                        border: '1px solid #ccc',
                                        cursor: 'pointer'
                                    },
                                    onClick: () => startEditingColor(color, index),
                                    title: 'Click to edit color'
                                }),
                                el('div', { style: { flex: 1 } },
                                    el('div', { style: { fontWeight: 'bold' } }, color.name),
                                    el('div', { style: { fontSize: '12px', color: '#666' } }, color.color)
                                ),
                                el(Button, {
                                    isSecondary: true,
                                    onClick: () => startEditingColor(color, index)
                                }, 'Edit')
                            )
                        ) :
                        el('p', { style: { color: '#666', fontStyle: 'italic' } }, 'No colors found in theme.json')
                ),
                
                // Color picker popup for editing - Fixed position in top right
                showColorPicker && editingColor !== null && el('div', {
                    style: {
                        position: 'fixed',
                        top: '60px',
                        right: '20px',
                        zIndex: 999999,
                        backgroundColor: 'white',
                        border: '1px solid #ccc',
                        borderRadius: '8px',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                        padding: '16px',
                        minWidth: '280px',
                        maxWidth: '320px'
                    }
                },
                    el('div', { style: { marginBottom: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                        el('h4', { style: { margin: '0', fontSize: '14px', fontWeight: '600' } }, 
                            `Edit ${colors[editingColor]?.name}`),
                        el('button', {
                            onClick: cancelEditingColor,
                            style: {
                                background: 'none',
                                border: 'none',
                                fontSize: '18px',
                                cursor: 'pointer',
                                padding: '0',
                                width: '20px',
                                height: '20px',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center'
                            }
                        }, '×')
                    ),
                    el(ColorPicker, {
                        color: editColorValue,
                        onChange: setEditColorValue,
                        enableAlpha: false
                    }),
                    el('div', { style: { marginTop: '12px', display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
                        el(Button, {
                            isSecondary: true,
                            onClick: cancelEditingColor,
                            style: { marginRight: '8px' }
                        }, 'Cancel'),
                        el(Button, {
                            isPrimary: true,
                            onClick: saveEditedColor,
                            disabled: isLoading
                        }, isLoading ? 'Saving...' : 'Save')
                    )
                ),
                
                // Add new color
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' } },
                    el('h4', { style: { margin: '0 0 10px 0' } }, 'Add New Color'),
                    el(TextControl, {
                        label: 'Color Name',
                        value: newColorName,
                        onChange: setNewColorName,
                        placeholder: 'e.g., Primary Blue'
                    }),
                    el('div', { style: { marginBottom: '10px' } },
                        el('label', { style: { display: 'block', marginBottom: '5px', fontWeight: 'bold' } }, 'Color Value'),
                        el('input', {
                            type: 'color',
                            value: newColorValue,
                            onChange: (e) => setNewColorValue(e.target.value),
                            style: { width: '100%', height: '40px', border: 'none', borderRadius: '4px' }
                        })
                    ),
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading,
                        onClick: saveColor
                    }, isLoading ? 'Saving...' : 'Save Color')
                )
            ),

            // Typography Module - Enhanced with sub-categories
            el(PanelBody, { 
                title: 'Typography', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Typography System'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                // Font Sizes Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Font Sizes'),
                    renderEditableTypographySection('Current Font Sizes', typography.fontSizes, 'fontSizes'),
                    
                    // Add new font size form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Font Size'),
                        el(TextControl, {
                            label: 'Name',
                            value: newFontSize.name,
                            onChange: (value) => setNewFontSize({...newFontSize, name: value}),
                            placeholder: 'e.g., Extra Large, Body Text',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Size',
                            value: newFontSize.size,
                            onChange: (value) => setNewFontSize({...newFontSize, size: value}),
                            placeholder: 'e.g., 2rem, 24px, 1.5em',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newFontSize.name || !newFontSize.size,
                            onClick: saveFontSize
                        }, isLoading ? 'Adding...' : 'Add Font Size')
                    )
                ),

                // Font Families Sub-category  
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Font Families'),
                    renderEditableTypographySection('Current Font Families', typography.fontFamilies, 'fontFamilies'),
                    
                    // Add new font family form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Font Family'),
                        el(TextControl, {
                            label: 'Name',
                            value: newFontFamily.name,
                            onChange: (value) => setNewFontFamily({...newFontFamily, name: value}),
                            placeholder: 'e.g., Heading Font, Body Font',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Font Family',
                            value: newFontFamily.fontFamily,
                            onChange: (value) => setNewFontFamily({...newFontFamily, fontFamily: value}),
                            placeholder: 'e.g., "Inter", sans-serif',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newFontFamily.name || !newFontFamily.fontFamily,
                            onClick: saveFontFamily
                        }, isLoading ? 'Adding...' : 'Add Font Family')
                    )
                ),

                // Font Weights Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Font Weights'),
                    renderEditableTypographySection('Current Font Weights', typography.fontWeights || [], 'fontWeights'),
                    
                    // Add new font weight form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Font Weight'),
                        el(TextControl, {
                            label: 'Name',
                            value: newFontWeight.name,
                            onChange: (value) => setNewFontWeight({...newFontWeight, name: value}),
                            placeholder: 'e.g., Medium, Semi Bold',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Weight',
                            value: newFontWeight.value,
                            onChange: (value) => setNewFontWeight({...newFontWeight, value: value}),
                            placeholder: 'e.g., 400, 600, bold',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newFontWeight.name || !newFontWeight.value,
                            onClick: saveFontWeight
                        }, isLoading ? 'Adding...' : 'Add Font Weight')
                    )
                ),

                // Line Heights Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Line Heights'),
                    renderEditableTypographySection('Current Line Heights', typography.lineHeights || [], 'lineHeights'),
                    
                    // Add new line height form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Line Height'),
                        el(TextControl, {
                            label: 'Name',
                            value: newLineHeight.name,
                            onChange: (value) => setNewLineHeight({...newLineHeight, name: value}),
                            placeholder: 'e.g., Tight, Relaxed',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Line Height',
                            value: newLineHeight.value,
                            onChange: (value) => setNewLineHeight({...newLineHeight, value: value}),
                            placeholder: 'e.g., 1.2, 1.5, 1.8',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newLineHeight.name || !newLineHeight.value,
                            onClick: saveLineHeight
                        }, isLoading ? 'Adding...' : 'Add Line Height')
                    )
                ),

                // Letter Spacing Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Letter Spacing'),
                    renderEditableTypographySection('Current Letter Spacing', typography.letterSpacing || [], 'letterSpacing'),
                    
                    // Add new letter spacing form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Letter Spacing'),
                        el(TextControl, {
                            label: 'Name',
                            value: newLetterSpacing.name,
                            onChange: (value) => setNewLetterSpacing({...newLetterSpacing, name: value}),
                            placeholder: 'e.g., Wide, Tight',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Letter Spacing',
                            value: newLetterSpacing.value,
                            onChange: (value) => setNewLetterSpacing({...newLetterSpacing, value: value}),
                            placeholder: 'e.g., 0.05em, 1px, -0.02em',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newLetterSpacing.name || !newLetterSpacing.value,
                            onClick: saveLetterSpacing
                        }, isLoading ? 'Adding...' : 'Add Letter Spacing')
                    )
                ),

                // Text Transforms Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Text Transforms'),
                    renderEditableTypographySection('Current Text Transforms', typography.textTransforms || [], 'textTransforms'),
                    
                    // Add new text transform form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Text Transform'),
                        el(TextControl, {
                            label: 'Name',
                            value: newTextTransform.name,
                            onChange: (value) => setNewTextTransform({...newTextTransform, name: value}),
                            placeholder: 'e.g., All Caps, Title Case',
                            style: { marginBottom: '8px' }
                        }),
                        el('div', { style: { marginBottom: '8px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontSize: '11px', fontWeight: 'bold' } }, 'Transform'),
                            el('select', {
                                value: newTextTransform.value,
                                onChange: (e) => setNewTextTransform({...newTextTransform, value: e.target.value}),
                                style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px', fontSize: '12px' }
                            },
                                el('option', { value: '' }, 'Select transform...'),
                                el('option', { value: 'none' }, 'None'),
                                el('option', { value: 'uppercase' }, 'Uppercase'),
                                el('option', { value: 'lowercase' }, 'Lowercase'),
                                el('option', { value: 'capitalize' }, 'Capitalize')
                            )
                        ),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newTextTransform.name || !newTextTransform.value,
                            onClick: saveTextTransform
                        }, isLoading ? 'Adding...' : 'Add Text Transform')
                    )
                )
            ),

            // Borders Module - Enhanced with sub-categories
            el(PanelBody, { 
                title: 'Borders', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Border System'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                // Border Widths Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Border Widths'),
                    renderEditableBorderSection('Current Border Widths', borders.borderWidths || [], 'borderWidths'),
                    
                    // Add new border width form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Border Width'),
                        el(TextControl, {
                            label: 'Name',
                            value: newBorderWidth.name,
                            onChange: (value) => setNewBorderWidth({...newBorderWidth, name: value}),
                            placeholder: 'e.g., Thin, Medium, Thick',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Width',
                            value: newBorderWidth.value,
                            onChange: (value) => setNewBorderWidth({...newBorderWidth, value: value}),
                            placeholder: 'e.g., 1px, 2px, 0.125rem',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newBorderWidth.name || !newBorderWidth.value,
                            onClick: saveBorderWidth
                        }, isLoading ? 'Adding...' : 'Add Border Width')
                    )
                ),

                // Border Styles Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Border Styles'),
                    renderEditableBorderSection('Current Border Styles', borders.borderStyles || [], 'borderStyles'),
                    
                    // Add new border style form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Border Style'),
                        el(TextControl, {
                            label: 'Name',
                            value: newBorderStyle.name,
                            onChange: (value) => setNewBorderStyle({...newBorderStyle, name: value}),
                            placeholder: 'e.g., Dashed Line, Dotted',
                            style: { marginBottom: '8px' }
                        }),
                        el('div', { style: { marginBottom: '8px' } },
                            el('label', { style: { display: 'block', marginBottom: '5px', fontSize: '11px', fontWeight: 'bold' } }, 'Style'),
                            el('select', {
                                value: newBorderStyle.value,
                                onChange: (e) => setNewBorderStyle({...newBorderStyle, value: e.target.value}),
                                style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px', fontSize: '12px' }
                            },
                                el('option', { value: '' }, 'Select style...'),
                                el('option', { value: 'none' }, 'None'),
                                el('option', { value: 'solid' }, 'Solid'),
                                el('option', { value: 'dashed' }, 'Dashed'),
                                el('option', { value: 'dotted' }, 'Dotted'),
                                el('option', { value: 'double' }, 'Double'),
                                el('option', { value: 'groove' }, 'Groove'),
                                el('option', { value: 'ridge' }, 'Ridge')
                            )
                        ),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newBorderStyle.name || !newBorderStyle.value,
                            onClick: saveBorderStyle
                        }, isLoading ? 'Adding...' : 'Add Border Style')
                    )
                ),

                // Border Radii Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Border Radii'),
                    renderEditableBorderSection('Current Border Radii', borders.borderRadii || [], 'borderRadii'),
                    
                    // Add new border radius form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Border Radius'),
                        el(TextControl, {
                            label: 'Name',
                            value: newBorderRadius.name,
                            onChange: (value) => setNewBorderRadius({...newBorderRadius, name: value}),
                            placeholder: 'e.g., Small, Large, Pill',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Radius',
                            value: newBorderRadius.value,
                            onChange: (value) => setNewBorderRadius({...newBorderRadius, value: value}),
                            placeholder: 'e.g., 4px, 0.5rem, 50%',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newBorderRadius.name || !newBorderRadius.value,
                            onClick: saveBorderRadius
                        }, isLoading ? 'Adding...' : 'Add Border Radius')
                    )
                )
            ) // Added closing parenthesis here
            ,

            // Layout Modules - Separate Accordion Panels
            
            // Containers Module
            el(PanelBody, { 
                title: 'Containers', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Container Sizes'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Manage content width containers. Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                renderEditableLayoutSection('Current Containers', layout.containers, 'containers'),
                
                // Add new container form
                el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                    el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Container'),
                    el(TextControl, {
                        label: 'Name',
                        value: newContainer.name,
                        onChange: (value) => setNewContainer({...newContainer, name: value}),
                        placeholder: 'e.g., Header, Footer',
                        style: { marginBottom: '8px' }
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newContainer.value,
                        onChange: (value) => setNewContainer({...newContainer, value: value}),
                        placeholder: 'e.g., 1200px, 90vw',
                        style: { marginBottom: '8px' }
                    }),
                    el(Button, {
                        isPrimary: true,
                        isSmall: true,
                        isBusy: isLoading,
                        disabled: isLoading || !newContainer.name || !newContainer.value,
                        onClick: saveContainer
                    }, isLoading ? 'Adding...' : 'Add Container')
                )
            ),

            // Aspect Ratios Module
            el(PanelBody, { 
                title: 'Aspect Ratios', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Aspect Ratios'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Manage aspect ratio values for responsive design. Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                renderEditableLayoutSection('Current Aspect Ratios', layout.aspectRatios, 'aspectRatios'),
                
                // Add new aspect ratio form
                el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                    el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Aspect Ratio'),
                    el(TextControl, {
                        label: 'Name',
                        value: newAspectRatio.name,
                        onChange: (value) => setNewAspectRatio({...newAspectRatio, name: value}),
                        placeholder: 'e.g., 16/9, 4/3',
                        style: { marginBottom: '8px' }
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newAspectRatio.value,
                        onChange: (value) => setNewAspectRatio({...newAspectRatio, value: value}),
                        placeholder: 'e.g., 16/9, 4/3',
                        style: { marginBottom: '8px' }
                    }),
                    el(Button, {
                        isPrimary: true,
                        isSmall: true,
                        isBusy: isLoading,
                        disabled: isLoading || !newAspectRatio.name || !newAspectRatio.value,
                        onClick: saveAspectRatio
                    }, isLoading ? 'Adding...' : 'Add Aspect Ratio')
                )
            ),

            // Z-Index Module
            el(PanelBody, { 
                title: 'Z-Index', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Z-Index Layers'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Manage z-index stacking layers. Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                renderEditableLayoutSection('Current Z-Index', layout.zIndex, 'zIndex'),
                
                // Add new z-index form
                el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                    el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Z-Index'),
                    el(TextControl, {
                        label: 'Name',
                        value: newZIndex.name,
                        onChange: (value) => setNewZIndex({...newZIndex, name: value}),
                        placeholder: 'e.g., Overlay, Dropdown',
                        style: { marginBottom: '8px' }
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newZIndex.value,
                        onChange: (value) => setNewZIndex({...newZIndex, value: value}),
                        placeholder: 'e.g., 100, 200',
                        style: { marginBottom: '8px' }
                    }),
                    el(Button, {
                        isPrimary: true,
                        isSmall: true,
                        isBusy: isLoading,
                        disabled: isLoading || !newZIndex.name || !newZIndex.value,
                        onClick: saveZIndex
                    }, isLoading ? 'Adding...' : 'Add Z-Index')
                )
            ),

            // Breakpoints Module
            el(PanelBody, { 
                title: 'Breakpoints', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Responsive Breakpoints'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Manage responsive breakpoint values. Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                renderEditableLayoutSection('Current Breakpoints', layout.breakpoints, 'breakpoints'),
                
                // Add new breakpoint form
                el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                    el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Breakpoint'),
                    el(TextControl, {
                        label: 'Name',
                        value: newBreakpoint.name,
                        onChange: (value) => setNewBreakpoint({...newBreakpoint, name: value}),
                        placeholder: 'e.g., Small, Medium',
                        style: { marginBottom: '8px' }
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newBreakpoint.value,
                        onChange: (value) => setNewBreakpoint({...newBreakpoint, value: value}),
                        placeholder: 'e.g., 768px, 1024px',
                        style: { marginBottom: '8px' }
                    }),
                    el(Button, {
                        isPrimary: true,
                        isSmall: true,
                        isBusy: isLoading,
                        disabled: isLoading || !newBreakpoint.name || !newBreakpoint.value,
                        onClick: saveBreakpoint
                    }, isLoading ? 'Adding...' : 'Add Breakpoint')
                )
            ),

            // Grid Templates Module
            el(PanelBody, { 
                title: 'Grid Templates', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'CSS Grid Templates'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Manage CSS grid template definitions. Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                renderEditableLayoutSection('Current Grid Templates', layout.grid, 'grid'),
                
                // Add new grid template form
                el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                    el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Grid Template'),
                    el(TextControl, {
                        label: 'Name',
                        value: newGrid.name,
                        onChange: (value) => setNewGrid({...newGrid, name: value}),
                        placeholder: 'e.g., 3-Column, Auto-Fit',
                        style: { marginBottom: '8px' }
                    }),
                    el(TextControl, {
                        label: 'Value',
                        value: newGrid.value,
                        onChange: (value) => setNewGrid({...newGrid, value: value}),
                        placeholder: 'e.g., repeat(3, 1fr)',
                        style: { marginBottom: '8px' }
                    }),
                    el(Button, {
                        isPrimary: true,
                        isSmall: true,
                        isBusy: isLoading,
                        disabled: isLoading || !newGrid.name || !newGrid.value,
                        onClick: saveGrid
                    }, isLoading ? 'Adding...' : 'Add Grid Template')
                )
            ),

            // Spacing Module
            el(PanelBody, { 
                title: 'Spacing', 
                initialOpen: false,
                style: { 
                    position: 'sticky', 
                    top: '0', 
                    zIndex: 100,
                    backgroundColor: 'white',
                    borderBottom: '1px solid #ddd'
                }
            },
                el('div', { style: { marginBottom: '20px' } },
                    el('h4', { style: { margin: '0 0 15px 0' } }, 'Spacing System'),
                    el('p', { style: { fontSize: '12px', color: '#666', margin: '0 0 15px 0' } }, 
                        'Click any item to edit, or use arrow buttons to reorder.'
                    )
                ),

                // Spacing Sizes Sub-category
                el('div', { style: { marginBottom: '20px', padding: '15px', border: '1px solid #e0e0e0', borderRadius: '4px', backgroundColor: '#fafafa' } },
                    el('h5', { style: { margin: '0 0 15px 0', fontSize: '14px', fontWeight: 'bold' } }, 'Spacing Sizes'),
                    renderEditableSpacingSection('Current Spacing Sizes', spacing.spacingSizes || [], 'spacingSizes'),
                    
                    // Add new spacing size form
                    el('div', { style: { marginTop: '15px', padding: '12px', border: '1px solid #ddd', borderRadius: '4px', backgroundColor: '#f9f9f9' } },
                        el('h6', { style: { margin: '0 0 10px 0', fontSize: '12px', fontWeight: 'bold' } }, 'Add New Spacing Size'),
                        el(TextControl, {
                            label: 'Name',
                            value: newSpacingSize.name,
                            onChange: (value) => setNewSpacingSize({...newSpacingSize, name: value}),
                            placeholder: 'e.g., Small, Medium, Large',
                            style: { marginBottom: '8px' }
                        }),
                        el(TextControl, {
                            label: 'Size',
                            value: newSpacingSize.value,
                            onChange: (value) => setNewSpacingSize({...newSpacingSize, value: value}),
                            placeholder: 'e.g., 4px, 0.5rem, 1em',
                            style: { marginBottom: '8px' }
                        }),
                        el(Button, {
                            isPrimary: true,
                            isSmall: true,
                            isBusy: isLoading,
                            disabled: isLoading || !newSpacingSize.name || !newSpacingSize.value,
                            onClick: saveSpacingSize
                        }, isLoading ? 'Adding...' : 'Add Spacing Size')
                    )
                )
            ),

            // Border editing popup - Fixed position
            editingBorder !== null && el('div', {
                style: {
                    position: 'fixed',
                    top: '60px',
                    right: '20px',
                    zIndex: 999999,
                    backgroundColor: 'white',
                    border: '1px solid #ccc',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    padding: '16px',
                    minWidth: '280px',
                    maxWidth: '320px'
                }
            },
                el('h4', { style: { margin: '0 0 12px 0', fontSize: '14px' } }, `Edit ${editingBorder.type.replace('border', '').replace(/([A-Z])/g, ' $1').trim()}`),
                
                el(TextControl, {
                    label: 'Value',
                    value: editBorderValue,
                    onChange: setEditBorderValue,
                    placeholder: editingBorder.type === 'borderWidths' ? 'e.g., 2px, 0.125rem' :
                                editingBorder.type === 'borderStyles' ? 'e.g., solid, dashed' :
                                'e.g., 4px, 0.5rem, 50%',
                    style: { marginBottom: '12px' }
                }),
                
                el('div', { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
                    el(Button, {
                        variant: 'secondary',
                        onClick: () => {
                            setEditingBorder(null);
                            setEditBorderValue('');
                        }
                    }, 'Cancel'),
                    
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading || !editBorderValue,
                        onClick: saveBorderEdit
                    }, isLoading ? 'Saving...' : 'Save')
                )
            ),

            // Typography picker popup for editing - Fixed position
            showTypographyPicker && editingTypography !== null && el('div', {
                style: {
                    position: 'fixed',
                    top: '60px',
                    right: '20px',
                    zIndex: 999999,
                    backgroundColor: 'white',
                    border: '1px solid #ccc',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    padding: '16px',
                    minWidth: '280px',
                    maxWidth: '320px'
                }
            },
                el('div', { style: { marginBottom: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } },
                    el('h4', { style: { margin: '0', fontSize: '14px', fontWeight: '600' } }, 
                        `Edit ${editingTypography.type.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}`
                    ),
                    el('button', {
                        onClick: cancelEditingTypography,
                        style: {
                            background: 'none',
                            border: 'none',
                            fontSize: '18px',
                            cursor: 'pointer',
                            padding: '0',
                            width: '20px',
                            height: '20px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        }
                    }, '×')
                ),
                el(TextControl, {
                    label: 'Name',
                    value: editTypographyName,
                    onChange: setEditTypographyName,
                    placeholder: 'Enter name'
                }),
                el(TextControl, {
                    label: 'Value',
                    value: editTypographyValue,
                    onChange: setEditTypographyValue,
                    placeholder: editingTypography.type === 'fontSizes' ? 'e.g., 2rem, 24px' :
                               editingTypography.type === 'fontFamilies' ? 'e.g., "Inter", sans-serif' :
                               editingTypography.type === 'fontWeights' ? 'e.g., 400, 600, bold' :
                               editingTypography.type === 'lineHeights' ? 'e.g., 1.5, 1.2' :
                               editingTypography.type === 'letterSpacing' ? 'e.g., 0.05em, 1px' :
                               'e.g., uppercase, lowercase, capitalize'
                }),
                el('div', { style: { marginTop: '12px', display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
                    el(Button, {
                        isSecondary: true,
                        onClick: cancelEditingTypography,
                        style: { marginRight: '8px' }
                    }, 'Cancel'),
                    el(Button, {
                        isPrimary: true,
                        onClick: saveEditedTypography,
                        disabled: isLoading
                    }, isLoading ? 'Saving...' : 'Save')
                )
            ),

            // Spacing editing popup - Fixed position
            editingSpacing !== null && el('div', {
                style: {
                    position: 'fixed',
                    top: '60px',
                    right: '20px',
                    zIndex: 999999,
                    backgroundColor: 'white',
                    border: '1px solid #ccc',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    padding: '16px',
                    minWidth: '280px',
                    maxWidth: '320px'
                }
            },
                el('h4', { style: { margin: '0 0 12px 0', fontSize: '14px' } }, `Edit ${editingSpacing.type.replace('spacing', '').replace(/([A-Z])/g, ' $1').trim()}`),
                
                el(TextControl, {
                    label: 'Value',
                    value: editSpacingValue,
                    onChange: setEditSpacingValue,
                    placeholder: 'e.g., 4px, 0.5rem, 1em',
                    style: { marginBottom: '12px' }
                }),
                
                el('div', { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
                    el(Button, {
                        variant: 'secondary',
                        onClick: () => {
                            setEditingSpacing(null);
                            setEditSpacingValue('');
                        }
                    }, 'Cancel'),
                    
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading || !editSpacingValue,
                        onClick: saveSpacingEdit
                    }, isLoading ? 'Saving...' : 'Save')
                )
            ),

            // Layout editing popup - Fixed position
            editingLayout !== null && el('div', {
                style: {
                    position: 'fixed',
                    top: '60px',
                    right: '20px',
                    zIndex: 999999,
                    backgroundColor: 'white',
                    border: '1px solid #ccc',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    padding: '16px',
                    minWidth: '280px',
                    maxWidth: '320px'
                }
            },
                el('h4', { style: { margin: '0 0 12px 0', fontSize: '14px' } }, `Edit ${editingLayout.type.charAt(0).toUpperCase() + editingLayout.type.slice(1)}`),
                
                el(TextControl, {
                    label: 'Value',
                    value: editLayoutValue,
                    onChange: setEditLayoutValue,
                    placeholder: editLayoutType === 'containers' ? 'e.g., 1200px, 90vw' :
                                editLayoutType === 'aspectRatios' ? 'e.g., 16/9, 4/3' :
                                editLayoutType === 'zIndex' ? 'e.g., 100, 200' :
                                editLayoutType === 'breakpoints' ? 'e.g., 768px, 1024px' :
                                'e.g., repeat(12, 1fr)',
                    style: { marginBottom: '12px' }
                }),
                
                el('div', { style: { display: 'flex', gap: '8px', justifyContent: 'flex-end' } },
                    el(Button, {
                        variant: 'secondary',
                        onClick: () => {
                            setEditingLayout(null);
                            setEditLayoutValue('');
                            setEditLayoutType('');
                        }
                    }, 'Cancel'),
                    
                    el(Button, {
                        isPrimary: true,
                        isBusy: isLoading,
                        disabled: isLoading || !editLayoutValue,
                        onClick: saveLayoutEdit
                    }, isLoading ? 'Saving...' : 'Save')
                )
            )
        );
    };

    // Register the plugin
    registerPlugin('ds-studio', {
        render: () => {
            return [
                el(PluginSidebarMoreMenuItem, {
                    target: 'ds-studio-sidebar',
                    icon: 'art'
                }, 'DS-Studio'),
                
                el(PluginSidebar, {
                    name: 'ds-studio-sidebar',
                    title: 'Design System Studio',
                    icon: 'art'
                }, el(DSStudioPanel))
            ];
        }
    });

})();
