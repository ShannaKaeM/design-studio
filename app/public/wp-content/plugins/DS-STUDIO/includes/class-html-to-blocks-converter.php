<?php
/**
 * DS-Studio HTML to WordPress Blocks Converter
 * 
 * Converts HTML with component classes into WordPress block JSON
 * that can be imported directly into the block editor
 */

if (!defined('ABSPATH')) {
    exit;
}

class DS_Studio_HTML_To_Blocks_Converter {
    
    private $component_mapping = [
        'section-outer' => 'core/group',
        'section' => 'core/group', 
        'section-header' => 'core/group',
        'section-title' => 'core/heading',
        'section-subtitle' => 'core/paragraph',
        'section-body' => 'core/columns',
        'card' => 'core/column',
        'card-header' => 'core/image',
        'card-body' => 'core/group',
        'card-title' => 'core/heading',
        'card-footer' => 'core/group',
        'button' => 'core/button',
        'section-footer' => 'core/group'
    ];
    
    private $component_css = [
        'section-outer' => 'width: 100%;',
        'section' => 'max-width: 48rem; margin: 0 auto; padding: 1.5rem;',
        'section-header' => 'text-align: center; margin-bottom: 1.5rem;',
        'section-title' => 'font-size: 4rem; color: #777777; font-weight: 700; margin: 0; line-height: 1.2;',
        'section-subtitle' => 'font-size: 0.8125rem; color: #7c8698; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.5rem;',
        'section-body' => 'display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;',
        'card' => 'border-radius: 0.75rem; overflow: hidden; background-color: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; cursor: pointer;',
        'card-header' => 'position: relative; aspect-ratio: 16 / 9; overflow: hidden;',
        'card-body' => 'position: absolute; bottom: 0; left: 0; right: 0; padding: 1.25rem; color: white; z-index: 10; background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));',
        'card-title' => 'font-size: 1.25rem; font-weight: 700; color: white; margin-bottom: 0.25rem; line-height: 1.2;',
        'card-footer' => 'display: flex; align-items: center; justify-content: space-between;',
        'button' => 'font-size: 0.8125rem; color: white; opacity: 0.9; display: flex; align-items: center; gap: 0.25rem; margin: 0; transition: all 0.15s ease;',
        'section-footer' => 'text-align: center; margin-top: 1.5rem;'
    ];
    
    public function __construct() {
        add_action('wp_ajax_ds_studio_convert_html_to_blocks', array($this, 'convert_html_to_blocks'));
    }
    
    /**
     * AJAX handler to convert HTML to WordPress blocks
     */
    public function convert_html_to_blocks() {
        if (!wp_verify_nonce($_POST['nonce'], 'ds_studio_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }
        
        if (empty($_POST['html'])) {
            wp_send_json_error('No HTML provided');
            return;
        }
        
        $html = stripslashes($_POST['html']);
        
        // Log the incoming HTML for debugging
        error_log('HTML to convert: ' . $html);
        
        $blocks = $this->parse_html_to_blocks($html);
        
        // Log the result for debugging
        error_log('Converted blocks: ' . print_r($blocks, true));
        
        if (empty($blocks)) {
            wp_send_json_error('No blocks could be generated from the provided HTML. Please check that your HTML contains valid component classes.');
            return;
        }
        
        $block_json = json_encode($blocks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        wp_send_json_success([
            'blocks' => $blocks,
            'block_json' => $block_json,
            'count' => count($blocks)
        ]);
    }
    
    /**
     * Parse HTML and convert to WordPress blocks
     */
    private function parse_html_to_blocks($html) {
        // Add error handling and debugging
        try {
            $dom = new DOMDocument();
            
            // Suppress warnings for malformed HTML
            libxml_use_internal_errors(true);
            
            // Load HTML with UTF-8 encoding
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            // Clear libxml errors
            libxml_clear_errors();
            
            $blocks = [];
            
            // Get all child elements from the HTML
            $xpath = new DOMXPath($dom);
            $elements = $xpath->query('//body/*');
            
            if ($elements->length === 0) {
                // If no body elements, try to get all elements
                $elements = $xpath->query('//*');
            }
            
            foreach ($elements as $element) {
                if ($element->nodeType === XML_ELEMENT_NODE) {
                    $block = $this->convert_element_to_block($element);
                    if ($block) {
                        $blocks[] = $block;
                    }
                }
            }
            
            return $blocks;
            
        } catch (Exception $e) {
            error_log('HTML to Blocks conversion error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convert DOM element to WordPress block
     */
    private function convert_element_to_block($element) {
        $classes = $this->get_element_classes($element);
        $component_class = $this->get_component_class($classes);
        
        // If no component class found, skip this element
        if (!$component_class) {
            return null;
        }
        
        $block_type = $this->component_mapping[$component_class] ?? 'core/group';
        
        $block = [
            'blockName' => $block_type,
            'attrs' => $this->get_block_attributes($element, $classes, $component_class),
            'innerBlocks' => [],
            'innerHTML' => '',
            'innerContent' => []
        ];
        
        // Handle content based on block type
        switch ($block_type) {
            case 'core/heading':
                $block['attrs']['content'] = $this->get_element_text_content($element);
                $block['attrs']['level'] = $this->get_heading_level($element);
                $block['innerHTML'] = '<h' . $block['attrs']['level'] . '>' . $block['attrs']['content'] . '</h' . $block['attrs']['level'] . '>';
                $block['innerContent'] = [$block['innerHTML']];
                break;
                
            case 'core/paragraph':
                $block['attrs']['content'] = $this->get_element_text_content($element);
                $block['innerHTML'] = '<p>' . $block['attrs']['content'] . '</p>';
                $block['innerContent'] = [$block['innerHTML']];
                break;
                
            case 'core/button':
                $block['attrs']['text'] = $this->get_element_text_content($element);
                $block['attrs']['url'] = $element->getAttribute('href') ?: '#';
                $block['innerHTML'] = '<div class="wp-block-button"><a class="wp-block-button__link" href="' . $block['attrs']['url'] . '">' . $block['attrs']['text'] . '</a></div>';
                $block['innerContent'] = [$block['innerHTML']];
                break;
                
            case 'core/image':
                $img = $element->getElementsByTagName('img')->item(0);
                if ($img) {
                    $block['attrs']['url'] = $img->getAttribute('src');
                    $block['attrs']['alt'] = $img->getAttribute('alt');
                    $block['innerHTML'] = '<figure class="wp-block-image"><img src="' . $block['attrs']['url'] . '" alt="' . $block['attrs']['alt'] . '"/></figure>';
                    $block['innerContent'] = [$block['innerHTML']];
                }
                break;
                
            case 'core/columns':
                $block['attrs']['isStackedOnMobile'] = true;
                break;
        }
        
        // Process child elements for container blocks
        if ($this->should_process_children($block_type)) {
            foreach ($element->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE) {
                    $child_block = $this->convert_element_to_block($child);
                    if ($child_block) {
                        $block['innerBlocks'][] = $child_block;
                        $block['innerContent'][] = null; // Placeholder for inner block
                    }
                }
            }
        }
        
        return $block;
    }
    
    /**
     * Get CSS classes from element
     */
    private function get_element_classes($element) {
        $class_attr = $element->getAttribute('class');
        return $class_attr ? explode(' ', $class_attr) : [];
    }
    
    /**
     * Get the main component class from classes array
     */
    private function get_component_class($classes) {
        foreach ($classes as $class) {
            if (array_key_exists($class, $this->component_mapping)) {
                return $class;
            }
        }
        return null;
    }
    
    /**
     * Get block attributes including CSS styles
     */
    private function get_block_attributes($element, $classes, $component_class) {
        $attrs = [];
        
        // Add CSS styles for the component
        if (isset($this->component_css[$component_class])) {
            $attrs['style'] = [
                'css' => $this->component_css[$component_class]
            ];
        }
        
        // Add any additional classes (excluding the component class)
        $additional_classes = array_filter($classes, function($class) use ($component_class) {
            return $class !== $component_class;
        });
        
        if (!empty($additional_classes)) {
            $attrs['className'] = implode(' ', $additional_classes);
        }
        
        return $attrs;
    }
    
    /**
     * Get text content from element
     */
    private function get_element_text_content($element) {
        return trim($element->textContent);
    }
    
    /**
     * Get heading level from element tag
     */
    private function get_heading_level($element) {
        $tag = strtolower($element->tagName);
        return (int) str_replace('h', '', $tag) ?: 2;
    }
    
    /**
     * Check if block type should process children
     */
    private function should_process_children($block_type) {
        $container_blocks = [
            'core/group',
            'core/columns',
            'core/column'
        ];
        
        return in_array($block_type, $container_blocks);
    }
    
    /**
     * Generate WordPress block editor JSON
     */
    public function generate_block_editor_json($blocks) {
        $block_content = '';
        
        foreach ($blocks as $block) {
            $block_content .= $this->serialize_block($block);
        }
        
        return $block_content;
    }
    
    /**
     * Serialize block to WordPress block format
     */
    private function serialize_block($block) {
        $block_name = $block['blockName'];
        $attrs = $block['attrs'] ?? [];
        $inner_blocks = $block['innerBlocks'] ?? [];
        
        $serialized = "<!-- wp:{$block_name}";
        
        if (!empty($attrs)) {
            $serialized .= ' ' . json_encode($attrs, JSON_UNESCAPED_SLASHES);
        }
        
        $serialized .= " -->\n";
        
        // Add inner blocks
        if (!empty($inner_blocks)) {
            foreach ($inner_blocks as $inner_block) {
                $serialized .= $this->serialize_block($inner_block);
            }
        }
        
        $serialized .= "<!-- /wp:{$block_name} -->\n\n";
        
        return $serialized;
    }
}

// Initialize the converter
new DS_Studio_HTML_To_Blocks_Converter();
