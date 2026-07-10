<?php
/**
 * Automatic backflow sync tests.
 *
 * @package Moment
 */

/**
 * Tests Moment_Backflow_Sync scheduling and sync behavior.
 */
class Test_Backflow_Sync extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );
		delete_transient( 'moment_backflow_freshened' );
	}

	/**
	 * Create a published Moment with an external post reference.
	 *
	 * @param bool $backflow_supported Whether the reference is a real connector's.
	 * @return int Post ID.
	 */
	private function create_syndicated_moment( bool $backflow_supported ): int {
		$post_id = (int) self::factory()->post->create( array( 'post_status' => 'publish' ) );
		update_post_meta( $post_id, '_moment_is_moment', '1' );
		update_post_meta( $post_id, '_moment_primary_type', 'note' );
		update_post_meta( $post_id, '_moment_comment_backflow_enabled', '1' );
		update_post_meta(
			$post_id,
			'_moment_external_posts',
			wp_json_encode(
				array(
					'bluesky' => array(
						'external_id'        => $backflow_supported ? 'at://did:plc:x/app.bsky.feed.post/' . $post_id : 'mock-bsky-' . $post_id,
						'external_url'       => 'https://bsky.app/profile/demo/post/' . $post_id,
						'label'              => 'Bluesky',
						'status'             => $backflow_supported ? 'published' : 'mocked',
						'backflow_supported' => $backflow_supported,
					),
				)
			)
		);

		return $post_id;
	}

	/** The recurring schedule is created and cleared. */
	public function test_schedule_and_unschedule() {
		Moment_Backflow_Sync::unschedule();
		$this->assertFalse( wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK ) );

		Moment_Backflow_Sync::schedule();
		$this->assertNotFalse( wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK ) );

		Moment_Backflow_Sync::unschedule();
		$this->assertFalse( wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK ) );
	}

	/** Real syndicated Moments get synced; mock-only Moments are skipped. */
	public function test_sync_targets_real_references_only() {
		$real_id = $this->create_syndicated_moment( true );
		$mock_id = $this->create_syndicated_moment( false );

		$synced = array();
		add_filter(
			'moment_import_network_responses',
			function ( $handled, $post_id, $network ) use ( &$synced ) {
				$synced[] = array( (int) $post_id, $network );

				return array(); // Handled: no comments imported.
			},
			10,
			3
		);

		$sync = new Moment_Backflow_Sync();
		$sync->sync_recent_moments();

		$this->assertContains( array( $real_id, 'bluesky' ), $synced );

		foreach ( $synced as $call ) {
			$this->assertNotSame( $mock_id, $call[0], 'Mock-only Moments must not auto-sync.' );
		}
	}

	/** The per-post cooldown prevents immediate re-polling. */
	public function test_per_post_cooldown() {
		$post_id = $this->create_syndicated_moment( true );

		$calls = 0;
		add_filter(
			'moment_import_network_responses',
			function ( $handled ) use ( &$calls ) {
				++$calls;

				return array();
			}
		);

		$sync = new Moment_Backflow_Sync();
		$sync->sync_recent_moments();
		$sync->sync_recent_moments();

		$this->assertSame( 1, $calls, 'Second sync within the cooldown must skip the post.' );
	}

	/** Viewing notifications schedules one async freshen per window. */
	public function test_maybe_freshen_schedules_once() {
		$sync = new Moment_Backflow_Sync();

		$this->assertFalse( wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK . '_now' ) );

		$sync->maybe_freshen();
		$first = wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK . '_now' );
		$this->assertNotFalse( $first );

		// Within the freshness window a second view is a no-op.
		wp_unschedule_event( $first, Moment_Backflow_Sync::CRON_HOOK . '_now' );
		$sync->maybe_freshen();
		$this->assertFalse( wp_next_scheduled( Moment_Backflow_Sync::CRON_HOOK . '_now' ) );
	}

	/** The notifications endpoint triggers the freshen path. */
	public function test_notifications_endpoint_freshens() {
		$request = new WP_REST_Request( 'GET', '/moment/v1/notifications' );
		$request->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		rest_do_request( $request );

		$this->assertNotFalse( get_transient( 'moment_backflow_freshened' ) );
	}
}
