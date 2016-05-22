<?php

class Bridge_Test_Plugin extends WP_UnitTestCase {

	protected $client_id = 'minnie';


	public function setUp() {
		parent::setUp();

		add_filter( 'bridge_client_ids', array( $this, '_register_client_id' ) );
	}


	public function _register_client_id( $client_ids ) {
		$client_ids[] = $this->client_id;

		return $client_ids;
	}

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
	 * @covers ::bridge_should_filter_result
	 */
	public function test_bridge_should_filter_result() {
		$request = new WP_REST_Request( 'OPTIONS', '/wp/v2/posts' );

		$this->assertFalse( bridge_should_filter_result( $request ) );

		$request->set_header( 'X-Requested-With', $this->client_id );
		$this->assertTrue( bridge_should_filter_result( $request ) );
	}
}
