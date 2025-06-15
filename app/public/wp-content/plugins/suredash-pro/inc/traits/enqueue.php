<?php
/**
 * Enqueue all styles and scripts
 *
 * @package SureDashboardPro
 * @since 1.0.0
 */

namespace SureDashboardPro\Inc\Traits;

/**
 * Trait Enqueue.
 *
 * @since 1.0.0
 */
trait Enqueue {
	/**
	 * Enqueue prefix
	 *
	 * @var string
	 */
	public $enqueue_prefix = 'portal_pro';

	/**
	 * Enqueue scripts
	 * This function should be called from the class constructor.
	 * It will add action to enqueue scripts.
	 * Further create a static function wp_enqueue_scripts() to enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts admin
	 * This function should be called from the class constructor
	 * It will add action to enqueue scripts in admin.
	 * Further create a static function admin_enqueue_scripts() to enqueue admin scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts_admin(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * This function does the actual enqueuing of scripts.
	 * It should be called from the static function wp_enqueue_scripts() or admin_enqueue_scripts() created in the class.
	 * It will register and enqueue the script.
	 * It will also localize the script if the localization array is not empty.
	 *
	 * @param string $hook               Hook name user wish to choose for new js file.
	 * @param string $path               Path to the script.
	 * @param array  $dependency         Array of dependencies required for this script.
	 * @param array  $localization_array Array of localization data, if required.
	 *                                   It should contain hook, object_name and data.
	 *                                   Example: [ 'hook' => 'example', 'object_name' => 'example', 'data' => $localized_data ].
	 * @param string $version            Version of the script.
	 *                                   Default is the plugin version.
	 * @return void
	 */
	public function register_enqueue_localize_script( $hook, $path, $dependency, $localization_array = [], $version = SUREDASH_PRO_VER ): void {
		$this->register_script( $hook, $path, $dependency, $version );
		$this->enqueue_script( $hook );

		if ( ! empty( $localization_array ) && is_array( $localization_array ) && ! empty( $localization_array['hook'] ) && ! empty( $localization_array['object_name'] ) && ! empty( $localization_array['data'] ) ) {
			$this->localize_script( $localization_array['hook'], $localization_array['object_name'], $localization_array['data'] );
		}
	}

	/**
	 * This function does the actual enqueuing of styles.
	 * It should be called from the static function wp_enqueue_scripts() or admin_enqueue_scripts() created in the class.
	 * It will register and enqueue the style.
	 *
	 * @param string $hook      Hook name user wish to choose for new css file.
	 * @param string $path      Path to the style.
	 * @param array  $dependency Array of dependencies required for this style.
	 * @param string $version   Version of the style.
	 * @return void
	 */
	public function register_enqueue_style( $hook, $path, $dependency, $version = SUREDASH_PRO_VER ): void {
		$this->register_style( $hook, $path, $dependency, $version );
		$this->enqueue_style( $hook );
	}

	/**
	 * Register script.
	 * This function should be called from the static function register_enqueue_localize_script() created in the class.
	 * But it can also be called directly if user wants to register the script only.
	 *
	 * @param string $hook      Hook name user wish to choose for new js file.
	 * @param string $path      Path to the script.
	 * @param array  $dependency Array of dependencies required for this script.
	 * @param string $version   Version of the script.
	 * @return void
	 */
	public function register_script( $hook, $path, $dependency, $version = SUREDASH_PRO_VER ): void {
		wp_register_script(
			$this->enqueue_prefix . '-' . $hook,
			$path,
			$dependency,
			$version,
			true
		);
	}

	/**
	 * Enqueue script.
	 * This function should be called from the static function register_enqueue_localize_script() created in the class.
	 * But it can also be called directly if user wants to enqueue the script which is already registered.
	 * This function should be called after the register_script() function.
	 * It will add prefix to the hook name and enqueue the script, should not be used for already existing scripts.
	 *
	 * @param string $hook Hook name user wish to choose for new js file.
	 * @return void
	 */
	public function enqueue_script( $hook ): void {
		wp_enqueue_script( $this->enqueue_prefix . '-' . $hook );
	}

	/**
	 * Localize script.
	 * This function should be called from the static function register_enqueue_localize_script() created in the class.
	 * But it can also be called directly if user wants to localize the script which is already registered.
	 * This function should be called after the enqueue_script() function.
	 * It will add prefix to the hook name and localize the script, should not be used for already existing scripts.
	 *
	 * @param string $hook        Hook name user wish to choose for new js file.
	 * @param string $object_name Name of the object to be used in js file.
	 * @param array  $data        Array of data to be localized.
	 * @return void
	 */
	public function localize_script( $hook, $object_name, $data ): void {
		wp_localize_script(
			$this->enqueue_prefix . '-' . $hook,
			$this->enqueue_prefix . '_' . $object_name,
			$data
		);
	}

	/**
	 * Register style.
	 * This function should be called from the static function register_enqueue_style() created in the class.
	 * But it can also be called directly if user wants to register the style only.
	 *
	 * @param string $hook       Hook name user wish to choose for new css file.
	 * @param string $path       Path to the style.
	 * @param array  $dependency Array of dependencies required for this style.
	 * @param string $version    Version of the style.
	 * @return void
	 */
	public function register_style( $hook, $path, $dependency, $version = SUREDASH_PRO_VER ): void {
		wp_register_style(
			$this->enqueue_prefix . '-' . $hook,
			$path,
			$dependency,
			$version
		);
	}

	/**
	 * Enqueue style.
	 * This function should be called from the static function register_enqueue_style() created in the class.
	 * But it can also be called directly if user wants to enqueue the style which is already registered.
	 * This function should be called after the register_style() function.
	 * It will add prefix to the hook name and enqueue the style, should not be used for already existing styles.
	 *
	 * @param string $hook Hook name user wish to choose for new css file.
	 * @return void
	 */
	public function enqueue_style( $hook ): void {
		wp_enqueue_style( $this->enqueue_prefix . '-' . $hook );
	}
}
