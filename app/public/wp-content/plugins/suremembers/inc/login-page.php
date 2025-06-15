<?php
/**
 * Handles Login page customizations options.
 *
 * @package Suremembers.
 * @since 1.5.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Settings;

/**
 * Login Page Customizations Class
 */
class Login_Page {

	use Get_Instance;

	/**
	 * Class Constructor.
	 */
	public function __construct() {
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_filter( 'login_headerurl', [ $this, 'filter_logo_link' ] );
	}

	/**
	 * Enqueue Styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'suremembers-login-page-style', SUREMEMBERS_URL . 'assets/css/login-page.css', [], SUREMEMBERS_VER );

		$login_page_settings = Settings::get_setting( SUREMEMBERS_LOGIN_FORM_SETTINGS );
		$logo_image          = isset( $login_page_settings['logo_image'] ) ? esc_url( $login_page_settings['logo_image'] ) : '';
		$custom_css          = '';

		if ( ! empty( $login_page_settings['primary_color'] ) ) {
			$primary_color = esc_attr( $login_page_settings['primary_color'] );
			$custom_css   .= ".wp-core-ui .button-primary, .wp-core-ui .button-primary:hover, .wp-core-ui .button-primary.active, .wp-core-ui .button-primary.active:focus, .wp-core-ui .button-primary.active:hover, .wp-core-ui .button-primary:active {
				background: #$primary_color;
				border-color: #$primary_color;
			}
			input[type=checkbox]:focus, input[type=color]:focus, input[type=date]:focus, input[type=datetime-local]:focus, input[type=datetime]:focus, input[type=email]:focus, input[type=month]:focus, input[type=number]:focus, input[type=password]:focus, input[type=radio]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=text]:focus, input[type=time]:focus, input[type=url]:focus, input[type=week]:focus, select:focus, textarea:focus {
				border-color: #$primary_color;
				box-shadow: 0 0 0 1px #$primary_color;
			}";

			$custom_css .= "body.login a:hover, .login #nav a:hover, .login #backtoblog a:hover {
				color: #$primary_color;
			}
			.login .message {
				border-left: 4px solid #$primary_color;
			}";
		}

		if ( ! empty( $login_page_settings['secondary_color'] ) ) {
			$secondary_color = esc_attr( $login_page_settings['secondary_color'] );
			$svg_check       = '<svg xmlns="http://www.w3.org/2000/svg" fill="#' . esc_attr( $secondary_color ) . '" viewBox="0 0 20 20"><path d="M14.83 4.89l1.34.94-5.81 8.38H9.02L5.78 9.67l1.34-1.25 2.57 2.4z" fill="P0000c4"/></svg>';
			$svg_check_url   = 'data:image/svg+xml;base64,' . base64_encode( $svg_check ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

			$custom_css .= ".wp-core-ui .button-secondary {
				color: #$secondary_color;
				border-color: #$secondary_color;
			}
			.wp-core-ui .button-secondary:hover, .wp-core-ui .button:focus {
				color: #$secondary_color;
			}
			.login .button.wp-hide-pw:focus {
				border-color: #$secondary_color;
				box-shadow: 0 0 0 1px #$secondary_color;
			}
			input[type=checkbox]:focus {
				border-color: #$secondary_color;
				box-shadow: 0 0 0 1px #$secondary_color;
			}
			input[type=checkbox]:checked::before {
				content: url('{$svg_check_url}')
			}";
		}

		if ( ! empty( $login_page_settings['text_color'] ) ) {
			$text_color  = esc_attr( $login_page_settings['text_color'] );
			$custom_css .= "body.login {
				color: #$text_color;
			}";
		}

		if ( ! empty( $login_page_settings['link_color'] ) ) {
			$link_color  = esc_attr( $login_page_settings['link_color'] );
			$custom_css .= "body.login a, .login #nav a, .login #backtoblog a {
				color: #$link_color;
			}";
		}

		if ( $login_page_settings['disable_logo'] ) {
			$custom_css .= '.login h1 {
				display: none;
			}';
		}

		if ( $login_page_settings['enable_transparent_form'] ) {
			$custom_css .= '.login form {
				background: transparent;
				border: transparent;
				box-shadow: none;
			}';
		} else {
			if ( ! empty( $login_page_settings['login_form_background'] ) ) {
				$login_form_background = esc_attr( $login_page_settings['login_form_background'] );
				$custom_css           .= ".login form {
					background: #$login_form_background;
				}";
			}

			if ( ! empty( $login_page_settings['login_form_border'] ) ) {
				$login_form_border = esc_attr( $login_page_settings['login_form_border'] );
				$custom_css       .= ".login form {
					border: 1px solid #$login_form_border;
				}";
			}
		}

		if ( $login_page_settings['custom_logo'] ) {
			if ( ! empty( $logo_image ) ) {
				$custom_css .= ".login h1 a, .login .wp-login-logo a {
					background-image: url($logo_image);
					background-size: contain;
				}";

				if ( $login_page_settings['logo_width'] ) {
					$logo_width  = esc_attr( $login_page_settings['logo_width'] );
					$custom_css .= ".login h1 a, .login .wp-login-logo a {
						width: {$logo_width}px;
					}";
				}

				if ( $login_page_settings['logo_height'] ) {
					$logo_height = esc_attr( $login_page_settings['logo_height'] );
					$custom_css .= ".login h1 a, .login .wp-login-logo a {
						height: {$logo_height}px;
					}";
				}
			}
		}

		if ( ! empty( $login_page_settings['background_color'] ) ) {
			$bg_color    = esc_attr( $login_page_settings['background_color'] );
			$custom_css .= "body.login {
				background: #$bg_color;
			}";
		}

		if ( $login_page_settings['enable_background_image'] ) {
			if ( ! empty( $login_page_settings['background_image'] ) ) {
				$bg_image    = esc_url( $login_page_settings['background_image'] );
				$bg_repeat   = esc_attr( $login_page_settings['background_repeat'] );
				$bg_position = esc_attr( $login_page_settings['background_position'] );
				$bg_position = str_replace( '-', ' ', $bg_position );
				$bg_size     = esc_attr( $login_page_settings['background_size'] );

				$custom_css .= "body.login {
					background: url('$bg_image');
					background-repeat: $bg_repeat;
					background-position: $bg_position;
					background-size: $bg_size;
				}";
			}
		}

		wp_add_inline_style( 'suremembers-login-page-style', $custom_css );
	}

	/**
	 * Filter Login page logo link.
	 *
	 * @param string $link Default URL.
	 * @return string Modified URL.
	 * @since 1.5.1
	 */
	public function filter_logo_link( $link ) {
		$login_page_settings = Settings::get_setting( SUREMEMBERS_LOGIN_FORM_SETTINGS );
		return $login_page_settings['custom_logo'] ? esc_url( home_url( '/' ) ) : $link;
	}

}
