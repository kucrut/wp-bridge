<?php

/**
 * Expose post formats taxonomy in REST API
 *
 * @wp_hook action init
 */
function _bridge_add_extra_api_taxonomy_arguments() {
	global $wp_taxonomies;

	if ( isset( $wp_taxonomies['post_format'] ) ) {
		$wp_taxonomies['post_format']->show_in_rest = true;
		$wp_taxonomies['post_format']->rest_base = 'formats';
		$wp_taxonomies['post_format']->rest_controller_class = 'WP_REST_Terms_Controller';
	}
}
add_action( 'init', '_bridge_add_extra_api_taxonomy_arguments', 11 );
