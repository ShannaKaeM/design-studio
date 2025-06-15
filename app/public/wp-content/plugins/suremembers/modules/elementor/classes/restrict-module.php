<?php
/**
 * Elementor Restrict Module.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Modules\Elementor\Classes;

use SureMembers\Inc\Access_Groups;
use \Elementor\Base_Data_Control;

/**
 * Elementor Restriction class.
 */
class Restrict_Module extends Base_Data_Control {
	/**
	 * Controller name.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'suremembers_restrictions';
	}

	/**
	 * Setup default settings.
	 *
	 * @return array
	 */
	protected function get_default_settings() {
		return [
			'label_block'       => true,
			'rows'              => 3,
			'mycontrol_options' => [],
		];
	}

	/**
	 * Enqueue Controller scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_register_style( 'suremembers-elementor-restriction', SUREMEMBERS_URL . 'modules/elementor/css/style.css', [], SUREMEMBERS_VER );
		wp_enqueue_style( 'suremembers-elementor-restriction' );
		wp_register_script( 'suremembers-elementor-restriction-js', SUREMEMBERS_URL . 'modules/elementor/js/script.js', [ 'elementor-editor' ], SUREMEMBERS_VER, true );
		wp_enqueue_script( 'suremembers-elementor-restriction-js' );
		// Check and localize plans group conditionally.
		$localize_array = $this->localize_data();
		wp_localize_script( 'suremembers-elementor-restriction-js', 'suremembers_elementor', $localize_array );
	}

	/**
	 * Access plan localization data.
	 *
	 * @return array Localization Data.
	 */
	public function localize_data() {
		$get_access_groups = Access_Groups::get_active();
		$return            = [];
		foreach ( $get_access_groups as $key => $value ) {
			$return[] = [
				'id'    => $key,
				'title' => $value,
			];
		}
		if ( empty( $return ) ) {
			return [];
		}

		$localize_array['ajax_url']                  = admin_url( 'admin-ajax.php' );
		$localize_array['sure_member_access_groups'] = $return;
		$localize_array['suremembers_erb_security']  = current_user_can( 'edit_posts' ) ? wp_create_nonce( 'suremembers_erb_security' ) : '';
		return $localize_array;
	}

	/**
	 * Elementor Control.
	 *
	 * @return void
	 */
	public function content_template() {
		$create_new_url = Access_Groups::get_admin_url( [ 'page' => 'suremembers_rules' ] );
		?>
		<div class="elementor-control-field">
		<!-- Ignored in favor of elementor control. -->
		<label class="elementor-control-title">{
			{{ data.label }}
		}</label> <?php // phpcs:ignore WordPressVIPMinimum.Security.Mustache.OutputNotation ?>
		<div class="suremembers-erb-controller elementor-control-input-wrapper">
			<a href="<?php echo esc_url( $create_new_url ); ?>" target="_blank" class="suremember-elementor-no-restriction-access"><?php esc_html_e( 'Please Create Access Groups', 'suremembers' ); ?></a>
			<div class="suremembers-search-container">
				<input type="text" class="suremembers-erb-search-input" />
			</div>
			<div class="suremembers-search-result">

			</div>
		</div>
		</div>
		<?php
	}
}
