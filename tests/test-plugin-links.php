<?php
/**
 * Plugins list table action link tests.
 *
 * @package Moment
 */

/**
 * The plugin row must offer an "Open Moment" link to /moment.
 */
class Test_Plugin_Links extends WP_UnitTestCase {

	public function test_open_moment_action_link_is_prepended() {
		$links = apply_filters(
			'plugin_action_links_' . plugin_basename( MOMENT_PLUGIN_FILE ),
			array( 'deactivate' => '<a href="#">Deactivate</a>' )
		);

		$this->assertArrayHasKey( 'open-moment', $links );
		$this->assertStringContainsString( home_url( '/moment' ), $links['open-moment'] );
		$this->assertStringContainsString( 'Open Moment', $links['open-moment'] );
		$this->assertSame( 'open-moment', array_key_first( $links ), 'Open Moment should come before Deactivate' );
		$this->assertArrayHasKey( 'deactivate', $links, 'Existing links must be preserved' );
	}
}
