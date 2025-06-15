<?php
/**
 * Feeds Integration.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Integrations;

use SureDashboard\Core\Models\Controller;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\PostMeta;

defined( 'ABSPATH' ) || exit;

/**
 * Feeds Integration.
 *
 * @since 1.0.0
 */
class Feeds extends Base {
	use Get_Instance;

	/**
	 * Current Processing Space ID.
	 *
	 * @var int
	 */
	public $active_space_id = 0;

	/**
	 * Current Processing Taxonomy.
	 *
	 * @var int
	 */
	public $active_space_tax_id = 0;

	/**
	 * Set status if footer post creation loaded.
	 *
	 * @var bool
	 */
	private $footer_post_creation_loaded = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'Feeds';
		$this->slug        = 'store-categories';
		$this->description = __( 'Feeds Integration', 'suredash' );
		$this->is_active   = true; // @phpstan-ignore-line
		parent::__construct( $this->name, $this->slug, $this->description, $this->is_active ); // @phpstan-ignore-line

		if ( ! $this->is_active ) {
			return;
		}
	}

	/**
	 * Get Query for Content Groups.
	 *
	 * @param int $category_id Category ID.
	 * @return array<mixed>
	 * @since 0.0.1
	 */
	public function get_query( $category_id ) {
		return Controller::get_query_data(
			'Feeds',
			[
				'category_id'    => $category_id,
				'post_type'      => SUREDASHBOARD_FEED_POST_TYPE,
				'taxonomy'       => SUREDASHBOARD_FEED_TAXONOMY,
				'posts_per_page' => Helper::get_option( 'feeds_per_page', 5 ),
			]
		);
	}

	/**
	 * Get content for archive content categories.
	 *
	 * @return string|false
	 * @since 0.0.1
	 */
	public function get_archive_content() {
		ob_start();

		$feed_group_id = get_queried_object_id();

		if ( is_post_type_archive( SUREDASHBOARD_FEED_POST_TYPE ) ) {
			$query_posts = sd_get_posts(
				[
					'post_type'      => [ SUREDASHBOARD_FEED_POST_TYPE ],
					'posts_per_page' => Helper::get_option( 'feeds_per_page', 5 ),
					'post_status'    => 'publish',
				]
			);
		} else {
			$query_posts = $this->get_query( $feed_group_id );
		}

		if ( ! empty( $query_posts ) && is_array( $query_posts ) ) {
			foreach ( $query_posts as $post ) {
				$post_id = absint( $post['ID'] );

				// Ensure the post object is valid.
				if ( empty( $post_id ) ) {
					continue;
				}

				// Render the post.
				Helper::render_post( $post );
			}
		}

		wp_reset_postdata(); // Reset post data.

		Helper::get_archive_pagination_markup( $feed_group_id );

		return ob_get_clean();
	}

	/**
	 * Add Post Creation Modal.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function add_post_creation_modal(): void {
		if ( $this->footer_post_creation_loaded ) {
			return;
		}
		if ( ! suredash_frontend() ) {
			return;
		}

		?>
			<div id="portal-post-creation-modal" class="portal-modal portal-content">
				<div class="portal-modal-content">
					<div class="portal-modal-header">
						<h2 class="sd-no-space"><?php Labels::get_label( 'write_a_post', true ); ?></h2>
						<div class="portal-post-creation-supports">
							<a href="#" title="<?php echo esc_attr__( 'Cover Image', 'suredash' ); ?>" class="portal-linked-content-field" data-section="portal-featured-image-field"> <?php Helper::get_library_icon( 'Image', true, 'md' ); ?> </a>
							<a href="#" title="<?php echo esc_attr__( 'Cover Media Embed', 'suredash' ); ?>" class="portal-linked-content-field" data-section="portal-embed-field"> <?php Helper::get_library_icon( 'SquarePlay', true, 'md' ); ?> </a>
						</div>
					</div>
					<div class="portal-modal-body">
						<?php suredash_image_uploader_field( __( 'Cover Image', 'suredash' ), 'custom_post_cover_image', false, true ); ?>

						<div class="portal-custom-topic-field portal-extended-linked-field portal-hidden-field portal-embed-field">
							<label for="custom_post_embed_media">
							<?php
							esc_html_e( 'Cover Embed Media', 'suredash' );

							echo sprintf(
								'<p class="portal-help-description">%s%s&nbsp;%s%s</p>',
								'(',
								esc_html__( 'See supported embeds', 'suredash' ),
								'<a href="' . esc_url( 'https://wordpress.org/documentation/article/embeds/#list-of-sites-you-can-embed-from' ) . '" target="_blank">' . esc_html__( 'here', 'suredash' ) . '</a>',
								')'
							);
							?>
							</label>
							<input type="text" id="custom_post_embed_media" name="custom_post_embed_media" class="portal_topic_input portal_feed_input" placeholder="<?php echo esc_attr__( 'Please enter the URL of the media you want to embed', 'suredash' ); ?>" />
						</div>

						<div class="portal-custom-topic-field">
							<input type="text" id="custom_post_title" name="custom_post_title" class="portal_topic_input post_creation_title sd-force-font-28 sd-force-font-medium sd-force-p-0 sd-force-border-none sd-force-shadow-none" autocomplete="off" placeholder="<?php echo esc_attr__( 'Enter a title', 'suredash' ); ?>" />

							<textarea id="custom_post_content" name="custom_post_content" class="portal_topic_input post_creation_content"></textarea>
						</div>

						<input type="hidden" id="custom_post_tax_id" name="custom_post_tax_id" class="portal_feed_input" value="<?php echo esc_attr( (string) $this->active_space_tax_id ); ?>" />
					</div>
					<div class="portal-modal-footer">
						<div class="portal-post-creation-actions">
							<button class="portal-button button-secondary portal-modal-close"><?php Labels::get_label( 'close_button', true ); ?></button>
							<button id="portal-post-creation-submit" class="portal-button button-primary"><?php Labels::get_label( 'submit_button', true ); ?></button>
						</div>
					</div>
				</div>

				<div class="portal-modal-backdrop"></div>
			</div>
		<?php

		do_action( 'suredashboard_after_post_creation_modal', $this->active_space_id );

		$this->footer_post_creation_loaded = true;
	}

	/**
	 * Get top sub-header section where we ask user to create a post.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function get_post_creation_section(): void {
		if ( ! is_user_logged_in() ) {
			?>
				<div class="comment-modal-login-notice sd-w-full sd-flex sd-items-center sd-justify-center sd-p-8 sd-radius-6">
					<?php Helper::get_login_notice( 'comment' ); ?>
				</div>
			<?php
			return;
		}
		?>

		<div id="portal-write-a-post" class="portal-store-list-post sd-box-shadow portal-content">
			<div class="portal-write-a-post-header">
				<?php suredash_get_user_avatar( get_current_user_id() ); ?>
				<span class="portal-write-a-post-heading sd-font-16 sd-p-12 sd-radius-8 sd-border">
					<?php Labels::get_label( 'start_writing_post', true ); ?>
				</span>
			</div>
		</div>

		<?php
		add_action( 'suredash_footer', [ $this, 'add_post_creation_modal' ] );
	}

	/**
	 * Get item single content.
	 *
	 * @param int $base_id Post ID.
	 * @return string|false
	 * @since 0.0.1
	 */
	public function get_integration_content( $base_id ) {
		ob_start();

		$feed_group_id = absint( PostMeta::get_post_meta_value( $base_id, 'feed_group_id' ) );

		$allow_members_to_post = boolval( PostMeta::get_post_meta_value( $base_id, 'allow_members_to_post' ) );

		if ( $feed_group_id && $allow_members_to_post ) {
			$this->active_space_id     = $base_id;
			$this->active_space_tax_id = $feed_group_id;
			$this->get_post_creation_section();
		}

		if ( $feed_group_id ) {
			do_action( 'suredashboard_before_wp_single_content_load', $feed_group_id );

			$pinned_posts = Helper::get_pinned_posts( $base_id );
			$query_posts  = $this->get_query( $feed_group_id );

			if ( ! empty( $query_posts ) && is_array( $query_posts ) ) {
				if ( ! empty( $pinned_posts ) ) {
					foreach ( $pinned_posts as $pinned_post_id ) {
						if ( sd_post_exists( $pinned_post_id ) ) {
							$pinned_post = (array) sd_get_post( $pinned_post_id );
							$comments    = sd_get_post_field( $pinned_post_id, 'comment_status' ); // Updated to use $pinned_post_id.
							Helper::render_post( $pinned_post, $base_id, true, $comments );
						}
					}
				}

				foreach ( $query_posts as $post ) {
					$post_id        = absint( $post['ID'] );
					$is_pinned_post = in_array( $post_id, $pinned_posts, true );

					if ( $is_pinned_post ) {
						continue;
					}
					$comments = sd_get_post_field( $post_id, 'comment_status' );

					Helper::render_post( $post, $base_id, $is_pinned_post, $comments );
				}
			} else {
				suredash_get_template_part( 'parts', '404' );
			}

			wp_reset_postdata(); // Reset post data.

			Helper::get_archive_pagination_markup( $feed_group_id, $base_id );

			do_action( 'suredashboard_after_wp_single_content_load', $feed_group_id );
		} else {
			suredash_get_template_part( 'parts', '404' );
		}

		return ob_get_clean();
	}
}
