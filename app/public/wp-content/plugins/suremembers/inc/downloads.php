<?php
/**
 * Handles downloads for access groups.
 *
 * @package Suremembers.
 * @since 1.3.0
 */

namespace SureMembers\Inc;

use SureMembers\Inc\Traits\Get_Instance;
use SureMembers\Inc\Access_Groups;
use SureMembers\Inc\Streamer;
use WP_Filesystem_Base;

/**
 * Downloads
 */
class Downloads {

	use Get_Instance;

	/**
	 * Allowed ip addresses to private folder
	 *
	 * @var array
	 */
	protected $allowed_ips = [];

	/**
	 * Private folder name
	 *
	 * @var string
	 */
	protected $private_folder = 'suremembers-private';

	/**
	 * Store allowed ips and let user filter private folder
	 */
	public function __construct() {
		$this->private_folder = apply_filters( 'suremembers_private_folder_name', $this->private_folder );
		$this->register();
		add_action( 'wp_ajax_suremembers_access_groups_file_uploads', [ $this, 'process_media_files_upload' ] );
	}

	/**
	 * Register actions and filters
	 *
	 * @return object Current class instance.
	 */
	public function register() {
		add_filter( 'upload_dir', [ $this, 'media_upload_folder' ] );
		add_filter( 'wp_prepare_attachment_for_js', [ $this, 'gallery_label' ] );
		add_filter( 'wp_generate_attachment_metadata', [ $this, 'private_meta' ], 10, 2 );
		add_filter( 'ajax_query_attachments_args', [ $this, 'hide_private_files' ] );
		add_filter( 'rest_attachment_query', [ $this, 'filter_media_query' ], 10, 2 );

		add_filter( 'wp_get_attachment_url', [ $this, 'replace_url_link' ], 10, 2 );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_action( 'generate_rewrite_rules', [ $this, 'custom_rewrite_rules' ] );
		add_action( 'template_redirect', [ $this, 'load_virtual_download_page' ] );

		return $this;
	}

	/**
	 * Get directory path of private folder.
	 *
	 * @return string Private Folder Path.
	 * @since 1.3.0
	 */
	public function get_directory_path() {
		$wp_upload_dir       = wp_upload_dir();
		$private_folder_name = apply_filters( 'suremembers_private_folder_name', $this->private_folder );
		$private_folder      = trailingslashit( $wp_upload_dir['basedir'] ) . $private_folder_name;

		return $private_folder;
	}

	/**
	 * Gets a public or private type
	 *
	 * @return array|string
	 */
	public function get_attachment_type() {
		$query = [];
		$url   = wp_get_raw_referer();
		if ( ! is_string( $url ) || empty( $url ) ) {
			return 'public';
		}
		$parts = wp_parse_url( $url );
		isset( $parts['query'] ) ? parse_str( $parts['query'], $query ) : '';
		return isset( $query['suremembers_upload_type'] ) ? $query['suremembers_upload_type'] : 'public';
	}

	/**
	 * Filter REST media query.
	 *
	 * @param array            $query The media query.
	 * @param \WP_REST_Request $request Request.
	 * @return array Modified Query.
	 */
	public function filter_media_query( $query, $request ) {
		$request_params = $request->get_query_params();
		$request_type   = isset( $request_params['suremembers_upload_type'] ) ? $request_params['suremembers_upload_type'] : 'public';

		switch ( $request_type ) {
			case 'public': // public only, don't show private.
				$query['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'relation' => 'OR',
						[
							'key'     => 'suremembers-private-media',
							'compare' => 'NOT EXISTS',                  // works!
							'value'   => '',                             // This is ignored, but is necessary...
						],
						[
							'key'   => 'suremembers-private-media',
							'value' => false,
						],
					],
				];
				break;
			case 'private': // private only.
				$query['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'   => 'suremembers-private-media',
						'value' => true,
					],
				];
				break;
		}

		return $query;
	}

	/**
	 * Hides private/public items based on video type query
	 *
	 * @param array $query Attachment query.
	 * @return array
	 */
	public function hide_private_files( $query ) {
		$type = $this->get_attachment_type();

		switch ( $type ) {
			case 'public': // public only, don't show private.
				$query['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'relation' => 'OR',
						[
							'key'     => 'suremembers-private-media',
							'compare' => 'NOT EXISTS',                  // works!
							'value'   => '',                             // This is ignored, but is necessary...
						],
						[
							'key'   => 'suremembers-private-media',
							'value' => false,
						],
					],
				];
				break;
			case 'private': // private only.
				$query['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'   => 'suremembers-private-media',
						'value' => true,
					],
				];
				break;
		}

		return $query;
	}

	/**
	 * Check if media file is in `suremembers-private`
	 *
	 * @param int $id Attachment ID.
	 * @return boolean|int
	 */
	public function is_private_media( $id ) {
		$attachment_url = wp_get_attachment_url( $id );
		if ( empty( $attachment_url ) ) {
			return false;
		}
		return strpos( $attachment_url, 'suremembers-download' );
	}

	/**
	 * Add meta data to attachment so WP knows it's private
	 *
	 * @param array $data metadata array.
	 * @param int   $id Current Attachment ID.
	 * @return array Modified Metadata array.
	 */
	public function private_meta( $data, $id ) {
		if ( $this->is_private_media( $id ) ) {
			update_post_meta( $id, 'suremembers-private-media', true );
			update_post_meta( $id, 'suremembers-attachment-type', 'private' );
		}

		return $data;
	}


	/**
	 * Change media uploader folder only in case of private files
	 *
	 * @param array $data upload path data.
	 * @return array
	 */
	public function media_upload_folder( $data ) {
		if ( $this->get_attachment_type() === 'private' ) {
			$data['path']   = $data['basedir'] . '/' . $this->private_folder;
			$data['url']    = $data['baseurl'] . '/' . $this->private_folder;
			$data['subdir'] = $this->private_folder;
		}

		return $data;
	}

	/**
	 * If the media is into private folder change response to show
	 *
	 * @param array $response Gallery label response.
	 * @return array Modified array.
	 */
	public function gallery_label( $response ) {
		if ( strpos( $response['url'], $this->private_folder ) !== false ) {
			$response['filename'] = __( 'Private: ', 'suremembers' ) . $response['filename'];
		}

		return $response;
	}

	/**
	 * Get Private src URl for the given attachment.
	 *
	 * @param int $id Attachment ID.
	 * @return string Updated private src URL string.
	 */
	public function get_private_src( $id ) {
		if ( ! function_exists( 'wp_create_nonce' ) ) {
			return '';
		}

		return sprintf( site_url( 'suremembers-download/%s/%d' ), wp_create_nonce( 'suremembers-downloads-user-token' ), $id );
	}

	/**
	 * Adds query vars for rewrites
	 *
	 * @param array $query_vars Default query variables.
	 * @return array Modified query variables.
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars[] = 'suremembers-download-id';
		$query_vars[] = 'suremembers-download-token';
		return $query_vars;
	}

	/**
	 * Add custom rewrite rules
	 *
	 * @param \WP_Rewrite $wp_rewrite WP Rewrite.
	 * @return void
	 */
	public function custom_rewrite_rules( $wp_rewrite ) {
		$wp_rewrite->rules = array_merge(
			[ 'suremembers-download/([^/]*)/(\d+)/?$' => 'index.php?suremembers-download-token=$matches[1]&suremembers-download-id=$matches[2]' ],
			$wp_rewrite->rules
		);
	}

	/**
	 * Load virtual template to stream video by id
	 *
	 * @return void
	 */
	public function load_virtual_download_page() {
		$download_id    = intval( get_query_var( 'suremembers-download-id' ) );
		$download_token = strval( get_query_var( 'suremembers-download-token' ) );
		$token          = sanitize_text_field( $download_token );

		if ( $download_id && $token ) {
			$associated_access_groups = Access_Groups::by_download_id( $download_id );
			$check_user_has_access    = Access_Groups::check_if_user_has_access( $associated_access_groups );

			if ( current_user_can( 'administrator' ) || $check_user_has_access ) {
				// Load File.
				$attachment = get_attached_file( $download_id );
				if ( is_string( $attachment ) ) {
					$attachment_type = wp_check_filetype( $attachment );

					if ( is_string( $attachment_type['type'] ) ) {
						/**
						 * Start stream to show file.
						 */
						$file_streamer = new Streamer( $attachment, $attachment_type['type'] );
						$file_streamer->start();
					}
				}
			} else {
				$get_priority_id  = Access_Groups::get_priority_id( $associated_access_groups );
				$restriction_meta = get_post_meta( $get_priority_id, SUREMEMBERS_PLAN_RULES, true );
				$restriction_data = is_array( $restriction_meta ) && isset( $restriction_meta['restrict'] ) ? $restriction_meta['restrict'] : [];

				switch ( $restriction_data['unauthorized_action'] ) {
					case 'redirect':
						$redirect_url = Utils::maybe_append_url_params( esc_url( trim( $restriction_data['redirect_url'] ) ) );
						if ( ! empty( $redirect_url ) ) {
							Utils::stop_infinite_redirect( $redirect_url );
							wp_redirect( $redirect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
							exit;
						}
						break;

					case 'preview':
						$url = SUREMEMBERS_DIR . 'inc/restricted-template.php';
						if ( file_exists( $url ) ) {
							load_template( $url, true, $restriction_data );
							die();
						}
						break;

					default:
						break;
				}
			}
		}
	}

	/**
	 * Replaces attachment link
	 *
	 * @param string $url Current media URL.
	 * @param int    $post_id Attachment ID.
	 * @return string Modified URL.
	 */
	public function replace_url_link( $url, $post_id ) {
		// only replace for our folder.
		if ( ! stristr( $url, $this->private_folder ) ) {
			return $url;
		}
		return $this->get_private_src( $post_id );
	}

	/**
	 * Adds the private folder
	 *
	 * @return void
	 */
	public function add_private_folder() {
		\WP_Filesystem();
		global $wp_filesystem;

		$private_folder_name = apply_filters( 'suremembers_private_folder_name', $this->private_folder );
		$private_folder_path = $this->get_directory_path();

		if ( ! $wp_filesystem->is_dir( $private_folder_path ) ) {
			$private_folder = $this->make_folder( $wp_filesystem, $private_folder_name );
			$this->set_htaccess( $wp_filesystem, $private_folder );
		}

		if ( ! empty( $wp_filesystem->errors->errors ) ) {
			add_action( 'admin_notices', [ $this, 'error_notice' ] );
		}
	}

	/**
	 * Show an error notice if we can't create the private folder.
	 *
	 * @return void
	 */
	public function error_notice() {
		$class   = 'notice notice-error';
		$message = __( 'Irks! Error when creating a new private folder for private media', 'suremembers' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Makes our custom folder in the .htaccess directory
	 *
	 * @param WP_Filesystem_Base $wp_filesystem WordPress Filesystem.
	 * @param string             $folder_name Folder Name.
	 * @return string Folder Name.
	 */
	private function make_folder( $wp_filesystem, $folder_name ) {
		$wp_upload_dir  = wp_upload_dir();
		$private_folder = trailingslashit( $wp_upload_dir['basedir'] ) . $folder_name;
		$wp_filesystem->mkdir( $private_folder );

		return $private_folder;
	}

	/**
	 * Sets htaccess rules in the new private folder
	 *
	 * @param WP_Filesystem_Base $wp_filesystem WP Filesystem.
	 * @param string             $private_folder folder name.
	 * @return void
	 */
	private function set_htaccess( $wp_filesystem, $private_folder ) {
		$file = trailingslashit( $private_folder ) . '.htaccess';
		$wp_filesystem->put_contents( $file, $this->return_htaccess_file_content(), FS_CHMOD_FILE );
	}

	/**
	 * Make IP whitelist.
	 *
	 * @return string $url The out URL.
	 */
	public function make_ip_whitelist() {
		$out = '';
		foreach ( $this->allowed_ips as $ip ) {
			$out .= "allow from $ip \n";
		}
		return $out;
	}

	/**
	 * Htaccess configuration
	 *
	 * @return string (heredoc)
	 */
	private function return_htaccess_file_content() {
		$list = $this->make_ip_whitelist();
		return <<<END
# Deny access to everything by default
Order Deny,Allow
deny from all
$list
# Deny access to sub directory
<Files subdirectory/*>
	deny from all
	$list
</Files>
END;
	}

	/**
	 * Process media files uploads in access groups.
	 *
	 * @return void
	 */
	public function process_media_files_upload() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( [ 'message' => __( 'Current user does not have required permission.', 'suremembers' ) ] );
		}

		$upload_files = ! empty( $_FILES ) ? $_FILES : [];

		if ( empty( $upload_files ) ) {
			wp_send_json_error( [ 'message' => __( 'No files found for upload.', 'suremembers' ) ] );
		}

		foreach ( $upload_files as $file ) {
			$new_upload = [
				'name'     => $file['name'],
				'type'     => $file['type'],
				'tmp_name' => $file['tmp_name'],
				'error'    => $file['error'],
				'size'     => $file['size'],
			];

			$_FILES       = array( 'upload' => $new_upload );
			$uploaded_ids = [];

			foreach ( $_FILES as $file => $array ) {
				$new_upload = media_handle_upload( $file, 0 );
				if ( \is_wp_error( $new_upload ) ) {
					wp_send_json_error( [ 'message' => $new_upload->get_error_message() ] );
				} else {
					$uploaded_ids[] = $new_upload;
				}
			}
		}
		wp_send_json_success(
			[
				'message'      => __( 'Files uploaded successfully', 'suremembers' ),
				'uploaded_ids' => $uploaded_ids,
			]
		);
	}
}
