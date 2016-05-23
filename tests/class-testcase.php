<?php

class Bridge_Test_Case extends WP_UnitTestCase {

	protected $client_id = 'bridge-test';


	public function setUp() {
		parent::setUp();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_Test_Spy_REST_Server;
		do_action( 'rest_api_init' );

		add_filter( 'bridge_client_ids', array( $this, '_register_client_id' ) );
	}


	public function _register_client_id( $client_ids ) {
		$client_ids[] = $this->client_id;

		return $client_ids;
	}


	/**
	* Delete the $wp_rest_server global when cleaning up scope.
	*/
	public function clean_up_global_scope() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::clean_up_global_scope();
	}
}
