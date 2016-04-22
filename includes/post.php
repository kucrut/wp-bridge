<?php

/**
 * Modify results of API Request to posts
 *
 */
class Bridge_Rest_Post_Modifier {

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
		'attachment' => array(),
	);

	/**
	 * Map of taxonomy endpoints
	 *
	 * @var array
	 */
	protected static $taxonomy_map = array(
		'tags' => array(
			'taxonomy' => 'post_tag',
		),
		'categories' => array(
			'taxonomy' => 'category',
		),
		'formats' => array(
			'taxonomy' => 'post_format',
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
	 * Modify post data
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
	 * Convert term IDs to objects
	 *
	 * @param  array $data Result data.
	 * @return array
	 */
	protected static function convert_term_ids_to_object( $data ) {
		foreach ( self::$taxonomy_map as $key => $props ) {
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
}
