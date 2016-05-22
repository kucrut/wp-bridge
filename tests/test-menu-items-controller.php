<?php

class Bridge_Test_REST_Menu_Items_Controller extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_Test_Spy_REST_Server;
		do_action( 'rest_api_init' );
	}


	/**
	 * Make sure our routes are registered
	 *
	 * @covers Bridge_Menu_Items_Controller::register_routes
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/bridge/v1/menus/(?P<id>[\d]+)', $routes );
		$this->assertCount( 1, $routes['/bridge/v1/menus/(?P<id>[\d]+)'] );
		$this->assertArrayHasKey( '/bridge/v1/menus/(?P<location>[\w-]+)', $routes );
		$this->assertCount( 1, $routes['/bridge/v1/menus/(?P<location>[\w-]+)'] );
	}
}
