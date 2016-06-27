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


	protected function get_options() {
		$request = new WP_REST_Request( 'OPTIONS', '/bridge/v1/info' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		return $data;
	}


	/**
	 *  Test item schema
	 *
	 *  @covers Bridge_REST_Info_Controller::get_item_schema
	 */
	public function test_get_item_schema() {
		$data = $this->get_options();

		$this->assertArrayHasKey( 'schema', $data );
		$this->assertArrayHasKey( 'properties', $data['schema'] );
	}


	protected function compare_schema_with_data( $properties, $data ) {
		foreach ( $properties as $key => $props ) {
			$this->assertArrayHasKey( $key, $data );

			$type = gettype( $data[ $key ] );

			if ( 'array' === $type ) {
				$type = 'object';

				#var_dump( $key );

				$this->assertArrayHasKey( 'properties', $props );
				$this->compare_schema_with_data( $props['properties'], $data[ $key ] );
			}

			$this->assertEquals( $properties[ $key ]['type'], $type );
		}
	}


	/**
	 *  Make sure the route returns the correct data
	 *
	 *  @covers Bridge_REST_Info_Controller::get_item
	 */
	public function test_get_item() {
		$request    = new WP_REST_Request( 'GET', '/bridge/v1/info' );
		$response   = $this->server->dispatch( $request );
		$data       = $response->get_data();
		$options    = $this->get_options();
		$properties = $options['schema']['properties'];
		$html_dir   = ( function_exists( 'is_rtl' ) && is_rtl() ) ? 'rtl' : 'ltr';

		$this->compare_schema_with_data( $properties, $data );

		$this->assertEquals( get_option( 'siteurl' ), $data['url'] );
		$this->assertEquals( home_url(), $data['home'] );
		$this->assertEquals( get_option( 'blogname' ), $data['name'] );
		$this->assertEquals( get_option( 'blogdescription' ), $data['description'] );
		$this->assertEquals( get_bloginfo( 'language' ), $data['lang'] );
		$this->assertEquals( $html_dir, $data['html_dir'] );
	}
}
