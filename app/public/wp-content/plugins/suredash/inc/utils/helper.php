<?php
/**
 * Helper.
 *
 * @package SureDash
 * @since 0.0.1
 */

namespace SureDashboard\Inc\Utils;

use SureDashboard\Core\Models\Controller;
use WP_Comment;

/**
 * Initialize setup
 *
 * @since 0.0.1
 * @package SureDash
 */

defined( 'ABSPATH' ) || exit;

/**
 * This class setup all AJAX action
 *
 * @class Ajax
 */
class Helper {
	/**
	 * Returns an option from the database for the admin settings.
	 *
	 * @param  string $key     The option key.
	 * @param  mixed  $default Option default value if option is not available.
	 * @return mixed   Returns the option value
	 *
	 * @since 0.0.1
	 */
	public static function get_option( $key, $default = false ) {
		$portal_settings = Settings::get_suredash_settings();

		if ( empty( $portal_settings ) || ! is_array( $portal_settings ) || ! array_key_exists( $key, $portal_settings ) ) {
			$portal_settings[ $key ] = '';
		}

		// Get the setting option if we're in the admin panel.
		$value = $portal_settings[ $key ];

		if ( $value === '' && $default !== false ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Update option from the database for the admin settings.
	 *
	 * @param  string $key      The option key.
	 * @param  mixed  $value    Option value to update.
	 * @return string           Return the option value
	 *
	 * @since 0.0.1
	 */
	public static function update_option( $key, $value = true ) {
		$portal_settings = Settings::get_suredash_settings( false );

		if ( ! is_array( $portal_settings ) ) {
			$portal_settings = [];
		}

		// If the value is same as default then remove it from the DB.
		// This will help in the translatable strings.
		if ( Settings::get_default_option( $key ) === $value ) {
			unset( $portal_settings[ $key ] );
		} else {
			$portal_settings[ $key ] = $value;
		}

		update_option( SUREDASHBOARD_SETTINGS, $portal_settings );

		return $value;
	}

	/**
	 * Get placeholder image URL.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @return string Placeholder image URL.
	 */
	public static function get_placeholder_image() {
		return apply_filters( 'suredashboard_placeholder_image', SUREDASHBOARD_URL . 'assets/images/placeholder.jpg' );
	}

	/**
	 * Get banner placeholder image URL.
	 *
	 * @since 0.0.1
	 * @access public
	 * @return string Banner placeholder image URL.
	 */
	public static function get_banner_placeholder_image() {
		return apply_filters( 'suredashboard_banner_placeholder_image', SUREDASHBOARD_URL . 'assets/images/banner-placeholder.jpg' );
	}

	/**
	 * Get default space featured image markup.
	 *
	 * @access public
	 * @param int  $post_id Post ID.
	 * @param bool $fallback_placeholder Fallback to placeholder image.
	 * @since 0.0.1
	 * @return string
	 */
	public static function get_space_featured_image( $post_id, $fallback_placeholder = true ) {
		$featured_link = PostMeta::get_post_meta_value( $post_id, 'image_url' );

		if ( empty( $featured_link ) && $fallback_placeholder ) {
			$featured_link = self::get_placeholder_image();
		}

		if ( empty( $featured_link ) ) {
			return '';
		}

		return '<img src="' . esc_url( $featured_link ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="portal-item-featured-image">';
	}

	/**
	 * Get default space banner image markup.
	 *
	 * @access public
	 * @param int  $post_id Post ID.
	 * @param bool $fallback_placeholder Fallback to placeholder image.
	 * @since 0.0.1
	 * @return string
	 */
	public static function get_space_banner_image( $post_id, $fallback_placeholder = true ) {
		$featured_link = PostMeta::get_post_meta_value( $post_id, 'banner_url' );

		if ( empty( $featured_link ) && $fallback_placeholder ) {
			$featured_link = self::get_placeholder_image();
		}

		if ( empty( $featured_link ) ) {
			return '';
		}

		return '<img src="' . esc_url( $featured_link ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="portal-item-featured-image">';
	}

	/**
	 * Get course space featured image markup.
	 *
	 * @access public
	 * @param int  $post_id Post ID.
	 * @param bool $fallback_placeholder Fallback to placeholder image.
	 * @since 0.0.6
	 * @return string
	 */
	public static function get_course_featured_image( $post_id, $fallback_placeholder = true ) {
		$featured_link = PostMeta::get_post_meta_value( $post_id, 'course_thumbnail_url' );

		if ( empty( $featured_link ) && $fallback_placeholder ) {
			$featured_link = self::get_placeholder_image();
		}

		if ( empty( $featured_link ) ) {
			return '';
		}

		return '<img src="' . esc_url( $featured_link ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" class="portal-item-featured-image">';
	}

	/**
	 * Get CSS value
	 *
	 * Syntax:
	 *
	 *  get_css_value( VALUE, UNIT );
	 *
	 * E.g.
	 *
	 *  get_css_value( VALUE, 'em' );
	 *
	 * @param string $value  CSS value.
	 * @param string $unit  CSS unit.
	 * @since 0.0.1
	 *
	 * @return string
	 */
	public static function get_css_value( $value = '', $unit = '' ) {
		$css_val = '';

		if ( is_array( $value ) || is_array( $unit ) ) {
			return $css_val;
		}

		if ( $value !== '' ) {
			$css_val = esc_attr( $value ) . $unit;
		}

		return $css_val;
	}

	/**
	 * Parse blocks CSS into correct CSS syntax.
	 *
	 * @param array<mixed> $combined_selectors The combined selector array.
	 * @param string       $id The selector ID.
	 * @since 0.0.1
	 *
	 * @return array<string, string>
	 */
	public static function generate_all_css( $combined_selectors, $id ) {
		return [
			'desktop' => self::generate_css( $combined_selectors['desktop'], $id ),
			'tablet'  => self::generate_css( $combined_selectors['tablet'], $id ),
			'mobile'  => self::generate_css( $combined_selectors['mobile'], $id ),
		];
	}

	/**
	 * Parse blocks CSS into correct CSS syntax.
	 *
	 * @param array<mixed> $selectors The block selectors.
	 * @param string       $id The selector ID.
	 * @since 0.0.1
	 *
	 * @return string
	 */
	public static function generate_css( $selectors, $id ) {
		$styling_css = '';

		if ( ! empty( $selectors ) ) {
			foreach ( $selectors as $key => $value ) {
				$css = '';

				foreach ( $value as $j => $val ) {
					if ( $j === 'font-family' && $val === 'Default' ) {
						continue;
					}

					if ( ! empty( $val ) && is_array( $val ) ) {
						foreach ( $val as $key => $css_value ) {
							$css .= $key . ': ' . $css_value . ';';
						}
					} elseif ( ! empty( $val ) || $val === 0 ) {
						if ( $j === 'font-family' ) {
							$css .= $j . ': "' . $val . '";';
						} else {
							$css .= $j . ': ' . $val . ';';
						}
					}
				}

				if ( ! empty( $css ) ) {
					$styling_css .= $id;
					$styling_css .= $key . '{';
					$styling_css .= $css . '}';
				}
			}
		}

		return $styling_css;
	}

	/**
	 * Method to get the skeleton
	 *
	 * @param string $for Argument for skeleton type.
	 *
	 * @return string|false Returns skeleton HTML.
	 *
	 * @since 0.0.1
	 */
	public static function get_skeleton( $for ) {
		$structure = [];
		switch ( $for ) {
			case 'search':
				$structure = [
					[
						'type' => 'paragraphs',
						'data' => [
							[ 'count' => [ 20, 40, 50, 40, 30, 40 ] ],
						],
					],
				];
				break;
			default:
				break;
		}

		ob_start();
		?>
		<div class="portal-skeleton-container">
			<div class="portal-skeleton-content portal-skeleton-<?php echo esc_attr( $for ); ?>">
				<?php
				foreach ( $structure as $section ) {
					switch ( $section['type'] ) {
						case 'paragraphs':
							if ( is_array( $section['data'] ) ) {
								foreach ( $section['data'] as $row ) {
									if ( is_array( $row['count'] ) ) {
										$widths = $row['count'];
										foreach ( $widths as $key => $width ) {
											$last_row = ( $key === count( $widths ) - 1 );
											echo wp_kses_post( '<div class="portal-skeleton-row" style="width: ' . $width . '%; height: 16px; margin-bottom: ' . ( $last_row ? '32px' : '12px' ) . '"></div>' );
										}
									} else {
										/**
										 * Defining the row count.
										 *
										 * @var int $count row count
										 */
										$count = (int) $row['count'];

										for ( $j = 0; $j < $count; $j++ ) {
											$last_row = $j === $count - 1;
											$min      = $last_row ? 50 : 90;
											$max      = $last_row ? 80 : 100;
											$width    = wp_rand( $min, $max );
											echo wp_kses_post( '<div class="portal-skeleton-row" style="width: ' . $width . '%; height: 16px; margin-bottom: ' . ( $last_row ? '32px' : '12px' ) . '"></div>' );
										}
									}
								}
							}
							break;
					}
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Method to get the doc order.
	 *
	 * @param int|WP_term|object $term Doc category term ID, instance or object.
	 * @param array<mixed>       $args Default docs arguments.
	 *
	 * @return array<mixed> Returns array or docs order.
	 * @since 0.0.1
	 */
	public static function get_items_order_sequence( $term, $args = [] ) {
		$term = get_term( $term );
		if ( ! is_object( $term ) || is_wp_error( $term ) ) {
			return [];
		}

		$order_sequence   = [];
		$order_posts_meta = get_term_meta( $term->term_id, '_link_order', true );
		if ( ! empty( $order_posts_meta ) ) {
			$order_sequence = explode( ',', $order_posts_meta );
		}

		// Fetch unordered docs ( old or newly created docs ) which are not there is docs order.
		global $wpdb;
		$query = "SELECT p.ID FROM {$wpdb->posts} p
			JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE tt.term_id = %d
			AND p.post_type = %s";

		$query_values = [ $term->term_id, SUREDASHBOARD_POST_TYPE ];

		// Post status to include.
		if ( isset( $args['post_status'] ) ) {
			$query         .= ' AND p.post_status = %s';
			$query_values[] = $args['post_status'];
		}

		// Avoid docs that are already in docs order sequence meta.
		if ( ! empty( $order_sequence ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $order_sequence ), '%d' ) );
			$query       .= " AND p.ID NOT IN ({$placeholders})";
			$query_values = array_merge( $query_values, array_map( 'intval', $order_sequence ) );
		}

		// phpcs:disable
		$unordered_spaces = $wpdb->get_col($wpdb->prepare($query, ...$query_values));
		// phpcs:enable

		// Add unordered spaces at the end.
		if ( ! empty( $unordered_spaces ) ) {
			$order_sequence = array_merge( $order_sequence, $unordered_spaces );
		}

		return $order_sequence;
	}

	/**
	 * Return search results only by post title.
	 *
	 * @param string $search   Search SQL for WHERE clause.
	 * @param object $wp_query The current WP_Query object.
	 *
	 * @return string The Modified Search SQL for WHERE clause.
	 */
	public static function search_only_titles( $search, $wp_query ) {
		if ( ! empty( $search ) && is_object( $wp_query ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = [];

			foreach ( (array) $q['search_terms'] as $term ) {
				$search[] = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search[] = "{$wpdb->posts}.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search );
		}

		return $search;
	}

	/**
	 * Get the post excerpt by post ID.
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $echo Whether to echo or return.
	 *
	 * @return string Post excerpt.
	 * @since 0.0.1
	 */
	public static function get_read_more_link( $post_id, $echo = true ) {
		$read_more_text = Labels::get_label( 'read_more' );
		$read_more_link = apply_filters( 'suredashboard_read_more_link', get_permalink( $post_id ), $post_id );
		$markup         = '<a href="' . esc_url( $read_more_link ) . '" class="portal-read-more-link">' . esc_html( $read_more_text ) . '</a>';

		if ( $echo ) {
			echo wp_kses_post( $markup );
			return '';
		}
			return $markup;
	}

	/**
	 * Returns an array of logo svg icons.
	 *
	 * @return array<mixed>
	 * @since 0.0.1
	 */
	public static function get_icon_library() {
		static $portal_all_svg_icons = [];

		if ( $portal_all_svg_icons ) {
			return $portal_all_svg_icons;
		}

		$icons_dir = SUREDASHBOARD_DIR . 'assets/icon-library';
		$file      = "{$icons_dir}/lucide-icons.php";

		if ( file_exists( $file ) ) {
			$icons                = include_once $file;
			$portal_all_svg_icons = $icons;
		}

		return apply_filters( 'suredashboard_icons_chunks', $portal_all_svg_icons );
	}

	/**
	 * Get the SVG icon markup by icon name.
	 *
	 * @param string $icon_handle Icon name.
	 * @param bool   $echo Whether to echo or return.
	 *
	 * @return mixed Icon markup or void.
	 * @since 0.0.1
	 */
	public static function get_library_svg( $icon_handle, $echo = false ) {
		$svg_icons = self::get_icon_library();
		$svg_logo  = ! empty( $svg_icons[ $icon_handle ]['rendered'] ) ? $svg_icons[ $icon_handle ]['rendered'] : '';

		if ( ! $svg_logo ) {
			return '';
		}

		if ( $echo ) {
			echo do_shortcode( $svg_logo );
		} else {
			return $svg_logo;
		}
	}

	/**
	 * Get the icon markup by icon name.
	 *
	 * @param string $icon_handle Icon name.
	 * @param bool   $echo Whether to echo or return.
	 * @param string $size Icon size (sm, md, lg, xl).
	 * @param string $classes Additional CSS classes.
	 *
	 * @return mixed Icon markup or void.
	 * @since 0.0.1
	 */
	public static function get_library_icon( $icon_handle, $echo = true, $size = 'sm', $classes = '' ) {
		$svg_icons   = self::get_icon_library();
		$icon_handle = ucfirst( $icon_handle );
		$svg_logo    = self::get_library_svg( $icon_handle );
		$svg_label   = ! empty( $svg_icons[ $icon_handle ]['label'] ) ? $svg_icons[ $icon_handle ]['label'] : 'Link';

		if ( $svg_logo ) {
			$svg_logo = '<span class="portal-svg-icon portal-icon-' . $size . ' ' . esc_attr( $classes ) . '" aria-hidden="true" aria-label="' . esc_attr( $svg_label ) . '">' . $svg_logo . '</span>';
		}

		if ( $echo ) {
			echo do_shortcode( apply_filters( 'suredashboard_library_icon', $svg_logo, $icon_handle ) );
		} else {
			return apply_filters( 'suredashboard_library_icon', $svg_logo, $icon_handle );
		}
	}

	/**
	 * Generate a badge with icon and text.
	 *
	 * @param string       $type Badge type/style (primary, secondary, neutral, success, danger, warning).
	 * @param string       $icon Icon handle for the badge.
	 * @param string       $text Text to display in the badge.
	 * @param string       $size Size variant of the badge (sm, md, lg).
	 * @param string       $custom_classes Additional CSS classes for the badge.
	 * @param array<mixed> $data_attributes Optional. Array of data attributes ['attribute' => 'value'].
	 *
	 * @return void.
	 * @since 0.0.2
	 */
	public static function show_badge( $type, $icon, $text, $size = 'md', $custom_classes = '', $data_attributes = [] ): void {
		// Define allowed badge types.
		$allowed_types = [ 'primary', 'secondary', 'neutral', 'success', 'danger', 'warning' ];

		// Validate and fallback for badge type.
		$type      = in_array( $type, $allowed_types ) ? $type : 'primary';
		$icon_size = $size === 'xs' ? 'sm' : ( $size === 'lg' ? 'md' : $size );
		// Get icon markup.
		$icon_markup = self::get_library_icon( $icon, false, $icon_size );

		// Fallback to info icon if no icon found.
		if ( empty( $icon_markup ) ) {
			$icon_markup = self::get_library_icon( 'info', false, $icon_size );
		}

		// Build data attributes string.
		$data_attrs = '';
		foreach ( $data_attributes as $key => $value ) {
			$data_attrs .= sprintf( ' data-%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}

		// Build badge markup.
		$badge_text = ! empty( $text ) ? sprintf( '<span class="portal-badge-text sd-ml-6">%s</span>', esc_html( $text ) ) : '';

		$badge = sprintf(
			'<span class="portal-badge sd-inline-flex sd-items-center sd-justify-center sd-max-w-fit sd-max-h-fit sd-radius-9999 sd-font-medium sd-cursor-default sd-nowrap portal-badge-%s %s" data-type="%s"%s>%s%s</span>',
			esc_attr( $size ),
			esc_attr( $custom_classes ),
			esc_attr( $type ),
			$data_attrs,
			$icon_markup, // phpstan:ignore.
			$badge_text
		);

		// Allow filtering of final badge markup.
		echo do_shortcode( apply_filters( 'suredashboard_badge_markup', $badge, $type ) );
	}

	/**
	 * Method to return attribute type, default array.
	 *
	 * @param string $type    Attribute type.
	 * @param string $default Attribute default value.
	 *
	 * @return array<mixed> Returns attribute type, default array.
	 *
	 * @since 0.0.1
	 */
	public static function block_attr( $type = 'string', $default = '' ) {
		return [
			'type'    => $type,
			'default' => $default,
		];
	}

	/**
	 * Get Typography Dynamic CSS.
	 *
	 * @param  array<mixed> $attr The Attribute array.
	 * @param  string       $slug The field slug.
	 * @param  string       $selector The selector array.
	 * @param  array<mixed> $combined_selectors The combined selector array.
	 * @since  0.0.1
	 * @return array<mixed>
	 */
	public static function get_typography_css( $attr, $slug, $selector, $combined_selectors ) {
		$typo_css_desktop = [];
		$typo_css_tablet  = [];
		$typo_css_mobile  = [];

		$already_selectors_desktop = $combined_selectors['desktop'][ $selector ] ?? [];
		$already_selectors_tablet  = $combined_selectors['tablet'][ $selector ] ?? [];
		$already_selectors_mobile  = $combined_selectors['mobile'][ $selector ] ?? [];

		$family_slug     = $slug === '' ? 'fontFamily' : $slug . 'FontFamily';
		$weight_slug     = $slug === '' ? 'fontWeight' : $slug . 'FontWeight';
		$transform_slug  = $slug === '' ? 'fontTransform' : $slug . 'Transform';
		$decoration_slug = $slug === '' ? 'fontDecoration' : $slug . 'Decoration';
		$style_slug      = $slug === '' ? 'fontStyle' : $slug . 'FontStyle';

		$l_ht_slug        = $slug === '' ? 'lineHeight' : $slug . 'LineHeight';
		$f_sz_slug        = $slug === '' ? 'fontSize' : $slug . 'FontSize';
		$l_ht_type_slug   = $slug === '' ? 'lineHeightType' : $slug . 'LineHeightType';
		$f_sz_type_slug   = $slug === '' ? 'fontSizeType' : $slug . 'FontSizeType';
		$f_sz_type_t_slug = $slug === '' ? 'fontSizeTypeTablet' : $slug . 'FontSizeTypeTablet';
		$f_sz_type_m_slug = $slug === '' ? 'fontSizeTypeMobile' : $slug . 'FontSizeTypeMobile';
		$l_sp_slug        = $slug === '' ? 'letterSpacing' : $slug . 'LetterSpacing';
		$l_sp_type_slug   = $slug === '' ? 'letterSpacingType' : $slug . 'LetterSpacingType';

		$text_transform  = $attr[ $transform_slug ] ?? 'normal';
		$text_decoration = $attr[ $decoration_slug ] ?? 'none';
		$font_style      = $attr[ $style_slug ] ?? 'normal';

		$typo_css_desktop[ $selector ] = [
			'font-family'     => $attr[ $family_slug ],
			'text-transform'  => $text_transform,
			'text-decoration' => $text_decoration,
			'font-style'      => $font_style,
			'font-weight'     => $attr[ $weight_slug ],
			'font-size'       => isset( $attr[ $f_sz_slug ] ) ? self::get_css_value( $attr[ $f_sz_slug ], $attr[ $f_sz_type_slug ] ) : '',
			'line-height'     => isset( $attr[ $l_ht_slug ] ) ? self::get_css_value( $attr[ $l_ht_slug ], $attr[ $l_ht_type_slug ] ) : '',
			'letter-spacing'  => isset( $attr[ $l_sp_slug ] ) ? self::get_css_value( $attr[ $l_sp_slug ], $attr[ $l_sp_type_slug ] ) : '',
		];

		$typo_css_desktop[ $selector ] = array_merge(
			$typo_css_desktop[ $selector ],
			$already_selectors_desktop
		);

		$typo_css_tablet[ $selector ] = [
			'font-size'      => isset( $attr[ $f_sz_slug . 'Tablet' ] ) ? self::get_css_value( $attr[ $f_sz_slug . 'Tablet' ], $attr[ $f_sz_type_t_slug ] ?? $attr[ $f_sz_type_slug ] ) : '',
			'line-height'    => isset( $attr[ $l_ht_slug . 'Tablet' ] ) ? self::get_css_value( $attr[ $l_ht_slug . 'Tablet' ], $attr[ $l_ht_type_slug ] ) : '',
			'letter-spacing' => isset( $attr[ $l_sp_slug . 'Tablet' ] ) ? self::get_css_value( $attr[ $l_sp_slug . 'Tablet' ], $attr[ $l_sp_type_slug ] ) : '',
		];

		$typo_css_tablet[ $selector ] = array_merge(
			$typo_css_tablet[ $selector ],
			$already_selectors_tablet
		);

		$typo_css_mobile[ $selector ] = [
			'font-size'      => isset( $attr[ $f_sz_slug . 'Mobile' ] ) ? self::get_css_value( $attr[ $f_sz_slug . 'Mobile' ], $attr[ $f_sz_type_m_slug ] ?? $attr[ $f_sz_type_slug ] ) : '',
			'line-height'    => isset( $attr[ $l_ht_slug . 'Mobile' ] ) ? self::get_css_value( $attr[ $l_ht_slug . 'Mobile' ], $attr[ $l_ht_type_slug ] ) : '',
			'letter-spacing' => isset( $attr[ $l_sp_slug . 'Mobile' ] ) ? self::get_css_value( $attr[ $l_sp_slug . 'Mobile' ], $attr[ $l_sp_type_slug ] ) : '',
		];

		$typo_css_mobile[ $selector ] = array_merge(
			$typo_css_mobile[ $selector ],
			$already_selectors_mobile
		);

		return [
			'desktop' => array_merge(
				$combined_selectors['desktop'],
				$typo_css_desktop
			),
			'tablet'  => array_merge(
				$combined_selectors['tablet'],
				$typo_css_tablet
			),
			'mobile'  => array_merge(
				$combined_selectors['mobile'],
				$typo_css_mobile
			),
		];
	}

	/**
	 * Border attribute generation Function.
	 *
	 * @since 0.0.1
	 * @param  string $prefix   Attribute Prefix.
	 * @return array<string, string>
	 */
	public static function generate_border_attribute( $prefix ) {
		$border_attr = [];

		$device = [ '', 'Tablet', 'Mobile' ];

		foreach ( $device as $data ) {
			$border_attr[ "{$prefix}BorderTopWidth{$data}" ]          = '';
			$border_attr[ "{$prefix}BorderLeftWidth{$data}" ]         = '';
			$border_attr[ "{$prefix}BorderRightWidth{$data}" ]        = '';
			$border_attr[ "{$prefix}BorderBottomWidth{$data}" ]       = '';
			$border_attr[ "{$prefix}BorderTopLeftRadius{$data}" ]     = '';
			$border_attr[ "{$prefix}BorderTopRightRadius{$data}" ]    = '';
			$border_attr[ "{$prefix}BorderBottomLeftRadius{$data}" ]  = '';
			$border_attr[ "{$prefix}BorderBottomRightRadius{$data}" ] = '';
			$border_attr[ "{$prefix}BorderRadiusUnit{$data}" ]        = 'px';
		}

		$border_attr[ "{$prefix}BorderStyle" ]  = '';
		$border_attr[ "{$prefix}BorderColor" ]  = '';
		$border_attr[ "{$prefix}BorderHColor" ] = '';
		return $border_attr;
	}

	/**
	 * Background Control CSS Generator Function.
	 *
	 * @param array<string, mixed> $bg_obj          The background object with all CSS properties.
	 * @param string               $css_for_overlay The overlay option ('no' or 'yes') to determine whether to include overlay CSS. Leave empty for blocks that do not use the '::before' overlay.
	 *
	 * @return array<string, mixed>                  The formatted CSS properties for the background.
	 */
	public static function get_background_obj( $bg_obj, $css_for_overlay = '' ) {
		$gen_bg_css         = [];
		$gen_bg_overlay_css = [];

		$bg_type             = $bg_obj['backgroundType'] ?? '';
		$bg_img              = isset( $bg_obj['backgroundImage'] ) && isset( $bg_obj['backgroundImage']['url'] ) ? $bg_obj['backgroundImage']['url'] : '';
		$bg_color            = $bg_obj['backgroundColor'] ?? '';
		$gradient_value      = $bg_obj['gradientValue'] ?? '';
		$gradient_color1     = $bg_obj['gradientColor1'] ?? '';
		$gradient_color2     = $bg_obj['gradientColor2'] ?? '';
		$gradient_type       = $bg_obj['gradientType'] ?? '';
		$gradient_location1  = $bg_obj['gradientLocation1'] ?? '';
		$gradient_location2  = $bg_obj['gradientLocation2'] ?? '';
		$gradient_angle      = $bg_obj['gradientAngle'] ?? '';
		$select_gradient     = $bg_obj['selectGradient'] ?? '';
		$repeat              = $bg_obj['backgroundRepeat'] ?? '';
		$position            = $bg_obj['backgroundPosition'] ?? '';
		$size                = $bg_obj['backgroundSize'] ?? '';
		$attachment          = $bg_obj['backgroundAttachment'] ?? '';
		$overlay_type        = $bg_obj['overlayType'] ?? '';
		$overlay_opacity     = $bg_obj['overlayOpacity'] ?? '';
		$bg_image_color      = $bg_obj['backgroundImageColor'] ?? '';
		$bg_custom_size      = $bg_obj['backgroundCustomSize'] ?? '';
		$bg_custom_size_type = $bg_obj['backgroundCustomSizeType'] ?? '';
		$bg_video            = $bg_obj['backgroundVideo'] ?? '';
		$bg_video_color      = $bg_obj['backgroundVideoColor'] ?? '';

		$custom_position = $bg_obj['customPosition'] ?? '';
		$x_position      = $bg_obj['xPosition'] ?? '';
		$x_position_type = $bg_obj['xPositionType'] ?? '';
		$y_position      = $bg_obj['yPosition'] ?? '';
		$y_position_type = $bg_obj['yPositionType'] ?? '';

		$bg_overlay_img              = $bg_obj['backgroundOverlayImage']['url'] ?? '';
		$overlay_repeat              = $bg_obj['backgroundOverlayRepeat'] ?? '';
		$overlay_position            = $bg_obj['backgroundOverlayPosition'] ?? '';
		$overlay_size                = $bg_obj['backgroundOverlaySize'] ?? '';
		$overlay_attachment          = $bg_obj['backgroundOverlayAttachment'] ?? '';
		$blend_mode                  = $bg_obj['blendMode'] ?? '';
		$bg_overlay_custom_size      = $bg_obj['backgroundOverlayCustomSize'] ?? '';
		$bg_overlay_custom_size_type = $bg_obj['backgroundOverlayCustomSizeType'] ?? '';

		$custom_overlay__position = $bg_obj['customOverlayPosition'] ?? '';
		$x_overlay_position       = $bg_obj['xOverlayPosition'] ?? '';
		$x_overlay_position_type  = $bg_obj['xOverlayPositionType'] ?? '';
		$y_overlay_position       = $bg_obj['yOverlayPosition'] ?? '';
		$y_overlay_position_type  = $bg_obj['yOverlayPositionType'] ?? '';

		$custom_x_position = self::get_css_value( $x_position, $x_position_type );
		$custom_y_position = self::get_css_value( $y_position, $y_position_type );

		$gradient = '';
		if ( $size === 'custom' ) {
			$size = $bg_custom_size . $bg_custom_size_type;
		}
		if ( $select_gradient === 'basic' ) {
			$gradient = $gradient_value;
		} elseif ( $gradient_type === 'linear' && $select_gradient === 'advanced' ) {
			$gradient = 'linear-gradient(' . $gradient_angle . 'deg, ' . $gradient_color1 . ' ' . $gradient_location1 . '%, ' . $gradient_color2 . ' ' . $gradient_location2 . '%)';
		} elseif ( $gradient_type === 'radial' && $select_gradient === 'advanced' ) {
			$gradient = 'radial-gradient( at center center, ' . $gradient_color1 . ' ' . $gradient_location1 . '%, ' . $gradient_color2 . ' ' . $gradient_location2 . '%)';
		}

		if ( $bg_type !== '' ) {
			switch ( $bg_type ) {
				case 'color':
					if ( $bg_img !== '' && $bg_color !== '' ) {
						$gen_bg_css['background-image'] = 'linear-gradient(to right, ' . $bg_color . ', ' . $bg_color . '), url(' . $bg_img . ');';
					} elseif ( $bg_img === '' ) {
						$gen_bg_css['background-color'] = $bg_color . ';';
					}
					break;

				case 'image':
					$gen_bg_css['background-repeat'] = esc_attr( $repeat );

					if ( $custom_position !== 'custom' && isset( $position['x'] ) && isset( $position['y'] ) ) {
						$position_value                    = $position['x'] * 100 . '% ' . $position['y'] * 100 . '%';
						$gen_bg_css['background-position'] = $position_value;
					} elseif ( $custom_position === 'custom' ) {
						$position_value                    = $bg_obj['centralizedPosition'] === false ? $custom_x_position . ' ' . $custom_y_position : 'calc(50% +  ' . $custom_x_position . ') calc(50% + ' . $custom_y_position . ')';
						$gen_bg_css['background-position'] = $position_value;
					}

					$gen_bg_css['background-size'] = esc_attr( $size );

					$gen_bg_css['background-attachment'] = esc_attr( $attachment );

					if ( $overlay_type === 'color' && $bg_img !== '' && $bg_image_color !== '' ) {
						if ( ! empty( $css_for_overlay ) ) {
							$gen_bg_css['background-image']   = 'url(' . $bg_img . ');';
							$gen_bg_overlay_css['background'] = $bg_image_color;
							$gen_bg_overlay_css['opacity']    = $overlay_opacity;
						} else {
							$gen_bg_css['background-image'] = 'linear-gradient(to right, ' . $bg_image_color . ', ' . $bg_image_color . '), url(' . $bg_img . ');';
						}
					}
					if ( $overlay_type === 'gradient' && $bg_img !== '' && $gradient !== '' ) {
						if ( ! empty( $css_for_overlay ) ) {
							$gen_bg_css['background-image']         = 'url(' . $bg_img . ');';
							$gen_bg_overlay_css['background-image'] = $gradient;
							$gen_bg_overlay_css['opacity']          = $overlay_opacity;
						} else {
							$gen_bg_css['background-image'] = $gradient . ', url(' . $bg_img . ');';
						}
					}
					if ( $bg_img !== '' && in_array( $overlay_type, [ '', 'none', 'image' ] ) ) {
						$gen_bg_css['background-image'] = 'url(' . $bg_img . ');';
					}

					$gen_bg_css['background-clip'] = 'padding-box';
					break;

				case 'gradient':
					if ( isset( $gradient ) ) {
						$gen_bg_css['background']      = $gradient . ';';
						$gen_bg_css['background-clip'] = 'padding-box';
					}
					break;
				case 'video':
					if ( $overlay_type === 'color' && $bg_video !== '' && $bg_video_color !== '' ) {
						$gen_bg_css['background'] = $bg_video_color . ';';
					}
					if ( $overlay_type === 'gradient' && $bg_video !== '' && $gradient !== '' ) {
						$gen_bg_css['background-image'] = $gradient . ';';
					}
					break;

				default:
					break;
			}
		} elseif ( $bg_color !== '' ) {
			$gen_bg_css['background-color'] = $bg_color . ';';
		}

		// image overlay.
		if ( $overlay_type === 'image' ) {
			if ( $overlay_size === 'custom' ) {
				$overlay_size = $bg_overlay_custom_size . $bg_overlay_custom_size_type;
			}

			if ( $overlay_repeat ) {
				$gen_bg_overlay_css['background-repeat'] = esc_attr( $overlay_repeat );
			}
			if ( $custom_overlay__position !== 'custom' && $overlay_position && isset( $overlay_position['x'] ) && isset( $overlay_position['y'] ) ) {
				$position_overlay_value                    = $overlay_position['x'] * 100 . '% ' . $overlay_position['y'] * 100 . '%';
				$gen_bg_overlay_css['background-position'] = $position_overlay_value;
			} elseif ( $custom_overlay__position === 'custom' && $x_overlay_position && $y_overlay_position && $x_overlay_position_type && $y_overlay_position_type ) {
				$position_overlay_value                    = $x_overlay_position . $x_overlay_position_type . ' ' . $y_overlay_position . $y_overlay_position_type;
				$gen_bg_overlay_css['background-position'] = $position_overlay_value;
			}

			if ( $overlay_size ) {
				$gen_bg_overlay_css['background-size'] = esc_attr( $overlay_size );
			}

			if ( $overlay_attachment ) {
				$gen_bg_overlay_css['background-attachment'] = esc_attr( $overlay_attachment );
			}
			if ( $blend_mode ) {
				$gen_bg_overlay_css['mix-blend-mode'] = esc_attr( $blend_mode );
			}
			if ( $bg_overlay_img !== '' ) {
				$gen_bg_overlay_css['background-image'] = 'url(' . $bg_overlay_img . ');';
			}
			$gen_bg_overlay_css['background-clip'] = 'padding-box';
			$gen_bg_overlay_css['opacity']         = $overlay_opacity;
		}

		return $css_for_overlay === 'yes' ? $gen_bg_overlay_css : $gen_bg_css;
	}

	/**
	 * Border CSS generation Function.
	 *
	 * @since 0.0.1
	 * @param  array<string, string> $attr   Attribute List.
	 * @param  string                $prefix Attribute prefix .
	 * @param  string                $device Responsive.
	 * @return array<string, string>         border css array.
	 */
	public static function generate_border_css( $attr, $prefix, $device = 'desktop' ) {
		$gen_border_css = [];
		// ucfirst function is used to transform text into first letter capital.
		$device = $device === 'desktop' ? '' : ucfirst( $device );
		if ( $attr[ $prefix . 'BorderStyle' ] !== 'none' && ! empty( $attr[ $prefix . 'BorderStyle' ] ) ) {
			$gen_border_css['border-top-width']    = self::get_css_value( $attr[ $prefix . 'BorderTopWidth' . $device ], 'px' );
			$gen_border_css['border-left-width']   = self::get_css_value( $attr[ $prefix . 'BorderLeftWidth' . $device ], 'px' );
			$gen_border_css['border-right-width']  = self::get_css_value( $attr[ $prefix . 'BorderRightWidth' . $device ], 'px' );
			$gen_border_css['border-bottom-width'] = self::get_css_value( $attr[ $prefix . 'BorderBottomWidth' . $device ], 'px' );
		}
		$gen_border_unit                              = $attr[ $prefix . 'BorderRadiusUnit' . $device ] ?? 'px';
		$gen_border_css['border-top-left-radius']     = self::get_css_value( $attr[ $prefix . 'BorderTopLeftRadius' . $device ], $gen_border_unit );
		$gen_border_css['border-top-right-radius']    = self::get_css_value( $attr[ $prefix . 'BorderTopRightRadius' . $device ], $gen_border_unit );
		$gen_border_css['border-bottom-left-radius']  = self::get_css_value( $attr[ $prefix . 'BorderBottomLeftRadius' . $device ], $gen_border_unit );
		$gen_border_css['border-bottom-right-radius'] = self::get_css_value( $attr[ $prefix . 'BorderBottomRightRadius' . $device ], $gen_border_unit );

		$gen_border_css['border-style'] = $attr[ $prefix . 'BorderStyle' ];
		$gen_border_css['border-color'] = $attr[ $prefix . 'BorderColor' ];

		if ( $attr[ $prefix . 'BorderStyle' ] === 'default' ) {
			return [];
		}

		return $gen_border_css;
	}

	/**
	 * Returns recent docs stored in user's browser cookie.
	 *
	 * @return array<int, int|string> Returns first n recent docs.
	 *
	 * @since 0.0.1
	 */
	public static function get_recent_searched_items() {
		global $post;

		$recent_items_limit = apply_filters( 'portal_search_recent_items_limit', 4 );

		if ( ! is_int( $recent_items_limit ) || $recent_items_limit < 1 ) {
			return [];
		}

		// Validate and sanitize the recent docs cookie.
		$recent_items_cookie = isset( $_COOKIE['portal_recently_viewed'] ) ? explode( 'portal', sanitize_text_field( wp_unslash( $_COOKIE['portal_recently_viewed'] ) ) ) : [];

		if ( is_single() && is_object( $post ) && isset( $post->post_type ) ) {
			// Remove the current item ID if it's already in the list, add fetch first n-1 recent docs.
			$recent_items_cookie = array_diff( $recent_items_cookie, [ strval( $post->ID ?? 0 ) ] );

			// Insert the current item ID at the beginning of the recent docs list.
			array_unshift( $recent_items_cookie, intval( $post->ID ?? 0 ) );
		}

		// Fetch the first n recent docs.
		return array_slice( $recent_items_cookie, 0, $recent_items_limit );
	}

	/**
	 * Method to get pagination markup for internal categories.
	 *
	 * @param int  $category_id Category ID.
	 * @param int  $base_id Base ID.
	 * @param bool $echo Whether to echo or return.
	 *
	 * @return string Returns pagination markup.
	 *
	 * @since 0.0.1
	 */
	public static function get_archive_pagination_markup( $category_id, $base_id = 0, $echo = true ) {
		$term_object = get_term( $category_id );
		$taxonomy    = ! empty( $term_object->taxonomy ) ? $term_object->taxonomy : '';
		$post_type   = SUREDASHBOARD_FEED_POST_TYPE;
		$markup      = sprintf(
			'</div>
			<div class="portal-pagination-loader">
				<div class="portal-pagination-loader-1"></div>
				<div class="portal-pagination-loader-2"></div>
				<div class="portal-pagination-loader-3"></div>
			</div>
			<div class="portal-infinite-trigger" data-category="%d" data-base_id="%d" data-post_type="%s" data-taxonomy="%s">', // Closed div first for .portal-content-area and opened a new div for .portal-infinite-trigger, to get this after the content area.
			absint( $category_id ),
			absint( $base_id ),
			$post_type,
			$taxonomy
		);

		if ( $echo ) {
			echo wp_kses_post( $markup );
			return '';
		}

		return wp_kses_post( $markup );
	}

	/**
	 * Method to get the post excerpt by post ID.
	 *
	 * @param string $post_link Post link.
	 * @param string $post_title Post title.
	 *
	 * @return array<string, array<string, mixed>> social triggers.
	 * @since 0.0.1
	 */
	public static function suredash_social_triggers( $post_link, $post_title ) {
		return apply_filters(
			'portal_social_triggers',
			[
				'facebook' => [
					'label' => Labels::get_label( 'share_on_facebook' ),
					'icon'  => '<span class="portal-svg-icon portal-icon-sm" aria-hidden="true" aria-label="Facebook"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.8 90.69 226.4 209.3 245V327.7h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.3 482.4 504 379.8 504 256z"></path></svg></span>',
					'link'  => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode( $post_link ),
				],
				'twitter'  => [
					'label' => Labels::get_label( 'share_on_twitter' ),
					'icon'  => '<span class="portal-svg-icon portal-icon-sm" aria-hidden="true" aria-label="Twitter"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg></span>',
					'link'  => add_query_arg(
						[
							'url'  => $post_link,
							'text' => rawurlencode( html_entity_decode( wp_strip_all_tags( $post_title ), ENT_COMPAT, 'UTF-8' ) ),
						],
						'http://twitter.com/share'
					),
				],
				'linkedin' => [
					'label' => Labels::get_label( 'share_on_linkedin' ),
					'icon'  => '<span class="portal-svg-icon portal-icon-sm" aria-hidden="true" aria-label="LinkedIn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"></path></svg></span>',
					'link'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . urlencode( $post_link ) . '&title=' . rawurlencode( $post_title ) . '&source=' . urlencode( get_bloginfo( 'name' ) ),
				],

				'copy'     => [
					'label' => Labels::get_label( 'copy_to_clipboard' ),
					'icon'  => '<span class="portal-svg-icon portal-icon-sm" aria-hidden="true" aria-label="Copy"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-copy-icon lucide-clipboard-copy" style="color: #000;"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/><path d="M16 4h2a2 2 0 0 1 2 2v4"/><path d="M21 14H11"/><path d="m15 10-4 4 4 4"/></svg></span>',
					'link'  => $post_link,
				],
			]
		);
	}

	/**
	 * Method to get the post excerpt length.
	 *
	 * @param int $length Post excerpt length.
	 *
	 * @return int Returns the post excerpt length.
	 * @since 0.0.1
	 */
	public static function suredash_excerpt_length( $length ) {
		return apply_filters( 'suredash_excerpt_length', 20 );
	}

	/**
	 * Method to get the post content by post ID.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $content_type Content type.
	 *
	 * @return string Returns the post content.
	 * @since 0.0.1
	 */
	public static function get_post_content( $post_id, $content_type ) {
		if ( $content_type === 'excerpt' ) {
			add_filter( 'excerpt_length', self::class . '::suredash_excerpt_length', 1 );
			add_filter(
				'excerpt_more',
				static function ( $more ) {
					return '...';
				}
			);
			$post_content = wp_trim_excerpt( get_the_excerpt( $post_id ) );
			remove_filter( 'excerpt_length', self::class . '::suredash_excerpt_length', 1 );
		} else {
			$post_content = get_post_field( 'post_content', $post_id );
		}

		return do_shortcode( $post_content );
	}

	/**
	 * Render Post.
	 *
	 * @param array<int, array<string, mixed>> $post Post array.
	 * @param int                              $base_post_id Base Post ID.
	 * @param bool                             $is_pinned Is Pinned.
	 * @param bool                             $comments Show Comments.
	 * @return mixed HTML content.
	 * @since 0.0.1
	 */
	public static function render_post( $post, $base_post_id = 0, $is_pinned = false, $comments = true ) {
		$args = [
			'post'         => $post,
			'base_post_id' => $base_post_id,
			'is_pinned'    => $is_pinned,
			'comments'     => $comments,
		];

		$post_id = absint( $post['ID'] ?? 0 );
		if ( ! $post_id || ! sd_post_exists( $post_id ) ) {
			return '';
		}
		if ( ! sd_is_post_publish( $post_id ) ) {
			return '';
		}

		ob_start();

		if ( suredash_is_post_protected( $post_id ) ) {
			suredash_get_restricted_template_part(
				$post_id,
				'parts',
				'restricted',
				[
					'icon'        => 'Lock',
					'label'       => 'restricted_content',
					'description' => 'restricted_content_description',
				]
			);
		} else {
			suredash_get_template_part(
				'single',
				'post',
				$args
			);
		}

		$content = (string) preg_replace( '/<p>\s*<\/p>/', '', (string) ob_get_clean() );

		$content = suredash_dynamic_content_support( $content );

		echo do_shortcode( $content );
	}

	/**
	 * Method to get lightbox filtered selectors.
	 *
	 * @param string $type Selector type.
	 *
	 * @return string Returns lightbox filtered selectors.
	 * @since 0.0.1
	 */
	public static function get_lightbox_selector( $type ) {
		$selectors = '';
		switch ( $type ) {
			case 'single':
				$selectors = apply_filters( 'suredash_lightbox_single_selectors', '.wp-block-media-text, .wp-block-image, .wp-block-uagb-image__figure' );
				break;
			case 'gallery':
				$selectors = apply_filters( 'suredash_lightbox_gallery_selectors', '.wp-block-gallery' );
				break;
			default:
				break;
		}

		// filter selectors.
		$selectors = explode( ',', $selectors );
		$selectors = array_map( 'trim', $selectors );
		return implode( ', ', $selectors );
	}

	/**
	 * Returns array in format required by React Select dropdown
	 * passed array should have id in key and label in value.
	 *
	 * @param array<string, string> $data_array The data array to be converted to React Select format.
	 * @return array<int, array<string, string>>
	 */
	public static function get_react_select_format( $data_array = [] ) {
		$response = [];
		if ( empty( $data_array ) ) {
			return $response;
		}

		foreach ( $data_array as $id => $title ) {
			$response[] = [
				'id'   => $id,
				'name' => $title,
			];
		}

		return $response;
	}

	/**
	 * Method to render post reaction markup. It includes like and comment buttons.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $wrapper Wrapper class.
	 * @param bool   $show_comments Show comments.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public static function render_post_reaction( $post_id, $wrapper, $show_comments = true ): void {
		$comments_open   = $show_comments && comments_open( $post_id );
		$current_user_id = get_current_user_id();

		$user_liked_posts = sd_get_user_meta( $current_user_id, 'portal_user_liked_posts', true );
		$user_liked_posts = is_array( $user_liked_posts ) ? $user_liked_posts : [];
		$is_user_liked    = in_array( $post_id, $user_liked_posts, true );

		$permalink  = (string) get_the_permalink( $post_id );
		$post_title = get_the_title( $post_id );
		$post_id    = (string) $post_id;

		?>
		<div class="<?php echo esc_attr( $wrapper ); ?> sd-relative">
			<div class="portal-comment-header">
				<?php if ( $current_user_id ) { ?>
					<span class="sd-post-reaction <?php echo esc_attr( $is_user_liked ? 'liked' : '' ); ?>" data-post_id="<?php echo esc_attr( $post_id ); ?>" title="<?php echo esc_attr__( 'Like', 'suredash' ); ?>" aria-label="<?php echo esc_attr__( 'Like', 'suredash' ); ?>" data-reaction_type="like"> <?php self::get_library_icon( 'Heart', true ); ?> </span>

					<?php if ( $comments_open ) { ?>
						<span class="sd-post-reaction" data-post_id="<?php echo esc_attr( $post_id ); ?>" title="<?php echo esc_attr__( 'Comment', 'suredash' ); ?>" aria-label="<?php echo esc_attr__( 'Comment', 'suredash' ); ?>" data-reaction_type="comment" data-type="comment"> <?php self::get_library_icon( 'MessageCircle', true ); ?> </span>
					<?php } ?>
				<?php } ?>

				<?php if ( $current_user_id ) { ?>
					<span class="portal-post-share-trigger sd-flex sd-pointer" data-post-id="<?php echo esc_attr( $post_id ); ?>" title="<?php esc_attr_e( 'Share Post', 'suredash' ); ?>">
						<?php
						Helper::get_library_icon( 'Share2', true );
						?>
					</span>
				<div class="portal-post-sharing-wrapper">
					<div class="portal-post-sharing-links sd-box-shadow sd-p-4 sd-px-8 sd-flex sd-items-center sd-gap-12 sd-radius-4">
						<?php
						foreach ( Helper::suredash_social_triggers( $permalink, $post_title ) as $key => $trigger ) {
							if ( $key === 'copy' ) {
								?>
								<button class="portal-post-sharing-link portal-copy-to-clipboard sd-flex sd-items-center sd-relative sd-pointer sd-bg-transparent sd-m-0 sd-p-0 sd-border-none sd-outline-none sd-hover-scale-110 sd-force-shadow-none"
									data-link="<?php echo esc_url( $permalink ); ?>"
									title="<?php echo esc_attr( $trigger['label'] ); ?>">
									<?php echo do_shortcode( $trigger['icon'] ); ?>
									<span class="tooltip sd-hidden sd-opacity-0 sd-color-white sd-text-center sd-radius-4 sd-p-6 sd-absolute sd-nowrap sd-font-12"><?php echo esc_html__( 'Copied!', 'suredash' ); ?></span>
								</button>
								<?php
							} else {
								?>
								<a href="<?php echo esc_url( $trigger['link'] ); ?>"
									class="portal-post-sharing-link sd-flex sd-items-center sd-hover-scale-110"
									title="<?php echo esc_attr( $trigger['label'] ); ?>"
									target="_blank">
									<?php echo do_shortcode( $trigger['icon'] ); ?>
								</a>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php } ?>

				<?php
				if ( ! $current_user_id ) {
					echo '<div class="sd-flex sd-gap-6 sd-items-center sd-justify-center sd-font-14">';
					Helper::get_login_notice( 'comment' );
					echo '</div>';
				}
				?>

			</div>

			<div class="portal-post-interactions-wrap">
				<?php
				$likes_count = sd_get_post_meta( absint( $post_id ), 'portal_post_likes', true );
				$likes_count = is_array( $likes_count ) ? $likes_count : [];
				$likes_count = (string) count( $likes_count );
				?>
				<span class="portal-likes-count" data-count="<?php echo esc_attr( $likes_count ); ?>" data-type="like" data-post_id="<?php echo esc_attr( (string) $post_id ); ?>">
					<?php echo '<span class="counter">' . esc_html( $likes_count ) . '</span> ' . esc_html( _n( 'Like', 'Likes', absint( $likes_count ), 'suredash' ) ); ?>
				</span>

				<?php if ( $likes_count && $comments_open ) { ?>
					<span class="portal-reaction-separator sd-no-space"></span>
					<?php
				}

				if ( $comments_open ) {
					?>
					<span class="portal-comments-count" data-type="comment" data-post_id="<?php echo esc_attr( $post_id ); ?>">
						<?php
						$comments_count = get_comments_number( absint( $post_id ) );
						echo '<span class="counter">' . esc_html( (string) $comments_count ) . '</span> ' . esc_html( _n( 'Comment', 'Comments', absint( $comments_count ), 'suredash' ) );
						?>
					</span>
				<?php } ?>
			</div>
		</div>

		<?php if ( $comments_open && $current_user_id ) { ?>
			<?php
			$params = [
				[
					'post_id'    => absint( $post_id ),
					'number'     => 1,
					'orderby'    => 'comment_date',
					'order'      => 'DESC',
					'meta_query' => [
						[
							'key'     => 'portal_comment_likes',
							'value'   => sprintf( ':%d;', get_post_field( 'post_author', absint( $post_id ) ) ),
							'compare' => 'LIKE',
						],
					],
				],
				[
					'post_id' => (int) $post_id,
					'user_id' => $current_user_id,
					'parent'  => 0,
					'number'  => 1,
					'status'  => 'approve',
					'orderby' => 'comment_date',
					'order'   => 'DESC',
				],
			];

			suredash_comments_markup( (int) $post_id, true, $params, ' sd-mt-20', '' );
		}
	}

	/**
	 * Method to render featured excerpt if available.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public static function suredash_featured_cover( $post_id ): void {
		$cover_image = sd_get_post_meta( $post_id, 'custom_post_cover_image', true );
		$cover_embed = sd_get_post_meta( $post_id, 'custom_post_embed_media', true );
		$post_title  = get_the_title( $post_id );

		if ( $cover_image ) {
			?>
			<div class="portal-post-cover-image">
				<img src="<?php echo esc_url( $cover_image ); ?>" alt="<?php echo esc_attr( $post_title ); ?>">
			</div>
			<?php
		} elseif ( $cover_embed ) {
			?>
			<div class="portal-post-cover-embed">
				<?php
				$embed_html = wp_oembed_get( $cover_embed, [ 'width' => 600 ] );
				$html       = $embed_html ? $embed_html : '';
				wp_maybe_enqueue_oembed_host_js( $html );
				echo do_shortcode( $html );
				?>
			</div>
			<?php
		}
	}

	/**
	 * Method to get all posts with internal CPTs associated with a user.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public static function suredash_user_posts( $user_id ): void {
		$post_types = apply_filters(
			'suredashboard_user_posts_within',
			[
				SUREDASHBOARD_FEED_POST_TYPE,
			]
		);

		$user_posts_query = Controller::get_user_query_data(
			'Feeds',
			apply_filters(
				'suredashboard_user_queried_post_args',
				[
					'post_types'     => $post_types,
					'user_id'        => $user_id,
					'posts_per_page' => self::get_option( 'feeds_per_page', 5 ),
				]
			)
		);

		if ( ! empty( $user_posts_query ) && is_array( $user_posts_query ) ) {
			foreach ( $user_posts_query as $post ) {
				// Ensure the post object is valid.
				if ( empty( $post ) || ! isset( $post['ID'] ) ) {
					continue;
				}

				// If the topic is private, skip rendering.
				if ( suredash_is_post_protected( absint( $post['ID'] ) ) ) {
					continue;
				}

				// Render the post.
				Helper::render_post( $post );
			}
		} else {
			suredash_get_template_part( 'parts', '404' );
		}

		wp_reset_postdata();
	}

	/**
	 * Method to get all comments associated with a user on internal CPTs.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public static function suredash_user_comments( $user_id ): void {
		$post_types = apply_filters(
			'suredashboard_user_posts_within',
			[
				SUREDASHBOARD_FEED_POST_TYPE,
			]
		);

		$no_of_comments = self::get_option( 'feeds_per_page', 5 );

		$args = apply_filters(
			'suredashboard_user_queried_comment_args',
			[
				'post_type'                 => $post_types,
				'author__in'                => [ $user_id ],
				'number'                    => $no_of_comments,
				'status'                    => 'approve',
				'update_comment_meta_cache' => false,
			]
		);

		$user_comments_query = new \WP_Comment_Query( $args );

		if ( ! empty( $user_comments_query->comments ) && is_array( $user_comments_query->comments ) ) {
			foreach ( $user_comments_query->comments as $comment ) {
				if ( ! $comment instanceof \WP_Comment ) {
					continue;
				}

				$post_id = absint( $comment->comment_post_ID );

				if ( suredash_is_post_protected( $post_id ) ) {
					continue;
				}

				$post_title = get_the_title( $post_id );

				$args = [
					'author_id'       => $user_id,
					'post_id'         => $post_id,
					'post_title'      => $post_title,
					'comment_content' => $comment->comment_content,
				];

				ob_start();

				suredash_get_template_part(
					'single',
					'comment',
					$args
				);

				$content = (string) preg_replace( '/<p>\s*<\/p>/', '', (string) ob_get_clean() );

				echo do_shortcode( $content );
			}
		} else {
			suredash_get_template_part(
				'parts',
				'404',
				[
					'not_found_text' => Labels::get_label( 'no_comments_found' ),
				]
			);
		}

		wp_reset_postdata();
	}

	/**
	 * Get all community posts.
	 *
	 * @param array<mixed> $args Args.
	 *
	 * @return mixed
	 */
	public static function get_community_posts( $args = [] ) {
		$posts    = [];
		$category = $args['category'] ?? 0;
		if ( $category ) {
			$query = sd_query()->select( 'p.ID, p.post_title, p.post_type, p.post_status, p.post_date, p.post_author, p.comment_count' )
				->from( 'posts AS p' )
				->join( 'term_relationships AS tr', 'p.ID', '=', 'tr.object_id' )
				->join( 'term_taxonomy AS tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id' )
				->join( 'terms AS t', 'tt.term_id', '=', 't.term_id' ) // Ensure the join with 'terms' to filter based on terms.
				->where( 'p.post_type', '=', SUREDASHBOARD_FEED_POST_TYPE )
				->where( 'tt.taxonomy', '=', SUREDASHBOARD_FEED_TAXONOMY ) // Match the taxonomy.
				->where( 'tt.term_id', '=', $category ) // Match the category ID.
				->where( 'p.post_status', '!=', 'auto-draft' )
				->order_by( 'p.post_date', 'DESC' )
				->get( ARRAY_A );
		} else {
			$query = sd_query()->select( 'p.ID, p.post_title, p.post_type, p.post_status, p.post_date, p.post_author, p.comment_count' )
				->from( 'posts AS p' )
				->where( 'p.post_type', '=', SUREDASHBOARD_FEED_POST_TYPE )
				->where( 'p.post_status', '!=', 'auto-draft' )
				->order_by( 'p.post_date', 'DESC' )
				->get( ARRAY_A );
		}

		if ( ! empty( $query ) && is_array( $query ) ) {
			foreach ( $query as $post ) {
				if ( empty( $post['ID'] ) ) {
					continue;
				}

				$feed_id = absint( $post['ID'] );
				$group   = get_the_terms( $feed_id, SUREDASHBOARD_FEED_TAXONOMY ) ?? null;

				if ( is_array( $group ) ) {
					$group = $group[0];
				}

				$posts[] = [
					'id'             => $feed_id,
					'name'           => html_entity_decode( $post['post_title'] ),
					'author'         => [
						'id'   => $post['post_author'],
						'name' => get_the_author_meta( 'display_name', $post['post_author'] ),
					],
					'view_url'       => get_permalink( $feed_id ),
					'edit_url'       => get_edit_post_link( $feed_id ),
					'group'          => [
						'id'   => $group->term_id ?? 0,
						'name' => $group->name ?? __( 'Uncategorized', 'suredash' ),
					],
					'status'         => $post['post_status'],
					'comments'       => [
						'count' => $post['comment_count'],
						'url'   => admin_url( 'edit-comments.php?p=' . $feed_id ),
					],
					'is_restricted'  => suredash_get_post_backend_restriction( $feed_id ),
					'comment_status' => get_post_field( 'comment_status', $post['ID'] ) ?? 'closed',
				];
			}
		}

		return $posts;
	}

	/**
	 * Method to get a login notice.
	 * This method is used to display a login notice for users who are not logged in.
	 *
	 * @param string $type Notice type.
	 * @param bool   $sure_member SureMembers status.
	 *
	 * @return void
	 * @since 0.0.2
	 */
	public static function get_login_notice( $type = 'comment', $sure_member = false ): void {
		if ( $sure_member ) {
			?>
			<div class="sd-flex-col sd-items-start sd-justify-center sd-font-14">
				<span class="portal-public-login-link"><?php esc_html_e( 'This discussion is only available for VIP members.', 'suredash' ); ?></span>
				<span class="sd-flex sd-gap-6">
					<span class=""><?php esc_html_e( 'Please upgrade to add response.', 'suredash' ); ?></span>
					<a href="<?php echo esc_url( suredash_get_login_page_url() ); ?>" class=""><?php esc_html_e( 'Upgrade Now', 'suredash' ); ?></a>
				</span>
			</div>
			<?php
			return;
		}

		if ( $type === 'comment' ) {
			?>
				<div class="sd-flex sd-gap-6 sd-font-14 sd-font-medium">
					<span class=""><?php Labels::get_label( 'login_or_join', true ); ?></span>
					<a href="<?php echo esc_url( suredash_get_login_page_url() ); ?>" class="sd-font-semibold"><?php esc_html_e( 'Login', 'suredash' ); ?></a>
				</div>
			<?php
		}
	}

	/**
	 * Method to set/unset wp core interactions.
	 * This method is used to bypass the default wp functions, hooks, and filters. And to use SureDash own REST + ORM functions.
	 *
	 * @return bool
	 */
	public static function bypass_wp_interfere() {
		return boolval( Helper::get_option( 'bypass_wp_interactions' ) );
	}

	/**
	 * Remove other template includes.
	 *
	 * 1. Breakdance.
	 *
	 * @since 0.0.3
	 * @return void
	 */
	public static function remove_other_template_includes(): void {
		if ( function_exists( 'Breakdance\ActionsFilters\template_include' ) ) {
			remove_action( 'template_include', 'Breakdance\ActionsFilters\template_include', 1000000 );
		}
	}

	/**
	 * Method to check if the post is third-party restricted.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array<string, mixed> Array with status and content.
	 * @since 1.0.0
	 */
	public static function maybe_third_party_restricted( $post_id ) {
		$ruleset = suredash_restriction_defaults();

		do_action( 'suredash_post_restriction_before_check', $ruleset, $post_id );

		$restriction = apply_filters(
			'suredash_post_restriction_ruleset',
			$ruleset,
			$post_id
		);

		do_action( 'suredash_post_restriction_after_check', $ruleset, $post_id );

		return $restriction;
	}

	/**
	 * Method to get the pinned posts.
	 *
	 * @param int $space_id Space ID.
	 *
	 * @return array<int, int> Pinned posts.
	 * @since 1.0.0
	 */
	public static function get_pinned_posts( $space_id ) {
		$pinned_posts = PostMeta::get_post_meta_value( $space_id, 'pinned_posts' );
		$pinned_posts = ! is_array( $pinned_posts ) ? [] : $pinned_posts;
		$pinned_posts = array_column( $pinned_posts, 'value' );
		$pinned_posts = array_map( 'absint', $pinned_posts );

		if ( empty( $pinned_posts ) ) {
			return [];
		}

		// Verify the posts under pinned posts are publish.
		return array_filter(
			$pinned_posts,
			static function ( $post_id ) {
				return sd_is_post_publish( $post_id );
			}
		);
	}

	/**
	 * Method to get the layout details, like type, content width, and aside spacing.
	 *
	 * @param string $layout Layout.
	 * @param string $style Layout style.
	 *
	 * @return array<string, string> Layout details.
	 * @since 1.0.0
	 */
	public static function get_layout_details( $layout = '', $style = '' ) {
		if ( empty( $layout ) || $layout === 'global' ) {
			$global_layout = Helper::get_option( 'global_layout' );
			$layout        = $global_layout;
		}

		$style = self::get_layout_style( $style );

		switch ( $layout ) {
			default:
			case $layout === 'full_width':
				$layout_details = [
					'layout'        => 'full_width',
					'style'         => $style,
					'content_width' => '100%',
					'aside_spacing' => $style === 'unboxed' ? '0' : '32px',
				];
				break;

			case $layout === 'normal':
				$layout_details = [
					'layout'        => 'normal',
					'style'         => $style,
					'content_width' => 'var(--portal-normal-container-width)',
					'aside_spacing' => '0 auto 32px',
				];
				break;

			case $layout === 'narrow':
				$layout_details = [
					'layout'        => 'narrow',
					'style'         => $style,
					'content_width' => 'var(--portal-narrow-container-width)',
					'aside_spacing' => '0 auto 32px',
				];
				break;
		}

		return $layout_details;
	}

	/**
	 * Method to get synced layout style.
	 *
	 * @param string $style Layout.
	 *
	 * @return string Layout Style.
	 * @since 1.0.0
	 */
	public static function get_layout_style( $style = '' ) {
		if ( empty( $style ) || $style === 'global' ) {
			$global_layout_style = Helper::get_option( 'global_layout_style' );
			$style               = $global_layout_style;
		}

		if ( $style === 'unboxed' ) {
			return 'unboxed';
		}

		return 'boxed';
	}
}
