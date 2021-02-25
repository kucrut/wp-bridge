<?php

class Bridge_Test_Plugin extends Bridge_Test_Case {

	/**
	 * Make sure all needed classes are loaded and actions are added
	 *
	 * @covers ::bridge_load
	 */
	public function test_bridge_load() {
		$this->assertTrue( class_exists( 'WP_REST_Controller' ) );
		$this->assertTrue( class_exists( 'Bridge_Rest_Mods_Post' ) );
		$this->assertTrue( class_exists( 'Bridge_Rest_Mods_Term' ) );
	}

	/**
	 * Make sure /bridge/v1/info controller is loaded
	 *
	 * @covers ::bridge_register_routes
	 */
	public function test_bridge_register_routes() {
		$this->assertTrue( class_exists( 'Bridge_REST_Info_Controller' ) );
		$this->assertEquals( 10, has_action( 'rest_api_init', 'bridge_register_routes' ) );
	}

	/**
	 * @covers ::bridge_should_filter_result
	 */
	public function test_bridge_should_filter_result() {
		$request = new WP_REST_Request( 'OPTIONS', '/wp/v2/posts' );

		$this->assertFalse( bridge_should_filter_result( $request ) );

		$request->set_header( 'X-Requested-With', $this->client_id );
		$this->assertTrue( bridge_should_filter_result( $request ) );
	}

	/**
	 * @covers ::bridge_strip_home_url
	 */
	public function test_bridge_strip_home_url() {
		$stripped = bridge_strip_home_url( get_stylesheet_uri() );
		$this->assertFalse( strpos( $stripped, home_url() ) );
	}
}
