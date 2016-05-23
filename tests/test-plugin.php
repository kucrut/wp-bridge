<?php

class Bridge_Test_Plugin extends Bridge_Test_Case {

	/**
	 * Make sure all needed classes are loaded and actions are added
	 *
	 * @covers ::bridge_load
	 */
	public function test_bridge_load() {
		$this->assertTrue( class_exists( 'WP_REST_Controller' ) );

		$this->assertEquals( 11, has_action( 'init', '_bridge_add_extra_api_taxonomy_arguments' ) );

		$this->assertTrue( class_exists( 'Bridge_Walker_Nav_Menu' ) );

		$this->assertTrue( class_exists( 'Bridge_Menu_Items_Controller' ) );

		$this->assertTrue( class_exists( 'Bridge_Rest_Post_Modifier' ) );

		$this->assertTrue( class_exists( 'Bridge_Rest_Term_Modifier' ) );

		$this->assertEquals( 10, has_action( 'rest_api_init', 'bridge_register_routes' ) );
	}


	/**
	 * @covers ::_bridge_add_extra_api_taxonomy_arguments
	 */
	public function test_bridge_add_extra_api_taxonomy_arguments() {

		// bootstrap the taxonomy variables
		_bridge_add_extra_api_taxonomy_arguments();

		$taxonomy = get_taxonomy( 'post_format' );
		$this->assertTrue( $taxonomy->show_in_rest );
		$this->assertEquals( 'formats', $taxonomy->rest_base );
		$this->assertEquals( 'WP_REST_Terms_Controller', $taxonomy->rest_controller_class );
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
