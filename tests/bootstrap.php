<?php
/**
 * Bootstrap the plugin unit testing environment.
 */

// Support for:
// 1. `WP_DEVELOP_DIR` environment variable
// 2. Plugin installed inside of WordPress.org developer checkout
// 3. Tests checked out to /tmp
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' );
} else if ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} else if ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
}

require $test_root . '/includes/functions.php';

// Activate plugin.
function _manually_load_plugin() {
	$bridge_dir  = dirname( dirname( __FILE__ ) );
	$plugins_dir = dirname( $bridge_dir );

	// WP API.
	require $plugins_dir . '/wp-api/plugin.php';

	// Bridge.
	require $bridge_dir . '/bridge.php';
	bridge_load();
}
tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );

require $test_root . '/includes/bootstrap.php';
