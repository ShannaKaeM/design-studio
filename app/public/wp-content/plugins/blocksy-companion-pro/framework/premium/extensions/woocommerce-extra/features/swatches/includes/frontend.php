<?php

namespace Blocksy\Extensions\WoocommerceExtra;

class SwatchesFrontend {
	public function render_variation_swatches($args) {
		$product = $args['product'];

		$taxonomies_to_show = blocksy_akg(
			'taxonomies_to_show',
			$args['layer'],
			[]
		);

		$product_attributes_source = blocksy_akg(
			'product_attributes_source',
			$args['layer'],
			'all'
		);

		$swatches_html = [];
		$attributes = $product->get_variation_attributes();

		$displayed_attributes = [];

		if ($product_attributes_source === 'custom') {
			$sorted = [
				'global' => [],
				'custom' => []
			];

			foreach ($attributes as $attribute_name => $options) {
				if (taxonomy_exists($attribute_name)) {
					$sorted['global'][$attribute_name] = $options;
				} else {
					$sorted['custom'][$attribute_name] = $options;
				}
			}

			foreach ($taxonomies_to_show as $layer => $values) {
				if (! $values['enabled']) {
					continue;
				}

				$woo_layer_name = wc_attribute_taxonomy_name($values['id']);
				$is_custom_attribute = $values['id'] === 'ct_custom_attributes';

				if (
					! in_array($woo_layer_name, array_keys($attributes))
					&&
					! in_array($values['id'], array_keys($attributes))
					&&
					! $is_custom_attribute
				) {
					continue;
				}

				$limits = [
					'limit_number_of_swatches' => blocksy_akg('limit_number_of_swatches', $values, 'no'),
					'limit_number' => blocksy_akg('limit', $values, 10)
				];

				if ($is_custom_attribute) {
					foreach ($sorted['custom'] as $attribute_name => $options) {
						$swatches_html[] = $this->render_single_attribute(
							$attribute_name,
							$options,
							array_merge($args, [
								'limits' => $limits
							])
						);

						$displayed_attributes[] = $attribute_name;
					}
				} else {
					$swatches_html[] = $this->render_single_attribute(
						$woo_layer_name,
						$sorted['global'][$woo_layer_name],
						array_merge($args, [
							'limits' => $limits
						])
					);

					$displayed_attributes[] = $woo_layer_name;
				}
			}
		} else {
			foreach ($attributes as $attribute_name => $options) {
				$swatches_html[] = $this->render_single_attribute(
					$attribute_name,
					$options,
					array_merge($args, [
						'limits' => [
							'limit_number_of_swatches' => blocksy_akg(
								'limit_number_of_swatches',
								$args['layer'],
								'no'
							),
							'limit_number' => blocksy_akg(
								'limit',
								$args['layer'],
								10
							)
						]
					])
				);

				$displayed_attributes[] = $attribute_name;
			}
		}

		$not_displayed_attributes = array_diff(
			array_keys($attributes),
			$displayed_attributes
		);

		// If there are attributes that are not displayed and those attributes
		// don't have a default value -- the form is incomplete.
		$not_displayed_without_defaults = [];

		foreach ($not_displayed_attributes as $not_displayed_attribute) {
			$default_value = $product->get_variation_default_attribute($not_displayed_attribute);

			if (! empty($default_value)) {
				continue;
			}

			$not_displayed_without_defaults[] = $not_displayed_attribute;
		}

		$swatches_html = implode('', $swatches_html);
		$attr = [
			'class' => 'ct-card-variation-swatches variations_form'
		];

		$json = blc_get_ext('woocommerce-extra')
			->utils
			->get_available_variations($product->get_id());

		$get_variations = count($json) <= apply_filters(
			'woocommerce_ajax_variation_threshold',
			30,
			$product
		);

		if (! $get_variations) {
			$hidden_fields_html = [];

			foreach ($not_displayed_attributes as $not_displayed_attribute) {
				$default_value = $product->get_variation_default_attribute($not_displayed_attribute);

				if (empty($default_value)) {
					continue;
				}

				ob_start();

				wc_dropdown_variation_attribute_options([
					'options' => [
						$default_value
					],
					'attribute' => $not_displayed_attribute,
					'product' => $product,
					'selected' => $default_value,
					'id' => strtolower(
						wp_unique_id(
							str_replace(
								'attribute_',
								'',
								$not_displayed_attribute
							) . '--'
						)
					)
				]);

				$select_hidden_result = ob_get_clean();

				$select_hidden_result = str_replace(
					'<select',
					'<select hidden',
					$select_hidden_result
				);

				$hidden_fields_html[] = $select_hidden_result;
			}

			$swatches_html .= implode('', $hidden_fields_html);
		}

		$swatches_html = blocksy_html_tag(
			'div',
			['class' => 'variations'],
			$swatches_html
		);

		$simple = new \WC_Product_Simple($product->get_id());

		$attr['data-product_variations'] = "false";
		$attr['data-product_id'] = $product->get_id();

		if ($get_variations) {
			$attr['data-product_variations'] = wc_esc_json(wp_json_encode($json));
		}

		$attr['data-dynamic-card-data'] = wc_esc_json(wp_json_encode([
			'variable' => [
				'text' => $product->add_to_cart_text(),
				'link' => $product->add_to_cart_url(),
				'price' => '<span class="price">' . $product->get_price_html() . '</span>',
			],

			'simple' => [
				'text' => $simple->add_to_cart_text(),
				'link' => $simple->add_to_cart_url()
			],

			'isCompleteVariationsForm' => count($not_displayed_without_defaults) === 0
		]));

		return blocksy_html_tag('div', $attr, $swatches_html);
	}

	public function get_swatch_html($args) {
		if (
			(
				is_admin()
				&&
				! defined('DOING_AJAX')
			)
			||
			empty($args['options'])
			||
			! $args['product']
		) {
			return '';
		}

		$result = '';

		$elements = $this->get_attribute_elements($args);

		foreach ($elements as $single_element) {
			$swatch_element = new SwatchElementRender($single_element);
			$result .= $swatch_element->get_output();
		}

		return $result;
	}

	private function is_selected($term_slug, $term_attribute, $product) {
		$maybe_current_variation = null;

		if (blc_theme_functions()->blocksy_manager()) {
			$maybe_current_variation = blc_theme_functions()->blocksy_manager()
				->woocommerce
				->retrieve_product_default_variation($product);
		}

		$attributes = $product->get_attributes();

		if ($maybe_current_variation) {
			$attributes = $maybe_current_variation->get_attributes();
		}

		$is_selected = false;

		if (isset($attributes[$term_attribute])) {
			$is_selected = $attributes[$term_attribute] === $term_slug;

			if ($product) {
				$is_selected = $term_slug === $product->get_variation_default_attribute(sanitize_title($term_attribute));
			}
		}

		$selected_key = 'attribute_' . sanitize_title($term_attribute);

		if (
			isset($_REQUEST[$selected_key])
			&&
			get_queried_object_id() === $product->get_id()
		) {
			$is_selected = wc_clean(
				wp_unslash(
					strtolower(
						$_REQUEST[$selected_key]
					)
				)
			) === strtolower($term_slug);
		}

		return $is_selected;
	}

	// element_slug
	// element_label
	//
	// element_type
	//
	// element_atts
	// is_selected
	//
	//
	// is_out_of_stock - we need to track out of stock status specifically
	//                   because there can be 3 states of a specific swatch:
	//                   - in stock
	//                   - out of stock
	//                   - no existing variation at all (disable click in this case)
	private function get_attribute_elements($args = []) {
		$result = [];

		$is_custom_attribute = ! taxonomy_exists($args['attribute']);

		$conf = new SwatchesConfig();
		$meta = $conf->get_attributes_meta($args['attribute']);

		if (! $is_custom_attribute) {
			$terms = wc_get_product_terms(
				$args['product']->get_id(),
				$args['attribute'],
				[
					'fields' => 'all',
				]
			);

			if (
				isset($args['limits']['limit_number_of_swatches'])
				&&
				$args['limits']['limit_number_of_swatches'] === 'yes'
			) {
				$terms = array_slice($terms, 0, $args['limits']['limit_number']);
			}

			foreach ($terms as $single_term) {
				if (! in_array($single_term->slug, $args['options'])) {
					continue;
				}

				$is_selected = $this->is_selected(
					$single_term->slug,
					$single_term->taxonomy,
					$args['product']
				);

				$swatch_type = blocksy_akg('swatch_type', $meta, 'inherit');

				$base_element = $conf->get_swatch_element_descriptor(
					$single_term,
					[
						'read_atts' => $swatch_type === 'inherit'
					]
				);

				$term_status = $this->get_term_status(
					$single_term->slug,
					$single_term->taxonomy,
					$args['product']
				);

				$base_element['is_selected'] = $is_selected;
				$base_element['is_out_of_stock'] = $term_status === 'out_of_stock';
				$base_element['is_invalid'] = $term_status === 'invalid';

				if ($swatch_type !== 'inherit') {
					$base_element['element_type'] = blocksy_akg(
						'swatch_type',
						$meta,
						'inherit'
					);

					$base_element['element_atts'] = blocksy_akg(
						implode('/', [
							'values',
							$single_term->term_id
						]),
						$meta,
						[]
					);
				}

				$result[] = $base_element;
			}
		}

		if ($is_custom_attribute) {
			$options = $args['options'];

			if (
				isset($args['limits']['limit_number_of_swatches'])
				&&
				$args['limits']['limit_number_of_swatches'] === 'yes'
			) {
				$options = array_slice($options, 0, $args['limits']['limit_number']);
			}

			$custom_attribute_slug = null;

			foreach ($args['product']->get_attributes() as $key => $value) {
				if ($value->get_name() !== $args['attribute']) {
					continue;
				}

				$custom_attribute_slug = $key;
			}

			if (! $custom_attribute_slug) {
				return [];
			}

			foreach ($options as $future_element) {
				$term_status = $this->get_term_status(
					$future_element,
					$custom_attribute_slug,
					$args['product']
				);

				$is_out_of_stock = $term_status === 'out_of_stock';

				$is_selected = $this->is_selected(
					$future_element,
					$custom_attribute_slug,
					$args['product']
				);

				$element = [
					'element_slug' => $future_element,
					'element_label' => $future_element,

					'is_selected' => $is_selected,
					'is_out_of_stock' => $is_out_of_stock,
					'is_invalid' => $term_status === 'invalid',

					'element_atts' => [],
					'element_type' => ''
				];

				$element['element_type'] = blocksy_akg(
					'swatch_type',
					$meta,
					'button'
				);

				$element['element_atts'] = isset($meta['values'][$future_element]) ? $meta['values'][$future_element] : [];

				$result[] = $element;
			}
		}

		return $result;
	}

	// valid | invalid | out_of_stock
	private function get_term_status($term_slug, $term_attribute, $product) {
		$attribute_terms_stock = blc_get_ext('woocommerce-extra')
			->utils
			->get_attributes_terms_stock($product, $term_attribute);

		if (in_array(null, $attribute_terms_stock['valid'])) {
			return 'valid';
		}

		$is_out_of_stock = in_array($term_slug, $attribute_terms_stock['out_of_stock']);

		if ($is_out_of_stock) {
			return 'out_of_stock';
		}

		if (! in_array($term_slug, $attribute_terms_stock['valid'])) {
			return 'invalid';
		}

		return 'valid';
	}

	public function render_single_attribute($attribute_name, $options, $args) {
		$product = $args['product'];

		$limits = [
			'limit_number_of_swatches' => 'no',
			'limit_number' => 0
		];

		if (isset($args['limits'])) {
			$limits = $args['limits'];
		}

		$conf = new SwatchesConfig();

		$type = $conf->get_attribute_type($attribute_name, [
			'product' => $product
		]);

		$html_attr = [
			'class' => 'ct-variation-swatches',
			'data-swatches-type' => $type,
			'data-attr' => $attribute_name
		];

		if ($type === 'color') {
			$html_attr['data-swatches-shape'] = blc_theme_functions()->blocksy_get_theme_mod(
				'color_swatch_shape',
				'round'
			);
		}

		if ($type === 'image') {
			$html_attr['data-swatches-shape'] = blc_theme_functions()->blocksy_get_theme_mod(
				'image_swatch_shape',
				'round'
			);
		}

		if ($type === 'button') {
			$html_attr['data-swatches-shape'] = blc_theme_functions()->blocksy_get_theme_mod(
				'button_swatch_shape',
				'round'
			);
		}

		if ($type === 'mixed') {
			$html_attr['data-swatches-shape'] = blc_theme_functions()->blocksy_get_theme_mod(
				'mixed_swatch_shape',
				'round'
			);
		}

		ob_start();
		wc_dropdown_variation_attribute_options([
			'options' => $options,
			'attribute' => $attribute_name,
			'product' => $product,
			'id' => strtolower(
				wp_unique_id(
					str_replace(
						'attribute_',
						'',
						$attribute_name
					) . '--'
				)
			)
		]);
		$content = ob_get_clean();

		if ($type !== 'select') {
			$content .= $this->get_swatch_html(
				array_merge(
					$args,
					[
						'options' => $options,
						'attribute' => $attribute_name,
						'product' => $product,
						'limits' => $limits
					]
				)
			);
		}

		return blocksy_html_tag(
			'div',
			$html_attr,
			$content
		);
	}
}

