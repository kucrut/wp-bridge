<?php

class Bridge_Test_REST_Info_Controller extends Bridge_Test_Case {
	/**
	 * Make sure our route is registered
	 *
	 * @covers Bridge_REST_Info_Controller::register_routes
	 */
	public function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( '/bridge/v1/info', $routes );
		$this->assertCount( 1, $routes['/bridge/v1/info'] );
	}
}
