<?php

class Bridge_Test_Mods_Index extends Bridge_Test_Case {
	/**
	 * @covers ::bridge_rest_index
	 */
	public function test_bridge_rest_index() {
		$request  = new WP_REST_Request( 'GET', '/' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$lang     = get_bloginfo( 'language' );
		$html_dir = ( function_exists( 'is_rtl' ) && is_rtl() ) ? 'rtl' : 'ltr';

		$this->assertArrayHasKey( 'lang', $data );
		$this->assertEquals( $lang, $data['lang'] );
		$this->assertArrayHasKey( 'html_dir', $data );
		$this->assertEquals( $html_dir, $data['html_dir'] );
	}
}
