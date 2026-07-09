<?php
/**
 * Notifications + backflow tests — E2E scenarios 7, 8, 9.
 *
 * @package Moment
 */

/**
 * Tests Moment_Notifications backflow import, exclusion scope, portability.
 */
class Test_Notifications extends WP_UnitTestCase {

	/** Scenario 8: comments on non-Moment posts are excluded. */
	public function test_excludes_normal_post_comments() {
		$normal_post = self::factory()->post->create( array( 'post_type' => 'post' ) );
		$comment_id  = self::factory()->comment->create( array( 'comment_post_ID' => $normal_post ) );

		$moment_post = self::factory()->post->create( array( 'post_type' => 'post' ) );
		update_post_meta( $moment_post, '_moment_is_moment', '1' );
		update_post_meta( $moment_post, '_moment_primary_type', 'note' );
		$moment_comment = self::factory()->comment->create( array( 'comment_post_ID' => $moment_post ) );

		$notifications = new Moment_Notifications();
		$results       = $notifications->get_notifications();

		$returned_ids = array_column( $results, 'comment_ID' );
		$this->assertContains( (int) $moment_comment, $returned_ids, 'Moment comment should appear' );
		$this->assertNotContains( (int) $comment_id, $returned_ids, 'Normal post comment must not appear' );
	}

	/** Scenario 7: mocked sync imports labeled comments and dedupes on repeat. */
	public function test_import_responses_labels_and_dedupes() {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$publisher = new Moment_Publisher();
		$post_id   = $publisher->publish(
			array(
				'caption'             => 'Backflow test note',
				'primary_type'        => 'note',
				'syndication_targets' => array( 'bluesky' ),
			)
		);

		$notifications = new Moment_Notifications();
		$result        = $notifications->import_responses( $post_id, array( 'bluesky' ) );

		$this->assertIsArray( $result );
		$this->assertGreaterThanOrEqual( 1, $result['imported_count'] );
		$labels = array_column( $result['comments'], 'source_label' );
		$this->assertContains( 'Reply from Bluesky', $labels );

		// Repeat sync must not duplicate.
		$second = $notifications->import_responses( $post_id, array( 'bluesky' ) );
		$this->assertSame( 0, $second['imported_count'] );
	}

	/** Sync against a non-Moment post is a 404. */
	public function test_sync_non_moment_post_is_404() {
		$normal_post   = self::factory()->post->create( array( 'post_type' => 'post' ) );
		$notifications = new Moment_Notifications();
		$result        = $notifications->import_responses( $normal_post, array( 'bluesky' ) );

		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( 404, $result->get_error_data()['status'] );
	}

	/** Scenario 9: deactivation preserves Moment posts. */
	public function test_post_survives_deactivation() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'  => 'post',
				'post_title' => 'Portability test',
			)
		);
		update_post_meta( $post_id, '_moment_is_moment', '1' );

		// Simulate deactivation (flushes rewrites only; never deletes content).
		Moment_Plugin::deactivate();

		$post = get_post( $post_id );
		$this->assertNotNull( $post );
		$this->assertEquals( 'Portability test', $post->post_title );
		$this->assertEquals( '1', get_post_meta( $post_id, '_moment_is_moment', true ) );
	}
}
