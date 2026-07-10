<?php
/**
 * E2E fixture: stubs the Bluesky (AT Protocol) HTTP API.
 *
 * Installed as a mu-plugin on the E2E target site (the CI workflow copies
 * it into wp-content/mu-plugins/) alongside fake Bluesky credentials, so
 * the browser tests exercise the real connected publish + backflow paths
 * deterministically — no network, no real account.
 *
 * NOT part of the shipped plugins. Test infrastructure only.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'pre_http_request',
	static function ( $pre, $args, $url ) {
		if ( ! str_contains( (string) $url, 'bsky.social' ) ) {
			return $pre;
		}

		$json = static function ( array $body ): array {
			return array(
				'response' => array(
					'code'    => 200,
					'message' => 'OK',
				),
				'body'     => wp_json_encode( $body ),
			);
		};

		if ( str_contains( $url, 'createSession' ) ) {
			return $json(
				array(
					'accessJwt' => 'e2e-stub-jwt',
					'did'       => 'did:plc:e2estub',
				)
			);
		}

		if ( str_contains( $url, 'createRecord' ) ) {
			// Unique rkey per post so reply IDs never collide across
			// Moments (imports deduplicate globally by external ID).
			return $json(
				array(
					'uri' => 'at://did:plc:e2estub/app.bsky.feed.post/e2e' . uniqid(),
					'cid' => 'bafye2estub',
				)
			);
		}

		if ( str_contains( $url, 'getPostThread' ) ) {
			// Derive the reply from the requested post URI so each Moment
			// gets its own stable, unique reply.
			$query = array();
			wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			$post_uri = isset( $query['uri'] ) ? rawurldecode( (string) $query['uri'] ) : 'at://did:plc:e2estub/app.bsky.feed.post/unknown';
			$parts    = explode( '/', $post_uri );
			$rkey     = end( $parts );

			return $json(
				array(
					'thread' => array(
						'replies' => array(
							array(
								'post' => array(
									'uri'     => 'at://did:plc:e2efan/app.bsky.feed.post/' . $rkey . '-reply1',
									'author'  => array(
										'handle'      => 'fan.bsky.social',
										'displayName' => 'E2E Fan',
									),
									'record'  => array(
										'text'      => 'Love this one!',
										'createdAt' => '2026-07-09T12:00:00.000Z',
									),
								),
							),
						),
					),
				)
			);
		}

		return $pre;
	},
	10,
	3
);
