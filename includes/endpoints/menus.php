<?php

class Bridge_Menu_Items_Controller extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'bridge/v1';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base = 'menus';


	/**
	 * Register routes.
	 */
	public function register_routes() {
		$routes = array(
			'/(?P<id>[\d]+)',         // By nav menu ID.
			'/(?P<location>[\w-]+)',  // By location.
		);

		foreach ( $routes as $route ) {
			register_rest_route( $this->namespace, '/' . $this->rest_base . $route , array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			) );
		}
	}


	/**
	 * Check if a given request has access to read /menus.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( 'view' !== $request['context'] ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are only allowed to view.' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}


	/**
	 * Get nav menu by location
	 *
	 * @param  string         $location Menu location.
	 * @return object|boolean Nav menu object or FALSE.
	 */
	protected function get_menu_by_location( $location ) {
		$locations = get_nav_menu_locations();

		if ( ! empty( $locations[ $location ] ) ) {
			return wp_get_nav_menu_object( $locations[ $location ] );
		}

		return false;
	}


	/**
	 * Prepare response data
	 *
	 * @param  WP_Term          $item Nav menu object.
	 * @return WP_REST_Response $data Response data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$items = wp_get_nav_menu_items( $item->term_id );

		if ( ! empty( $items ) ) {
			$walker = new Bridge_Walker_Nav_Menu;
			$items  = $walker->walk( $items, 0 );
		} else {
			$items = array();
		}

		$data = array(
			'id'          => $item->term_id,
			'name'        => $item->name,
			'slug'        => $item->slug,
			'description' => $item->description,
			'items'       => $items,
		);

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filter menu data for a response.
		 *
		 * @param WP_REST_Response   $response   The response object.
		 * @param WP_Term            $menu       Nav menu object.
		 * @param WP_REST_Request    $request    Request object.
		 */
		return apply_filters( 'bridge_rest_prepare_menu', $response, $item, $request );
	}


	/**
	 * Get a collection of menu items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$menu_id  = (int) $request['id'];
		$location = $request['location'];

		if ( empty( $menu_id ) && empty( $location ) ) {
			return new WP_Error(
				'bridge_rest_menu_items_invalid_menu_id_location',
				__( 'Invalid post id or location.', 'bridge' ),
				array( 'status' => 404 )
			);
		}

		if ( 0 < $menu_id ) {
			$menu = wp_get_nav_menu_object( $menu_id );
		} elseif ( ! empty( $location ) ) {
			$menu = $this->get_menu_by_location( $location );
		}

		$data = $this->prepare_item_for_response( $menu, $request );
		$data = $this->add_additional_fields_to_object( $data, $request );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}


	/**
	 * Get the menu item's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'nav-menu',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name' => array(
					'description' => __( 'The name for the object.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'slug' => array(
					'description' => __( 'An alphanumeric identifier for the object unique to its type.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'description' => array(
					'description' => __( 'The description for the resource.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'items' => array(
					'description' => __( 'The items for the object.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
					'properties'  => array(
						// ...
					),
				),
			),
		);

		return $schema;
	}


	/**
	 * Get the query params for collections of menu items.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = array();

		$params['context']  = $this->get_context_param();
		$params['location'] = array(
			'description'       => __( 'Limit result set to menu set to specific location.' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}
}
