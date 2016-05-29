<?php

class Bridge_Test_Mods_Menu extends Bridge_Test_Case {

	/**
	 * Create nav menu with one item
	 *
	 * @return object Nav menu object.
	 */
	protected function create_menu() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Hello World' ) );
		$menu_id = wp_create_nav_menu( 'Primary' );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'post',
			'menu-item-object-id' => $post_id,
			'menu-item-title'     => 'Hello',
			'menu-item-status'    => 'publish',
		) );

		set_theme_mod( 'nav_menu_locations', array(
			$this->menu_location => $menu_id,
		) );

		$menu = wp_get_nav_menu_object( $menu_id );

		return $menu;
	}


	/**
	 * @covers Bridge_Rest_Mods_Menu::modify_menu_data
	 */
	public function test_modify_menu_data() {
		if ( ! class_exists( 'Bridge_REST_Menus_Controller' ) ) {
			return;
		}

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$menu = $this->create_menu();

		$request  = new WP_REST_Request( 'GET', '/bridge/v1/menus/' . $menu->term_id );
		$request->set_header( 'X-Requested-With', 'minnie' );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		$this->assertFalse( strpos( $data['items'][0]['title'], home_url() ) );
	}
}
