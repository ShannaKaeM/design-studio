<?php
/**
 * Add Elementor Controls.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Modules\Elementor\Classes;

use SureMembers\Inc\Traits\Get_Instance;
use \Elementor\Controls_Manager;


/**
 * Add Controls.
 *
 * @since 1.0.0
 */
class Add_Controls {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'elementor/element/common/_section_style/after_section_end', [ $this, 'elementor_add_section' ] );
		add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'elementor_add_section' ] );
		add_action( 'elementor/element/column/section_advanced/after_section_end', [ $this, 'elementor_add_section' ] );
		add_action( 'elementor/element/section/section_advanced/after_section_end', [ $this, 'elementor_add_section' ] );
		add_action(
			'elementor/element/common/restrict_for_elementor_section/before_section_end',
			[ $this, 'elementor_add_controls' ],
			10,
			2
		);
		add_action(
			'elementor/element/container/restrict_for_elementor_section/before_section_end',
			[ $this, 'elementor_add_controls' ],
			10,
			2
		);
		add_action(
			'elementor/element/column/restrict_for_elementor_section/before_section_end',
			[ $this, 'elementor_add_controls' ],
			10,
			2
		);
		add_action(
			'elementor/element/section/restrict_for_elementor_section/before_section_end',
			[ $this, 'elementor_add_controls' ],
			10,
			2
		);
	}

	/**
	 * Add Tab in elementor widget.
	 *
	 * @param object $element Elementor object.
	 * @return void
	 */
	public function elementor_add_section( $element ) {
		$element->start_controls_section(
			'restrict_for_elementor_section',
			[
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => __( 'Restrict This Block', 'suremembers' ),
			]
		);
		$element->end_controls_section();
	}

	/**
	 * Elementor add control.
	 *
	 * @param object $element Elementor object.
	 * @param array  $args Elementor settings.
	 * @return void
	 */
	public function elementor_add_controls( $element, $args ) {
		$element->add_control(
			'sureMemberShowOnRestriction',
			[
				'label'   => esc_html__( 'Show block when user ', 'suremembers' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'is_in'     => [
						'title' => esc_html__( 'Is In', 'suremembers' ),
						'icon'  => 'eicon-check-circle-o',
					],
					'is_not_in' => [
						'title' => esc_html__( 'Is Not In', 'suremembers' ),
						'icon'  => 'eicon-ban',
					],
				],
				'default' => 'is_in',
				'toggle'  => true,
			]
		);
		$element->add_control(
			'sureMemberRestrictions',
			[
				'label'   => __( 'Access Groups', 'suremembers' ),
				'type'    => 'suremembers_restrictions',
				'default' => [],
			]
		);
	}
}
