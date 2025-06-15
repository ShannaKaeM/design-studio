<?php
/**
 * Elementor Loader.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Modules\Elementor;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Modules\Elementor\Classes\Frontend_Restriction;
use SureMembers\Modules\Elementor\Classes\Restrict_Module;
use SureMembers\Modules\Elementor\Classes\Add_Controls;

/**
 * Elementor classes loader.
 */
class Elementor_Loader {
	use Get_Instance;

	/**
	 * Stores Elementor templates meta
	 *
	 * @var array
	 * @since 1.6.1
	 */
	const ELEMENTOR_TEMPLATE_TYPES = [
		'single_post'     => [
			'location'            => 'single',
			'condition_indicator' => 'post',
		],
		'single_product'  => [
			'location'            => 'single',
			'condition_indicator' => 'product',
		],
		'product_archive' => [
			'location'            => 'archive',
			'condition_indicator' => 'product_archive',
		],
	];

	/**
	 * Stores if the current page is overridden by Elementor or not (checks by ::is_elementor_template method) according to the location.
	 *
	 * @var array
	 * @since 1.6.1
	 */
	private static $cache_post_type_has_template = [];

	/**
	 * Stores Elementor Pro Conditions_Manager instance.
	 *
	 * @var false|\ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager
	 * @since 1.6.1
	 */
	private static $elementor_conditions_manager = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			Add_Controls::get_instance();
			add_action( 'elementor/controls/register', [ $this, 'load_elementor' ] );
		} else {
			Frontend_Restriction::get_instance();
		}
		add_filter( 'suremembers_should_redirect_to_custom_template', [ $this, 'redirect_to_custom_template' ], 10, 1 );
	}

	/**
	 * Load elementor custom controller.
	 *
	 * @param object $widgets_manager Elementor manager object.
	 * @return void
	 */
	public function load_elementor( $widgets_manager ) {
		$widgets_manager->register( new Restrict_Module() );
	}

	/**
	 * Checks whether custom template redirection is required.
	 *
	 * @param bool $status current status to redirect to custom template.
	 * @return bool
	 * @since 1.6.1
	 */
	public static function redirect_to_custom_template( $status ) {
		if ( self::is_elementor_template( 'single_post' ) ) {
			return true;
		}
		return $status;
	}

	/**
	 * Is the current page has an elementor template. Looks if the an Elementor template is applied to the current page or not.
	 *
	 * @param  string $elementor_template_type valid types: single|single_product|product_archive (keys of the self::ELEMENTOR_TEMPLATE_TYPES const array). To available params; see keys of the self::ELEMENTOR_TEMPLATE_TYPES array.
	 * @return bool
	 * @since 1.6.1
	 */
	public static function is_elementor_template( $elementor_template_type ) {
		if ( ! class_exists( '\ElementorPro\Plugin', false ) ) {
			return false;
		}

		if ( ! array_key_exists( $elementor_template_type, self::ELEMENTOR_TEMPLATE_TYPES ) ) {
			return false;
		}

		$location = self::ELEMENTOR_TEMPLATE_TYPES[ $elementor_template_type ]['location'];

		if ( array_key_exists( $elementor_template_type, self::$cache_post_type_has_template ) ) {
			return self::$cache_post_type_has_template[ $location ];
		}

		/**
		 * Elementor Conditions Manager.
		 *
		 * @var \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager $conditions_manager
		 * @since 1.6.1
		 */
		$conditions_manager = self::get_condition_manager();

		if ( ! is_object( $conditions_manager ) || ! method_exists( $conditions_manager, 'get_location_templates' ) ) {
			return false;
		}

		$templates = $conditions_manager->get_location_templates( $location );

		self::$cache_post_type_has_template[ $location ] = ( count( $templates ) > 0 );

		return self::$cache_post_type_has_template[ $location ];
	}

	/**
	 * Returns Condition_Manager instance of the Elementor Pro.
	 *
	 * @return false|\ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager
	 * @since 1.6.1
	 */
	private static function get_condition_manager() {
		if ( false !== self::$elementor_conditions_manager ) {
			return self::$elementor_conditions_manager;
		}

		if ( ! method_exists( '\ElementorPro\Modules\ThemeBuilder\Module', 'instance' ) ) {
			return false;
		}

		$theme_builder = (object) \ElementorPro\Modules\ThemeBuilder\Module::instance();

		if ( ! method_exists( $theme_builder, 'get_conditions_manager' ) ) {
			return false;
		}

		self::$elementor_conditions_manager = $theme_builder->get_conditions_manager();
		return self::$elementor_conditions_manager;
	}
}
