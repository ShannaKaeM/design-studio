<?php

$common_options = blocksy_get_options(
	dirname(__FILE__) . '/common.php',
	[
		'sync_id' => $sync_id
	],
	false
);

$general_options = [
	$common_options,

	[
		'ls_items_spacing' => [
			'label' => __( 'Items Spacing', 'blocksy-companion' ),
			'type' => 'ct-slider',
			'min' => 5,
			'max' => 50,
			'value' => 20,
			'responsive' => true,
			'divider' => 'top',
		],

		'hide_current_language' => [
			'label' => __( 'Hide Current Language', 'blocksy-companion' ),
			'type' => 'ct-switch',
			'design' => 'inline',
			'divider' => 'top',
			'disableRevertButton' => true,
			'value' => 'no',
			'sync' => [
				'id' => $sync_id
			]
		],
	]
];

$design_options = [
	'ls_font' => [
		'type' => 'ct-typography',
		'label' => __( 'Font', 'blocksy-companion' ),
		'value' => blocksy_typography_default_values([
			'size' => '12px',
			'variation' => 'n6',
			'text-transform' => 'uppercase',
		])
	],

	blocksy_rand_md5() => [
		'type' => 'ct-labeled-group',
		'label' => __( 'Font Color', 'blocksy-companion' ),
		'responsive' => true,
		'choices' => [
			[
				'id' => 'ls_label_color',
				'label' => __('Default State', 'blocksy-companion')
			],

			[
				'id' => 'transparent_ls_label_color',
				'label' => __('Transparent State', 'blocksy-companion'),
				'condition' => [
					'row' => '!offcanvas',
					'builderSettings/has_transparent_header' => 'yes',
				],
			],

			[
				'id' => 'sticky_ls_label_color',
				'label' => __('Sticky State', 'blocksy-companion'),
				'condition' => [
					'row' => '!offcanvas',
					'builderSettings/has_sticky_header' => 'yes',
				],
			],
		],
		'options' => [

			'ls_label_color' => [
				'label' => __( 'Font Color', 'blocksy-companion' ),
				'type'  => 'ct-color-picker',
				'design' => 'block:right',
				'responsive' => true,

				'value' => [
					'default' => [
						'color' => 'var(--theme-text-color)',
					],

					'hover' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy-companion' ),
						'id' => 'default',
					],

					[
						'title' => __( 'Hover/Active', 'blocksy-companion' ),
						'id' => 'hover',
						'inherit' => 'var(--theme-link-hover-color)'
					],
				],
			],

			'transparent_ls_label_color' => [
				'label' => __( 'Font Color', 'blocksy-companion' ),
				'type'  => 'ct-color-picker',
				'design' => 'block:right',
				'responsive' => true,

				'value' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],

					'hover' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy-companion' ),
						'id' => 'default',
					],

					[
						'title' => __( 'Hover/Active', 'blocksy-companion' ),
						'id' => 'hover',
					],
				],
			],

			'sticky_ls_label_color' => [
				'label' => __( 'Font Color', 'blocksy-companion' ),
				'type'  => 'ct-color-picker',
				'design' => 'block:right',
				'responsive' => true,

				'value' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],

					'hover' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy-companion' ),
						'id' => 'default',
					],

					[
						'title' => __( 'Hover/Active', 'blocksy-companion' ),
						'id' => 'hover',
					],
				],
			],

		],
	],

	'ls_margin' => [
		'label' => __( 'Margin', 'blocksy-companion' ),
		'type' => 'ct-spacing',
		'divider' => 'top',
		'value' => blocksy_spacing_value(),
		'responsive' => true
	],
];

