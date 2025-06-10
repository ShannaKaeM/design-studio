/**
 * DS-Studio Utility Class Injector
 * 
 * Enhances GenerateBlocks class editor with intelligent utility class suggestions
 * Provides autocomplete, categorized suggestions, and visual previews
 */

(function() {
    'use strict';
    
    let utilityClasses = [];
    let categories = {};
    
    // Wait for WordPress and GenerateBlocks to be ready
    wp.domReady(function() {
        console.log('🛠️ DS-Studio Utility Class Injector: Starting...');
        
        // Check if utility data is available
        if (typeof dsStudioUtilities === 'undefined') {
            console.warn('DS-Studio: Utility class data not found');
            return;
        }
        
        utilityClasses = dsStudioUtilities.classes || [];
        categories = dsStudioUtilities.categories || {};
        
        console.log('🛠️ DS-Studio: Loaded', utilityClasses.length, 'utility classes');
        
        // Initialize utility class enhancements
        initClassFieldEnhancements();
        initUtilityClassPicker();
        initSmartSuggestions();
        
        console.log('✅ DS-Studio Utility Class Injector: Complete!');
    });
    
    /**
     * Enhance GenerateBlocks additional CSS classes field
     */
    function initClassFieldEnhancements() {
        console.log('🎯 Enhancing class fields...');
        
        // Hook into GenerateBlocks class field rendering
        wp.hooks.addFilter(
            'generateblocks.editor.additionalClasses',
            'ds-studio/enhanced-class-field',
            function(classField, props) {
                if (!classField || !classField.props) {
                    return classField;
                }
                
                // Add autocomplete functionality
                classField.props.autoComplete = 'off';
                classField.props.placeholder = 'Type utility class names... (e.g., bg-primary, p-lg)';
                
                // Add data attributes for our enhancement
                classField.props['data-ds-studio-enhanced'] = 'true';
                classField.props['data-block-type'] = props.name || 'unknown';
                
                return classField;
            }
        );
        
        // Enhance existing class fields in the DOM
        setTimeout(enhanceExistingClassFields, 1000);
    }
    
    /**
     * Enhance existing class fields in the DOM
     */
    function enhanceExistingClassFields() {
        const classFields = document.querySelectorAll('input[placeholder*="class"], input[id*="class"], textarea[placeholder*="class"]');
        
        classFields.forEach(field => {
            if (field.dataset.dsStudioEnhanced) return;
            
            field.dataset.dsStudioEnhanced = 'true';
            addAutocompleteToField(field);
        });
    }
    
    /**
     * Add autocomplete functionality to a field
     */
    function addAutocompleteToField(field) {
        let suggestionBox = null;
        let currentSuggestions = [];
        let selectedIndex = -1;
        
        // Create suggestion box
        function createSuggestionBox() {
            if (suggestionBox) return suggestionBox;
            
            suggestionBox = document.createElement('div');
            suggestionBox.className = 'ds-studio-suggestions';
            suggestionBox.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 4px 4px;
                max-height: 200px;
                overflow-y: auto;
                z-index: 999999;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                font-size: 12px;
                display: none;
            `;
            
            field.parentNode.style.position = 'relative';
            field.parentNode.appendChild(suggestionBox);
            
            return suggestionBox;
        }
        
        // Show suggestions
        function showSuggestions(query) {
            const box = createSuggestionBox();
            const suggestions = getSuggestions(query);
            
            if (suggestions.length === 0) {
                box.style.display = 'none';
                return;
            }
            
            currentSuggestions = suggestions;
            selectedIndex = -1;
            
            box.innerHTML = suggestions.map((suggestion, index) => `
                <div class="ds-suggestion" data-index="${index}" style="
                    padding: 8px 12px;
                    cursor: pointer;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div>
                        <strong>${suggestion.class}</strong>
                        <div style="color: #666; font-size: 11px;">${suggestion.description}</div>
                    </div>
                    <span style="
                        background: #f0f0f0;
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-size: 10px;
                        color: #666;
                    ">${suggestion.category}</span>
                </div>
            `).join('');
            
            box.style.display = 'block';
            
            // Add click handlers
            box.querySelectorAll('.ds-suggestion').forEach((item, index) => {
                item.addEventListener('click', () => {
                    insertClass(suggestions[index].class);
                    box.style.display = 'none';
                });
                
                item.addEventListener('mouseenter', () => {
                    selectedIndex = index;
                    updateSelection();
                });
            });
        }
        
        // Hide suggestions
        function hideSuggestions() {
            if (suggestionBox) {
                suggestionBox.style.display = 'none';
            }
        }
        
        // Get suggestions based on query
        function getSuggestions(query) {
            if (!query || query.length < 2) return [];
            
            const words = query.toLowerCase().split(/\s+/);
            const lastWord = words[words.length - 1];
            
            return utilityClasses
                .filter(cls => 
                    cls.class.toLowerCase().includes(lastWord) ||
                    cls.description.toLowerCase().includes(lastWord)
                )
                .slice(0, 10);
        }
        
        // Insert class into field
        function insertClass(className) {
            const currentValue = field.value;
            const words = currentValue.split(/\s+/);
            
            // Replace the last word with the selected class
            words[words.length - 1] = className;
            
            field.value = words.join(' ') + ' ';
            field.focus();
            
            // Trigger change event
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Update selection highlighting
        function updateSelection() {
            if (!suggestionBox) return;
            
            suggestionBox.querySelectorAll('.ds-suggestion').forEach((item, index) => {
                if (index === selectedIndex) {
                    item.style.background = '#0073aa';
                    item.style.color = 'white';
                } else {
                    item.style.background = 'white';
                    item.style.color = 'black';
                }
            });
        }
        
        // Event listeners
        field.addEventListener('input', (e) => {
            const query = e.target.value;
            showSuggestions(query);
        });
        
        field.addEventListener('keydown', (e) => {
            if (!suggestionBox || suggestionBox.style.display === 'none') return;
            
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
                    updateSelection();
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateSelection();
                    break;
                    
                case 'Enter':
                case 'Tab':
                    if (selectedIndex >= 0) {
                        e.preventDefault();
                        insertClass(currentSuggestions[selectedIndex].class);
                        hideSuggestions();
                    }
                    break;
                    
                case 'Escape':
                    hideSuggestions();
                    break;
            }
        });
        
        field.addEventListener('blur', () => {
            // Delay hiding to allow clicks on suggestions
            setTimeout(hideSuggestions, 150);
        });
        
        field.addEventListener('focus', () => {
            if (field.value) {
                showSuggestions(field.value);
            }
        });
    }
    
    /**
     * Create utility class picker modal
     */
    function initUtilityClassPicker() {
        console.log('🎨 Initializing utility class picker...');
        
        // Create picker button
        function addPickerButton(field) {
            if (field.nextElementSibling && field.nextElementSibling.classList.contains('ds-picker-btn')) {
                return;
            }
            
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'ds-picker-btn button button-secondary';
            button.textContent = '🎨 Browse Classes';
            button.style.cssText = 'margin-left: 8px; font-size: 11px;';
            
            button.addEventListener('click', () => openUtilityPicker(field));
            
            field.parentNode.appendChild(button);
        }
        
        // Open utility class picker modal
        function openUtilityPicker(targetField) {
            const modal = createPickerModal(targetField);
            document.body.appendChild(modal);
            modal.style.display = 'flex';
        }
        
        // Create picker modal
        function createPickerModal(targetField) {
            const modal = document.createElement('div');
            modal.className = 'ds-utility-picker-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 999999;
            `;
            
            const content = document.createElement('div');
            content.style.cssText = `
                background: white;
                border-radius: 8px;
                width: 90%;
                max-width: 800px;
                max-height: 80%;
                overflow: hidden;
                display: flex;
                flex-direction: column;
            `;
            
            content.innerHTML = `
                <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0;">🎨 DS-Studio Utility Classes</h2>
                    <button class="ds-close-modal" style="background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
                </div>
                <div style="display: flex; flex: 1; overflow: hidden;">
                    <div class="ds-categories" style="width: 200px; border-right: 1px solid #eee; overflow-y: auto;">
                        ${Object.entries(categories).map(([key, cat]) => `
                            <div class="ds-category" data-category="${key}" style="
                                padding: 12px;
                                cursor: pointer;
                                border-bottom: 1px solid #eee;
                            ">
                                <strong>${cat.label}</strong>
                                <div style="font-size: 11px; color: #666;">${cat.description}</div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="ds-classes" style="flex: 1; padding: 20px; overflow-y: auto;">
                        <div id="ds-class-grid" style="
                            display: grid;
                            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                            gap: 10px;
                        "></div>
                    </div>
                </div>
            `;
            
            modal.appendChild(content);
            
            // Event handlers
            content.querySelector('.ds-close-modal').addEventListener('click', () => {
                modal.remove();
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
            
            // Category selection
            content.querySelectorAll('.ds-category').forEach(cat => {
                cat.addEventListener('click', () => {
                    const category = cat.dataset.category;
                    showCategoryClasses(content.querySelector('#ds-class-grid'), category, targetField);
                    
                    // Update active category
                    content.querySelectorAll('.ds-category').forEach(c => c.style.background = '');
                    cat.style.background = '#f0f0f0';
                });
            });
            
            // Show first category by default
            if (Object.keys(categories).length > 0) {
                const firstCategory = Object.keys(categories)[0];
                showCategoryClasses(content.querySelector('#ds-class-grid'), firstCategory, targetField);
                content.querySelector(`[data-category="${firstCategory}"]`).style.background = '#f0f0f0';
            }
            
            return modal;
        }
        
        // Show classes for a category
        function showCategoryClasses(container, category, targetField) {
            const categoryClasses = utilityClasses.filter(cls => cls.category === category);
            
            container.innerHTML = categoryClasses.map(cls => `
                <div class="ds-class-item" data-class="${cls.class}" style="
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: all 0.2s;
                ">
                    <div style="font-weight: bold; margin-bottom: 4px;">${cls.class}</div>
                    <div style="font-size: 11px; color: #666;">${cls.description}</div>
                    ${cls.value ? `<div style="font-size: 10px; color: #999; margin-top: 4px;">Value: ${cls.value}</div>` : ''}
                </div>
            `).join('');
            
            // Add click handlers
            container.querySelectorAll('.ds-class-item').forEach(item => {
                item.addEventListener('click', () => {
                    const className = item.dataset.class;
                    addClassToField(targetField, className);
                    item.style.background = '#0073aa';
                    item.style.color = 'white';
                    setTimeout(() => {
                        item.style.background = '';
                        item.style.color = '';
                    }, 200);
                });
                
                item.addEventListener('mouseenter', () => {
                    item.style.background = '#f0f0f0';
                });
                
                item.addEventListener('mouseleave', () => {
                    item.style.background = '';
                });
            });
        }
        
        // Add class to field
        function addClassToField(field, className) {
            const currentValue = field.value.trim();
            const newValue = currentValue ? `${currentValue} ${className}` : className;
            
            field.value = newValue;
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Add picker buttons to existing fields
        setTimeout(() => {
            const classFields = document.querySelectorAll('input[placeholder*="class"], input[id*="class"], textarea[placeholder*="class"]');
            classFields.forEach(addPickerButton);
        }, 1000);
    }
    
    /**
     * Initialize smart suggestions based on block context
     */
    function initSmartSuggestions() {
        console.log('🧠 Initializing smart suggestions...');
        
        // Context-aware suggestions
        const contextSuggestions = {
            'generateblocks/button': ['bg-primary', 'text-white', 'px-lg', 'py-md', 'rounded-base', 'font-semibold'],
            'generateblocks/container': ['max-w-screen-lg', 'mx-auto', 'px-md', 'py-lg'],
            'generateblocks/headline': ['text-xl', 'font-bold', 'text-primary', 'mb-md'],
            'generateblocks/grid': ['grid', 'gap-lg', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3']
        };
        
        // Add smart suggestion buttons
        function addSmartSuggestions(field, blockType) {
            if (!contextSuggestions[blockType]) return;
            
            const suggestions = contextSuggestions[blockType];
            const container = document.createElement('div');
            container.className = 'ds-smart-suggestions';
            container.style.cssText = 'margin-top: 8px; display: flex; flex-wrap: wrap; gap: 4px;';
            
            suggestions.forEach(suggestion => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'button button-small';
                button.textContent = suggestion;
                button.style.cssText = 'font-size: 10px; padding: 2px 6px;';
                
                button.addEventListener('click', () => {
                    addClassToField(field, suggestion);
                });
                
                container.appendChild(button);
            });
            
            field.parentNode.appendChild(container);
        }
        
        // Monitor for new fields and add suggestions
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        const classFields = node.querySelectorAll ? 
                            node.querySelectorAll('input[placeholder*="class"], input[id*="class"], textarea[placeholder*="class"]') : 
                            [];
                        
                        classFields.forEach(field => {
                            if (!field.dataset.dsStudioEnhanced) {
                                field.dataset.dsStudioEnhanced = 'true';
                                addAutocompleteToField(field);
                                
                                // Detect block type from context
                                const blockType = detectBlockType(field);
                                if (blockType) {
                                    addSmartSuggestions(field, blockType);
                                }
                            }
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * Detect block type from field context
     */
    function detectBlockType(field) {
        const panel = field.closest('[class*="generateblocks"]');
        if (!panel) return null;
        
        const className = panel.className;
        if (className.includes('button')) return 'generateblocks/button';
        if (className.includes('container')) return 'generateblocks/container';
        if (className.includes('headline')) return 'generateblocks/headline';
        if (className.includes('grid')) return 'generateblocks/grid';
        
        return null;
    }
    
    /**
     * Add utility class to field
     */
    function addClassToField(field, className) {
        const currentValue = field.value.trim();
        const classes = currentValue.split(/\s+/).filter(Boolean);
        
        // Avoid duplicates
        if (!classes.includes(className)) {
            classes.push(className);
            field.value = classes.join(' ');
            field.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    
})();
