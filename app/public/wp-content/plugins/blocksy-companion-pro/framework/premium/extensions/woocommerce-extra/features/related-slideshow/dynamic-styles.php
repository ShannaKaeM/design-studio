<?php

// related slideshow columns
if (blc_theme_functions()->blocksy_get_theme_mod('woocommerce_related_products_slideshow', 'default') === 'slider') {
	$related_slideshow_columns = blc_theme_functions()->blocksy_get_theme_mod('woocommerce_related_products_slideshow_columns', [
		'desktop' => 4,
		'tablet' => 3,
		'mobile' => 1,
	]);

	$related_slideshow_columns = blocksy_expand_responsive_value($related_slideshow_columns);

	$related_slideshow_columns['desktop'] = 'calc(100% / ' . $related_slideshow_columns['desktop'] . ')';
	$related_slideshow_columns['tablet'] = 'calc(100% / ' . $related_slideshow_columns['tablet'] . ')';
	$related_slideshow_columns['mobile'] = 'calc(100% / ' . $related_slideshow_columns['mobile'] . ')';

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.related [data-products], .upsells [data-products]',
		'variableName' => 'grid-columns-width',
		'value' => $related_slideshow_columns,
		'unit' => ''
	]);
}