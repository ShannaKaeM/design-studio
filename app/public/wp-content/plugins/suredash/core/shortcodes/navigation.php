<?php
/**
 * Portals Docs Navigation Shortcode Initialize.
 *
 * @package SureDash
 */

namespace SureDashboard\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureDashboard\Core\Models\Controller;
use SureDashboard\Inc\Traits\Get_Instance;
use SureDashboard\Inc\Traits\Shortcode;
use SureDashboard\Inc\Utils\Helper;
use SureDashboard\Inc\Utils\Labels;

/**
 * Class Navigation Shortcode.
 */
class Navigation {
	use Shortcode;
	use Get_Instance;

	/**
	 * Register_shortcode_event.
	 *
	 * @return void
	 */
	public function register_shortcode_event(): void {
		$this->add_shortcode( 'navigation' );
	}

	/**
	 * Display portal navigation.
	 *
	 * @param array<mixed> $atts Array of attributes.
	 * @since 1.0.0
	 * @return string|false
	 */
	public function render_navigation( $atts ) {
		$atts = apply_filters(
			'suredash_navigation_attributes',
			shortcode_atts(
				[
					'show_only_navigation' => false,
				],
				$atts
			)
		);

		ob_start();

		$this->process_side_navigation_query( $atts );

		return ob_get_clean();
	}

	/**
	 * Get the global portal query.
	 *
	 * @param array<mixed> $attr shortcode attributes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function process_side_navigation_query( $attr ): void {
		$show_only_navigation = boolval( $attr['show_only_navigation'] ) ? true : false;

		$results = Controller::get_query_data( 'Navigation' );

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

		if ( ! $show_only_navigation ) {
			suredash_get_template_part( 'parts', 'identity' );
		}

		if ( ! empty( $space_groups ) ) {
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

			$sub_query              = suredash_get_sub_queried_page();
			$is_feeds               = $sub_query === 'feeds';
			$is_portal_home         = suredash_is_home();
			$current_space_id       = get_the_ID();
			$collapsible_navigation = apply_filters( 'suredashboard_enable_collapsible_navigation', false );
			?>

			<div class="portal-aside-list-wrapper">
				<div class="portal-aside-group-wrap <?php echo esc_attr( $collapsible_navigation ? 'pfd-collapsible-enabled' : '' ); ?>">
					<?php
					$show_feed = Helper::get_option( 'enable_feeds' );
					if ( $show_feed ) {
						$feed_url = get_home_url() . '/' . SUREDASHBOARD_SLUG . '/feeds';
						?>
							<a href="<?php echo esc_url( $feed_url ); ?>" class="portal-aside-feed portal-aside-group-body portal-feeds <?php echo esc_attr( $is_feeds ? 'active' : '' ); ?>">
								<?php Helper::get_library_icon( apply_filters( 'suredash_feeds_icon', 'Newspaper' ), true, 'sm', 'portal-feeds-icon' ); ?>
								<span class="portal-aside-feed-text"> <?php echo esc_html( apply_filters( 'suredash_feeds_text', __( 'Feeds', 'suredash' ) ) ); ?> </span>
							</a>
						<?php
					}
					foreach ( $space_groups as $space_group ) {
						$is_hide_label    = boolval( $space_group[0]['hide_label'] ?? false );
						$space_term_id    = absint( $space_group[0]['term_id'] ?? '' );
						$space_group_name = $space_group[0]['name'] ?? '';
						?>
						<div class="portal-aside-group <?php echo esc_attr( $is_hide_label ? 'pinned-group' : '' ); ?>" data-id="<?php echo esc_attr( (string) $space_term_id ); ?>">
							<?php if ( ! $is_hide_label ) { ?>
								<div class="portal-aside-group-header">
									<span class="portal-aside-group-title-link sd-no-space"> <h5 class="portal-aside-group-title"><?php echo esc_html( $space_group_name ); ?></h5> </span>
									<span class="pfd-aside-doc-trigger" tabindex="0"> <?php Helper::get_library_icon( 'ChevronDown', true ); ?> </span>
								</div>
							<?php } ?>

							<div class="portal-aside-group-body">
								<ul role="list" class="portal-aside-group-list sd-no-space">
									<?php
									foreach ( $space_group as $space_item ) {
										$post_id = (int) $space_item['ID'];
										if ( ! $post_id || $space_item['post_status'] !== 'publish' ) {
											continue;
										}

										$active_class   = ! $is_portal_home && $post_id === $current_space_id ? ' active' : '';
										$content_type   = $space_item['integration'] ?? '';
										$layout         = $space_item['layout'] ?? '';
										$featured_image = $space_item['image_url'] ?? '';
										$icon           = $space_item['item_emoji'] ?? 'Link';
										$link_target    = $space_item['link_target'] ?? '';
										$link_attr      = $content_type === 'link' ? 'target="' . esc_attr( $link_target ) . '"' : '';
										$link           = $content_type === 'link' ? $space_item['link_url'] : get_permalink( $post_id );

										do_action( 'suredash_before_aside_navigation_item', $post_id );

										$icon = apply_filters( 'suredash_aside_navigation_space_icon_' . $post_id, Helper::get_library_icon( $icon, false ), $post_id );

										echo do_shortcode( '<li class="sd-no-space"> <a ' . $link_attr . ' class="portal-aside-group-link' . $active_class . '" data-post_id="' . $post_id . '" data-space_type="' . $content_type . '"  data-layout_type="' . $layout . '" data-featured_image="' . ! empty( $featured_image ) . '" href="' . esc_url( $link ) . '">' . $icon . '<span class="portal-aside-item-title">' . $space_item['post_title'] . '</span> </a> </li>' );

										do_action( 'suredash_after_aside_navigation_item', $post_id );
									}
									?>
								</ul>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		} else {
			esc_html_e( 'No groups found.', 'suredash' );
		}

		if ( ! $show_only_navigation ) {
			echo do_shortcode( '[portal_user_profile]' );
		}
	}
}
