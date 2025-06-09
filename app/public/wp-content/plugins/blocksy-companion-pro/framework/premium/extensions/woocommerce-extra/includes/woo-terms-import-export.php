<?php

namespace Blocksy\Extensions\WoocommerceExtra;

class WooTermsImportExport {	
	public function __construct() {
		add_filter('blocksy_export_custom_data', [$this, 'export_terms_meta'], 10, 2);
		add_filter('woocommerce_product_import_inserted_product_object', [$this, 'set_taxonomy_meta'], 10, 2);
	}

	public function export_terms_meta($data) {
		$categories = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]);

		$brands = get_terms([
			'taxonomy' => 'product_brand',
			'hide_empty' => false,
		]);

		$attributes = wc_get_attribute_taxonomies();
		
		$formatted_categories = [];
		$formatted_brands = [];
		$formatted_attributes = [];

		if (! is_wp_error($categories)) {
			foreach ($categories as $category) {
				$category_meta = get_term_meta(
					$category->term_id,
					'blocksy_taxonomy_meta_options',
					true
				);

				$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);

				$formatted_categories[] = [
					'name' => $category->name,
					'thumb' => wp_get_attachment_url($thumbnail_id),
					'meta' => $category_meta
				];
			}
		}

		if (! is_wp_error($attributes)) {
			foreach ($attributes as $attribute) {
				$terms = get_terms([
					'taxonomy' => wc_attribute_taxonomy_name($attribute->attribute_name),
					'hide_empty' => false,
				]);

				$children_data = [];

				foreach ($terms as $term) {
					$term_meta = get_term_meta(
						$term->term_id,
						'blocksy_taxonomy_meta_options',
						true
					);

					$children_data[] = [
						'name' => $term->name,
						'meta' => $term_meta
					];
				}

				$formatted_attributes[] = [
					'name' => $attribute->attribute_label,
					'slug' => $attribute->attribute_name,
					'type' => $attribute->attribute_type,
					'children' => $children_data
				];
			}
		}

		if (! is_wp_error($brands)) {
			foreach ($brands as $brand) {
				$brand_meta = get_term_meta(
					$brand->term_id,
					'blocksy_taxonomy_meta_options',
					true
				);

				$thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);

				if (
					isset($brand_meta['image']['attachment_id'])
					&&
					! empty($brand_meta['image']['attachment_id'])
				) {
					$brand_meta['image'] = [
						'attachment_id' => $brand_meta['image']['attachment_id'],
						'url' => wp_get_attachment_url($brand_meta['image']['attachment_id'])
					];
				}

				$formatted_brands[] = [
					'name' => $brand->name,
					'thumb' => wp_get_attachment_url($thumbnail_id),
					'meta' => $brand_meta
				];
			}
		}

		$data['blocksy_product_categories'] = $formatted_categories;
		$data['blocksy_product_attributes'] = $formatted_attributes;
		$data['blocksy_product_brands'] = $formatted_brands;

		return $data;
	}

	public function set_taxonomy_meta($product, $data) {
		if (! $product instanceof \WC_Product) {
			return $product;
		}

		$parsed_data = \Blocksy\WooImportExport::get_import_file_data();

		$parsed_categories_data = blocksy_akg('blocksy_product_categories', $parsed_data, []);
		$parsed_brands_data = blocksy_akg('blocksy_product_brands', $parsed_data, []);
		$parsed_attributes_data = blocksy_akg('blocksy_product_attributes', $parsed_data, []);

		$product_id = $product->get_id();

		if (! empty($parsed_categories_data)) {
			$categories = [];

			foreach ($parsed_categories_data as $category_data) {
				$category = get_term_by('name', $category_data['name'], 'product_cat');

				if (! $category) {
					continue;
				}

				$category_id = $category->term_id;

				if (
					isset($category_data['thumb'])
					&&
					! empty($category_data['thumb'])
				) {
					$image = \Blocksy\WooImportExport::get_attachment_id_from_url($category_data['thumb'], $product->get_id());

					if (! is_wp_error($image)) {
						update_term_meta($category_id, 'thumbnail_id', $image);
					}
				}

				if (
					isset($category_data['meta'])
					&&
					! get_term_meta($category_id, 'blocksy_taxonomy_meta_options', true)
				) {
					update_term_meta($category_id, 'blocksy_taxonomy_meta_options', $category_data['meta']);
				}
			}
		}

		if (! empty($parsed_brands_data)) {
			$brands = [];

			foreach ($parsed_brands_data as $brand_data) {
				$brand = get_term_by('name', $brand_data['name'], 'product_brand');

				if (! $brand) {
					continue;
				}

				$brand_id = $brand->term_id;

				if (
					isset($brand_data['thumb'])
					&&
					! empty($brand_data['thumb'])
				) {
					$image = \Blocksy\WooImportExport::get_attachment_id_from_url($brand_data['thumb'], $product->get_id());

					if (! is_wp_error($image)) {
						update_term_meta($brand_id, 'thumbnail_id', $image);
					}
				}

				if (
					isset($brand_data['meta'])
					&&
					! get_term_meta($brand_id, 'blocksy_taxonomy_meta_options', true)
				) {
					if (isset($brand_data['meta']['image'])) {
						$image = \Blocksy\WooImportExport::get_attachment_id_from_url($brand_data['meta']['image']['url'], $product->get_id());

						if (! is_wp_error($image)) {
							$brand_data['meta']['image'] = [
								'attachment_id' => $image,
								'url' => wp_get_attachment_url($image)
							];
						}
					}

					update_term_meta($brand_id, 'blocksy_taxonomy_meta_options', $brand_data['meta']);
				}
			}
		}

		if (! empty($parsed_attributes_data)) {
			foreach (array_values(wc_get_attribute_taxonomies()) as $tax) {
				$taxonomy = (array) $tax;

				foreach ($parsed_attributes_data as $attribute_data) {
					if ($attribute_data['name'] !== $taxonomy['attribute_label']) {
						continue;
					}

					wc_update_attribute(
						$taxonomy['attribute_id'],
						[
							'type' => $attribute_data['type'] ?? 'select',
						]
					);
				}
			}

			foreach ($parsed_attributes_data as $attribute_data) {
				if (
					isset($attribute_data['children'])
					&&
					! empty($attribute_data['children'])
				) {
					foreach ($attribute_data['children'] as $term_data) {
						$term = get_term_by('name', $term_data['name'], wc_attribute_taxonomy_name($attribute_data['slug']));

						if (! $term) {
							continue;
						}

						$term_id = $term->term_id;

						if (
							isset($term_data['meta'])
							&&
							! get_term_meta($term_id, 'blocksy_taxonomy_meta_options', true)
						) {

							if (isset($term_data['meta']['image'])) {
								$image = \Blocksy\WooImportExport::get_attachment_id_from_url($term_data['meta']['image']['url'], $product->get_id());

								if (! is_wp_error($image)) {
									$term_data['meta']['image'] = [
										'attachment_id' => $image,
										'url' => wp_get_attachment_url($image)
									];
								}
							}

							update_term_meta($term_id, 'blocksy_taxonomy_meta_options', $term_data['meta']);
						}
					}
				}
			}
		}

		return $product;
	}
}