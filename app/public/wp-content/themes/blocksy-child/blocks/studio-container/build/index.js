!function(){"use strict";var e=window.wp.blocks,t=window.wp.blockEditor,n=window.wp.components,i=window.wp.i18n,a=window.wp.data,s=window.wp.element;

// Helper function to get theme settings
const useThemeSettings = () => {
    return (0,a.useSelect)((select) => {
        const settings = select('core/block-editor').getSettings();
        return settings.__experimentalFeatures?.custom || {};
    }, []);
};

(0,e.registerBlockType)("studio/container",{
    edit:function(e){
        var r=e.attributes,d=e.setAttributes,
            u=r.widthPreset,p=r.paddingPreset,h=r.heightPreset,c=r.tagName,m=r.minHeight;
        
        // State for preset saving
        const [isPresetModalOpen, setIsPresetModalOpen] = (0,s.useState)(false);
        const [presetName, setPresetName] = (0,s.useState)('');
        const [saveNotice, setSaveNotice] = (0,s.useState)(null);
        
        // Get theme settings
        const themeSettings = useThemeSettings();
        const containerSettings = themeSettings.container || {};
        const paddingScale = themeSettings.paddingScale || {};
        
        // Build presets from theme
        const widthPresets = Object.entries(containerSettings.widthPresets || {}).map(([key, preset]) => ({
            label: preset.name,
            value: preset.value
        }));
        
        const heightPresets = Object.entries(containerSettings.heightPresets || {}).map(([key, preset]) => ({
            label: preset.name,
            value: preset.value
        }));
        
        const paddingPresets = [
            { label: 'None', value: 'none' },
            ...Object.entries(paddingScale).map(([key, value]) => ({
                label: key.charAt(0).toUpperCase() + key.slice(1),
                value: key
            }))
        ];
        
        const tagOptions = Object.entries(containerSettings.htmlTags || {}).map(([key, tag]) => ({
            label: tag.name,
            value: tag.value
        }));
        
        // Fallbacks
        const fallbackWidthPresets = [
            {label:"Content Width",value:"content"},
            {label:"Wide Width",value:"wide"},
            {label:"Full Width",value:"full"},
            {label:"Custom",value:"custom"}
        ];
        
        const fallbackHeightPresets = [
            {label:"Auto",value:"auto"},
            {label:"25% Viewport",value:"25vh"},
            {label:"50% Viewport",value:"50vh"},
            {label:"75% Viewport",value:"75vh"},
            {label:"Full Viewport",value:"100vh"}
        ];
        
        const fallbackPaddingPresets = [
            {label:"None",value:"none"},
            {label:"Small",value:"small"},
            {label:"Medium",value:"medium"},
            {label:"Large",value:"large"},
            {label:"Extra Large",value:"xlarge"}
        ];
        
        const fallbackTagOptions = [
            {label:"Div",value:"div"},
            {label:"Section",value:"section"},
            {label:"Article",value:"article"},
            {label:"Aside",value:"aside"},
            {label:"Main",value:"main"},
            {label:"Header",value:"header"},
            {label:"Footer",value:"footer"}
        ];
        
        // Use theme presets or fallbacks
        const finalWidthPresets = widthPresets.length > 0 ? widthPresets : fallbackWidthPresets;
        const finalHeightPresets = heightPresets.length > 0 ? heightPresets : fallbackHeightPresets;
        const finalPaddingPresets = paddingPresets.length > 1 ? paddingPresets : fallbackPaddingPresets;
        const finalTagOptions = tagOptions.length > 0 ? tagOptions : fallbackTagOptions;
        
        // Get saved presets from theme.json
        const savedPresets = React.useMemo(() => {
            console.log('Building savedPresets, window.studioThemeData:', window.studioThemeData);
            const presets = [];
            if (window.studioThemeData?.blockPresets?.container) {
                console.log('Container presets found:', window.studioThemeData.blockPresets.container);
                Object.entries(window.studioThemeData.blockPresets.container).forEach(([id, preset]) => {
                    console.log('Adding preset:', id, preset);
                    presets.push({
                        label: preset.name,
                        value: id,
                        preset: preset
                    });
                });
            } else {
                console.log('No container presets found');
            }
            
            const finalPresets = [
                { label: (0,i.__)('Select a preset...', 'studio'), value: '', disabled: true },
                ...presets
            ];
            
            console.log('Final savedPresets array:', finalPresets);
            return finalPresets;
        }, []);
        
        // Apply saved preset
        const applyPreset = (presetId) => {
            if (!presetId || !window.studioThemeData?.blockPresets?.container?.[presetId]) return;
            
            const preset = window.studioThemeData.blockPresets.container[presetId];
            const attrs = preset.attributes;
            
            d({
                widthPreset: attrs.widthPreset || 'content',
                paddingPreset: attrs.paddingPreset || 'medium',
                heightPreset: attrs.heightPreset || 'auto',
                tagName: attrs.tagName || 'div',
                minHeight: attrs.minHeight || ''
            });
        };
        
        // Save preset function
        const saveAsPreset = () => {
            if (!presetName.trim()) return;
            
            console.log('Save preset function called:', presetName);
            console.log('studioAdmin globals:', window.studioAdmin);
            
            const presetData = {
                name: presetName.trim(),
                attributes: {
                    widthPreset: u || 'content',
                    paddingPreset: p || 'medium', 
                    heightPreset: h || 'auto',
                    tagName: c || 'div',
                    minHeight: m || ''
                },
                description: `Saved from editor on ${new Date().toLocaleDateString()}`
            };
            
            console.log('Preset data to save:', presetData);
            
            // Create form data for AJAX
            const formData = new FormData();
            formData.append('action', 'studio_save_preset');
            formData.append('nonce', window.studioAdmin?.nonce || '');
            formData.append('block_type', 'container');
            formData.append('preset_data', JSON.stringify(presetData));
            
            console.log('Making AJAX request to:', window.studioAdmin?.ajaxUrl || '/wp-admin/admin-ajax.php');
            
            // Use fetch API for better reliability
            fetch(window.studioAdmin?.ajaxUrl || '/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text();
            })
            .then(data => {
                console.log('Response data:', data);
                try {
                    const jsonData = JSON.parse(data);
                    if (jsonData.success) {
                        setSaveNotice({
                            type: 'success',
                            message: `Preset "${presetName}" saved successfully!`
                        });
                        setIsPresetModalOpen(false);
                        setPresetName('');
                        
                        // Clear notice after 3 seconds
                        setTimeout(() => setSaveNotice(null), 3000);
                    } else {
                        setSaveNotice({
                            type: 'error',
                            message: `Failed to save preset: ${jsonData.data || 'Unknown error'}`
                        });
                    }
                } catch (e) {
                    console.error('JSON parse error:', e, 'Raw data:', data);
                    setSaveNotice({
                        type: 'error',
                        message: `Failed to save preset: Invalid response`
                    });
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                setSaveNotice({
                    type: 'error',
                    message: `Failed to save preset: ${error.message}`
                });
            });
        };
        
        var f=(0,t.useBlockProps)({
            className:"studio-container width-".concat(u||'content'," padding-").concat(p||'medium'," height-").concat(h||'auto'),
            style: {
                ...(m && m !== 'auto' ? { minHeight: m } : {}),
                ...(h && h !== 'auto' ? { minHeight: h } : {})
            }
        }),
        g=(0,t.useInnerBlocksProps)(f,{templateLock:!1,renderAppender:t.InnerBlocks.ButtonBlockAppender});
        
        return React.createElement(React.Fragment,null,
            React.createElement(t.InspectorControls,null,
                React.createElement(n.PanelBody,{title:(0,i.__)("Layout Settings","studio"),initialOpen:true},
                    React.createElement(n.SelectControl,{
                        label:(0,i.__)("Width","studio"),
                        value:u||'content',
                        options:finalWidthPresets,
                        onChange:function(e){return d({widthPreset:e})},
                        help:(0,i.__)("Choose container width preset","studio")
                    }),
                    React.createElement(n.SelectControl,{
                        label:(0,i.__)("Height","studio"),
                        value:h||'auto',
                        options:finalHeightPresets,
                        onChange:function(e){return d({heightPreset:e})},
                        help:(0,i.__)("Choose container height preset","studio")
                    }),
                    React.createElement(n.SelectControl,{
                        label:(0,i.__)("Padding","studio"),
                        value:p||'medium',
                        options:finalPaddingPresets,
                        onChange:function(e){return d({paddingPreset:e})},
                        help:(0,i.__)("Choose padding preset","studio")
                    })
                ),
                React.createElement(n.PanelBody,{title:(0,i.__)("Container Settings","studio"),initialOpen:false},
                    React.createElement(n.SelectControl,{
                        label:(0,i.__)("HTML Tag","studio"),
                        value:c||'div',
                        options:finalTagOptions,
                        onChange:function(e){return d({tagName:e})},
                        help:(0,i.__)("Choose semantic HTML tag","studio")
                    }),
                    h === 'custom' && React.createElement(n.TextControl,{
                        label:(0,i.__)("Custom Height","studio"),
                        value:m||'',
                        onChange:function(e){return d({minHeight:e})},
                        help:(0,i.__)("Enter custom height (e.g., 300px, 50vh)","studio")
                    })
                ),
                React.createElement(n.PanelBody,{title:(0,i.__)("Block Presets","studio"),initialOpen:false},
                    saveNotice && React.createElement(n.Notice,{
                        status:saveNotice.type,
                        isDismissible:false,
                        style:{marginBottom:'12px'}
                    }, saveNotice.message),
                    React.createElement(n.SelectControl,{
                        label:(0,i.__)("Load Preset","studio"),
                        value:'',
                        options:savedPresets,
                        onChange:applyPreset,
                        help:(0,i.__)("Load a saved preset","studio")
                    }),
                    React.createElement(n.Button,{
                        variant:"secondary",
                        onClick:()=>setIsPresetModalOpen(true),
                        style:{width:'100%',marginBottom:'8px'}
                    }, (0,i.__)("Save Current as Preset","studio")),
                    React.createElement("p",{style:{fontSize:'12px',color:'#666',margin:'4px 0 0 0'}},
                        (0,i.__)("Save your current settings as a reusable preset","studio")
                    )
                )
            ),
            isPresetModalOpen && React.createElement(n.Modal,{
                title:(0,i.__)("Save Container Preset","studio"),
                onRequestClose:()=>{
                    setIsPresetModalOpen(false);
                    setPresetName('');
                },
                size:"medium"
            },
                React.createElement("div",{style:{padding:'16px 0'}},
                    React.createElement(n.TextControl,{
                        label:(0,i.__)("Preset Name","studio"),
                        value:presetName,
                        onChange:setPresetName,
                        placeholder:(0,i.__)("e.g., Hero Section, Content Block, etc.","studio"),
                        help:(0,i.__)("Give your preset a descriptive name","studio")
                    }),
                    React.createElement("div",{style:{
                        background:'#f8f9fa',
                        padding:'12px',
                        borderRadius:'4px',
                        margin:'16px 0',
                        fontSize:'13px'
                    }},
                        React.createElement("strong",null,(0,i.__)("Current Settings:","studio")),
                        React.createElement("br",null),
                        `Width: ${u||'content'}`,
                        React.createElement("br",null),
                        `Height: ${h||'auto'}`,
                        React.createElement("br",null),
                        `Padding: ${p||'medium'}`,
                        React.createElement("br",null),
                        `HTML Tag: ${c||'div'}`,
                        m && React.createElement(React.Fragment,null,React.createElement("br",null),`Custom Height: ${m}`)
                    ),
                    React.createElement("div",{style:{display:'flex',gap:'12px',justifyContent:'flex-end'}},
                        React.createElement(n.Button,{
                            variant:"tertiary",
                            onClick:()=>{
                                setIsPresetModalOpen(false);
                                setPresetName('');
                            }
                        }, (0,i.__)("Cancel","studio")),
                        React.createElement(n.Button,{
                            variant:"primary",
                            onClick:saveAsPreset,
                            disabled:!presetName.trim()
                        }, (0,i.__)("Save Preset","studio"))
                    )
                )
            ),
            React.createElement("div",g)
        )
    },
    save:function(e){
        var n=e.attributes,l=n.widthPreset,a=n.paddingPreset,h=n.heightPreset,r=n.tagName,o=n.minHeight,
            s=r||'div',
            d=t.useBlockProps.save({
                className:"studio-container width-".concat(l||'content'," padding-").concat(a||'medium'," height-").concat(h||'auto'),
                style: {
                    ...(o && o !== 'auto' ? { minHeight: o } : {}),
                    ...(h && h !== 'auto' && h !== 'custom' ? { minHeight: h } : {})
                }
            }),
            innerBlocksProps = (0,t.useInnerBlocksProps).save(d);
        return React.createElement(s,innerBlocksProps)
    }
})
}();
