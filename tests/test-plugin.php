<?php

class Bridge_Test_Plugin extends Bridge_Test_Case {

	/**
	 * Make sure all needed classes are loaded and actions are added
	 *
	 * @covers ::bridge_load
	 */
	public function test_bridge_load() {
		$this->assertTrue( class_exists( 'WP_REST_Controller' ) );
		$this->assertTrue( class_exists( 'Bridge_Rest_Post_Modifier' ) );
		$this->assertTrue( class_exists( 'Bridge_Rest_Term_Modifier' ) );
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
