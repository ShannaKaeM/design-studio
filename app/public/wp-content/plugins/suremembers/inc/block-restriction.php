<?php
/**
 * Block Restriction.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;

/**
 * Block Restriction
 *
 * @since  1.0.0
 */
class Block_Restriction {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		if ( ! is_admin() || empty(
			array_intersect_key(
				[
					'post_type' => 1,
					'action'    => 1,
				],
				$_GET //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			)
		) ) {
			add_filter( 'render_block', [ $this, 'render_block_template' ], 5, 2 );
		}
	}

	/**
	 * Filter function.
	 *
	 * @param string $block_content Block template.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function render_block_template( $block_content, $block ) {
		$block_attributes = isset( $block['attrs'] ) ? $block['attrs'] : [];
		if ( empty( $block_attributes['sureMemberRestrictions'] ) || ! is_array( $block_attributes['sureMemberRestrictions'] ) ) {
			return $block_content;
		}
		$check_user_has_access = Access_Groups::check_if_user_has_access( $block_attributes['sureMemberRestrictions'] );

		$user_condition = isset( $block_attributes['sureMemberShowOnRestriction'] ) ? esc_html( $block_attributes['sureMemberShowOnRestriction'] ) : 'is_in';

		if ( 'is_in' === $user_condition && $check_user_has_access ) {
			return $block_content;
		}

		// Handling the not_in condition.
		if ( 'is_not_in' === $user_condition && ! $check_user_has_access ) {
			return $block_content;
		}

		return '';
	}

	/**
	 * Block restrict content. Note : this function is not in use.
	 *
	 * @param array  $access_group_ids plan ids.
	 * @param array  $get_rules Access group rules.
	 * @param string $button_text preview button text.
	 * @return void
	 */
	public function restrict_text_wrapper( $access_group_ids, $get_rules, $button_text ) {
		$suremembers_redirect_url = ! empty( $get_rules['restrict']['redirect_url'] ) ? $get_rules['restrict']['redirect_url'] : '#';
		$preview_button_text      = ! empty( $get_rules['restrict']['preview_button'] ) ? $get_rules['restrict']['preview_button'] : __( 'Preview', 'suremembers' );
		$preview_button_text      = $button_text ? $button_text : $preview_button_text;
		$target_blank             = '#' !== $suremembers_redirect_url ? "target='_blank'" : '';
		?>
		<div class="suremembers-restrict-wrapper">
			<a class="suremembers-restrict-link" href="<?php echo esc_url( $suremembers_redirect_url ); ?>" <?php echo esc_attr( $target_blank ); ?>><?php echo esc_html( $preview_button_text ); ?></a>
		</div>
		<?php
	}
}
