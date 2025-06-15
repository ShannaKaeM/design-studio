<?php
/**
 * Portals block template service.
 *
 * @package SureDash
 */

namespace SureDashboard\Inc\Templator;

use SureDashboard\Inc\Traits\Get_Instance;

/**
 * The block templates service.
 */
class Service {
	use Get_Instance;

	/**
	 * The utility service.
	 *
	 * @var \SureDashboard\Inc\Templator\Utility
	 */
	private $utility;

	/**
	 * BlockTemplatesService constructor.
	 */
	public function __construct() {
		add_theme_support( 'block-template-parts' );
		add_theme_support( 'appearance-tools' );
		add_theme_support( 'custom-spacing' );
		add_theme_support( 'custom-line-height' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'spacing' );
		add_theme_support( 'typography' );

		$this->utility = new Utility(
			SUREDASHBOARD_DIR . 'templates/parts',
			SUREDASHBOARD_DIR . 'templates/parts'
		);

		$this->register();
	}

	/**
	 * Bootstrap the block templates service.
	 *
	 * @return void
	 */
	public function register(): void {
		// add block templates.
		add_filter( 'get_block_templates', [ $this, 'addBlockTemplates' ], 10, 3 );
		add_filter( 'pre_get_block_file_template', [ $this, 'getBlockFileTemplate' ], 10, 3 );
	}

	/**
	 * Checks whether a block template with that name exists in Woo Blocks
	 *
	 * @param string $template_name Template to check.
	 * @param array  $template_type wp_template or wp_template_part.
	 *
	 * @return bool
	 */
	public function blockTemplateIsAvailable( $template_name, $template_type = 'wp_template' ) {
		if ( ! $template_name ) {
			return false;
		}

		$directory = $this->utility->getTemplatesDirectory( $template_type ) . '/' . $template_name . '.html';

		return is_readable( $directory ) || $this->getBlockTemplates( [ $template_name ], $template_type );
	}

	/**
	 * This function is used on the `pre_get_block_template` hook to return the fallback template from the db in case
	 * the template is eligible for it.
	 *
	 * @param \WP_Block_Template|null $template Block template object to short-circuit the default query,
	 *                                          or null to allow WP to run its normal queries.
	 * @param string                  $id Template unique identifier (example: theme_slug//template_slug).
	 * @param string                  $template_type wp_template or wp_template_part.
	 *
	 * @return object|null
	 */
	public function getBlockFileTemplate( $template, $id, $template_type ) {
		$template_name_parts = explode( '//', $id );

		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		[ $template_id, $template_slug ] = $template_name_parts;

		// If we are not dealing with a Plugin template let's return early and let it continue through the process.
		if ( $this->utility::PLUGIN_SLUG !== $template_id ) {
			return $template;
		}

		// If we don't have a template let Gutenberg do its thing.
		if ( ! $this->blockTemplateIsAvailable( $template_slug, $template_type ) ) {
			return $template;
		}

		$directory          = $this->utility->getTemplatesDirectory( $template_type );
		$template_file_path = $directory . '/' . $template_slug . '.html';
		$template_object    = $this->utility->createNewBlockTemplateObject( $template_file_path, $template_type, $template_slug );
		$template_built     = $this->utility->buildTemplateResultFromFile( $template_object, $template_type );

		if ( $template_built !== null ) {
			return $template_built;
		}

		// Hand back over to Gutenberg if we can't find a template.
		return $template;
	}

	/**
	 * Add the block template objects to be used.
	 *
	 * @param array  $query_result Array of template objects.
	 * @param array  $query Optional. Arguments to retrieve templates.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array
	 */
	public function addBlockTemplates( $query_result, $query, $template_type ) {
		// does not support block templates.
		if ( $template_type === 'wp_template' && ! $this->utility->supportsBlockTemplates() ) {
			return $query_result;
		}

		$post_type = $query['post_type'] ?? '';
		$slugs     = $query['slug__in'] ?? [];

		$template_files = $this->getBlockTemplates( $slugs, $template_type );
		foreach ( $template_files as $template_file ) {
			// If the current $post_type is set (e.g. on an Edit Post screen), and isn't included in the available post_types
			// on the template file, then lets skip it so that it doesn't get added. This is typically used to hide templates
			// in the template dropdown on the Edit Post page.
			if ( $post_type &&
				isset( $template_file->post_types ) &&
				! in_array( $post_type, $template_file->post_types, true )
			) {
				continue;
			}

			// this supports block templates and the template is not available in the site editor.
			if ( $this->utility->supportsBlockTemplates() && ! $this->utility->isBlockAvailableInSiteEditor( $template_file->slug ) ) {
				continue;
			}

			// It would be custom if the template was modified in the editor, so if it's not custom we can load it from
			// the filesystem.
			if ( $template_file->source !== 'custom' ) {
				$template = $this->utility->buildTemplateResultFromFile( $template_file, $template_type );
			} else {
				$template_file->title       = $this->utility->getBlockTemplateTitle( $template_file->slug );
				$template_file->description = $this->utility->getBlockTemplateDescription( $template_file->slug );
				$query_result[]             = $template_file;
				continue;
			}

			$is_not_custom   = array_search(
				wp_get_theme()->get_stylesheet() . '//' . $template_file->slug,
				array_column( $query_result, 'id' ),
				true
			) === false;
			$fits_slug_query =
				! isset( $query['slug__in'] ) || in_array( $template_file->slug, $query['slug__in'], true );
			$fits_area_query =
				! isset( $query['area'] ) || $template_file->area === $query['area'];
			$should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;
			if ( $should_include ) {
				$query_result[] = $template;
			}
		}

		// We need to remove theme (i.e. filesystem) templates that have the same slug as a customised one.
		// This only affects saved templates that were saved BEFORE a theme template with the same slug was added.
		$query_result = $this->utility->removeThemeTemplatesWithCustomAlternative( $query_result );

		/**
		 * Plugin templates from theme aren't included in `$this->get_block_templates()` but are handled by Gutenberg.
		 * We need to do additional search through all templates file to update title and description for Plugin
		 * templates that aren't listed in theme.json.
		 */
		return array_map(
			function ( $template ) {
				if ( $template->origin === 'theme' && $this->utility->templateHasTitle( $template ) ) {
					return $template;
				}
				if ( $template->title === $template->slug ) {
					$template->title = $this->utility->getBlockTemplateTitle( $template->slug );
				}
				if ( ! $template->description ) {
					$template->description = $this->utility->getBlockTemplateDescription( $template->slug );
				}

				return $this->setTemplateName( $template );
			},
			$query_result
		);
	}

	/**
	 * Set the template name
	 *
	 * @param [type] $template
	 *
	 * @return void
	 */
	public function setTemplateName( $template ) {
		if ( preg_match( '/(portal)-(.+)/', $template->slug, $matches ) ) {
			$type = $matches[1];

			if ( $type === 'portal' ) {
				$template->title = sprintf(
					// translators: Represents the title of a user's custom template in the Site Editor, where %s is the author's name, e.g. "Author: Jane Doe".
					__( 'SureDash: %s', 'suredash' ),
					__( 'Community Portal', 'suredash' )
				);
				$template->description = __( 'Template for your SureDash community.', 'suredash' );
			}
		}

		return $template;
	}

	/**
	 * Get and build the block template objects from the block template files.
	 *
	 * @param array  $slugs An array of slugs to retrieve templates for.
	 * @param string $template_type wp_template or wp_template_part.
	 *
	 * @return array WP_Block_Template[] An array of block template objects.
	 */
	public function getBlockTemplates( $slugs = [], $template_type = 'wp_template' ) {
		$templates_from_db     = $this->getBlockTemplatesFromDB( $slugs, $template_type );
		$templates_from_plugin = $this->getBlockTemplatesFromPlugin( $slugs, $templates_from_db, $template_type );
		return array_merge( $templates_from_db, $templates_from_plugin );
	}

	/**
	 * Gets the templates saved in the database.
	 *
	 * @param array  $slugs An array of slugs to retrieve templates for.
	 * @param string $template_type wp_template or wp_template_part.
	 *
	 * @return array<int>|array<\WP_Post> An array of found templates.
	 */
	public function getBlockTemplatesFromDB( $slugs = [], $template_type = 'wp_template' ) {
		$check_query_args = [
			'post_type'      => $template_type,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => [ $this->utility::PLUGIN_SLUG ],
				],
			],
		];

		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$check_query_args['post_name__in'] = $slugs;
		}

		$check_query     = new \WP_Query( $check_query_args );
		$saved_templates = $check_query->posts;

		return array_map(
			function ( $saved_template ) {
				return $this->utility->buildTemplateResultsFromPost( $saved_template );
			},
			$saved_templates
		);
	}

	/**
	 * Gets the templates from the Plugin blocks directory, skipping those for which a template already exists
	 * in the theme directory.
	 *
	 * @param array<string> $slugs An array of slugs to filter templates by. Templates whose slug does not match will not be returned.
	 * @param array         $already_found_templates Templates that have already been found, these are customised templates that are loaded from the database.
	 * @param string        $template_type wp_template or wp_template_part.
	 *
	 * @return array Templates from the Plugin blocks plugin directory.
	 */
	public function getBlockTemplatesFromPlugin( $slugs, $already_found_templates, $template_type = 'wp_template' ) {
		$directory      = $this->utility->getTemplatesDirectory( $template_type );
		$template_files = $this->utility->getTemplatePaths( $directory );

		$templates = [];
		foreach ( $template_files as $template_file ) {
			$template_slug = $this->utility->generateTemplateSlugFromPath( $template_file );

			// This template does not have a slug we're looking for. Skip it.
			if ( is_array( $slugs ) && count( $slugs ) > 0 && ! in_array( $template_slug, $slugs, true ) ) {
				continue;
			}

			// If the theme already has a template, or the template is already in the list (i.e. it came from the
			// database) then we should not overwrite it with the one from the filesystem.
			if (
				$this->utility->themeHasTemplate( $template_slug ) ||
				count(
					array_filter(
						$already_found_templates,
						static function ( $template ) use ( $template_slug ) {
							$template_obj = (object) $template; //phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found
							return $template_obj->slug === $template_slug;
						}
					)
				) > 0 ) {
				continue;
			}

			// At this point the template only exists in the Blocks filesystem and has not been saved in the DB,
			// or superseded by the theme.
			$templates[] = $this->utility->createNewBlockTemplateObject( $template_file, $template_type, $template_slug );
		}

		return $templates;
	}
}
