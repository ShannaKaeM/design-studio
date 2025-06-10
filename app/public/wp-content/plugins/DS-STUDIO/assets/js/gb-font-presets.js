/**
 * GenerateBlocks Font Size Presets
 * 
 * Adds theme.json font size presets to GenerateBlocks typography controls
 */

const { addFilter } = wp.hooks;
const { createElement, Fragment } = wp.element;
const { SelectControl } = wp.components;

// Get font sizes from localized data
const fontSizes = window.dsStudioFontSizes?.fontSizes || [];

console.log('DS Studio GB Font Presets: Loaded with font sizes:', fontSizes);

/**
 * Add font size preset dropdown to GenerateBlocks typography controls
 */
function addFontSizePresets(OriginalComponent) {
    return function EnhancedFontSizeComponent(props) {
        // Only enhance if this is a font size control
        if (!props.label || !props.label.toLowerCase().includes('font size')) {
            return createElement(OriginalComponent, props);
        }

        console.log('DS Studio: Enhancing font size control with presets');

        // Create preset options
        const presetOptions = [
            { value: '', label: 'Select preset...' },
            ...fontSizes.map(size => ({
                value: size.size,
                label: `${size.name} (${size.size})`
            }))
        ];

        // Handle preset selection
        const handlePresetChange = (selectedSize) => {
            if (selectedSize && props.onChange) {
                props.onChange(selectedSize);
            }
        };

        return createElement(
            Fragment,
            null,
            // Font size preset dropdown
            fontSizes.length > 0 && createElement(SelectControl, {
                label: 'Font Size Presets',
                value: '',
                options: presetOptions,
                onChange: handlePresetChange,
                style: { marginBottom: '12px' }
            }),
            // Original font size control
            createElement(OriginalComponent, props)
        );
    };
}

/**
 * Filter GenerateBlocks UnitControl to add presets for font size
 */
addFilter(
    'generateblocks.typography.fontSize',
    'ds-studio/add-font-size-presets',
    addFontSizePresets
);

// Also try to hook into the component directly if the above doesn't work
document.addEventListener('DOMContentLoaded', function() {
    // Wait for GenerateBlocks to load
    setTimeout(() => {
        console.log('DS Studio: Attempting to enhance font size controls');
        
        // Look for font size controls and add presets
        const fontSizeControls = document.querySelectorAll('[id*="font-size"], [class*="font-size"]');
        
        fontSizeControls.forEach(control => {
            // Check if we already added presets
            if (control.previousElementSibling?.classList.contains('ds-studio-font-presets')) {
                return;
            }
            
            // Create preset dropdown
            const presetContainer = document.createElement('div');
            presetContainer.className = 'ds-studio-font-presets';
            presetContainer.style.marginBottom = '12px';
            
            const presetLabel = document.createElement('label');
            presetLabel.textContent = 'Font Size Presets';
            presetLabel.style.display = 'block';
            presetLabel.style.marginBottom = '4px';
            presetLabel.style.fontSize = '11px';
            presetLabel.style.fontWeight = '500';
            presetLabel.style.textTransform = 'uppercase';
            presetLabel.style.color = '#1e1e1e';
            
            const presetSelect = document.createElement('select');
            presetSelect.style.width = '100%';
            presetSelect.style.padding = '6px 8px';
            presetSelect.style.border = '1px solid #949494';
            presetSelect.style.borderRadius = '2px';
            presetSelect.style.fontSize = '13px';
            
            // Add options
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select preset...';
            presetSelect.appendChild(defaultOption);
            
            fontSizes.forEach(size => {
                const option = document.createElement('option');
                option.value = size.size;
                option.textContent = `${size.name} (${size.size})`;
                presetSelect.appendChild(option);
            });
            
            // Handle selection
            presetSelect.addEventListener('change', function() {
                if (this.value) {
                    // Find the input field and set its value
                    const input = control.querySelector('input[type="text"], input[type="number"]');
                    if (input) {
                        input.value = this.value;
                        
                        // Trigger change event
                        const event = new Event('input', { bubbles: true });
                        input.dispatchEvent(event);
                        
                        // Also try React's way
                        const reactEvent = new Event('change', { bubbles: true });
                        input.dispatchEvent(reactEvent);
                    }
                }
            });
            
            presetContainer.appendChild(presetLabel);
            presetContainer.appendChild(presetSelect);
            
            // Insert before the font size control
            control.parentNode.insertBefore(presetContainer, control);
        });
    }, 1000);
});
