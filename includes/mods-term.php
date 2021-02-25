<?php

/**
 * Modify results of API Request to terms
 */
class Bridge_Rest_Mods_Term {
	/**
	 * Register hook callbacks
	 */
	public static function init() {
		foreach ( get_taxonomies( [ 'show_in_rest' => true ] ) as $taxonomy ) {
			add_filter( "rest_prepare_${taxonomy}", [ __CLASS__, 'modify_term_data' ], 10, 3 );
		}
	}

	/**
	 * Modify term data
	 *
	 * @param WP_REST_Response   $response   The response object.
	 * @param object             $term       The original term object.
	 * @param WP_REST_Request    $request    Request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function modify_term_data( $response, $term, $request ) {
		if ( ! bridge_should_filter_result( $request ) ) {
			return $response;
		}

		$data = $response->data;

		$data['link'] = bridge_strip_home_url( $data['link'] );

		if ( ! empty( $data['description'] ) ) {
			$data['description'] = wp_kses_post( wpautop( $data['description'] ) );
		}

		$response->set_data( $data );

		return $response;
	}
}
