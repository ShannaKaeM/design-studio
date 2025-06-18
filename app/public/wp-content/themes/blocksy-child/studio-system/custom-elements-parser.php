<?php
/**
 * Studio Custom Elements Parser
 * Converts custom HTML elements to GenerateBlocks
 */

class Studio_Custom_Elements_Parser {
    
    private $element_mappings = [];
    
    public function __construct() {
        $this->register_element_mappings();
        add_filter('the_content', [$this, 'parse_content'], 5);
        add_filter('studio_parse_html', [$this, 'parse_html']);
    }
    
    /**
     * Register custom element to block mappings
     */
    private function register_element_mappings() {
        $this->element_mappings = [
            // Query elements
            'query-root' => [
                'block' => 'generateblocks/query-loop',
                'attributes' => [
                    'post-type' => 'queryPostType',
                    'posts-per-page' => 'postsPerPage',
                    'order-by' => 'orderBy',
                    'order' => 'order',
                    'meta-query' => 'metaQuery',
                    'tax-query' => 'taxQuery'
                ]
            ],
            'query-content' => [
                'block' => 'generateblocks/container',
                'wrapper' => true
            ],
            'query-item' => [
                'block' => 'generateblocks/container',
                'context' => 'loop-item'
            ],
            'query-pagination' => [
                'block' => 'generateblocks/query-pagination',
                'attributes' => [
                    'mid-size' => 'midSize'
                ]
            ],
            'query-no-results' => [
                'block' => 'generateblocks/container',
                'context' => 'no-results'
            ],
            
            // Accordion elements
            'accordion-root' => [
                'block' => 'generateblocks-pro/accordion',
                'container' => true
            ],
            'accordion-item' => [
                'block' => 'generateblocks-pro/accordion-item',
                'attributes' => [
                    'default-open' => 'defaultOpen'
                ]
            ],
            'accordion-trigger' => [
                'block' => 'generateblocks-pro/accordion-toggle'
            ],
            'accordion-content' => [
                'block' => 'generateblocks-pro/accordion-content'
            ],
            'accordion-icon' => [
                'block' => 'generateblocks/icon',
                'attributes' => [
                    'open-icon' => 'openIcon',
                    'close-icon' => 'closeIcon'
                ]
            ],
            
            // Tab elements
            'tabs-root' => [
                'block' => 'generateblocks-pro/tabs',
                'container' => true
            ],
            'tab-list' => [
                'block' => 'generateblocks-pro/tab-buttons'
            ],
            'tab-button' => [
                'block' => 'generateblocks-pro/tab-button',
                'attributes' => [
                    'tab-id' => 'tabId',
                    'active' => 'isActive'
                ]
            ],
            'tab-panels' => [
                'block' => 'generateblocks-pro/tab-panels'
            ],
            'tab-panel' => [
                'block' => 'generateblocks-pro/tab-panel',
                'attributes' => [
                    'tab-id' => 'tabId',
                    'active' => 'isActive'
                ]
            ],
            
            // Grid elements
            'grid-root' => [
                'block' => 'generateblocks/grid',
                'attributes' => [
                    'columns' => 'gridColumns',
                    'gap' => 'gridGap',
                    'responsive' => 'responsiveColumns'
                ]
            ],
            'grid-item' => [
                'block' => 'generateblocks/container',
                'context' => 'grid-item'
            ],
            
            // Container elements
            'section' => [
                'block' => 'generateblocks/container',
                'attributes' => [
                    'tag' => 'tagName',
                    'width' => 'containerWidth',
                    'spacing' => 'paddingTop'
                ]
            ],
            'card' => [
                'block' => 'generateblocks/container',
                'class' => 'studio-card'
            ],
            'hero' => [
                'block' => 'generateblocks/container',
                'class' => 'studio-hero'
            ],
            
            // Typography elements
            'heading' => [
                'block' => 'generateblocks/headline',
                'attributes' => [
                    'level' => 'element',
                    'size' => 'fontSize',
                    'weight' => 'fontWeight'
                ]
            ],
            'text' => [
                'block' => 'generateblocks/headline',
                'attributes' => [
                    'element' => 'element'
                ]
            ],
            
            // Form elements
            'form-root' => [
                'block' => 'generateblocks/container',
                'tag' => 'form'
            ],
            'form-field' => [
                'block' => 'generateblocks/container',
                'class' => 'form-field'
            ],
            'form-label' => [
                'block' => 'generateblocks/headline',
                'element' => 'label'
            ],
            'form-input' => [
                'shortcode' => '[contact-form-7]'
            ]
        ];
    }
    
    /**
     * Parse content and convert custom elements
     */
    public function parse_content($content) {
        if (!$this->should_parse($content)) {
            return $content;
        }
        
        return $this->parse_html($content);
    }
    
    /**
     * Check if content should be parsed
     */
    private function should_parse($content) {
        // Check if content contains any custom elements
        foreach (array_keys($this->element_mappings) as $element) {
            if (strpos($content, "<{$element}") !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Parse HTML and convert custom elements to blocks
     */
    public function parse_html($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $this->process_dom_node($dom);
        
        // Convert back to HTML
        $html = $dom->saveHTML();
        
        // Clean up encoding declaration
        $html = str_replace('<?xml encoding="utf-8" ?>', '', $html);
        
        return $html;
    }
    
    /**
     * Process DOM nodes recursively
     */
    private function process_dom_node($node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }
        
        $tagName = $node->tagName;
        
        // Check if this is a custom element we need to convert
        if (isset($this->element_mappings[$tagName])) {
            $this->convert_element($node);
        }
        
        // Process child nodes
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        
        foreach ($children as $child) {
            $this->process_dom_node($child);
        }
    }
    
    /**
     * Convert custom element to GenerateBlocks
     */
    private function convert_element($element) {
        $tagName = $element->tagName;
        $mapping = $this->element_mappings[$tagName];
        
        // Extract attributes
        $attributes = $this->extract_attributes($element, $mapping);
        
        // Generate block comment
        $blockComment = $this->generate_block_comment($mapping['block'], $attributes);
        
        // Create replacement structure
        $doc = $element->ownerDocument;
        
        // Create block wrapper
        $wrapper = $doc->createComment(' wp:' . $mapping['block'] . ' ' . json_encode($attributes) . ' ');
        $element->parentNode->insertBefore($wrapper, $element);
        
        // Create new container div
        $newElement = $doc->createElement('div');
        
        // Add classes
        $classes = ['gb-block'];
        if (isset($mapping['class'])) {
            $classes[] = $mapping['class'];
        }
        if ($element->hasAttribute('class')) {
            $classes[] = $element->getAttribute('class');
        }
        $newElement->setAttribute('class', implode(' ', $classes));
        
        // Move children
        while ($element->firstChild) {
            $newElement->appendChild($element->firstChild);
        }
        
        // Replace element
        $element->parentNode->replaceChild($newElement, $element);
        
        // Add closing comment
        $closer = $doc->createComment(' /wp:' . $mapping['block'] . ' ');
        $newElement->parentNode->insertBefore($closer, $newElement->nextSibling);
    }
    
    /**
     * Extract and map attributes
     */
    private function extract_attributes($element, $mapping) {
        $attributes = [];
        
        if (isset($mapping['attributes'])) {
            foreach ($mapping['attributes'] as $customAttr => $blockAttr) {
                if ($element->hasAttribute($customAttr)) {
                    $value = $element->getAttribute($customAttr);
                    
                    // Parse JSON attributes
                    if (in_array($customAttr, ['meta-query', 'tax-query'])) {
                        $value = json_decode($value, true);
                    }
                    
                    $attributes[$blockAttr] = $value;
                }
            }
        }
        
        // Add default attributes based on mapping
        if (isset($mapping['tag'])) {
            $attributes['tagName'] = $mapping['tag'];
        }
        
        if (isset($mapping['element'])) {
            $attributes['element'] = $mapping['element'];
        }
        
        return $attributes;
    }
    
    /**
     * Generate block comment
     */
    private function generate_block_comment($blockName, $attributes) {
        $comment = "<!-- wp:{$blockName}";
        
        if (!empty($attributes)) {
            $comment .= ' ' . json_encode($attributes);
        }
        
        $comment .= ' -->';
        
        return $comment;
    }
    
    /**
     * Convert custom elements string to blocks
     */
    public function convert_to_blocks($html) {
        // This would be used by AI or developers to convert HTML to block markup
        $blocks = [];
        
        // Parse HTML
        $parsed = $this->parse_html($html);
        
        // Convert to block format
        $blockContent = $this->html_to_block_markup($parsed);
        
        return $blockContent;
    }
    
    /**
     * Convert HTML to WordPress block markup
     */
    private function html_to_block_markup($html) {
        // This is a simplified version - in production would need more robust conversion
        $blockMarkup = $html;
        
        // Add WordPress block wrappers
        $blockMarkup = preg_replace('/<div class="gb-block([^"]*)">(.*?)<\/div>/s', 
            '<!-- wp:generateblocks/container -->
            <div class="gb-container gb-container-$1">$2</div>
            <!-- /wp:generateblocks/container -->', 
            $blockMarkup
        );
        
        return $blockMarkup;
    }
}

// Initialize the parser
new Studio_Custom_Elements_Parser();

/**
 * Helper function to convert custom HTML to blocks
 */
function studio_parse_custom_html($html) {
    $parser = new Studio_Custom_Elements_Parser();
    return $parser->convert_to_blocks($html);
}

/**
 * Documentation for AI training
 */
function studio_get_custom_elements_docs() {
    return [
        'query' => [
            'description' => 'Create dynamic content loops',
            'example' => '
<query-root post-type="post" posts-per-page="6" order-by="date" order="DESC">
    <query-content class="grid-layout">
        <query-item>
            <heading level="3">{title}</heading>
            <text>{excerpt}</text>
        </query-item>
    </query-content>
    <query-pagination mid-size="2"></query-pagination>
    <query-no-results>
        <text>No posts found.</text>
    </query-no-results>
</query-root>'
        ],
        'accordion' => [
            'description' => 'Create collapsible content sections',
            'example' => '
<accordion-root>
    <accordion-item default-open="true">
        <accordion-trigger>
            <heading level="3">Question 1</heading>
            <accordion-icon open-icon="minus" close-icon="plus"></accordion-icon>
        </accordion-trigger>
        <accordion-content>
            <text>Answer to question 1</text>
        </accordion-content>
    </accordion-item>
</accordion-root>'
        ],
        'tabs' => [
            'description' => 'Create tabbed content',
            'example' => '
<tabs-root>
    <tab-list>
        <tab-button tab-id="tab1" active="true">Tab 1</tab-button>
        <tab-button tab-id="tab2">Tab 2</tab-button>
    </tab-list>
    <tab-panels>
        <tab-panel tab-id="tab1" active="true">
            <text>Content for tab 1</text>
        </tab-panel>
        <tab-panel tab-id="tab2">
            <text>Content for tab 2</text>
        </tab-panel>
    </tab-panels>
</tabs-root>'
        ],
        'grid' => [
            'description' => 'Create responsive grid layouts',
            'example' => '
<grid-root columns="3" gap="20" responsive="2,1">
    <grid-item>
        <card>
            <heading level="3">Card 1</heading>
            <text>Card content</text>
        </card>
    </grid-item>
</grid-root>'
        ]
    ];
}