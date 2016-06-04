<?php

/**
 * Plugin Name: Bridge
 * Description: Additional REST API endpoints and functionalities.
 * Version: 0.3.0
 * Author: Dzikri Aziz
 * Author URI: http://kucrut.org
 * Plugin URI: https://github.com/kucrut/wp-bridge
 * License: GPLv2
 */

/**
 * Load plugin
 *
 * @wp_hook action plugins_loaded
 */
function bridge_load() {
	if ( ! class_exists( 'WP_REST_Controller' ) ) {
		return;
	}

	$inc_dir = dirname( __FILE__ ) . '/includes';

	require_once $inc_dir . '/mods-index.php';

	require_once $inc_dir . '/mods-menu.php';
	Bridge_Rest_Mods_Menu::init();

	require_once $inc_dir . '/mods-post.php';
	Bridge_Rest_Mods_Post::init();

	require_once $inc_dir . '/mods-term.php';
	Bridge_Rest_Mods_Term::init();

	require_once $inc_dir . '/mods-comments.php';
	Bridge_Rest_Mods_Comments::init();
}
add_action( 'plugins_loaded', 'bridge_load' );


/**
 * Check if we need to filter the result of API request.
 *
 * Only requests came from listed clients will be filtered.
 *
 * @param  WP_REST_Request $request Request.
 * @return bool
 */
function bridge_should_filter_result( $request ) {
	$headers = $request->get_headers();
	if ( ! array_key_exists( 'x_requested_with', $headers ) ) {
		return false;
	}

	$client_ids = array_filter( (array) apply_filters( 'bridge_client_ids', array() ) );
	if ( empty( $client_ids ) ) {
		return false;
	}

	$clients_found = array_intersect( $client_ids, $headers['x_requested_with'] );

	return ( ! empty( $clients_found ) );
}


if ( ! function_exists( 'bridge_strip_home_url' ) ) :
	/**
	 * Strip home URL from string
	 *
	 * @param  string $string Text.
	 * @return string
	 */
	function bridge_strip_home_url( $string ) {
		return str_replace( home_url(), '', $string );
	}
endif;
