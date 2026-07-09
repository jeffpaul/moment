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
	 * Publish a text status.
	 *
	 * @param string $text Status text (truncated to the Mastodon limit).
	 * @return array{id: string, url: string}|WP_Error
	 */
	public function create_status( string $text ) {
		$response = wp_remote_post(
			$this->instance . '/api/v1/statuses',
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $this->access_token,
				),
				'body'    => wp_json_encode(
					array(
						'status'     => $this->truncate( $text ),
						'visibility' => 'public',
					)
				),
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
