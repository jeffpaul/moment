<?php
/**
 * Minimal Mastodon REST API client.
 *
 * Bearer-token requests against the configured instance: posting via
 * POST /api/v1/statuses and reply fetching via GET /api/v1/statuses/{id}/context.
 * Never throws — every method returns WP_Error on failure.
 *
 * @package Moment_Mastodon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mastodon API client authenticated with an access token.
 */
class Moment_Mastodon_Client {

	/**
	 * Default Mastodon status length limit (instance-configurable upward).
	 *
	 * @var int
	 */
	private const MAX_STATUS_LENGTH = 500;

	/**
	 * Maximum media upload size in bytes (Mastodon default image limit: 8MB).
	 *
	 * @var int
	 */
	private const MAX_MEDIA_BYTES = 8388608;

	/**
	 * Instance base URL, e.g. https://mastodon.social.
	 *
	 * @var string
	 */
	private string $instance;

	/**
	 * Access token from the Connectors API setting.
	 *
	 * @var string
	 */
	private string $access_token;

	/**
	 * Constructor.
	 *
	 * @param string $instance     Instance base URL.
	 * @param string $access_token Access token.
	 */
	public function __construct( string $instance, string $access_token ) {
		$this->instance     = untrailingslashit( trim( $instance ) );
		$this->access_token = trim( $access_token );
	}

	/**
	 * Upload a media file for later attachment to a status.
	 *
	 * POST /api/v2/media with a manually built multipart body (wp_remote_post
	 * has no native multipart support). Mastodon returns 200 when processing
	 * is complete or 202 when accepted but still processing; for images the
	 * processing is effectively synchronous, so either code with an ID is
	 * treated as success (no polling in the prototype).
	 *
	 * @param string $file_path   Absolute path to a local media file.
	 * @param string $description Alt text for the media (optional).
	 * @return array{id: string}|WP_Error
	 */
	public function upload_media( string $file_path, string $description = '' ) {
		if ( '' === $file_path || ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error( 'moment_mastodon_media_file', __( 'Media file is missing or unreadable.', 'moment-connector-mastodon' ) );
		}

		$size = (int) filesize( $file_path );

		if ( $size <= 0 || $size > self::MAX_MEDIA_BYTES ) {
			return new WP_Error(
				'moment_mastodon_media_size',
				sprintf(
					/* translators: %s: maximum upload size, e.g. "8 MB". */
					__( 'Media file exceeds the Mastodon upload limit (%s).', 'moment-connector-mastodon' ),
					size_format( self::MAX_MEDIA_BYTES )
				)
			);
		}

		$boundary = 'moment-mastodon-' . wp_generate_password( 24, false );

		$response = wp_remote_post(
			$this->instance . '/api/v2/media',
			array(
				// Uploads are larger than status posts; allow more time.
				'timeout' => 30,
				'headers' => array(
					'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
					'Authorization' => 'Bearer ' . $this->access_token,
				),
				'body'    => $this->build_multipart_body( $boundary, $file_path, $description ),
			)
		);

		$data = $this->parse_response( $response, 'media' );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// 200 (processed) and 202 (accepted) both pass parse_response; only
		// the returned media ID matters for attaching to a status.
		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'moment_mastodon_media', __( 'Mastodon did not return a media ID.', 'moment-connector-mastodon' ) );
		}

		return array( 'id' => (string) $data['id'] );
	}

	/**
	 * Publish a status, optionally with previously uploaded media attached.
	 *
	 * @param string   $text      Status text (truncated to the Mastodon limit).
	 * @param string[] $media_ids Mastodon media IDs from upload_media() (max 4).
	 * @return array{id: string, url: string}|WP_Error
	 */
	public function create_status( string $text, array $media_ids = array() ) {
		$body = array(
			'status'     => $this->truncate( $text ),
			'visibility' => 'public',
		);

		if ( array() !== $media_ids ) {
			$body['media_ids'] = array_values( array_map( 'strval', $media_ids ) );
		}

		$response = wp_remote_post(
			$this->instance . '/api/v1/statuses',
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->access_token,
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		$data = $this->parse_response( $response, 'statuses' );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'moment_mastodon_publish', __( 'Mastodon did not return a status ID.', 'moment-connector-mastodon' ) );
		}

		return array(
			'id'  => (string) $data['id'],
			'url' => isset( $data['url'] ) ? (string) $data['url'] : '',
		);
	}

	/**
	 * Fetch direct replies to a status.
	 *
	 * The context endpoint returns the whole thread below the status;
	 * only direct replies (in_reply_to_id === the status) are returned.
	 *
	 * @param string $status_id The status ID.
	 * @return array<int, array{external_id: string, external_url: string, author: string, content: string, created_at: string}>|WP_Error
	 */
	public function get_replies( string $status_id ) {
		$response = wp_remote_get(
			$this->instance . '/api/v1/statuses/' . rawurlencode( $status_id ) . '/context',
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $this->access_token ),
			)
		);

		$data = $this->parse_response( $response, 'context' );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$replies = array();

		foreach ( (array) ( $data['descendants'] ?? array() ) as $status ) {
			if ( ! is_array( $status ) || empty( $status['id'] ) ) {
				continue;
			}

			// Direct replies only; deeper thread branches reply to replies.
			if ( (string) ( $status['in_reply_to_id'] ?? '' ) !== $status_id ) {
				continue;
			}

			$acct         = (string) ( $status['account']['acct'] ?? '' );
			$display_name = (string) ( $status['account']['display_name'] ?? '' );
			$author       = '' !== $display_name
				? sprintf( '%s (@%s)', $display_name, $acct )
				: '@' . $acct;

			$created = (string) ( $status['created_at'] ?? '' );

			$replies[] = array(
				'external_id'  => (string) $status['id'],
				'external_url' => (string) ( $status['url'] ?? '' ),
				'author'       => $author,
				// Status content is HTML; comments store plain text.
				'content'      => trim( wp_strip_all_tags( (string) ( $status['content'] ?? '' ) ) ),
				'created_at'   => $created ? gmdate( 'Y-m-d H:i:s', (int) strtotime( $created ) ) : current_time( 'mysql', true ),
			);
		}

		return $replies;
	}

	/**
	 * Decode an API response into an array or a WP_Error.
	 *
	 * @param array|WP_Error $response wp_remote_* response.
	 * @param string         $context  Label for error messages.
	 * @return array<string, mixed>|WP_Error
	 */
	private function parse_response( $response, string $context ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( 401 === $code || 403 === $code ) {
			return new WP_Error( 'moment_mastodon_auth', __( 'Mastodon authentication failed — check the access token.', 'moment-connector-mastodon' ) );
		}

		if ( $code < 200 || $code >= 300 ) {
			$message = is_array( $data ) && ! empty( $data['error'] ) ? (string) $data['error'] : $context;

			return new WP_Error(
				'moment_mastodon_http',
				sprintf(
					/* translators: 1: HTTP status code, 2: error detail. */
					__( 'Mastodon request failed (%1$d): %2$s', 'moment-connector-mastodon' ),
					$code,
					$message
				)
			);
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Build a multipart/form-data body for a media upload.
	 *
	 * WordPress's HTTP API does not build multipart bodies natively, so the
	 * `file` part (raw bytes) and optional `description` part (alt text) are
	 * assembled by hand against the given boundary.
	 *
	 * @param string $boundary    Multipart boundary string.
	 * @param string $file_path   Absolute path to a readable local file.
	 * @param string $description Alt text for the media.
	 * @return string
	 */
	private function build_multipart_body( string $boundary, string $file_path, string $description ): string {
		$filename = wp_basename( $file_path );
		$mime     = (string) ( wp_check_filetype( $filename )['type'] ?? '' );

		if ( '' === $mime ) {
			$mime = 'application/octet-stream';
		}

		$body = '';

		if ( '' !== $description ) {
			$body .= '--' . $boundary . "\r\n";
			$body .= 'Content-Disposition: form-data; name="description"' . "\r\n\r\n";
			$body .= $description . "\r\n";
		}

		$body .= '--' . $boundary . "\r\n";
		$body .= 'Content-Disposition: form-data; name="file"; filename="' . $filename . '"' . "\r\n";
		$body .= 'Content-Type: ' . $mime . "\r\n\r\n";
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading a local Media Library file (path from get_attached_file()) for an API upload body; not a remote or user-supplied path.
		$body .= (string) file_get_contents( $file_path );
		$body .= "\r\n--" . $boundary . '--' . "\r\n";

		return $body;
	}

	/**
	 * Truncate text to the Mastodon status limit.
	 *
	 * @param string $text Input text.
	 * @return string
	 */
	private function truncate( string $text ): string {
		$text = trim( $text );

		if ( mb_strlen( $text ) <= self::MAX_STATUS_LENGTH ) {
			return $text;
		}

		return mb_substr( $text, 0, self::MAX_STATUS_LENGTH - 1 ) . '…';
	}
}
