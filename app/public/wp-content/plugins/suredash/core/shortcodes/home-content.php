<?php
/**
 * Portals Docs HomeContent Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureDashboard\Core\Integrations\SinglePost;
use SureDashboard\Core\Models\Controller;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

/**
 * Class HomeContent Shortcode.
 */
class HomeContent {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'home_content' );
	}

	/**
	 * Display content.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function render_home_content( $atts ) {
		$defaults = [
			'type' => 'home',
		];

		$atts = shortcode_atts( $defaults, $atts );

		$type = $atts['type'];

		switch ( $type ) {
			case 'home':
			default:
				$content = $this->get_home_content();
				break;

			case 'bookmarks':
				$content = $this->get_bookmarks_content();
				break;

			case 'user-profile':
				$content = $this->get_user_profile_content();
				break;

			case 'user-view':
				$content = $this->get_user_view_content();
				break;

			case 'feeds':
				$content = $this->get_feeds_posts();
				break;
		}

		return apply_filters( 'suredashboard_home_content', $content );
	}

	/**
	 * Get item markup for home content loop.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $skip_excerpt Skip excerpt.
	 * @since 1.0.0
	 * @return void
	 */
	public function get_item_markup( $post_id, $skip_excerpt = false ): void {
		$post_title   = PostMeta::get_post_meta_value( $post_id, 'post_title' );
		$post_link    = (string) get_permalink( $post_id );
		$post_excerpt = wp_trim_words( get_the_excerpt( $post_id ), 12, '...' );

		$thumbnail_html = Helper::get_space_featured_image( $post_id );

		ob_start();
		?>
			<div class="portal-home-post-item-content">
				<a href="<?php echo esc_url( $post_link ); ?>" data-id="<?php echo esc_attr( (string) $post_id ); ?>" class="portal-home-post-item">
					<?php echo wp_kses_post( $thumbnail_html ); ?>
					<?php echo esc_html( $post_title ); ?>
				</a>
				<?php if ( ! $skip_excerpt && ! empty( $post_excerpt ) ) { ?>
					<p class="sd-no-space"><?php echo esc_html( $post_excerpt ); ?></p>
				<?php } ?>
			</div>
		<?php
		echo wp_kses_post( (string) ob_get_clean() );
	}

	/**
	 * Get bookmarks content.
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_bookmarks_content() {
		$bookmarked_items = suredash_get_all_bookmarked_items();

		ob_start();

		if ( empty( $bookmarked_items ) ) {
			suredash_get_template_part(
				'parts',
				'404',
				[
					'not_found_text' => Labels::get_label( 'bookmark_not_found_text' ),
				]
			);
			return apply_filters( 'suredashboard_bookmark_content', ob_get_clean() );
		}

		// Capture all 'lesson' & 'portal' keys separately in an array.
		$lesson_keys = [];
		$portal_keys = [];
		$misc_keys   = [];

		foreach ( $bookmarked_items as $key => $value ) {
			if ( $value === 'lesson' ) {
				$lesson_keys[] = $key;
			} elseif ( $value === 'portal' ) {
				$portal_keys[] = $key;
			} else {
				$misc_keys[] = $key;
			}
		}

		// Display 'lesson' items.
		if ( ! empty( $lesson_keys ) ) {
			?>
			<div class="portal-content-area portal-content portal-home-grid">
				<div class="portal-home-post-grid-wrap">
					<h3 class="portal-home-post-title sd-no-space"><?php Labels::get_label( 'lesson_plural_text', true ); ?></h3>
					<section class="portal-home-posts-group">
					<?php
					foreach ( $lesson_keys as $lesson ) {
						$post_id = absint( $lesson );
						$this->get_item_markup( $post_id );
					}
					?>
					</section>
				</div>
			</div>
				<?php
		}

		// Display 'portal' items.
		if ( ! empty( $portal_keys ) ) {
			?>
			<div class="portal-content-area portal-content portal-home-grid">
				<div class="portal-home-post-grid-wrap">
					<h3 class="portal-home-post-title sd-no-space"> <?php Labels::get_label( 'portal_plural_text', true ); ?> </h3>
					<section class="portal-home-posts-group">
					<?php
					foreach ( $portal_keys as $portal ) {
						$post_id = absint( $portal );
						$this->get_item_markup( $post_id );
					}
					?>
					</section>
				</div>
			</div>
				<?php
		}

		// Display 'misc' items.
		if ( ! empty( $misc_keys ) ) {
			?>
			<div class="portal-content-area portal-content portal-home-grid">
				<div class="portal-home-post-grid-wrap">
					<h3 class="portal-home-post-title sd-no-space"> <?php Labels::get_label( 'misc_items_text', true ); ?> </h3>
					<section class="portal-home-posts-group">
					<?php
					foreach ( $misc_keys as $misc ) {
						$post_id = absint( $misc );
						$this->get_item_markup( $post_id );
					}
					?>
					</section>
				</div>
			</div>
				<?php
		}

		return ob_get_clean();
	}

	/**
	 * Get home content.
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_home_content() {
		$home_page    = Helper::get_option( 'home_page' );
		$home_page_id = is_array( $home_page ) && ! empty( $home_page['value'] ) ? absint( $home_page['value'] ) : 0;

		if ( $home_page_id ) {
			ob_start();
			if ( method_exists( SinglePost::get_instance(), 'get_integration_content' ) ) {
				echo '<div id="portal-post-' . esc_attr( (string) $home_page_id ) . '" class="portal-content-area sd-box-shadow entry-content portal-content-type-wordpress">';
				echo do_shortcode( apply_filters( 'the_content', SinglePost::get_instance()->get_integration_content( $home_page_id, true ), $home_page_id ) );
				echo '</div>';
			}
			return apply_filters( 'suredashboard_home_content', ob_get_clean() );
		}

		$results      = Controller::get_query_data(
			'Navigation',
		);
		$space_groups = array_reduce(
			$results,
			static function( $carry, $item ) {
				if ( is_array( $item ) && is_array( $carry ) ) {
					$carry[ $item['space_group_position'] ][] = $item;
				}
				return $carry;
			},
			[]
		);

		ksort( $space_groups );

		foreach ( $space_groups as &$group ) {
			$id_sequence = array_unique( explode( ',', strval( $group[0]['space_position'] ) ) );
			usort(
				$group,
				static function ( $a, $b ) use ( $id_sequence ) {
					$a_index = array_search( $a['ID'], $id_sequence );
					$b_index = array_search( $b['ID'], $id_sequence );
					return $a_index - $b_index;
				}
			);
		}

		ob_start();

		if ( empty( $space_groups ) ) {
			suredash_get_template_part( 'parts', '404' );
			return apply_filters( 'suredashboard_home_content', ob_get_clean() );
		}

		foreach ( $space_groups as $space_group ) {
			$space_group_name  = $space_group[0]['name'] ?? '';
			$show_group_header = false;
			$spaces_to_proceed = [];
			$counter           = 0;

			foreach ( $space_group as $space_item ) {
				if ( $space_item['post_status'] !== 'publish' ) {
					continue;
				}
				if ( isset( $space_item['integration'] ) && $space_item['integration'] === 'link' ) {
					continue;
				}
				$show_group_header   = true;
				$spaces_to_proceed[] = absint( $space_item['ID'] );
				$counter++;
				if ( $counter >= 3 ) {
					break;
				}
			}

			if ( $show_group_header ) {
				?>
					<div class="portal-content-area portal-content portal-home-grid">
						<div class="portal-home-post-grid-wrap">
							<h3 class="portal-home-post-title sd-no-space"><?php echo esc_html( $space_group_name ); ?></h3>
							<section class="portal-home-posts-group">
								<?php
								foreach ( $spaces_to_proceed as $space_item ) {
									$this->get_item_markup( $space_item, true );
								}
								?>
							</section>
						</div>
					</div>
				<?php
			}
		}

		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Show Feeds.
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_feeds_posts() {

		$feeds_posts = sd_get_posts(
			[
				'post_type'      => [ SUREDASHBOARD_FEED_POST_TYPE ],
				'posts_per_page' => Helper::get_option( 'feeds_per_page', 5 ),
				'post_status'    => 'publish',
			]
		);

		ob_start();
		?>
			<div class="portal-content-area portal-content-type-posts_discussion">
		<?php

		if ( empty( $feeds_posts ) ) {
			suredash_get_template_part( 'parts', '404' );
		}

		if ( ! empty( $feeds_posts ) ) {
			foreach ( $feeds_posts as $post ) {

				$post           = (array) $post;
				$post_id        = isset( $post['ID'] ) ? absint( $post['ID'] ) : 0;
				$allow_comments = sd_get_post_field( $post_id, 'comment_status' );
				Helper::render_post( $post, 0, false, $allow_comments ); // @phpstan-ignore-line
			}
		}

		?>
			</div>
		<?php

		wp_reset_postdata();

		$pagination_markup = sprintf(
			'<div class="portal-pagination-loader">
				<div class="portal-pagination-loader-1"></div>
				<div class="portal-pagination-loader-2"></div>
				<div class="portal-pagination-loader-3"></div>
			</div>
			<div class="portal-infinite-trigger" data-post_type="%s" data-taxonomy="%s"></div>',
			SUREDASHBOARD_FEED_POST_TYPE,
			SUREDASHBOARD_FEED_TAXONOMY
		);

		echo wp_kses_post( $pagination_markup );

		return ob_get_clean();
	}

	/**
	 * Update user profile content.
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_user_profile_content() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		$first_name_placeholder = __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'first_name' );
		$last_name_placeholder  = __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'last_name' );

		$first_name   = sd_get_user_meta( $user_id, 'first_name', true );
		$last_name    = sd_get_user_meta( $user_id, 'last_name', true );
		$cover_image  = sd_get_user_meta( $user_id, 'user_banner_image', true );
		$display_name = ! empty( $user->display_name ) ? $user->display_name : __( 'User', 'suredash' );
		$description  = ! empty( $user->description ) ? $user->description : '';

		$first_name_value = ! empty( $first_name ) ? $first_name : '';
		$last_name_value  = ! empty( $last_name ) ? $last_name : '';

		$user_profile_fields = apply_filters(
			'suredashboard_user_profile_fields',
			[
				'display_name' => [
					'label'       => Labels::get_label( 'display_name' ),
					'placeholder' => __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'display_name' ),
					'value'       => ! empty( $display_name ) ? $display_name : '',
					'type'        => 'input',
				],
				'description'  => [
					'label'       => Labels::get_label( 'bio' ),
					'placeholder' => __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'description' ),
					'value'       => ! empty( $description ) ? $description : '',
					'type'        => 'textarea',
				],
			]
		);

		$user_password_fields = [
			'new_password'         => [
				'label'       => Labels::get_label( 'new_password' ),
				'placeholder' => __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'new_password' ),
				'value'       => '',
				'type'        => 'password',
			],
			'confirm_new_password' => [
				'label'       => Labels::get_label( 'confirm_new_password' ),
				'placeholder' => __( 'Enter', 'suredash' ) . ' ' . Labels::get_label( 'confirm_new_password' ),
				'value'       => '',
				'type'        => 'password',
			],
		];

		$bg_prop = ! empty( $cover_image ) ? '--portal-user-profile-banner: url(' . esc_url( $cover_image ) . ');' : '';

		ob_start();
		?>
			<div class="portal-user-profile-main portal-content">
				<div class="portal-user-profile-editor-header">
					<h4 class="sd-no-space"> <?php Labels::get_label( 'profile_information', true ); ?> </h4>
					<button class="portal-button button-primary portal-user-profile-editor-save"> <?php Labels::get_label( 'save', true ); ?> </button>
				</div>

				<div class="portal-user-profile-editor-wrap portal-content-area sd-box-shadow">
					<div class="portal-wp-content-tabs">
						<span class="portal-user-view-tab active" data-tab="profile"> <?php echo esc_html( Labels::get_label( 'profile' ) ); ?> </span>
						<span class="portal-user-view-tab" data-tab="password"> <?php echo esc_html( Labels::get_label( 'password' ) ); ?> </span>
					</div>

					<div class="portal-user-posts-content-assets active" data-tab="profile">
						<div class="portal-user-profile-editor-avatar">
							<label for="profile-photo"> <?php Labels::get_label( 'profile_photo', true ); ?> </label>
							<div class="portal-user-profile-gravatar-setup">
								<?php suredash_get_user_avatar( $user_id ); ?>
								<div class="portal-user-profile-photo-upload">
									<button class="portal-button button-secondary"> <?php echo esc_html__( 'Upload', 'suredash' ); ?> </button>
									<?php suredash_image_uploader_field( '', 'user_profile_photo', true ); ?>
								</div>
							</div>
						</div>

						<div class="portal-user-profile-editor-fields">
							<div class="portal-user-profile-cover-banner">
								<label for="profile-photo"> <?php esc_html_e( 'Cover Image', 'suredash' ); ?> </label>
								<div class="portal-user-profile-cover-image-field" style="<?php echo esc_attr( $bg_prop ); ?>">
									<?php suredash_image_uploader_field( '', 'user_banner_image' ); ?>
								</div>
							</div>
						</div>

						<div class="portal-user-profile-editor-fields">
							<div class="portal-name-field">
								<div class="portal-fname-wrap">
									<label for="first_name"> <?php Labels::get_label( 'first_name', true ); ?> </label>
									<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $first_name_value ); ?>" placeholder="<?php echo esc_attr( $first_name_placeholder ); ?>">
								</div>

								<div class="portal-lname-wrap">
									<label for="last_name"> <?php Labels::get_label( 'last_name', true ); ?> </label>
									<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $last_name_value ); ?>" placeholder="<?php echo esc_attr( $last_name_placeholder ); ?>">
								</div>
							</div>
						</div>

						<div class="portal-user-profile-editor-fields">
						<?php
						foreach ( $user_profile_fields as $field => $field_data ) {
							?>
							<div class="portal-user-profile-editor-field">
								<label for="<?php echo esc_attr( $field ); ?>"> <?php echo esc_html( $field_data['label'] ); ?> </label>
								<?php
								if ( $field_data['type'] === 'textarea' ) {
									?>
										<textarea name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" placeholder="<?php echo esc_attr( $field_data['placeholder'] ); ?>" rows="4"><?php echo esc_html( $field_data['value'] ); ?></textarea>
									<?php
								} else {
									$input_type = $field_data['type'] === 'password' ? 'password' : 'text';
									?>
										<input type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $field_data['value'] ); ?>" placeholder="<?php echo esc_attr( $field_data['placeholder'] ); ?>">
									<?php
								}
								?>
							</div>
						<?php } ?>
						</div>
					</div>

					<div class="portal-user-posts-content-assets" data-tab="password">
						<div class="portal-user-profile-editor-fields">
							<?php
							foreach ( $user_password_fields as $field => $field_data ) {
								if ( ! is_array( $field_data ) ) {
									continue;
								}
								?>
									<div class="portal-user-profile-editor-field">
										<label for="<?php echo esc_attr( $field ); ?>"> <?php echo esc_html( $field_data['label'] ); ?> </label>
										<?php
										if ( $field_data['type'] === 'textarea' ) { // @phpstan-ignore-line
											?>
											<textarea name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" placeholder="<?php echo esc_attr( $field_data['placeholder'] ); ?>" rows="4"><?php echo esc_html( $field_data['value'] ); ?></textarea>
											<?php
										} else {
											$input_type = $field_data['type'] === 'password' ? 'password' : 'text'; // @phpstan-ignore-line
											?>
											<input type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $field_data['value'] ); ?>" placeholder="<?php echo esc_attr( $field_data['placeholder'] ); ?>">
											<?php
										}
										?>
									</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get user view content.
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	public function get_user_view_content() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id = ! empty( $_GET['id'] ) ? absint( $_GET['id'] ) : get_current_user_id(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here.

		$user = get_userdata( $user_id );

		$first_name = sd_get_user_meta( $user_id, 'first_name', true );
		$last_name  = sd_get_user_meta( $user_id, 'last_name', true );

		$user_email  = ! empty( $user->user_email ) ? $user->user_email : '';
		$description = ! empty( $user->description ) ? $user->description : '';

		$website     = ! empty( $user->user_url ) ? $user->user_url : '';
		$cover_image = sd_get_user_meta( $user_id, 'user_banner_image', true );

		if ( empty( $cover_image ) ) {
			$cover_image = Helper::get_banner_placeholder_image();
		}

		ob_start();

		?>
			<div class="portal-user-profile-user-view-wrap portal-content portal-content-area">
				<div class="portal-user-view-header">
					<img src="<?php echo esc_url( $cover_image ); ?>" alt="<?php echo esc_attr( $first_name ); ?>">
				</div>

				<div class="portal-user-profile-user-details-wrap">
					<div class="portal-user-profile-editor-avatar portal-user-view-details">
						<?php suredash_get_user_avatar( $user_id, true, 96 ); ?>
						<div class="portal-user-intro-details">
							<h4 class="sd-no-space">
								<span class="portal-user-view-fname"> <?php echo esc_html( $first_name ); ?> </span>
								<span class="portal-user-view-lname"> <?php echo esc_html( $last_name ); ?> </span>
							</h4>
							<span class="portal-user-view-socials">
								<?php
								if ( ! empty( $website ) ) {
									?>
								<a href="<?php echo esc_url( $website ); ?>" target="_blank">
									<?php Helper::get_library_icon( 'Globe' ); ?>
								</a>
									<?php
								}
								?>
								<a href="mailto:<?php echo esc_attr( $user_email ); ?>" target="_blank">
									<?php Helper::get_library_icon( 'Mail' ); ?>
								</a>
							</span>
						</div>
					</div>

					<?php
					if ( ! empty( $description ) ) {
						?>
							<div class="portal-user-view-description">
								<?php echo esc_html( $description ); ?>
							</div>
						<?php
					}
					?>

					<div class="portal-user-view-content-wrapper">
						<div class="portal-wp-content-tabs">
							<span class="portal-user-view-tab active" data-tab="posts"> <?php echo esc_html( Labels::get_label( 'posts' ) ); ?> </span>
							<span class="portal-user-view-tab" data-tab="comments"> <?php echo esc_html( Labels::get_label( 'comments' ) ); ?> </span>
						</div>

						<div class="portal-user-posts-content-assets active" data-tab="posts">
							<?php
								Helper::suredash_user_posts( $user_id );

								$pagination_markup = sprintf(
									'<div class="portal-pagination-loader">
										<div class="portal-pagination-loader-1"></div>
										<div class="portal-pagination-loader-2"></div>
										<div class="portal-pagination-loader-3"></div>
									</div>
									<div class="portal-infinite-trigger" data-post_type="%s" data-user_id="%s"></div>',
									SUREDASHBOARD_FEED_POST_TYPE,
									$user_id
								);

								echo wp_kses_post( $pagination_markup );
							?>
						</div>

						<div class="portal-user-posts-content-assets" data-tab="comments">
							<?php Helper::suredash_user_comments( $user_id ); ?>
						</div>
					</div>
				</div>
			</div>
		<?php

		return ob_get_clean();
	}
}
