<?php
/**
 * Activation page content tests.
 *
 * @package Moment
 */

/**
 * Section pages are created with dynamic block markup (block-theme
 * native), and blocks render identically to their shortcode twins.
 */
class Test_Activation_Pages extends WP_UnitTestCase {

	public function test_activation_creates_block_based_pages() {
		Moment_Plugin::activate();

		foreach ( array( 'timeline', 'images', 'videos', 'audio', 'notes' ) as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );

			$this->assertInstanceOf( WP_Post::class, $page, "Page /{$slug} should exist" );
			$this->assertStringContainsString( "<!-- wp:moment/{$slug} /-->", $page->post_content );
			$this->assertStringNotContainsString( 'wp:shortcode', $page->post_content );
			$this->assertTrue( has_blocks( $page ), 'Page content must parse as blocks' );
		}
	}

	public function test_block_and_shortcode_render_identically() {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$publisher = new Moment_Publisher();
		$publisher->publish( array( 'caption' => 'Parity check note' ) );

		foreach ( array( 'timeline', 'notes' ) as $view ) {
			$this->assertSame(
				do_shortcode( "[moment_{$view}]" ),
				do_blocks( "<!-- wp:moment/{$view} /-->" ),
				"moment/{$view} block must render byte-identically to [moment_{$view}]"
			);
		}
	}
}
