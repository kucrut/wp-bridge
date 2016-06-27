<?php

class Bridge_REST_Info_Controller extends WP_REST_Controller {
	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'bridge/v1';


	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/info' , array(
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
	 * Get site info
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$data = array();

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		return $response;
	}


	/**
	 * Get site info's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'info',
			'type'       => 'object',
			'properties' => array(
				'name' => array(
					'description' => __( 'The name for the object.' ),
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
			),
		);

		return $schema;
	}
}
