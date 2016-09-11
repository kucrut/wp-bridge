<?php

/**
 * Modify results of API Request to posts
 *
 */
class Bridge_Rest_Mods_Post {

	/**
	 * Map of post types and their modifiers
	 *
	 * @var array
	 */
	protected static $modifier_map = array(
		'page' => array(
			'cleanup_content',
		),
		'post' => array(
			'cleanup_content',
			'convert_term_ids_to_object',
		),
		'attachment' => array(
			'add_attachment_parent',
		),
	);


	/**
	 * Register hook callbacks
	 */
	public static function init() {
		add_filter( 'rest_prepare_page', array( __CLASS__, 'modify_post_data' ), 10, 3 );
		add_filter( 'rest_prepare_post', array( __CLASS__, 'modify_post_data' ), 10, 3 );
		add_filter( 'rest_prepare_attachment', array( __CLASS__, 'modify_post_data' ), 10, 3 );
	}


	/**
	 * Clean up
	 *
	 * TODO: Support other post types
	 *
	 * @param  array $data Result data.
	 * @return array
	 */
	protected static function cleanup_content( $data ) {
		$content     = &$data['content'];
		$tag_pattern = '/<script.*?script>/';
		$src_pattern = '/src="(.*?\.js)"/';

		preg_match( $tag_pattern, $content['rendered'], $script_tags );

		if ( empty( $script_tags ) ) {
			return $data;
		}

		$script_sources = array();
		foreach ( $script_tags as $script_tag ) {
			preg_match( $src_pattern, $script_tag, $src );
			if ( ! empty( $src ) ) {
				$script_sources[] = $src[1];
			}
		}

		$content['rendered'] = str_replace( $script_tags, '', $content['rendered'] );
		$content['scripts']  = $script_sources;

		return $data;
	}


	/**
	 *  Check if the requested post is for previewing
	 *
	 *  @since  0.5.0
	 *
	 *  @param  WP_REST_Request  $request Request object.
	 *  @return boolean
	 */
	protected static function is_preview_request( $request ) {
		$is_preview = $request->get_param( 'preview' );

		return ! empty( $is_preview );
	}


	/**
	 *  Merge post data with its latest revision's
	 *
	 *  @since  0.5.0
	 *
	 *  @param  array  $data Post data.
	 *  @return array
	 */
	protected static function merge_data_with_revision( $data ) {
		$revisions = wp_get_post_revisions( $data['id'], array(
			'posts_per_page' => 1,
		) );

		if ( empty( $revisions ) ) {
			return $data;
		}

		$post = array_shift( $revisions );

		// @codingStandardsIgnoreStart
		$GLOBALS['post'] = $post;
		// @codingStandardsIgnoreEnd

		setup_postdata( $post );

		$data['title'] = array(
			'raw'      => $post->post_title,
			'rendered' => get_the_title( $post->ID ),
		);
		$data['content'] = array(
			'raw'      => $post->post_content,
			'rendered' => apply_filters( 'the_content', $post->post_content ),
		);

		return $data;
	}


	/**
	 * Modify post data
	 *
	 *  @since  0.1.0
	 *  @since  0.5.0 Merge post data with its latest revision's
	 *
	 * @param WP_REST_Response   $response   The response object.
	 * @param WP_Post            $post       Post object.
	 * @param WP_REST_Request    $request    Request object.
	 *
	 * @return WP_REST_Response
	 */
	public static function modify_post_data( $response, $post, $request ) {
		if ( ! bridge_should_filter_result( $request ) ) {
			return $response;
		}

		$data = $response->data;
		if ( self::is_preview_request( $request ) ) {
			$data = self::merge_data_with_revision( $data );
		}

		$mods = self::$modifier_map[ $post->post_type ];
		foreach ( $mods as $post_type => $callback ) {
			$data = call_user_func( array( __CLASS__, $callback ), $data, $post );
		}

		// Common
		$data['link'] = bridge_strip_home_url( $data['link'] );
		$data['date_formatted'] = get_the_date( '', $post );
		$data['modified_formatted'] = get_post_modified_time( get_option( 'date_format' ), $gmt = false, $post );

		if ( 'attachment' !== $post->post_type ) {
			$data['content']['rendered'] = bridge_strip_home_url( $data['content']['rendered'] );
			$data['excerpt']['rendered'] = bridge_strip_home_url( $data['excerpt']['rendered'] );
			$data['title']['from_content'] = substr( strip_tags( $data['content']['rendered'] ), 0, 44 ) . '…';
		}

		$response->set_data( $data );

		return $response;
	}


	/**
	 * Get object taxonomy maps
	 *
	 * @since 0.6.1
	 * @param string $object_type Object type. Default 'post'.
	 *
	 * @return array
	 */
	protected static function get_taxonomy_map( $object_type = 'post' ) {
		$taxonomy_map    = array();
		$post_taxonomies = get_object_taxonomies( $object_type, 'objects' );

		if ( empty( $post_taxonomies ) ) {
			return $taxonomy_map;
		}

		foreach ( $post_taxonomies as $tax ) {
			if ( $tax->public && ! empty( $tax->show_in_rest ) ) {
				$taxonomy_map[ $tax->rest_base ] = array( 'taxonomy' => $tax->name );
			}
		}

		/**
		 * Filters taxonomies map
		 *
		 * @since 0.6.1
		 * @param array $map Taxonomy map.
		 */
		$taxonomy_map = apply_filters( 'bridge_post_taxonomies_map', $taxonomy_map );

		return $taxonomy_map;
	}


	/**
	 * Convert post term IDs to objects
	 *
	 * @param  array $data Result data.
	 * @return array
	 */
	protected static function convert_term_ids_to_object( $data ) {
		foreach ( self::get_taxonomy_map( $data['type'] ) as $key => $props ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$home_url = home_url();
			$term_ids = $data[ $key ];

			if ( empty( $term_ids ) ) {
				continue;
			}

			$taxonomy = $props['taxonomy'];
			$_terms   = get_terms( array(
				'taxonomy' => $taxonomy,
				'include'  => $term_ids,
			));

			if ( empty( $_terms ) || is_wp_error( $_terms ) ) {
				continue;
			}

			$terms = array();
			foreach ( $_terms as $_term ) {
				$term = array(
					'id'          => $_term->term_id,
					'name'        => $_term->name,
					'slug'        => $_term->slug,
					'description' => $_term->description,
					'link'        => bridge_strip_home_url( get_term_link( $_term, $taxonomy ) ),
				);

				$terms[] = $term;
			}

			$data[ $key ] = $terms;
		}

		return $data;
	}


	/**
	 * Add parent post to attachment data
	 *
	 * @param  array   $data Result data.
	 * @param  WP_Post $post Attachment post object.
	 * @return array
	 */
	protected static function add_attachment_parent( $data, $post ) {
		$parent = get_post( $post->post_parent );
		if ( ! $parent ) {
			return $data;
		}

		$data['parent_post'] = array(
			'id'    => $parent->ID,
			'link'  => bridge_strip_home_url( get_permalink( $parent ) ),
			'title' => array(
				'rendered' => get_the_title( $parent ),
			),
		);

		return $data;
	}
}
