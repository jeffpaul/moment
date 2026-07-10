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
	 * Mastodon is text-first, so every Moment type is supported — any
	 * Moment can be announced as caption + permalink. Image Moments
	 * additionally attach the images natively via /api/v2/media.
	 *
	 * @param string $type Moment primary type.
	 * @return bool
	 */
	public function supports_moment_type( string $type ): bool {
		return in_array( $type, array( 'note', 'image', 'gallery', 'video', 'audio', 'podcast', 'mixed' ), true );
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
	 * Maximum media attachments per Mastodon status.
	 *
	 * @var int
	 */
	private const MAX_MEDIA_PER_STATUS = 4;

	/**
	 * Publish a Moment to Mastodon.
	 *
	 * Real path: caption + permalink posted as a public status, with up to
	 * four image attachments uploaded natively first (alt text carried over
	 * as the media description); the status ID is stored as the external ID
	 * so backflow can query the thread context later. Media upload failures
	 * never block — the status falls through to text + link only, with the
	 * failure noted in the result message. Unconfigured or on API failure:
	 * a mocked result, so publishing never blocks (mirrors Moment's AI
	 * Assist philosophy).
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

		$media = $this->upload_image_media( $payload );

		$result = Moment_Mastodon_Integration::client()->create_status( $text, $media['ids'] );

		if ( is_wp_error( $result ) ) {
			// Never block publishing: record a failed-over mock result and
			// surface the reason for the demo/debug trail.
			$mock            = $this->mock_result( $post_id );
			$mock['message'] = $result->get_error_message();

			return $mock;
		}

		$message = __( 'Published to Mastodon.', 'moment-connector-mastodon' );

		if ( array() !== $media['errors'] ) {
			$message .= ' ' . sprintf(
				/* translators: %s: media upload error details. */
				__( 'Some media uploads were skipped: %s', 'moment-connector-mastodon' ),
				implode( '; ', $media['errors'] )
			);
		}

		return array(
			'success'            => true,
			'external_id'        => $result['id'],
			'external_url'       => $result['url'],
			'status'             => 'published',
			'backflow_supported' => true,
			'media_attached'     => count( $media['ids'] ),
			'message'            => $message,
		);
	}

	/**
	 * Upload the Moment's image attachments to Mastodon.
	 *
	 * Only images are uploaded — video/audio go through
	 * Mastodon's async processing pipeline (202 + polling), which is out of
	 * scope; text + permalink still covers them. Alt text becomes the media
	 * description. Individual failures are collected, never thrown, and
	 * never block the status post.
	 *
	 * @param array<string, mixed> $payload Moment context data with media_ids.
	 * @return array{ids: string[], errors: string[]} Mastodon media IDs and failure notes.
	 */
	private function upload_image_media( array $payload ): array {
		$uploaded = array();
		$errors   = array();

		$attachment_ids = isset( $payload['media_ids'] ) && is_array( $payload['media_ids'] )
			? array_map( 'absint', $payload['media_ids'] )
			: array();

		if ( array() === $attachment_ids ) {
			return array(
				'ids'    => array(),
				'errors' => array(),
			);
		}

		$client = Moment_Mastodon_Integration::client();

		foreach ( $attachment_ids as $attachment_id ) {
			if ( count( $uploaded ) >= self::MAX_MEDIA_PER_STATUS ) {
				break;
			}

			if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
				continue;
			}

			$file_path = (string) get_attached_file( $attachment_id );
			$alt_text  = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			$result = $client->upload_media( $file_path, sanitize_text_field( $alt_text ) );

			if ( is_wp_error( $result ) ) {
				$errors[] = sprintf(
					/* translators: 1: attachment ID, 2: error message. */
					__( 'attachment %1$d: %2$s', 'moment-connector-mastodon' ),
					$attachment_id,
					$result->get_error_message()
				);

				continue;
			}

			$uploaded[] = $result['id'];
		}

		return array(
			'ids'    => $uploaded,
			'errors' => $errors,
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
			'media_attached'     => 0,
			'message'            => __( 'Demo mode — Mastodon not connected.', 'moment-connector-mastodon' ),
		);
	}
}
