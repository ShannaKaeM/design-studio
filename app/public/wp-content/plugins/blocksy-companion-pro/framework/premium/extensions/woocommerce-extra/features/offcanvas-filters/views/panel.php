<?php

$class = 'ct-panel';

$behaviour = isset($_GET['filter_panel_position']) ? $_GET['filter_panel_position'] : blc_theme_functions()->blocksy_get_theme_mod('filter_panel_position', 'right');
$behavior = $behaviour . '-side';

$close_on_select = isset($_GET['filter_panel_close_on_select']) ? $_GET['filter_panel_close_on_select'] : blc_theme_functions()->blocksy_get_theme_mod('filter_panel_close_on_select', 'no');

$filter_panel_close_button_type = blc_theme_functions()->blocksy_get_theme_mod(
	'filter_panel_close_button_type',
	'type-1'
);

$filter_source = blc_theme_functions()->blocksy_get_theme_mod(
	'filter_source',
	'sidebar-woocommerce-offcanvas-filters'
);

if (isset($_GET['filter_source'])) {
	$filter_source = $_GET['filter_source'];
}

ob_start();
dynamic_sidebar($filter_source);
$content = ob_get_clean();

ob_start();
do_action('blocksy:pro:woo-extra:offcanvas-filters:top');
$content = ob_get_clean() . $content;

ob_start();
do_action('blocksy:pro:woo-extra:offcanvas-filters:bottom');
$content = $content . ob_get_clean();

$without_container = blocksy_html_tag(
	'div',
	[
		'class' => 'ct-panel-content',
	],
	'<div class="ct-panel-content-inner ct-sidebar">' . $content . '</div>'
);

echo blocksy_html_tag(
	'div',

	array_merge(
		[
			'id' => 'woo-filters-panel',
			'class' => $class,
			'data-behaviour' => $behavior,
			'role' => 'dialog',
			'aria-label' => __('Filters panel', 'blocksy-companion'),
			'tabindex' => '-1',
			'inert' => ''
		],
		$close_on_select === 'yes' ? ['data-close-on-select' => ''] : []
	),

	'<div class="ct-panel-inner">
	<div class="ct-panel-actions">
		<span class="ct-panel-heading">' . __('Available Filters', 'blocksy-companion') . '</span>
		<button class="ct-toggle-close" data-type="' . $filter_panel_close_button_type . '" aria-label="' . __('Close filters modal', 'blocksy-companion') . '">
			<svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
			<path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"/>
			</svg>
		</button>
	</div>' . $without_container . '</div>'
);
