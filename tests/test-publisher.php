<?php
/**
 * Publisher tests — E2E scenarios 2, 3, 5 (post creation, metadata, overrides).
 *
 * @package Moment
 */

/**
 * Tests Moment_Publisher and plugin activation basics.
 */
class Test_Publisher extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );
	}

	/** Scenario: plugin activates without fatals. */
	public function test_plugin_loads() {
		$this->assertTrue( class_exists( 'Moment_Plugin' ) );
	}

	/** Scenario: REST namespace registered. */
	public function test_rest_namespace_registered() {
		do_action( 'rest_api_init' );
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( '/moment/v1', $routes );
		$this->assertArrayHasKey( '/moment/v1/moments', $routes );
		$this->assertArrayHasKey( '/moment/v1/ai/suggestions', $routes );
		$this->assertArrayHasKey( '/moment/v1/notifications', $routes );
	}

	/** Scenario 3: note Moment creates a standard post with full metadata. */
	public function test_creates_standard_note_post() {
		$publisher = new Moment_Publisher();
		$post_id   = $publisher->publish(
			array(
				'caption'      => 'Test caption',
				'primary_type' => 'note',
			)
		);

		$this->assertIsInt( $post_id );
		$post = get_post( $post_id );
		$this->assertEquals( 'post', $post->post_type );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( '1', get_post_meta( $post_id, '_moment_is_moment', true ) );
		$this->assertEquals( 'note', get_post_meta( $post_id, '_moment_primary_type', true ) );
		$this->assertEquals( 'mobile', get_post_meta( $post_id, '_moment_created_from', true ) );
	}

	/** A Moment with no media and no caption is rejected. */
	public function test_empty_moment_rejected() {
		$publisher = new Moment_Publisher();
		$result    = $publisher->publish( array() );
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 'moment_empty', $result->get_error_code() );
	}

	/** Unauthenticated REST create is refused with 401. */
	public function test_unauthenticated_rest_create_returns_401() {
		wp_set_current_user( 0 );
		$request  = new WP_REST_Request( 'POST', '/moment/v1/moments' );
		$request->set_param( 'caption', 'nope' );
		$response = rest_do_request( $request );
		$this->assertEquals( 401, $response->get_status() );
	}

	/** Scenario 4: note defaults applied when no selection is sent. */
	public function test_note_defaults_to_bluesky_when_no_selection() {
		$publisher = new Moment_Publisher();
		$post_id   = $publisher->publish(
			array(
				'caption'      => 'Default routing note',
				'primary_type' => 'note',
			)
		);

		$targets = json_decode( (string) get_post_meta( $post_id, '_moment_syndication_targets', true ), true );
		$this->assertContains( 'bluesky', $targets );
	}

	/** Scenario 5: explicit empty selection overrides defaults. */
	public function test_explicit_empty_targets_respected() {
		$publisher = new Moment_Publisher();
		$post_id   = $publisher->publish(
			array(
				'caption'             => 'Override note',
				'primary_type'        => 'note',
				'syndication_targets' => array(),
			)
		);

		$targets = json_decode( (string) get_post_meta( $post_id, '_moment_syndication_targets', true ), true );
		$this->assertSame( array(), $targets );

		// Defaults remain stored for future Moments.
		$defaults = json_decode( (string) get_post_meta( $post_id, '_moment_default_destinations', true ), true );
		$this->assertContains( 'bluesky', $defaults );
	}
}
