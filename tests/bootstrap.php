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

define( 'BRIDGE_TESTS_PLUGINS_DIR', dirname( dirname( dirname( __FILE__ ) ) ) );
define( 'BRIDGE_TESTS_BRIDGE_DIR', BRIDGE_TESTS_PLUGINS_DIR . '/bridge' );
define( 'BRIDGE_TESTS_WP_API_DIR', BRIDGE_TESTS_PLUGINS_DIR . '/wp-api' );

// Activate plugin.
function _manually_load_plugin() {
	// WP API.
	require_once BRIDGE_TESTS_WP_API_DIR . '/plugin.php';

	// Bridge.
	require_once BRIDGE_TESTS_BRIDGE_DIR . '/bridge.php';
	bridge_load();
}
tests_add_filter( 'plugins_loaded', '_manually_load_plugin' );


require_once $test_root . '/includes/bootstrap.php';
require_once BRIDGE_TESTS_BRIDGE_DIR . '/tests/class-testcase.php';
require_once BRIDGE_TESTS_WP_API_DIR . '/tests/class-wp-test-spy-rest-server.php';
