<?php

/**
 * Modify results of API Request to comments
 *
 */
class Bridge_Rest_Mods_Comments {
	public static function init() {
		add_filter( 'rest_prepare_comment', array( __CLASS__, 'modify_comment_data' ), 10, 3 );
	}


	/**
	 * Modify comment data
	 *
	 * @param WP_REST_Response  $response   The response object.
	 * @param object            $comment    The original comment object.
	 * @param WP_REST_Request   $request    Request used to generate the response.
	 */
	public static function modify_comment_data( $response, $comment, $request ) {
		if ( ! bridge_should_filter_result( $request ) ) {
			return $response;
		}

		$data = $response->get_data();

		$data['link'] = bridge_strip_home_url( $data['link'] );
		$data['children_count'] = self::get_children_count( $comment );
		$data['date_formatted'] = self::get_formatted_date( $comment );

		$response->set_data( $data );

		return $response;
	}


	/**
	 * Get comment's formatted date
	 *
	 * @param  WP_Comment $comment The comment object.
	 * @return string
	 */
	public static function get_formatted_date( $comment ) {
		return sprintf(
			_x( '%1$s at %2$s', '1: date, 2: time', 'bridge' ),
			get_comment_date( '', $comment ),
			mysql2date( get_option( 'time_format' ), $comment->comment_date )
		);
	}


	/**
	 * Get number of child comments
	 *
	 * @param  WP_Comment  $comment The comment object.
	 * @return int
	 */
	protected static function get_children_count( $comment ) {
		$children = $comment->get_children();

		return count( $children );
	}
}
