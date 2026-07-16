<?php
/**
 * Save-as-draft and deferred-syndication tests.
 *
 * @package Moment
 */

/**
 * Drafts never syndicate; stored targets run when the Moment goes live,
 * whether published from the app or wp-admin.
 */
class Test_Drafts extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );
	}

	private function save_draft_with_target(): int {
		$publisher = new Moment_Publisher();

		return (int) $publisher->publish(
			array(
				'caption'             => 'Draft to finish later',
				'status'              => 'draft',
				'syndication_targets' => array( 'bluesky' ),
			)
		);
	}

	/** An explicit draft request wins even for users who can publish. */
	public function test_explicit_draft_is_saved_as_draft() {
		$post_id = $this->save_draft_with_target();

		$this->assertSame( 'draft', get_post_status( $post_id ) );
	}

	/** Drafts store their targets but never attempt syndication. */
	public function test_draft_does_not_syndicate() {
		$post_id = $this->save_draft_with_target();

		$this->assertSame(
			array( 'bluesky' ),
			json_decode( (string) get_post_meta( $post_id, '_moment_syndication_targets', true ), true )
		);
		$this->assertSame( 'not_attempted', get_post_meta( $post_id, '_moment_syndication_status', true ) );
		$this->assertSame(
			array(),
			(array) json_decode( (string) get_post_meta( $post_id, '_moment_external_posts', true ), true )
		);
	}

	/** Publishing the draft (from anywhere) runs the stored targets once. */
	public function test_publishing_draft_triggers_deferred_syndication() {
		$post_id = $this->save_draft_with_target();

		// Simulates a wp-admin publish: no Moment code path involved.
		wp_publish_post( $post_id );

		$this->assertNotSame( 'not_attempted', get_post_meta( $post_id, '_moment_syndication_status', true ) );
		$external = json_decode( (string) get_post_meta( $post_id, '_moment_external_posts', true ), true );
		$this->assertArrayHasKey( 'bluesky', (array) $external );

		// A later re-publish cycle must not syndicate again.
		$first = $external['bluesky']['external_id'];
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			)
		);
		wp_publish_post( $post_id );
		$external = json_decode( (string) get_post_meta( $post_id, '_moment_external_posts', true ), true );
		$this->assertSame( $first, $external['bluesky']['external_id'], 'Re-publishing must not duplicate syndication' );
	}

	/** The REST endpoint accepts status=draft. */
	public function test_rest_create_accepts_draft_status() {
		$request = new WP_REST_Request( 'POST', '/moment/v1/moments' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$request->set_param( 'caption', 'REST draft' );
		$request->set_param( 'status', 'draft' );

		$response = rest_do_request( $request );

		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( 'draft', $response->get_data()['status'] );
	}

	/** Requesting publish without the capability still degrades to draft. */
	public function test_publish_request_without_capability_stays_draft() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'contributor' ) ) );

		$publisher = new Moment_Publisher();
		$post_id   = (int) $publisher->publish(
			array(
				'caption' => 'Contributor moment',
				'status'  => 'publish',
			)
		);

		$this->assertSame( 'draft', get_post_status( $post_id ) );
	}
}
