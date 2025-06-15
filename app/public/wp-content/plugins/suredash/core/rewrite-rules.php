<?php
/**
 * Portals RewriteRules Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core;

use SureDashboard\Core\Integrations\SinglePost;
use SureDashboard\Core\Shortcodes\SingleComments;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;
use SureDashboard\Inc\Utils\WpPost;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class RewriteRules.
 */
class RewriteRules {
	use Get_Instance;

	/**
	 * Set status for post reaction modal loaded.
	 *
	 * @var bool
	 */
	private $post_reaction_modal_loaded = false;

	/**
	 * Set status for post quick view loaded.
	 *
	 * @var bool
	 */
	private $quick_view_modal_loaded = false;

	/**
	 * Set status for branding section loaded.
	 *
	 * @var bool
	 */
	private $branding_loaded = false;

	/**
	 * Set status for search modal loaded.
	 *
	 * @var bool
	 */
	private $search_modal_loaded = false;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function initialize_hooks(): void {
		add_action( 'init', [ $this, 'portal_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );

		// Add custom rewrite rules for community content.
		add_filter( 'rewrite_rules_array', [ $this, 'content_rewrite_rules' ] );

		// Load assets for single post.
		add_action( 'suredashboard_single_post_template', [ $this, 'load_post_assets' ] );

		// Quick view post content -- Adding here just to load early.
		add_action( 'suredashboard_quick_view_post_content', [ $this, 'load_quick_view_post_content' ], 10, 2 );

		add_action( 'suredash_footer', [ $this, 'render_search_modal' ] );
		add_action( 'suredash_footer', [ $this, 'add_post_reaction_modal' ] );
		add_action( 'suredash_footer', [ $this, 'quick_view_popup' ] );
		add_action( 'suredash_footer', [ $this, 'load_branding' ] );
	}

	/**
	 * Add Query Vars.
	 *
	 * @param array<int, string> $vars Query Vars.
	 * @return array<int, string>
	 * @since 0.0.1
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'portal_subpage';
		return $vars;
	}

	/**
	 * Custom Rewrite Rules as per suredash_sub_queries().
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function portal_rewrite_rules(): void {
		$sub_queries = suredash_sub_queries();
		foreach ( $sub_queries as $query ) {
			add_rewrite_rule( '^' . SUREDASHBOARD_SLUG . '/' . esc_attr( $query ) . '/?$', 'index.php?portal_subpage=' . esc_attr( $query ), 'top' );
		}
	}

	/**
	 * Add custom rewrite rules for community content.
	 *
	 * @param array<string, string> $rules Existing rewrite rules.
	 * @return array<string, string> Modified rewrite rules.
	 * @since 1.0.0
	 */
	public function content_rewrite_rules( $rules ) {
		$new_rules = [];

		foreach ( suredash_all_content_types() as $type ) {
			$new_rules[ "{$type}/([^/]+)/?$" ] = 'index.php?post_type=' . SUREDASHBOARD_SUB_CONTENT_POST_TYPE . '&name=$matches[1]';
		}

		return $new_rules + $rules;
	}

	/**
	 * Load quick view post content.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $comments Comments.
	 *
	 * @since 1.0.0
	 */
	public function load_quick_view_post_content( $post_id, $comments ): void {
		ob_start();

		$post_title       = get_the_title( $post_id );
		$wp_post_instance = new WpPost( $post_id );
		$wp_post_instance->enqueue_assets();

		?>
		<h3 class="sd-no-space sd-post-title"> <?php echo esc_html( $post_title ); ?> </h3>

		<div class="entry-content sd-post-content sd-border-b sd-pb-20">
			<?php
				Helper::suredash_featured_cover( $post_id );
			if ( method_exists( SinglePost::get_instance(), 'get_integration_content' ) ) {
				echo do_shortcode( apply_filters( 'the_content', SinglePost::get_instance()->get_integration_content( $post_id, true ), $post_id ) );
			}
			?>
		</div>

		<?php

		if ( method_exists( SingleComments::get_instance(), 'get_single_comments_content' ) ) {
			SingleComments::get_instance()->get_single_comments_content(
				[
					'item_id'  => $post_id,
					'echo'     => true,
					'in_qv'    => true,
					'comments' => $comments,
				]
			);
		}

		echo do_shortcode( (string) ob_get_clean() );
	}

	/**
	 * Load required single post assets.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 1.0.0
	 */
	public function load_post_assets( $post_id ): void {
		$wp_post_instance = new WpPost( $post_id );
		$wp_post_instance->enqueue_assets();
	}

	/**
	 * Add notification toaster for the portal.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function render_notification_toaster(): void {
		?>
			<div id="portal-notification-toaster" class="portal-notification-toaster portal-content"></div>
		<?php
	}

	/**
	 * Add Post Reaction Modal - Likes, Comments.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function add_post_reaction_modal(): void {
		if ( $this->post_reaction_modal_loaded ) {
			return;
		}

		$this->render_notification_toaster();

		?>
			<div id="portal-post-reaction-modal" class="portal-modal portal-content">
				<div class="portal-modal-content">
					<div class="portal-modal-header">
						<span class="portal-post-reactor-header">
							<h4 class="show-likes sd-no-space"><?php Labels::get_label( 'likes', true ); ?></h4>
							<h4 class="show-comments sd-no-space"><?php Labels::get_label( 'comments', true ); ?></h4>
						</span>
						<span class="portal-modal-close"> <?php Helper::get_library_icon( 'X' ); ?> </span>
					</div>
					<div class="portal-modal-body">
						<div class="portal-pagination-loader active">
							<div class="portal-pagination-loader-1"></div>
							<div class="portal-pagination-loader-2"></div>
							<div class="portal-pagination-loader-3"></div>
						</div>

						<div class="portal-post-reactor-content"></div>
					</div>

					<?php
					if ( ! is_user_logged_in() ) {
						?>
						<div class="portal-modal-footer">
							<div class="comment-modal-login-notice sd-w-full sd-flex sd-items-center sd-justify-center sd-p-8 sd-radius-6">
								<?php Helper::get_login_notice( 'comment' ); ?>
							</div>
							</div>
						<?php
					} else {
						?>
						<div class="portal-modal-footer">
							<!-- This is where the comment box would load -->
						</div>
						<?php
					}
					?>
				</div>

				<div class="portal-modal-backdrop"></div>
			</div>
		<?php

		$this->post_reaction_modal_loaded = true;
	}

	/**
	 * Load branding.
	 *
	 * @since 0.0.6
	 */
	public function load_branding(): void {
		if ( $this->branding_loaded ) {
			return;
		}

		suredash_get_template_part(
			'parts',
			'footer'
		);

		$this->branding_loaded = true;
	}

	/**
	 * Quick view HTML.
	 *
	 * @since 0.0.1
	 */
	public function quick_view_popup(): void {
		if ( $this->quick_view_modal_loaded ) {
			return;
		}

		suredash_get_template_part( 'quick-view', 'container' );

		$this->quick_view_modal_loaded = true;
	}

	/**
	 * Render Search Modal.
	 *
	 * @since 0.0.1
	 */
	public function render_search_modal(): void {
		if ( $this->search_modal_loaded ) {
			return;
		}

		?>
		<!--Search-term results-->
		<script type="text/html" id="portal-search-result-template">
			<?php // @codingStandardsIgnoreStart?>
			<li id="portal_search_item-<%= post.id %>">
				<a href="<%= post.url %>" class="portal-search-item-link">
					<div class="portal-search-item-title-wrap">
						<span class="portal-search-result-title"><%= post.title %></span>
						<?php // @codingStandardsIgnoreEnd?>
					</div>
				</a>
			</li>
		</script>
		<?php

		echo do_shortcode( '<div class="pfd-header-search"> [portal_search] </div>' );

		$this->search_modal_loaded = true;
	}
}
