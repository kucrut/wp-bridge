<?php

class Bridge_Test_Mods_Comments extends Bridge_Test_Case {
	/**
	 * @covers ::bridge_rest_index
	 */
	public function test_bridge_rest_comments() {
		$post_id = $this->factory->post->create( array(
			'post_title' => 'Yo!',
		));
		$parent_comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $post_id,
			'comment_author' => 'Bridge',
			'comment_author_email' => 'bridge@local.dev',
			'comment_content' => 'Lorem ipsum dolor sit amet bleh bleh.',
		));
		$child_comment_id = $this->factory->comment->create( array(
			'comment_post_ID' => $post_id,
			'comment_parent' => $parent_comment_id,
			'comment_author' => 'Jeff Bridges',
			'comment_author_email' => 'jeff.bridge@local.dev',
			'comment_content' => 'Lorem ipsum dolor sit amet bleh bleh. :P',
		));

		$request  = new WP_REST_Request( 'GET', '/wp/v2/comments', array(
			'post' => $post_id,
		) );
		$request->set_header( 'X-Requested-With', $this->client_id );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertCount( 2, $data );

		foreach ( $data as $comment ) {
			$comment_object = get_comment( $comment['id'] );

			$this->assertFalse( strpos( $comment['link'], home_url() ) );
			$this->assertFalse( strpos( $comment['author_url'], home_url() ) );

			$this->assertArrayHasKey( 'children_count', $comment );
			if ( $comment['id'] === $parent_comment_id ) {
				$this->assertEquals( 1, $comment['children_count'] );
			} else {
				$this->assertEquals( 0, $comment['children_count'] );
			}

			$this->assertArrayHasKey( 'date_formatted', $comment );
			$comment_date_formatted = Bridge_Rest_Mods_Comments::get_formatted_date( $comment_object );
			$this->assertEquals( $comment_date_formatted, $comment['date_formatted'] );
		}
	}
}
