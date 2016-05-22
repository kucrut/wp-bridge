<?php

class Bridge_Test_REST_Menu_Items_Controller extends WP_UnitTestCase {

	/**
	 * Menu
	 *
	 * @var WP_Term
	 */
	protected $menu;

	/**
	 * @var string
	 */
	protected $menu_title = 'Menu';

	/**
	 * @var integer
	 */
	protected $menu_item_id;

	/**
	 * @var string
	 */
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
		$this->menu_item_id = $item_id;
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
		$this->assertEquals( $this->menu->term_id, $data['id'] );

		$this->assertArrayHasKey( 'name', $data );
		$this->assertEquals( $this->menu_title, $data['name'] );

		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'description', $data );

		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 1, $data['items'] );

		$menu_item = $data['items'][0];
		$this->assertEquals( $menu_item['id'], $this->menu_item_id );
		$this->assertEquals( $menu_item['title'], $this->menu_item_title );
	}


	public function get_menu( $id_or_location ) {
		$request  = new WP_REST_Request( 'GET', '/bridge/v1/menus/' . $id_or_location );
		$response = $this->server->dispatch( $request );

		return $response->get_data();
	}


	public function test_get_menu_by_id() {
		$data = $this->get_menu( $this->menu->term_id );

		$this->assert_menu( $data );
	}
}