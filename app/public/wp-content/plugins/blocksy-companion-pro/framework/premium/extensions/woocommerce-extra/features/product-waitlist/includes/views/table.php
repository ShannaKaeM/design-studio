<?php

namespace Blocksy\Extensions\WoocommerceExtra;

$waitlist = ProductWaitlistDb::get_waitlist('', '', true);

if (empty($waitlist)) {
    echo blocksy_html_tag(
        'div',
        [
            'class' => 'woocommerce-Message woocommerce-Message--info woocommerce-info'
        ],
        blocksy_html_tag(
            'a',
            [
                'class' => 'woocommerce-Button button',
                'href' => esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop')))
            ],
            __('Browse products', 'blocksy-companion')
        ) .
        __("You don't have any products in your waitlist yet.", 'blocksy-companion')
    );

    return;
}

?>

<div class="ct-woocommerce-waitlist-table">
	<table class="shop_table">
		<thead>
			<tr>
                <th colspan="2">
                    <?php _e('Product', 'blocksy-companion') ?>
                </th>

                <th class="waitlist-product-status">
                    <?php _e('Stock Status', 'blocksy-companion') ?>
                </th>

                <th class="waitlist-subscription-status">
                    <?php _e('Confirmed', 'blocksy-companion') ?>
                </th>

                <th class="waitlist-product-actions">
                    <?php _e('Actions', 'blocksy-companion') ?>
                </th>
			</tr>
		</thead>

		<tbody>
            <?php
                foreach ($waitlist as $key => $entry) {
                    echo blocksy_render_view(
						dirname(__FILE__) . '/table-single-product-row.php',
						[
							'entry' => $entry,
						]
					);
                }
            ?>
        </tbody>

    </table>
</div>
