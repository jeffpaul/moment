<?php
/**
 * The real Mastodon connector for Moment's syndication registry.
 *
 * Replaces Moment's built-in mocked Mastodon connector (same ID) when this
 * plugin is active. Publishes via the Mastodon REST API when credentials
 * are configured; degrades to a mocked publish when they are not, so the
 * Moment demo flow keeps working unconfigured.
 *
 * @package Moment_Mastodon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Real Mastodon syndication connector.
 */
class Moment_Mastodon_Connector implements Moment_Syndication_Connector {

	/**
	 * Connector ID — matches Moment's built-in mock so this replaces it.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'mastodon';
	}

	/**
	 * Display label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Mastodon', 'moment-connector-mastodon' );
	}

	/**
	 * Same types as Moment's built-in mock: notes, images, mixed.
	 *
	 * @param string $type Moment primary type.
	 * @return bool
	 */
	public function supports_moment_type( string $type ): bool {
		return in_array( $type, array( 'note', 'image', 'mixed' ), true );
	}

	/**
	 * Connected when an instance URL and access token are configured.
	 *
	 * @return bool
	 */
	public function is_connected(): bool {
		return Moment_Mastodon_Integration::is_configured();
	}

	/**
	 * Status label for the publish screen.
	 *
	 * @return string
	 */
	public function get_status_label(): string {
		return $this->is_connected()
			? __( 'Connected', 'moment-connector-mastodon' )
			: __( 'Not connected · Mocked', 'moment-connector-mastodon' );
	}

	/**
	 * Publish a Moment to Mastodon.
	 *
	 * Real path: caption + permalink posted as a public status; the status
	 * ID is stored as the external ID so backflow can query the thread
	 * context later. Unconfigured or on API failure: a mocked result, so
	 * publishing never blocks (mirrors Moment's AI Assist philosophy).
	 *
	 * @param int                  $post_id Moment post ID.
	 * @param array<string, mixed> $payload Moment context data.
	 * @return array<string, mixed>
	 */
	public function publish( int $post_id, array $payload ): array {
		if ( ! $this->is_connected() ) {
			return $this->mock_result( $post_id );
		}

		$caption = isset( $payload['caption'] ) ? wp_strip_all_tags( (string) $payload['caption'] ) : '';

		if ( '' === trim( $caption ) ) {
			$caption = get_the_title( $post_id );
		}

		$permalink = get_permalink( $post_id );
		$text      = trim( $caption . "\n\n" . ( $permalink ? $permalink : '' ) );

		$result = Moment_Mastodon_Integration::client()->create_status( $text );

		if ( is_wp_error( $result ) ) {
			// Never block publishing: record a failed-over mock result and
			// surface the reason for the demo/debug trail.
			$mock            = $this->mock_result( $post_id );
			$mock['message'] = $result->get_error_message();

			return $mock;
		}

		return array(
			'success'            => true,
			'external_id'        => $result['id'],
			'external_url'       => $result['url'],
			'status'             => 'published',
			'backflow_supported' => true,
			'message'            => __( 'Published to Mastodon.', 'moment-connector-mastodon' ),
		);
	}

	/**
	 * Mocked publish result (unconfigured / failed-over path).
	 *
	 * Shape matches Moment's built-in mock connector so downstream
	 * handling is identical.
	 *
	 * @param int $post_id Moment post ID.
	 * @return array<string, mixed>
	 */
	private function mock_result( int $post_id ): array {
		return array(
			'success'            => true,
			'external_id'        => 'mock-mastodon-' . $post_id,
			'external_url'       => 'https://mastodon.social/@demo/mock-mastodon-' . $post_id,
			'status'             => 'mocked',
			'backflow_supported' => false,
			'message'            => __( 'Demo mode — Mastodon not connected.', 'moment-connector-mastodon' ),
		);
	}
}
