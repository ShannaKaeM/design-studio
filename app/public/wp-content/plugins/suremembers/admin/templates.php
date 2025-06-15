<?php
/**
 * Admin Templates.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Admin;

/**
 * Admin Templates
 *
 * @since 0.0.1
 */
class Templates {

	/**
	 * Generates post restriction metabox content
	 *
	 * @param integer $post_id current post id.
	 * @return void
	 * @since 1.0.0
	 */
	public function post_restriction_meta_box_content( $post_id ) {
		$decoded_data = $this->provide_meta_data( $post_id );
		$decoded_data = $decoded_data ? $decoded_data : '';
		?>

		<div class="suremembers-post-meta-container">
			<input type="hidden" name="suremembers_metabox_content" value="<?php echo esc_attr( wp_create_nonce( 'suremembers_metabox_content' ) ); ?>" />
			<div id='suremembers-post-meta-app' decoded-meta-data='<?php echo esc_attr( $decoded_data ); ?>'></div>
		</div>
		<?php
	}

	/**
	 * Get meta data from server.
	 *
	 * @param integer $post_id current post id.
	 * @return string|false
	 */
	public function provide_meta_data( $post_id ) {
		$post_id                 = intval( $post_id );
		$suremembers_select_plan = get_post_meta( $post_id, SUREMEMBERS_POST_META, true );
		$suremembers_select_plan = empty( $suremembers_select_plan ) || ! is_array( $suremembers_select_plan ) ? [] : $suremembers_select_plan;
		if ( ! empty( $suremembers_select_plan['access_group_ids'] ) && is_array( $suremembers_select_plan['access_group_ids'] ) ) {
			foreach ( $suremembers_select_plan['access_group_ids'] as $key => $value ) {
				$access_group_title = get_the_title( $value );
				if ( $access_group_title ) {
					$suremembers_select_plan['access_group_ids'][ $key ] = [
						'id'    => $value,
						'title' => $access_group_title,
					];
				} else {
					unset( $suremembers_select_plan['access_group_ids'][ $key ] );
				}
			}
		}
		return wp_json_encode( $suremembers_select_plan );
	}

	/**
	 * HTML to choose available access groups
	 *
	 * @param int $id current menu navigation id.
	 * @return void
	 * @since 1.0.0
	 */
	public static function menu_restriction_markup( $id ) {
		$saved_access_groups  = get_post_meta( $id, SUREMEMBERS_ACCESS_GROUPS, true );
		$menu_user_condition  = get_post_meta( $id, SUREMEMBERS_MENU_USER_CONDITION, true );
		$menu_user_condition  = is_string( $menu_user_condition ) ? $menu_user_condition : '';
		$saved_user_condition = ! empty( $menu_user_condition ) ? esc_html( $menu_user_condition ) : 'is_in';
		$sid                  = strval( $id );
		?>
			<p class="field-access-groups description description-wide">
				<?php wp_nonce_field( "menu-item-suremembers-access-groups-$id", "menu-item-suremembers-access-groups-$id" ); ?>
				<?php esc_html_e( 'Show menu when user', 'suremembers' ); ?>
				<select style="margin-bottom: 10px;" name="menu-item-suremembers-access-groups-condition[<?php echo esc_attr( $sid ); ?>]">
					<option <?php selected( $saved_user_condition, 'is_in' ); ?> value="is_in"><?php echo esc_html__( 'is in', 'suremembers' ); ?> </option>
					<option <?php selected( $saved_user_condition, 'is_not_in' ); ?> value="is_not_in"><?php echo esc_html__( 'is not in', 'suremembers' ); ?> </option>
				</select>
				<?php esc_html_e( 'Access Groups:', 'suremembers' ); ?>
				<select multiple="multiple" style="width:100%"
					class="menu-item-suremembers-access-groups suremembers-select2"
					id="menu-item-suremembers-access-groups-<?php echo esc_attr( $sid ); ?>"
					name="menu-item-suremembers-access-groups[<?php echo esc_attr( $sid ); ?>][]">
					<?php
					if ( is_array( $saved_access_groups ) && ! empty( $saved_access_groups ) ) {
						foreach ( $saved_access_groups as $aid ) {
							?>
								<option selected='selected' value=<?php echo esc_attr( $aid ); ?>><?php echo esc_html( get_the_title( $aid ) ); ?> </option>
								<?php
						}
					}

					?>
				</select>
				<span class="description"><?php esc_html_e( 'This menu item will be hidden for user not in selected access groups.', 'suremembers' ); ?></span>
			</p>
		<?php
	}

	/**
	 * HTML to choose available access groups
	 *
	 * @param int $id current menu navigation id.
	 * @return void
	 * @since 1.1.0
	 */
	public static function access_groups_markup( $id ) {
		$saved_access_groups = get_post_meta( $id, SUREMEMBERS_ACCESS_GROUPS, true );
		?>
			<p class="field-access-groups description description-wide">
				<?php wp_nonce_field( 'wc-suremembers-access-groups-nonce', 'wc-suremembers-access-groups-nonce' ); ?>
				<select multiple="multiple" style="width:100%"
					class="wc-suremembers-access-groups suremembers-select2"
					id="wc-suremembers-access-groups"
					name="wc-suremembers-access-groups[]">
					<?php
					if ( is_array( $saved_access_groups ) && ! empty( $saved_access_groups ) ) {
						foreach ( $saved_access_groups as $aid ) {
							?>
								<option selected='selected' value=<?php echo esc_attr( $aid ); ?>><?php echo esc_html( get_the_title( $aid ) ); ?> </option>
								<?php
						}
					}
					?>
				</select>
				<span class="description"><?php esc_html_e( 'Associate Access Groups with this product', 'suremembers' ); ?></span>
			</p>
		<?php
	}

	/**
	 * HTML markup for choosing and displaying user access groups in user edit page.
	 *
	 * @param object $user User object to get data.
	 * @return void
	 * @since 1.0.0
	 */
	public static function access_group_selection_markup( $user ) {
		$user_id = isset( $user->ID ) ? $user->ID : 0;
		?>
			<table id="suremembers-add-access-group-select" class="form-table" role="presentation">
				<tbody>
					<tr class="user-description-wrap">
						<th><label for="suremembers_access_groups"><?php echo esc_html__( 'Add Access Group', 'suremembers' ); ?></label></th>
						<td>
							<select name="access_group[]" id="suremembers_access_groups" multiple></select>
							<button data-user="<?php echo esc_attr( $user_id ); ?>" id="suremembers-add-access-group" class="button button-primary"><?php echo esc_html__( 'Add Access Group(s)', 'suremembers' ); ?></button>
							<p class="description"><?php echo esc_html__( 'Choose access groups to assign to this user.', 'suremembers' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}

	/**
	 * Users table bulk edit template.
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public static function users_bulk_edit_template() {
		?>
			<script type="text/html" id="tmpl-suremembers_users_bulk_edit_template">
				<tr class="hidden"></tr>
				<tr id="suremembers-access-groups-bulk-edit" class="inline-edit-row inline-edit-row-post bulk-edit-row bulk-edit-row-post bulk-edit-post inline-editor">
					<td colspan="{{data[0].firstHeadColSpan}}" class="colspanchange">
						<div class="inline-edit-wrapper" role="region" aria-labelledby="bulk-edit-legend" tabindex="-1">
							<fieldset class="inline-edit-col-left">
								<legend class="inline-edit-legend" id="bulk-edit-legend"><?php echo esc_html__( 'Bulk Edit', 'suremembers' ); ?></legend>
								<div class="suremembers-inline-edit-col">
									<div id="suremembers-bulk-title-div">
										<div id="suremembers-bulk-titles">
											<ul id="suremembers-bulk-titles-list" role="list">
												<# for ( index in data ) {
												let current_user = data[index];
												#>
													<li class="ntdelitem">
														<button type="button" id="_{{current_user.id}}" class="suremembers-button-link ntdelbutton">
															<span class="screen-reader-text">{{current_user.buttonVisuallyHiddenText}}</span>
														</button>
														<span class="ntdeltitle" aria-hidden="true">{{current_user.theTitle}}</span>
													</li>
												<#
												}
												#>
											</ul>
										</div>
									</div>
								</div>
							</fieldset>
							<fieldset class="inline-edit-col-right">
								<div class="inline-edit-tags-wrap">
									<label class="inline-edit-tags">
										<span class="title"><?php echo esc_html__( 'Select Access Groups', 'suremembers' ); ?></span>
										<select name="access_group[]" id="suremembers_access_groups" multiple></select>
									</label>
									<p class="howto" id="inline-edit-post_tag-desc"><?php echo esc_html__( 'Choose access groups to grant or revoke access to selected users.', 'suremembers' ); ?></p>
								</div>
							</fieldset>
							<div class="submit inline-edit-save">
								<button name="bulk_grant_access" id="bulk_grant_access" class="button button-primary"><?php echo esc_html__( 'Grant Access', 'suremembers' ); ?></button>
								<button name="bulk_revoke_access" id="bulk_revoke_access" class="button button-secondary"><?php echo esc_html__( 'Revoke Access', 'suremembers' ); ?></button>
								<button type="button" class="button cancel"><?php echo esc_html__( 'Cancel', 'suremembers' ); ?></button>
								<?php wp_nonce_field( 'suremembers_bulk_actions_nonce' ); ?>
							</div>
						</div> <!-- end of .inline-edit-wrapper -->
					</td>
				</tr>
			</script>
		<?php
	}

	/**
	 * Prepare the design template for email notification.
	 *
	 * @param string $from_name The name of the sender.
	 * @param string $message The Message to send in email.
	 *
	 * @return string $output The HTML format of the content template.
	 *
	 * @since 1.10.0
	 */
	public static function prepare_email_content( $from_name, $message ) {
		$site_logo_id = get_theme_mod( 'custom_logo' );

		// Get the logo URL.
		$logo_data = ! empty( $site_logo_id ) ? wp_get_attachment_image_src( intval( $site_logo_id ), 'full' ) : array();

		// Replace the base URL with the local site URL.
		$logo_url = is_array( $logo_data ) && ! empty( $logo_data[0] ) ? $logo_data[0] : '';

		ob_start();
		?>

			<table class="email-content-wrapper" style="width: 100%; font-size: 15px; background-color: #f8fafc; color: #26282c; font-family: Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;">
				<tbody>
					<tr>
						<td align="center">
							<table class="email-content" style="width: 100%;" cellpadding="0" cellspacing="0" role="presentation">
								<tbody>
									<tr>
										<td class="email-header" align="center" style="padding: 25px 45px; text-align: center;">
											<h3 style="font-size: 22px; margin: 0; font-weight: 600;"><?php echo esc_html( $from_name ); ?></h3>
										</td>
									</tr>
									<tr>
										<td class="email-body-wrapper" style="width: 100%;">
											<table class="email-body sm-w-full" style="margin-left: auto; margin-right: auto; width: 700px; background-color: #fff; border-radius: 8px;" align="center" cellpadding="0" cellspacing="0" role="presentation">
												<tbody>
													<tr>
														<td class="email-body-inner" style="padding: 35px 45px;">
															<?php echo wp_kses_post( $message ); ?>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
									<tr>
										<td class="email-footer-wrapper" style="width: 100%;">
											<table class="email-footer sm-w-full" style="padding: 45px 20px; text-align: center; margin-left: auto; margin-right: auto; width: 700px;" align="center" cellpadding="0" cellspacing="0" role="presentation">
												<tbody>
													<tr>
														<td>
															<p style="font-size: 13px; margin: 0 0 10px 0; text-align: center; line-height: 1.2rem;">
																<?php
																echo wp_kses_post(
																	apply_filters(
																		'suremembers_email_notification_footer_message',
																		sprintf(
																				/* translators: %1$s main website URL. */
																			__( 'This e-mail was sent from %1$s', 'suremembers' ),
																			'<a href="' . esc_url( site_url() ) . '" target="_blank">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
																		)
																	)
																);
																?>
															</p>
															<?php if ( ! empty( $logo_url ) ) : ?>
																<a href="<?php echo esc_url( site_url() ); ?>" style="text-decoration: none;">
																	<img style="width:20%;" src="<?php echo esc_url( $logo_url ); ?>">
																</a>
															<?php endif; ?>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>

		<?php
		$output = ob_get_clean();

		return ! is_string( $output ) ? '' : $output;
	}

	/**
	 * Prepare the email design using WooCommerce email format.
	 *
	 * @param string $from_name The name of the sender.
	 * @param string $subject The email subject.
	 * @param string $message The message to send in the email.
	 *
	 * @return string $output The HTML format of the email.
	 * @since 1.10.0
	 */
	public static function prepare_woo_email_content( $from_name, $subject, $message ) {
		ob_start();

		wc_get_template( 'emails/email-header.php', array( 'email_heading' => apply_filters( 'suremembers_woo_email_heading_text', esc_html( $subject ) ) ) );
		$email_header = ob_get_clean();

		ob_start();

		wc_get_template( 'emails/email-footer.php' );
		$email_footer = ob_get_clean();

		$site_title = get_bloginfo( 'name' );

		// This below line is added to solve the PHPstan error as the str_ireplace's 3rd para require to be string and not the false.
		$email_footer = false === $email_footer ? '' : $email_footer;

		$email_footer = str_ireplace( '{site_title}', $site_title, $email_footer );

		$output = $email_header . $message . $email_footer;

		return ! is_string( $output ) ? '' : $output;
	}
}
