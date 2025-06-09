<?php

$top_level_common_options = blocksy_get_options(
	dirname(__FILE__) . '/common.php',
	[
		'sync_id' => $sync_id
	],
	false
);

$dropdown_options = blocksy_get_options(
	dirname(__FILE__) . '/common.php',
	[
		'sync_id' => $sync_id,
		'prefix' => 'dropdown'
	],
	false
);

$general_options = [
	$top_level_common_options,

	[
		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'language_type/custom_icon' => true ],
			'options' => [
				'custom_icon' => [
					'type' => 'icon-picker',
					'label' => __('Icon', 'blocksy-companion'),
					'design' => 'inline',
					'divider' => 'top',
					'value' => [
						'icon' => 'blc blc-globe',
					],
					'sync' => [
						'id' => $sync_id
					]
				],

				'ls_icon_size' => [
					'label' => __( 'Icon Size', 'blocksy-companion' ),
					'type' => 'ct-slider',
					'min' => 5,
					'max' => 50,
					'value' => 15,
					'responsive' => true,
					'divider' => 'top',
					'setting' => [ 'transport' => 'postMessage' ],
				],
			],
		],
	]
];

$bottom_options = [
	blocksy_rand_md5() => [
		'type' => 'ct-title',
		'label' => __( 'Dropdown Options', 'blocksy-companion' ),
	],

	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy-companion' ),
		'type' => 'tab',
		'options' => [

			$dropdown_options,

			'ls_dropdown_offset' => [
				'label' => __( 'Dropdown Top Offset', 'blocksy-companion' ),
				'type' => 'ct-slider',
				'value' => 15,
				'min' => 0,
				'max' => 50,
				'divider' => 'top',
			],

			'ls_dropdown_items_spacing' => [
				'label' => __( 'Items Vertical Spacing', 'blocksy-companion' ),
				'type' => 'ct-slider',
				'value' => 15,
				'min' => 5,
				'max' => 30,
			],

			'ls_dropdown_arrow' => [
				'label' => __( 'Dropdown Arrow', 'blocksy-companion' ),
				'type' => 'ct-switch',
				'value' => 'no',
				'divider' => 'top',
				'sync' => [
					'id' => $sync_id
				]
			],

		],
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy-companion' ),
		'type' => 'tab',
		'options' => [
			'ls_dropdown_font' => [
				'type' => 'ct-typography',
				'label' => __( 'Font', 'blocksy-companion' ),
				'value' => blocksy_typography_default_values(),
				'setting' => [ 'transport' => 'postMessage' ],
			],

			'ls_dropdown_font_color' => [
				'label' => __( 'Font Color', 'blocksy-companion' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'divider' => 'bottom',

				'value' => [
					'default' => [
						'color' => '#ffffff',
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
						'title' => __( 'Hover', 'blocksy-companion' ),
						'id' => 'hover',
						'inherit' => 'var(--theme-link-hover-color)'
					],
				],
			],

			'ls_dropdown_background' => [
				'label' => __( 'Background Color', 'blocksy-companion' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'divider' => 'bottom',

				'value' => [
					'default' => [
						'color' => '#29333C',
					],
				],

				'pickers' => [
					[
						'title' => __( 'Initial', 'blocksy-companion' ),
						'id' => 'default',
					],
				],
			],

			'ls_dropdown_divider' => [
				'label' => __( 'Items Divider', 'blocksy-companion' ),
				'type' => 'ct-border',
				'design' => 'inline',
				'divider' => 'bottom',
				'value' => [
					'width' => 1,
					'style' => 'dashed',
					'color' => [
						'color' => 'rgba(255, 255, 255, 0.1)',
					],
				]
			],

			'ls_dropdown_shadow' => [
				'label' => __( 'Shadow', 'blocksy-companion' ),
				'type' => 'ct-box-shadow',
				'design' => 'inline',
				// 'responsive' => true,
				'divider' => 'bottom',
				'value' => blocksy_box_shadow_value([
					'enable' => true,
					'h_offset' => 0,
					'v_offset' => 10,
					'blur' => 20,
					'spread' => 0,
					'inset' => false,
					'color' => [
						'color' => 'rgba(41, 51, 61, 0.1)',
					],
				])
			],

			'ls_dropdown_radius' => [
				'label' => __( 'Border Radius', 'blocksy-companion' ),
				'type' => 'ct-spacing',
				'value' => blocksy_spacing_value(),
				'inputAttr' => [
					'placeholder' => '2'
				],
				'min' => 0,
			],

		],
	],
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


	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [ 'language_type/custom_icon' => true ],
		'options' => [

			blocksy_rand_md5() => [
				'type' => 'ct-divider',
			],

			blocksy_rand_md5() => [
				'type' => 'ct-labeled-group',
				'label' => __( 'Icon Color', 'blocksy-companion' ),
				'responsive' => true,
				'choices' => [
					[
						'id' => 'ls_custom_icon_color',
						'label' => __('Default State', 'blocksy-companion')
					],

					[
						'id' => 'transparent_ls_custom_icon_color',
						'label' => __('Transparent State', 'blocksy-companion'),
						'condition' => [
							'row' => '!offcanvas',
							'builderSettings/has_transparent_header' => 'yes',
						],
					],

					[
						'id' => 'sticky_ls_custom_icon_color',
						'label' => __('Sticky State', 'blocksy-companion'),
						'condition' => [
							'row' => '!offcanvas',
							'builderSettings/has_sticky_header' => 'yes',
						],
					],
				],
				'options' => [

					'ls_custom_icon_color' => [
						'label' => __( 'Icon Color', 'blocksy-companion' ),
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
								'inherit' => 'var(--theme-text-color)'
							],

							[
								'title' => __( 'Hover/Active', 'blocksy-companion' ),
								'id' => 'hover',
								'inherit' => 'var(--theme-palette-color-2)'
							],
						],
					],

					'transparent_ls_custom_icon_color' => [
						'label' => __( 'Icon Color', 'blocksy-companion' ),
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

					'sticky_ls_custom_icon_color' => [
						'label' => __( 'Icon Color', 'blocksy-companion' ),
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
