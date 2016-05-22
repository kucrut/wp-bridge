<?php

class Bridge_Test_Plugin extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_Test_Spy_REST_Server;
		do_action( 'rest_api_init' );
	}

	/**
	 * Make sure all needed classes are loaded and actions are added
	 *
	 * @covers ::bridge_load
	 */
	function test_bridge_load() {
		$this->assertTrue( class_exists( 'WP_REST_Controller' ) );

		$this->assertEquals( 11, has_action( 'init', '_bridge_add_extra_api_taxonomy_arguments' ) );

		$this->assertTrue( class_exists( 'Bridge_Walker_Nav_Menu' ) );

		$this->assertTrue( class_exists( 'Bridge_Menu_Items_Controller' ) );

		$this->assertTrue( class_exists( 'Bridge_Rest_Post_Modifier' ) );

		$this->assertTrue( class_exists( 'Bridge_Rest_Term_Modifier' ) );

		$this->assertEquals( 10, has_action( 'rest_api_init', 'bridge_register_routes' ) );
	}


	/**
	 * Make sure our routes are registered
	 *
	 * @covers ::bridge_register_routes
	 */
	function test_bridge_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/bridge/v1/menus/(?P<id>[\d]+)', $routes );
		$this->assertArrayHasKey( '/bridge/v1/menus/(?P<location>[\w-]+)', $routes );
	}
}
