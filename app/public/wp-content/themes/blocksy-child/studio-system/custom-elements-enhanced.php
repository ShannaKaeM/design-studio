<?php
/**
 * Enhanced Studio Custom Elements Parser
 * Implements Daniel's semantic HTML to blocks conversion
 */

class StudioCustomElementsEnhanced {
    
    private $element_mappings = [];
    private $ai_patterns = [];
    
    public function __construct() {
        $this->register_element_mappings();
        $this->register_ai_patterns();
    }
    
    /**
     * Register all custom element mappings
     */
    private function register_element_mappings() {
        $this->element_mappings = [
            // Layout Elements
            'hero' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'className' => 'studio-hero',
                    'sizing' => ['minHeight' => '80vh'],
                    'display' => ['display' => 'flex', 'flexDirection' => 'column', 'justifyContent' => 'center']
                ],
                'attributes' => [
                    'type' => 'heroType',
                    'background' => 'backgroundType',
                    'height' => 'minHeight',
                    'overlay' => 'overlayOpacity'
                ]
            ],
            'hero-title' => [
                'block' => 'generateblocks/headline',
                'defaults' => [
                    'element' => 'h1',
                    'className' => 'studio-hero-title'
                ]
            ],
            'hero-content' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'className' => 'studio-hero-content'
                ]
            ],
            'hero-cta' => [
                'block' => 'generateblocks/button-container',
                'defaults' => [
                    'className' => 'studio-hero-cta'
                ]
            ],
            
            // Card Elements
            'card' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'className' => 'studio-card',
                    'spacing' => ['paddingTop' => '30px', 'paddingRight' => '30px', 'paddingBottom' => '30px', 'paddingLeft' => '30px']
                ],
                'attributes' => [
                    'shadow' => 'boxShadow',
                    'hover' => 'hoverEffect',
                    'radius' => 'borderRadius'
                ]
            ],
            'card-header' => [
                'block' => 'generateblocks/container',
                'defaults' => ['className' => 'studio-card-header']
            ],
            'card-body' => [
                'block' => 'generateblocks/container',
                'defaults' => ['className' => 'studio-card-body']
            ],
            'card-footer' => [
                'block' => 'generateblocks/container',
                'defaults' => ['className' => 'studio-card-footer']
            ],
            
            // Dynamic Query Elements
            'query-root' => [
                'block' => 'generateblocks/query-loop',
                'attributes' => [
                    'post-type' => 'postType',
                    'posts-per-page' => 'postsPerPage',
                    'order-by' => 'orderBy',
                    'order' => 'order',
                    'meta-query' => 'metaQuery',
                    'tax-query' => 'taxQuery',
                    'offset' => 'offset',
                    'include' => 'include',
                    'exclude' => 'exclude'
                ]
            ],
            
            // Interactive Elements
            'accordion-root' => [
                'block' => 'generateblocks-pro/accordion',
                'attributes' => [
                    'allow-multiple' => 'allowMultiple',
                    'start-open' => 'startOpen',
                    'transition' => 'transitionDuration'
                ]
            ],
            
            'tabs-root' => [
                'block' => 'generateblocks-pro/tabs',
                'attributes' => [
                    'default-tab' => 'defaultTab',
                    'orientation' => 'orientation',
                    'mobile-collapse' => 'mobileCollapse'
                ]
            ],
            
            // Form Elements
            'form' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'tagName' => 'form',
                    'className' => 'studio-form'
                ],
                'attributes' => [
                    'method' => 'method',
                    'action' => 'action',
                    'ajax' => 'isAjax'
                ]
            ],
            
            // Grid System
            'row' => [
                'block' => 'generateblocks/grid',
                'defaults' => ['className' => 'studio-row']
            ],
            'col' => [
                'block' => 'generateblocks/container',
                'defaults' => ['className' => 'studio-col'],
                'attributes' => [
                    'width' => 'width',
                    'offset' => 'offset',
                    'order' => 'order'
                ]
            ],
            
            // Content Elements
            'section' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'tagName' => 'section',
                    'className' => 'studio-section'
                ]
            ],
            'article' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'tagName' => 'article',
                    'className' => 'studio-article'
                ]
            ],
            'aside' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'tagName' => 'aside',
                    'className' => 'studio-aside'
                ]
            ],
            
            // Media Elements
            'video-container' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'className' => 'studio-video-container',
                    'aspectRatio' => '16/9'
                ]
            ],
            'image-gallery' => [
                'block' => 'generateblocks/grid',
                'defaults' => [
                    'className' => 'studio-image-gallery'
                ]
            ],
            
            // Utility Elements
            'spacer' => [
                'block' => 'generateblocks/container',
                'defaults' => ['className' => 'studio-spacer'],
                'attributes' => [
                    'height' => 'minHeight'
                ]
            ],
            'divider' => [
                'block' => 'generateblocks/container',
                'defaults' => [
                    'className' => 'studio-divider',
                    'sizing' => ['height' => '1px'],
                    'backgroundColor' => 'var(--color-base-300)'
                ]
            ]
        ];
    }
    
    /**
     * Register AI-friendly patterns
     */
    private function register_ai_patterns() {
        $this->ai_patterns = [
            'hero_section' => '
<hero type="centered" background="gradient" height="80vh">
    <hero-title>Welcome to Our Site</hero-title>
    <hero-content>
        <p>Your compelling subtitle goes here</p>
    </hero-content>
    <hero-cta>
        <a href="#" class="button primary">Get Started</a>
        <a href="#" class="button secondary">Learn More</a>
    </hero-cta>
</hero>',
            
            'card_grid' => '
<row>
    <col width="4">
        <card shadow="medium" hover="lift">
            <card-header>
                <h3>Card Title</h3>
            </card-header>
            <card-body>
                <p>Card content goes here</p>
            </card-body>
            <card-footer>
                <a href="#" class="button">Read More</a>
            </card-footer>
        </card>
    </col>
</row>',
            
            'blog_query' => '
<query-root post-type="post" posts-per-page="6" order-by="date" order="DESC">
    <query-content class="grid-3">
        <query-item>
            <card>
                <query-featured-image size="medium" />
                <card-body>
                    <query-title level="3" />
                    <query-excerpt limit="20" />
                    <query-link text="Read More" class="button small" />
                </card-body>
            </card>
        </query-item>
    </query-content>
    <query-pagination />
</query-root>',
            
            'faq_accordion' => '
<accordion-root allow-multiple="true">
    <accordion-item>
        <accordion-trigger>
            <h3>Frequently Asked Question?</h3>
        </accordion-trigger>
        <accordion-content>
            <p>The answer to your question goes here.</p>
        </accordion-content>
    </accordion-item>
</accordion-root>'
        ];
    }
    
    /**
     * Convert custom HTML to GenerateBlocks
     */
    public function convert_to_blocks($html) {
        // Parse the HTML
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $body = $dom->getElementsByTagName('body')->item(0);
        $blocks = $this->process_node($body);
        
        return $this->blocks_to_markup($blocks);
    }
    
    /**
     * Process DOM node recursively
     */
    private function process_node($node) {
        $blocks = [];
        
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $block = $this->node_to_block($child);
                if ($block) {
                    $blocks[] = $block;
                }
            } elseif ($child->nodeType === XML_TEXT_NODE) {
                $text = trim($child->textContent);
                if (!empty($text)) {
                    // Wrap text in paragraph block
                    $blocks[] = [
                        'blockName' => 'core/paragraph',
                        'attrs' => [],
                        'innerContent' => [$text]
                    ];
                }
            }
        }
        
        return $blocks;
    }
    
    /**
     * Convert node to block
     */
    private function node_to_block($node) {
        $tagName = $node->tagName;
        
        if (isset($this->element_mappings[$tagName])) {
            $mapping = $this->element_mappings[$tagName];
            $attrs = $this->extract_attributes($node, $mapping);
            
            // Merge with defaults
            if (isset($mapping['defaults'])) {
                $attrs = array_merge($mapping['defaults'], $attrs);
            }
            
            // Process children
            $innerBlocks = $this->process_node($node);
            
            return [
                'blockName' => $mapping['block'],
                'attrs' => $attrs,
                'innerBlocks' => $innerBlocks,
                'innerHTML' => '',
                'innerContent' => $this->generate_inner_content($innerBlocks)
            ];
        }
        
        // Handle standard HTML elements
        return $this->handle_standard_element($node);
    }
    
    /**
     * Extract and map attributes
     */
    private function extract_attributes($node, $mapping) {
        $attrs = [];
        
        // Map custom attributes to block attributes
        if (isset($mapping['attributes'])) {
            foreach ($mapping['attributes'] as $custom => $block) {
                if ($node->hasAttribute($custom)) {
                    $value = $node->getAttribute($custom);
                    
                    // Parse JSON attributes
                    if (in_array($custom, ['meta-query', 'tax-query'])) {
                        $value = json_decode($value, true);
                    }
                    
                    $attrs[$block] = $value;
                }
            }
        }
        
        // Handle class attribute
        if ($node->hasAttribute('class')) {
            $attrs['className'] = $node->getAttribute('class');
        }
        
        // Handle ID attribute
        if ($node->hasAttribute('id')) {
            $attrs['anchor'] = $node->getAttribute('id');
        }
        
        return $attrs;
    }
    
    /**
     * Handle standard HTML elements
     */
    private function handle_standard_element($node) {
        $tagName = $node->tagName;
        $blockMap = [
            'h1' => 'generateblocks/headline',
            'h2' => 'generateblocks/headline',
            'h3' => 'generateblocks/headline',
            'h4' => 'generateblocks/headline',
            'h5' => 'generateblocks/headline',
            'h6' => 'generateblocks/headline',
            'p' => 'core/paragraph',
            'ul' => 'core/list',
            'ol' => 'core/list',
            'blockquote' => 'core/quote',
            'pre' => 'core/code',
            'table' => 'core/table',
            'img' => 'core/image',
            'button' => 'generateblocks/button',
            'a' => 'generateblocks/button',
            'div' => 'generateblocks/container'
        ];
        
        if (isset($blockMap[$tagName])) {
            $attrs = [];
            
            // Special handling for headlines
            if (strpos($tagName, 'h') === 0) {
                $attrs['element'] = $tagName;
            }
            
            // Special handling for lists
            if ($tagName === 'ol') {
                $attrs['ordered'] = true;
            }
            
            // Handle classes and IDs
            if ($node->hasAttribute('class')) {
                $attrs['className'] = $node->getAttribute('class');
            }
            if ($node->hasAttribute('id')) {
                $attrs['anchor'] = $node->getAttribute('id');
            }
            
            // Process inner content
            $innerBlocks = $this->process_node($node);
            $innerContent = $node->textContent;
            
            return [
                'blockName' => $blockMap[$tagName],
                'attrs' => $attrs,
                'innerBlocks' => $innerBlocks,
                'innerHTML' => '',
                'innerContent' => empty($innerBlocks) ? [$innerContent] : $this->generate_inner_content($innerBlocks)
            ];
        }
        
        return null;
    }
    
    /**
     * Generate inner content array for block
     */
    private function generate_inner_content($innerBlocks) {
        if (empty($innerBlocks)) {
            return [];
        }
        
        $content = [];
        foreach ($innerBlocks as $index => $block) {
            $content[] = null; // Placeholder for inner block
        }
        
        return $content;
    }
    
    /**
     * Convert blocks array to WordPress markup
     */
    private function blocks_to_markup($blocks) {
        $markup = '';
        
        foreach ($blocks as $block) {
            $markup .= $this->block_to_markup($block);
        }
        
        return $markup;
    }
    
    /**
     * Convert single block to markup
     */
    private function block_to_markup($block) {
        $markup = '<!-- wp:' . $block['blockName'];
        
        if (!empty($block['attrs'])) {
            $markup .= ' ' . json_encode($block['attrs']);
        }
        
        $markup .= ' -->' . "\n";
        
        // Add inner content
        if (!empty($block['innerBlocks'])) {
            foreach ($block['innerBlocks'] as $innerBlock) {
                $markup .= $this->block_to_markup($innerBlock);
            }
        } elseif (!empty($block['innerContent'])) {
            $markup .= implode('', $block['innerContent']);
        }
        
        $markup .= "\n" . '<!-- /wp:' . $block['blockName'] . ' -->' . "\n";
        
        return $markup;
    }
    
    /**
     * Get AI training examples
     */
    public function get_ai_examples() {
        return $this->ai_patterns;
    }
    
    /**
     * Get element documentation
     */
    public function get_element_docs() {
        $docs = [];
        
        foreach ($this->element_mappings as $element => $mapping) {
            $docs[$element] = [
                'element' => $element,
                'converts_to' => $mapping['block'],
                'attributes' => isset($mapping['attributes']) ? array_keys($mapping['attributes']) : [],
                'defaults' => isset($mapping['defaults']) ? $mapping['defaults'] : []
            ];
        }
        
        return $docs;
    }
}