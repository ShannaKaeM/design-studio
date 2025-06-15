<?php
/**
 * SureCart Integration.
 *
 * @package suremembers
 * @since 1.0.0
 */

namespace SureMembers\Integrations;

use SureCart\Integrations\Contracts\IntegrationInterface;
use SureCart\Integrations\Contracts\PurchaseSyncInterface;
use SureCart\Integrations\IntegrationService;
use SureMembers\Inc\Access;
use SureMembers\Inc\Access_Groups;

/**
 * SureCart Integration
 *
 * @since 0.0.1
 */
class Surecart_Integration extends IntegrationService implements IntegrationInterface, PurchaseSyncInterface {
	/**
	 * Return identifier for SureCart integration.
	 *
	 * @return string
	 */
	public function getName() {
		return 'suremembers/manage-access-groups';
	}

	/**
	 * Get the SureCart model used for the integration.
	 * Only 'product' is supported at this time.
	 *
	 * @return string
	 */
	public function getModel() {
		return 'product';
	}

	/**
	 * Returns Logo url for
	 * This url needs to be an absolute url to png, jpg, webp or svg.
	 *
	 * @return string
	 */
	public function getLogo() {
		return esc_url_raw( SUREMEMBERS_URL . 'admin/assets/images/icon.svg' );
	}

	/**
	 * The display name for the integration in the dropdown.
	 * This is displayed in a dropdown menu when a merchant selects an integration.
	 *
	 * @return string
	 */
	public function getLabel() {
		return __( 'SureMembers', 'suremembers' );
	}

	/**
	 * The label for the integration item that will be chosen.
	 * This is displayed in the second dropdown after a person selects your integration.
	 *
	 * @return string
	 */
	public function getItemLabel() {
		return __( 'Select an Access Group', 'suremembers' );
	}

	/**
	 * Help text for the integration item chooser.
	 * Additional help text for the integration item chooser.
	 *
	 * @return string
	 */
	public function getItemHelp() {
		return __( 'Links with Sure Members Access Group on Product Purchase', 'suremembers' );
	}

	/**
	 * Get item listing for the integration.
	 * These are a list of item the merchant can choose from when adding an integration.
	 *
	 * @param array  $items The integration items.
	 * @param string $search search items.
	 *
	 * @return array The items for the integration.
	 */
	public function getItems( $items = [], $search = '' ) {
		$plans    = Access_Groups::get_active(
			[
				'numberposts' => 20,
				's'           => $search,
			]
		);
		$response = [];
		foreach ( $plans as $id => $label ) {
			$sub['id']       = esc_attr( $id );
			$sub['label']    = esc_attr( $label );
			$response[ $id ] = $sub;
		}
		return $response;
	}

	/**
	 * Get individual Access Group data
	 *
	 * @param int $id access group id.
	 * @return array
	 * @since 1.0.0
	 */
	public function getItem( $id ) {
		$access_group = get_post( $id );
		$post_title   = isset( $access_group->post_title ) ? $access_group->post_title : '';
		return [
			'id'    => $id,
			'label' => $post_title,
		];
	}

		/**
		 * Add the role when the purchase is created.
		 *
		 * @param object   $integration \SureCart\Models\Integration The integrations.
		 * @param \WP_User $wp_user The user.
		 *
		 * @return boolean|void Returns true if the user course access updation was successful otherwise false.
		 */
	public function onPurchaseCreated( $integration, $wp_user ) {
		if ( empty( $wp_user->ID ) || empty( $integration->integration_id ) ) {
			return;
		}

		Access::grant( $wp_user->ID, $integration->integration_id, 'surecart' );
	}

	/**
	 * Add the role when the purchase is invoked
	 *
	 * @param object   $integration \SureCart\Models\Integration The integrations.
	 * @param \WP_User $wp_user The user.
	 *
	 * @return boolean|void Returns true if the user course access updation was successful otherwise false.
	 */
	public function onPurchaseInvoked( $integration, $wp_user ) {
		$this->onPurchaseCreated( $integration, $wp_user );
	}

	/**
	 * Remove a user role when the purchase is revoked.
	 *
	 * @param object   $integration The integrations.
	 * @param \WP_User $wp_user The user.
	 *
	 * @return boolean|void Returns true if the user course access updation was successful otherwise false.
	 */
	public function onPurchaseRevoked( $integration, $wp_user ) {
		if ( empty( $wp_user->ID ) || empty( $integration->integration_id ) ) {
			return;
		}

		Access::revoke( $wp_user->ID, $integration->integration_id );
	}
}
