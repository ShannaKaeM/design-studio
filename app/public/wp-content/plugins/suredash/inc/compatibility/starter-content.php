<?php
/**
 * Initialize Login/Register starter-content setup
 *
 * @package suredashboard
 * @since 1.0.0
 */

namespace SureDashboard\Inc\Compatibility;

use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * This class setup admin init
 *
 * @class Starter_Content
 */
class Starter_Content {
	use Get_Instance;

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * Get Login content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Login content.
	 */
	public function get_login_page_content() {
		// phpcs:disable WordPressVIPMinimum.Security.Mustache.OutputNotation

		return '<!-- wp:group {"align":"wide","style":{"color":{"background":"#f9fafb"},"dimensions":{"minHeight":"100vh"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group alignwide has-background" style="background-color:#f9fafb;min-height:100vh"><!-- wp:site-logo {"align":"center"} /-->

		<!-- wp:columns {"align":"wide","fontSize":"small"} -->
		<div class="wp-block-columns alignwide has-small-font-size"><!-- wp:column -->
		<div class="wp-block-column"></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"top","style":{"spacing":{"padding":{"top":"2rem","bottom":"0rem"},"blockGap":"0"},"border":{"width":"1px"}},"backgroundColor":"ast-global-color-5","fontSize":"small","borderColor":"ast-global-color-6","layout":{"type":"default"}} -->
		<div class="wp-block-column is-vertically-aligned-top has-border-color has-ast-global-color-6-border-color has-ast-global-color-5-background-color has-background has-small-font-size" style="border-width:1px;padding-top:2rem;padding-bottom:0rem"><!-- wp:post-title {"level":3,"align":"wide","style":{"spacing":{"padding":{"right":"var:preset|spacing|60","left":"var:preset|spacing|60","top":"0","bottom":"0"}}}} /-->

		<!-- wp:paragraph {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}}} -->
		<p style="padding-top:0;padding-right:var(--wp--preset--spacing--60);padding-bottom:0;padding-left:var(--wp--preset--spacing--60)">Don’t have an account? <a href="' . esc_url( home_url( '/portal-register/' ) ) . '" data-type="page">Sign up</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:columns {"verticalAlignment":"top"} -->
		<div class="wp-block-columns are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top","width":"100%"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:100%"><!-- wp:suredash/login {"block_id":"3a75e4b1","formBorderStyle":"none","formRowsGapSpace":25,"labelColor":"","linkColor":"","fieldsBackground":"","placeholderColor":"","fieldsColor":"","loginBackground":"#4338ca","loginColor":"","loginHBackground":"","loginHColor":"","showRegisterInfo":false,"facebookTopPadding":14,"facebookRightPadding":15,"facebookLeftPadding":15,"facebookBottomPadding":14,"enableGoogleLogin":true,"googleBackground":"","googleColor":"","googleTopPadding":14,"googleRightPadding":15,"googleLeftPadding":15,"googleBottomPadding":14,"eyeIconSize":19,"eyeIconColor":""} /--></div>
		<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column"></div>
		<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
		<!-- /wp:group -->';
	}

	/**
	 * Get Register content.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Register content.
	 */
	public function get_register_page_content() {
		// phpcs:disable WordPressVIPMinimum.Security.Mustache.OutputNotation

		return '<!-- wp:group {"align":"wide","style":{"dimensions":{"minHeight":"100vh"},"color":{"background":"#f9fafb"}},"layout":{"type":"constrained"}} -->
		<div class="wp-block-group alignwide has-background" style="background-color:#f9fafb;min-height:100vh"><!-- wp:site-logo {"align":"center"} /-->

		<!-- wp:columns {"align":"wide","fontSize":"small"} -->
		<div class="wp-block-columns alignwide has-small-font-size"><!-- wp:column -->
		<div class="wp-block-column"></div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"top","style":{"spacing":{"padding":{"top":"2rem","bottom":"0rem"},"blockGap":"0"},"border":{"width":"1px"}},"backgroundColor":"ast-global-color-5","fontSize":"small","borderColor":"ast-global-color-6","layout":{"type":"default"}} -->
		<div class="wp-block-column is-vertically-aligned-top has-border-color has-ast-global-color-6-border-color has-ast-global-color-5-background-color has-background has-small-font-size" style="border-width:1px;padding-top:2rem;padding-bottom:0rem"><!-- wp:post-title {"level":3,"align":"wide","style":{"spacing":{"padding":{"right":"var:preset|spacing|60","left":"var:preset|spacing|60","top":"0","bottom":"0"}}}} /-->

		<!-- wp:paragraph {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|60","right":"var:preset|spacing|60"}}}} -->
		<p style="padding-top:0;padding-right:var(--wp--preset--spacing--60);padding-bottom:0;padding-left:var(--wp--preset--spacing--60)">Already have an account? <a href="' . esc_url( home_url( '/portal-login/' ) ) . '" data-type="page">Log in here</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:columns {"verticalAlignment":"top"} -->
		<div class="wp-block-columns are-vertically-aligned-top"><!-- wp:column {"verticalAlignment":"top","width":"100%"} -->
		<div class="wp-block-column is-vertically-aligned-top" style="flex-basis:100%"><!-- wp:suredash/register {"formBorderStyle":"none","block_id":"58185566","btnSubmitLabel":"Sign up","registerBtnBgColor":"#4338ca","googlePaddingBtnTop":14,"googlePaddingBtnRight":15,"googlePaddingBtnBottom":14,"googlePaddingBtnLeft":15,"facebookPaddingBtnTop":14,"facebookPaddingBtnRight":15,"facebookPaddingBtnBottom":14,"facebookPaddingBtnLeft":15} -->
		<div class="wp-block-suredash-register wp-block-spectra-pro-register uagb-block-58185566"><form action="#" class="spectra-pro-register-form" method="post" name="spectra-pro-register-form-58185566" id="spectra-pro-register-form-58185566"><input type="hidden" name="_nonce" value="ssr_nonce_replace"/><!-- wp:suredash/register-username {"block_id":"291286b8","name":"username"} -->
		<div class="wp-block-suredash-register-username spectra-pro-register-form__username uagb-block-291286b8"><label for="spectra-pro-register-form__username-input-291286b8" class="spectra-pro-register-form__username-label " id="291286b8">Username</label><input id="spectra-pro-register-form__username-input-291286b8" type="text" placeholder="Username" class="spectra-pro-register-form__username-input" name="username"/></div>
		<!-- /wp:suredash/register-username -->

		<!-- wp:suredash/register-email {"block_id":"08d2ab15","name":"email"} -->
		<div class="wp-block-suredash-register-email spectra-pro-register-form__email uagb-block-08d2ab15"><label for="spectra-pro-register-form__email-input-08d2ab15" class="spectra-pro-register-form__email-label required" id="08d2ab15">Email</label><input id="spectra-pro-register-form__email-input-08d2ab15" type="email" class="spectra-pro-register-form__email-input" placeholder="Email" required name="email"/></div>
		<!-- /wp:suredash/register-email -->

		<!-- wp:suredash/register-password {"block_id":"a71f6c17"} -->
		<div class="wp-block-suredash-register-password spectra-pro-register-form__password uagb-block-a71f6c17"><label for="spectra-pro-register-form__password-input-a71f6c17" class="spectra-pro-register-form__password-label required" id="a71f6c17">Password</label><input id="spectra-pro-register-form__password-input-a71f6c17" type="password" class="spectra-pro-register-form__password-input" placeholder="Password" required name="password"/></div>
		<!-- /wp:suredash/register-password --><div class="wp-block-button"><button class="spectra-pro-register-form__submit wp-block-button__link" type="submit"><span class="label-wrap">Sign up</span></button></div></form><div class="spectra-pro-register-form-status"></div><div class="spectra-pro-register-form__footer"><p class="spectra-pro-register-login-info"> Already have an account? <a rel="noopener" href="' . esc_url( home_url( '/portal-login/' ) ) . '" class="spectra-pro-register-form-link">Login</a> </p></div></div>
		<!-- /wp:suredash/register --></div>
		<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column"></div>
		<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
		<!-- /wp:group -->';
	}

	/**
	 * Create Login/Register on plugin activation.
	 *
	 * Consider templates/docs-page.php as a block code editor markup.
	 *
	 * @since 1.0.0
	 */
	public function create_pages(): void {
		$login_page            = Helper::get_option( 'login_page' );
		$login_setting_page_id = is_array( $login_page ) && ! empty( $login_page['value'] ) ? absint( $login_page['value'] ) : 0;
		if ( ! $login_setting_page_id ) {
			$page_title = 'Portal Login';
			$login_page = [
				'post_title'   => $page_title,
				'post_content' => $this->get_login_page_content(),
				'post_status'  => 'publish',
				'post_type'    => 'page',
			];

			$login_page_id = sd_wp_insert_post( $login_page );

			// Theme settings compatibility.
			if ( $login_page_id ) {
				$login_page_id = absint( $login_page_id ); // @phpstan-ignore-line
				if ( suredash_is_on_astra_theme() ) {
					sd_update_post_meta( $login_page_id, 'ast-site-content-layout', 'full-width-container' );
					sd_update_post_meta( $login_page_id, 'theme-transparent-header-meta', 'disabled' );
					sd_update_post_meta( $login_page_id, 'site-sidebar-layout', 'no-sidebar' );
					sd_update_post_meta( $login_page_id, 'site-post-title', 'disabled' );
				}

				Helper::update_option(
					'login_page',
					[
						'label' => $page_title,
						'value' => strval( $login_page_id ),
					]
				);
			}
		}

		$register_page            = Helper::get_option( 'register_page' );
		$register_setting_page_id = is_array( $register_page ) && ! empty( $register_page['value'] ) ? absint( $register_page['value'] ) : 0;
		if ( ! $register_setting_page_id ) {
			$page_title    = 'Portal Register';
			$register_page = [
				'post_title'   => $page_title,
				'post_content' => $this->get_register_page_content(),
				'post_status'  => 'publish',
				'post_type'    => 'page',
			];

			$register_page_id = sd_wp_insert_post( $register_page );

			// Theme settings compatibility.
			if ( $register_page_id ) {
				$register_page_id = absint( $register_page_id ); // @phpstan-ignore-line
				if ( suredash_is_on_astra_theme() ) {
					sd_update_post_meta( $register_page_id, 'ast-site-content-layout', 'full-width-container' );
					sd_update_post_meta( $register_page_id, 'theme-transparent-header-meta', 'disabled' );
					sd_update_post_meta( $register_page_id, 'site-sidebar-layout', 'no-sidebar' );
					sd_update_post_meta( $register_page_id, 'site-post-title', 'disabled' );
				}

				Helper::update_option(
					'register_page',
					[
						'label' => $page_title,
						'value' => strval( $register_page_id ),
					]
				);
			}
		}
	}
}
