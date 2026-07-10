<?php
/**
 * REST title serialization tests.
 *
 * @package Moment
 */

/**
 * Titles returned by the REST API must be plain text, not HTML-entity
 * encoded — the app escapes at render time, so an encoded title would
 * double-escape (regression: "Tonight&#8217;s sky" shown literally).
 */
class Test_Rest_Titles extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );
	}

	private function publish_apostrophe_moment(): int {
		$publisher = new Moment_Publisher();

		return (int) $publisher->publish(
			array(
				'caption'      => "Tonight's sky. No filter.",
				'primary_type' => 'note',
			)
		);
	}

	/** GET /moments summaries carry decoded plain-text titles. */
	public function test_moments_list_title_is_plain_text() {
		$post_id = $this->publish_apostrophe_moment();

		$request = new WP_REST_Request( 'GET', '/moment/v1/moments' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$data = rest_do_request( $request )->get_data();

		$summary = null;
		foreach ( $data as $item ) {
			if ( $post_id === $item['id'] ) {
				$summary = $item;
			}
		}

		$this->assertNotNull( $summary, 'Published Moment should be listed' );
		$this->assertStringNotContainsString( '&#', $summary['title'], 'Title must not be entity-encoded' );
		$this->assertStringContainsString( 's sky', $summary['title'] );
	}

	/** Notification items carry decoded plain-text post titles. */
	public function test_notification_post_title_is_plain_text() {
		$post_id = $this->publish_apostrophe_moment();
		self::factory()->comment->create( array( 'comment_post_ID' => $post_id ) );

		$notifications = new Moment_Notifications();
		$items         = $notifications->get_notifications();

		$this->assertNotEmpty( $items );
		$this->assertStringNotContainsString( '&#', $items[0]['post_title'], 'post_title must not be entity-encoded' );
	}
}
