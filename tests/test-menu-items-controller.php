<?php

class Bridge_Test_REST_Menu_Items_Controller extends WP_UnitTestCase {

	/**
	 * Menu
	 *
	 * @var WP_Term
	 */
	protected $menu;

	protected $menu_title = 'Menu';

	protected $menu_item_title = 'Greetings';


	protected function create_menu() {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Hello World' ) );
		$menu_id = wp_create_nav_menu( $this->menu_title );
		$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'post',
			'menu-item-object-id' => $post_id,
			'menu-item-title'     => $this->menu_item_title,
			'menu-item-status'    => 'publish',
		) );

		$this->menu = wp_get_nav_menu_object( $menu_id );
	}


	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->create_menu();

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_Test_Spy_REST_Server;
		do_action( 'rest_api_init' );
	}


	/**
	* Delete the $wp_rest_server global when cleaning up scope.
	*/
	public function clean_up_global_scope() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::clean_up_global_scope();
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


	protected function assert_menu( $data ) {
		$this->assertNotEmpty( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertEquals( $data['name'], $this->menu_title );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 1, $data['items'] );

		$menu_item = $data['items'][0];
		$this->assertEquals( $menu_item['title'], $this->menu_item_title );
	}


	public function test_get_items_by_id() {
		$request  = new WP_REST_Request( 'GET', '/bridge/v1/menus/' . $this->menu->term_id );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assert_menu( $data );
	}
}
