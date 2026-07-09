<?php
/**
 * Minimal Bluesky (AT Protocol) HTTP client.
 *
 * Talks XRPC to the user's PDS (bsky.social by default): app-password
 * session creation, posting via com.atproto.repo.createRecord, and reply
 * fetching via app.bsky.feed.getPostThread. Never throws — every method
 * returns WP_Error on failure.
 *
 * @package Moment_Bluesky
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bluesky XRPC client authenticated with a handle + app password.
 */
class Moment_Bluesky_Client {

	/**
	 * Transient caching the session (accessJwt + did) between requests.
	 *
	 * @var string
	 */
	private const SESSION_TRANSIENT = 'moment_bluesky_session';

	/**
	 * Bluesky post length limit (graphemes; characters is close enough here).
	 *
	 * @var int
	 */
	private const MAX_POST_LENGTH = 300;

	/**
	 * Account handle, e.g. demo.bsky.social.
	 *
	 * @var string
	 */
	private string $handle;

	/**
	 * App password from the Connectors API setting.
	 *
	 * @var string
	 */
	private string $app_password;

	/**
	 * Constructor.
	 *
	 * @param string $handle       Bluesky handle.
	 * @param string $app_password Bluesky app password.
	 */
	public function __construct( string $handle, string $app_password ) {
		$this->handle       = ltrim( trim( $handle ), '@' );
		$this->app_password = trim( $app_password );
	}

	/**
	 * The PDS base URL.
	 *
	 * @return string
	 */
	private function service_url(): string {
		/**
		 * Filters the AT Protocol service (PDS) base URL.
		 *
		 * @param string $url Service URL, default https://bsky.social.
		 */
		return untrailingslashit( (string) apply_filters( 'moment_bluesky_service_url', 'https://bsky.social' ) );
	}

	/**
	 * Create (or reuse) an app-password session.
	 *
	 * @param bool $force Force a fresh session (after a 401).
	 * @return array{accessJwt: string, did: string}|WP_Error
	 */
	private function get_session( bool $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( self::SESSION_TRANSIENT );

			if ( is_array( $cached ) && ! empty( $cached['accessJwt'] ) && ! empty( $cached['did'] ) ) {
				return $cached;
			}
		}

		$response = wp_remote_post(
			$this->service_url() . '/xrpc/com.atproto.server.createSession',
			array(
				'timeout' => 15,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'identifier' => $this->handle,
						'password'   => $this->app_password,
					)
				),
			)
		);

		$data = $this->parse_response( $response, 'createSession' );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['accessJwt'] ) || empty( $data['did'] ) ) {
			return new WP_Error( 'moment_bluesky_session', __( 'Bluesky session response was missing credentials.', 'moment-connector-bluesky' ) );
		}

		$session = array(
			'accessJwt' => (string) $data['accessJwt'],
			'did'       => (string) $data['did'],
		);

		set_transient( self::SESSION_TRANSIENT, $session, 30 * MINUTE_IN_SECONDS );

		return $session;
	}

	/**
	 * Publish a text post.
	 *
	 * @param string $text Post text (truncated to the Bluesky limit).
	 * @return array{at_uri: string, cid: string, web_url: string}|WP_Error
	 */
	public function create_post( string $text ) {
		$session = $this->get_session();

		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$record = array(
			'$type'     => 'app.bsky.feed.post',
			'text'      => $this->truncate( $text ),
			'createdAt' => gmdate( 'Y-m-d\TH:i:s.v\Z' ),
		);

		$body = array(
			'repo'       => $session['did'],
			'collection' => 'app.bsky.feed.post',
			'record'     => $record,
		);

		$data = $this->authed_post( '/xrpc/com.atproto.repo.createRecord', $body, $session );

		// One retry with a fresh session on auth expiry.
		if ( is_wp_error( $data ) && 'moment_bluesky_auth' === $data->get_error_code() ) {
			$session = $this->get_session( true );

			if ( is_wp_error( $session ) ) {
				return $session;
			}

			$body['repo'] = $session['did'];
			$data         = $this->authed_post( '/xrpc/com.atproto.repo.createRecord', $body, $session );
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( empty( $data['uri'] ) ) {
			return new WP_Error( 'moment_bluesky_publish', __( 'Bluesky did not return a post URI.', 'moment-connector-bluesky' ) );
		}

		$at_uri = (string) $data['uri'];

		return array(
			'at_uri'  => $at_uri,
			'cid'     => isset( $data['cid'] ) ? (string) $data['cid'] : '',
			'web_url' => $this->web_url_from_at_uri( $at_uri, $this->handle ),
		);
	}

	/**
	 * Fetch direct replies to a post.
	 *
	 * @param string $at_uri The post's at:// URI.
	 * @return array<int, array{external_id: string, external_url: string, author: string, content: string, created_at: string}>|WP_Error
	 */
	public function get_replies( string $at_uri ) {
		$session = $this->get_session();

		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'uri'   => rawurlencode( $at_uri ),
				'depth' => 1,
			),
			$this->service_url() . '/xrpc/app.bsky.feed.getPostThread'
		);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 15,
				'headers' => array( 'Authorization' => 'Bearer ' . $session['accessJwt'] ),
			)
		);

		$data = $this->parse_response( $response, 'getPostThread' );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$replies = array();

		foreach ( (array) ( $data['thread']['replies'] ?? array() ) as $reply ) {
			$post = $reply['post'] ?? null;

			if ( ! is_array( $post ) || empty( $post['uri'] ) ) {
				continue;
			}

			$author_handle = (string) ( $post['author']['handle'] ?? '' );
			$display_name  = (string) ( $post['author']['displayName'] ?? '' );
			$author        = '' !== $display_name
				? sprintf( '%s (@%s)', $display_name, $author_handle )
				: '@' . $author_handle;

			$created = (string) ( $post['record']['createdAt'] ?? $post['indexedAt'] ?? '' );

			$replies[] = array(
				'external_id'  => (string) $post['uri'],
				'external_url' => $this->web_url_from_at_uri( (string) $post['uri'], $author_handle ),
				'author'       => $author,
				'content'      => (string) ( $post['record']['text'] ?? '' ),
				'created_at'   => $created ? gmdate( 'Y-m-d H:i:s', (int) strtotime( $created ) ) : current_time( 'mysql', true ),
			);
		}

		return $replies;
	}

	/**
	 * POST an authenticated XRPC request.
	 *
	 * @param string                   $path    XRPC path.
	 * @param array<string, mixed>     $body    JSON body.
	 * @param array{accessJwt: string} $session Active session.
	 * @return array<string, mixed>|WP_Error
	 */
	private function authed_post( string $path, array $body, array $session ) {
		$response = wp_remote_post(
			$this->service_url() . $path,
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $session['accessJwt'],
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		return $this->parse_response( $response, $path );
	}

	/**
	 * Decode an XRPC response into an array or a WP_Error.
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

		if ( 401 === $code ) {
			delete_transient( self::SESSION_TRANSIENT );

			return new WP_Error( 'moment_bluesky_auth', __( 'Bluesky authentication failed or expired.', 'moment-connector-bluesky' ) );
		}

		if ( $code < 200 || $code >= 300 ) {
			$message = is_array( $data ) && ! empty( $data['message'] ) ? (string) $data['message'] : $context;

			return new WP_Error(
				'moment_bluesky_http',
				sprintf(
					/* translators: 1: HTTP status code, 2: error detail. */
					__( 'Bluesky request failed (%1$d): %2$s', 'moment-connector-bluesky' ),
					$code,
					$message
				)
			);
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Convert an at:// URI to a public bsky.app URL.
	 *
	 * Example: at://did:plc:xyz/app.bsky.feed.post/rkey → https://bsky.app/profile/{handle}/post/{rkey}
	 *
	 * @param string $at_uri The at:// URI.
	 * @param string $handle Author handle for the profile path.
	 * @return string
	 */
	private function web_url_from_at_uri( string $at_uri, string $handle ): string {
		// Not wp_parse_url(): the colons in `at://did:plc:...` break PHP's
		// URL parsing, so take the record key as the last path segment.
		$parts = explode( '/', untrailingslashit( $at_uri ) );
		$rkey  = (string) end( $parts );

		if ( '' === $rkey || str_starts_with( $rkey, 'did:' ) || '' === $handle ) {
			return '';
		}

		return sprintf( 'https://bsky.app/profile/%s/post/%s', rawurlencode( $handle ), rawurlencode( $rkey ) );
	}

	/**
	 * Truncate text to the Bluesky post limit on a word boundary.
	 *
	 * @param string $text Input text.
	 * @return string
	 */
	private function truncate( string $text ): string {
		$text = trim( $text );

		if ( mb_strlen( $text ) <= self::MAX_POST_LENGTH ) {
			return $text;
		}

		return mb_substr( $text, 0, self::MAX_POST_LENGTH - 1 ) . '…';
	}
}
