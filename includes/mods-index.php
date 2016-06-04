<?php

/**
 * Filter response of index
 *
 * @param   WP_REST_Response $response REST Response.
 * @wp_hook rest_index
 * @return  WP_REST_Response
 */
function bridge_rest_index( $response ) {
	$data = $response->data;
	$data['lang']     = get_bloginfo( 'language' );
	$data['html_dir'] = ( function_exists( 'is_rtl' ) && is_rtl() ) ? 'rtl' : 'ltr';

	$response->set_data( $data );

	return $response;
}
add_filter( 'rest_index', 'bridge_rest_index' );
