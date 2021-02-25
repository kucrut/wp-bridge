<?php

class Bridge_Test_Mods_Post extends Bridge_Test_Case {

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * @var array
	 */
	protected $post_terms;

	/**
	 * @var WP_Post
	 */
	protected $attachment;

	public function setUp() {
		parent::setUp();

		wp_set_current_user( $this->factory->user->create( [ 'role' => 'administrator' ] ) );

		$tag = get_term_by( 'id', $this->factory->tag->create(), 'post_tag' );
		$cat = get_term_by( 'id', $this->factory->category->create(), 'category' );

		$post_id = $this->factory->post->create( [
			'post_content'  => sprintf(
				'Some content with <a href="%s/about">internal URL</a> and <script src="//google.com/ga.js"></script>',
				home_url()
			),
			'post_category' => [ $cat->term_id ],
			'tags_input'    => [ $tag->term_id ],
			'tax_input'     => [
				'post_format' => [ 'aside' ],
			],
		]);

		$this->post = get_post( $post_id );
		$this->post_terms = [
			'categories' => $cat->slug,
			'tags'       => $tag->slug,
			'formats'    => 'aside',
		];

		$orig_file = dirname( __FILE__ ) . '/data/canola.jpg';
		$test_file = '/tmp/canola.jpg';
		copy( $orig_file, $test_file );

		$this->attachment = get_post( $this->factory->attachment->create_object( $test_file, $this->post->ID, [
			'post_mime_type' => 'image/jpeg',
			'post_excerpt'   => 'A sample caption',
		] ) );
	}

	public function test_post() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->post->ID );
		$request->set_header( 'X-Requested-With', $this->client_id );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		$this->assertFalse( strpos( $data['link'], home_url() ) );
		$this->assertNotEmpty( $data['title']['from_content'] );
		$this->assertFalse( strpos( $data['content']['rendered'], home_url() ) );
		$this->assertContains( '//google.com/ga.js', $data['content']['scripts'] );

		$this->assertEquals( get_the_date( '', $this->post ), $data['date_formatted'] );
		$this->assertEquals(
			get_post_modified_time( get_option( 'date_format' ), $gmt = false, $this->post ),
			$data['modified_formatted']
		);

		foreach ( $this->post_terms as $key => $slug ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$terms = $data[ $key ];

			$this->assertArrayHasKey( $key, $data );
			$this->assertCount( 1, $terms );
			$this->assertEquals( $slug, $terms[0]['slug'] );
			$this->assertFalse( strpos( $terms[0]['link'], home_url() ) );
		}
	}

	public function test_preview() {
		$new_title = 'Post revised';
		$new_content = 'Random content for post';
		$rendered_content = apply_filters( 'the_content', $new_content );

		wp_update_post( [
			'ID'           => $this->post->ID,
			'post_title'   => $new_title,
			'post_content' => $new_content,
		]);

		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->post->ID );
		$request->set_param( 'preview', 1 );
		$request->set_header( 'X-Requested-With', $this->client_id );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		$this->assertEquals( $new_title, $data['title']['raw'] );
		$this->assertEquals( $new_content, $data['content']['raw'] );
		$this->assertEquals( $rendered_content, $data['content']['rendered'] );
	}

	public function test_attachment() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/media/' . $this->attachment->ID );
		$request->set_header( 'X-Requested-With', $this->client_id );
		$response = $this->server->dispatch( $request );

		$data = $response->get_data();

		$this->assertArrayHasKey( 'parent_post', $data );
		$parent = $data['parent_post'];
		$this->assertEquals( $this->post->ID, $parent['id'] );
		$this->assertEquals( $this->post->post_title, $parent['title']['rendered'] );
		$this->assertEquals( bridge_strip_home_url( get_permalink( $this->post ) ), $parent['link'] );
	}
}
