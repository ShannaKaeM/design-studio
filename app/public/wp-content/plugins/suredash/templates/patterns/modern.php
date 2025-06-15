<?php
/**
 * Modern portal block pattern.
 *
 * @package Suredash
 * @since 0.0.6
 * "' . esc_attr( strval( $portal_menu_id ) ) . '"
 */

defined( 'ABSPATH' ) || exit;

$portal_menu_id = suredash_get_portal_menu_id();

return [
	'title'      => __( 'Modern Layout', 'suredash' ),
	'categories' => [ 'suredash_portal' ],
	'blockTypes' => [ 'suredash/portal' ],
	'content'    => '<!-- wp:suredash/portal {"sidebartopoffset":"72px","metadata":{"categories":["suredash_portal"],"patternName":"suredash-modern","name":"Modern Layout"}} -->
<!-- wp:group {"metadata":{"name":"Responsive Header"},"className":"portal-hide-on-desktop hidden-on-lessons","style":{"position":{"type":"sticky","top":"0px"},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"36px","right":"36px"}},"border":{"bottom":{"color":"#e2e8f0","width":"1px"},"top":[],"right":[],"left":[]}},"backgroundColor":"white","layout":{"type":"grid","columnCount":2,"minimumColumnWidth":null}} -->
<div class="wp-block-group portal-hide-on-desktop hidden-on-lessons has-white-background-color has-background" style="border-bottom-color:#e2e8f0;border-bottom-width:1px;padding-top:20px;padding-right:36px;padding-bottom:20px;padding-left:36px"><!-- wp:suredash/identity {"elements":"title","width":"40px","responsivesidenavigation":true,"style":{"typography":{"fontWeight":"600","fontStyle":"normal"}},"fontSize":"medium"} /-->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:suredash/profile {"makefixed":false,"onlyavatar":true,"avatarsize":"40px","menuopenhorposition":"right","menuhorpositionoffset":"0px","style":{"typography":{"fontSize":"13px"}}} /-->

<!-- wp:suredash/notification {"drawerhorpositionoffset":"0px","drawerverpositionoffset":"50px"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:columns {"metadata":{"name":"Desktop Header"},"className":"portal-sticky-header portal-hide-on-responsive hidden-on-lessons","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":{"top":"0","left":"0"},"margin":{"top":"0","bottom":"0"}},"border":{"bottom":{"color":"#e2e8f0","width":"1px"}}},"backgroundColor":"white"} -->
<div class="wp-block-columns portal-sticky-header portal-hide-on-responsive hidden-on-lessons has-white-background-color has-background" style="border-bottom-color:#e2e8f0;border-bottom-width:1px;margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:column {"verticalAlignment":"center","width":"280px","style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"36px","right":"12px"},"blockGap":"0"},"border":{"right":{"color":"#e2e8f0","width":"1px"},"top":[],"bottom":[],"left":[]}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="border-right-color:#e2e8f0;border-right-width:1px;padding-top:24px;padding-right:12px;padding-bottom:24px;padding-left:36px;flex-basis:280px"><!-- wp:suredash/identity {"elements":"title","width":"40px","responsivesidenavigation":true,"style":{"typography":{"fontWeight":"600","fontStyle":"normal"}},"fontSize":"medium"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","style":{"spacing":{"padding":{"top":"16px","bottom":"16px","left":"36px","right":"36px"},"blockGap":"0"}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:16px;padding-right:36px;padding-bottom:16px;padding-left:36px"><!-- wp:group {"className":"sd-items-center","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
<div class="wp-block-group sd-items-center" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"}}},"layout":{"type":"constrained","contentSize":"200px","justifyContent":"left"}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:suredash/search {"inputborderradius":"99px","responsiveonlyicon":true,"style":{"layout":{"columnSpan":1,"rowSpan":1}}} /--></div>
<!-- /wp:group -->

<!-- wp:navigation {"ref":"' . esc_attr( strval( $portal_menu_id ) ) . '","style":{"typography":{"textDecoration":"none","fontSize":"14px"},"spacing":{"blockGap":"var:preset|spacing|40"},"layout":{"columnSpan":1,"rowSpan":1}},"layout":{"type":"flex","justifyContent":"center"}} /-->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:suredash/profile {"makefixed":false,"onlyavatar":true,"avatarsize":"40px","menuopenhorposition":"right","menuhorpositionoffset":"0px","style":{"typography":{"fontSize":"13px"}}} /-->

<!-- wp:suredash/notification {"drawerhorpositionoffset":"0px","drawerverpositionoffset":"50px"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"metadata":{"name":"Content Part"},"align":"full","className":"sd-gap-0","style":{"spacing":{"margin":{"top":"0","bottom":"0"},"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"blockGap":{"top":"0px","left":"0px"}}}} -->
<div class="wp-block-columns alignfull sd-gap-0" style="margin-top:0;margin-bottom:0;padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:column {"width":"280px","metadata":{"name":"Sidebar"},"className":"portal-sidebar portal-hide-on-responsive","style":{"spacing":{"padding":{"top":"24px","bottom":"24px","left":"24px","right":"24px"}},"border":{"right":{"color":"#e2e8f0","style":"solid","width":"1px"}}}} -->
<div class="wp-block-column portal-sidebar portal-hide-on-responsive" style="border-right-color:#e2e8f0;border-right-style:solid;border-right-width:1px;padding-top:24px;padding-right:24px;padding-bottom:24px;padding-left:24px;flex-basis:280px"><!-- wp:suredash/navigation {"spacegroupsgap":"16px","spacegrouptitlefirstspacegap":"8px","spacesgap":"8px","style":{"elements":{"spaceactivebackground":{"color":[]},"spaceactivetext":{"color":[]}}}} /--></div>
<!-- /wp:column -->

<!-- wp:column {"width":"","metadata":{"name":"Entry Container"},"className":"portal-no-space-wrapper","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0px","right":"0px"},"blockGap":"36px"},"color":{"background":"#f7f9fa"}}} -->
<div class="wp-block-column portal-no-space-wrapper has-background" style="background-color:#f7f9fa;padding-top:0;padding-right:0px;padding-bottom:0;padding-left:0px"><!-- wp:group {"metadata":{"name":"Sub Header"},"style":{"border":{"bottom":{"color":"#e2e8f0","width":"1px"},"top":[],"right":[],"left":[]},"spacing":{"padding":{"top":"20px","bottom":"20px","left":"36px","right":"36px"},"blockGap":"0","margin":{"top":"0","bottom":"0"}}},"backgroundColor":"white","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group has-white-background-color has-background" style="border-bottom-color:#e2e8f0;border-bottom-width:1px;margin-top:0;margin-bottom:0;padding-top:20px;padding-right:36px;padding-bottom:20px;padding-left:36px"><!-- wp:suredash/title {"style":{"typography":{"fontWeight":"600","fontStyle":"normal"},"elements":{"link":{"color":{"text":"#111827"}}},"color":{"text":"#111827"}},"fontSize":"medium"} /--></div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<div class="wp-block-columns" style="margin-top:0;margin-bottom:0"><!-- wp:column {"style":{"spacing":{"padding":{"top":"0px","bottom":"0px","left":"0px","right":"0px"}}}} -->
<div class="wp-block-column" style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px"><!-- wp:suredash/content /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<!-- /wp:suredash/portal -->',
];
