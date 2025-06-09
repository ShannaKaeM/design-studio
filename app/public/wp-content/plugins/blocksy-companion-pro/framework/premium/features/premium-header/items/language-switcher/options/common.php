<?php

if (isset($prefix)) {
	$prefix .= '_';
} else {
	$prefix = '';
}

$options = [
	$prefix . 'language_type' => [
		'label' => __( 'Display Type', 'blocksy-companion' ),
		'type' => 'ct-checkboxes',
		'design' => 'block',
		'view' => 'text',
		'divider' => 'top',
		'value' => [
			'icon' => true,
			'label' => true,
		],

		'choices' => blocksy_ordered_keys([
			'icon' => __( 'Flag', 'blocksy-companion' ),
			'label' => __( 'Label', 'blocksy-companion' ),
		]),

		'sync' => [
			'id' => $sync_id
		]
	],

	blocksy_rand_md5() => [
		'type' => 'ct-condition',
		'condition' => [
			$prefix . 'language_type/label' => true
		],
		'options' => [
			$prefix . 'language_label' => [
				'label' => __( 'Label Style', 'blocksy-companion' ),
				'type' => 'ct-radio',
				'value' => 'long',
				'view' => 'text',
				'design' => 'block',
				'divider' => 'top',
				'choices' => [
					'long' => __( 'Long', 'blocksy-companion' ),
					'short' => __( 'Short', 'blocksy-companion' ),
				],
				'sync' => [
					'id' => $sync_id
				]
			],

			$prefix . 'language_label_position' => [
				'type' => 'ct-radio',
				'label' => __( 'Label Position', 'blocksy-companion' ),
				'value' => 'right',
				'view' => 'text',
				'divider' => 'top',
				'design' => 'block',
				'responsive' => [ 'tablet' => 'skip' ],
				'choices' => [
					'left' => __( 'Left', 'blocksy-companion' ),
					'right' => __( 'Right', 'blocksy-companion' ),
					'bottom' => __( 'Bottom', 'blocksy-companion' ),
				],
			],
		],
	],

];