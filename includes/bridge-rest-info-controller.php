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
	 * Get blog page data
	 *
	 * @since 0.9.0
	 *
	 * @return array|null
	 */
	protected function get_blog_page(): ?array {
		$page_id = absint( get_option( 'page_for_posts' ) );

		if ( empty( $page_id ) ) {
			return null;
		}

		return [
			'id'  => $page_id,
			'url' => get_permalink( $page_id ),
		];
	}

	/**
	 * Get front page data
	 *
	 * @since 0.9.0
	 *
	 * @return array|null
	 */
	protected function get_front_page(): ?array {
		$page_id = absint( get_option( 'page_on_front' ) );

		if ( empty( $page_id ) ) {
			return null;
		}

		return [
			'id'  => $page_id,
			'url' => home_url(),
		];
	}

	/**
	 * Get site info
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$data = array(
			'url'         => get_option( 'siteurl' ),
			'home'        => home_url(),
			'name'        => get_option( 'blogname' ),
			'description' => get_option( 'blogdescription' ),
			'lang'        => get_bloginfo( 'language' ),
			'html_dir'    => ( function_exists( 'is_rtl' ) && is_rtl() ) ? 'rtl' : 'ltr',
			'settings'    => array(
				'archive' => array(
					'per_page' => absint( get_option( 'posts_per_page' ) ),
				),
				'comments' => array(
					'per_page'      => absint( get_option( 'comments_per_page' ) ),
					'threads'       => (bool) get_option( 'thread_comments' ),
					'threads_depth' => absint( get_option( 'thread_comments_depth' ) ),
				),
				'blog_page'  => $this->get_blog_page(),
				'front_page' => $this->get_front_page(),
			),
		);

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 *  Filter the /info data
		 *
		 * @param WP_REST_Response $response Response data.
		 */
		return apply_filters( 'bridge_rest_info', $response );
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
				'url' => array(
					'description' => __( 'Site URL.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'home' => array(
					'description' => __( 'Home URL.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
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
				'lang' => array(
					'description' => __( 'Site language.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'html_dir' => array(
					'description' => __( 'HTML direction.' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'settings' => array(
					'description' => __( 'Site settings.' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'archive' => array(
							'description' => __( 'Archive settings.' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'per_page' => array(
									'description' => __( 'Posts per page.' ),
									'type'        => 'integer',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'comments' => array(
							'description' => __( 'Comments settings.' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'per_page' => array(
									'description' => __( 'Comments per page.' ),
									'type'        => 'integer',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'threads' => array(
									'description' => __( 'Whether or not threaded comments is enabled.' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'threads_depth' => array(
									'description' => __( 'Comments threads depth.' ),
									'type'        => 'integer',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'blog_page' => [
							'description' => __( 'Blog page.' ),
							'type'        => 'object',
							'context'     => [ 'view' ],
							'readonly'    => true,
							'properties'  => [
								'id' => [
									'description' => __( 'Blog page ID.', 'bridge' ),
									'type'        => 'integer',
									'context'     => array( 'view' ),
									'readonly'    => true,
								],
								'url' => [
									'description' => __( 'Blog page URL.', 'bridge' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								],
							],
						],
					),
				),
			),
		);

		return $schema;
	}
}
