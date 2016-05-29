<?php

class Bridge_Rest_Mods_Menu {

	public static function init() {
		add_filter( 'bridge_rest_prepare_menu', array( __CLASS__, 'modify_menu_data' ), 10, 3 );
	}


	/**
	 * Strip home_url() from menu item data
	 *
	 * @return [type] [description]
	 */
	protected static function strip_home_url( &$item ) {
		$item['url'] = bridge_strip_home_url( $item['url'] );
		array_walk( $item['children'], array( __CLASS__, __METHOD__ ) );
	}


	/**
	 * Modify menu data
	 *
	 * @param WP_REST_Response   $response   The response object.
	 * @param WP_Term            $menu       Nav menu object.
	 * @param WP_REST_Request    $request    Request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function modify_menu_data( $response, $menu, $request ) {
		if ( ! bridge_should_filter_result( $request ) ) {
			return $response;
		}

		$data = $response->data;
		array_walk( $data['items'], array( __CLASS__, 'strip_home_url' ) );

		$response->set_data( $data );

		return $response;
	}
}
