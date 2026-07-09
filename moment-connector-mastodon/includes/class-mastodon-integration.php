<?php
/**
 * WordPress integration: Connectors API registration, settings, backflow.
 *
 * Credentials model (WP 7.0 native):
 * - The access token (the secret) lives in the Connectors API: registered
 *   on `wp_connectors_init`, managed on the Settings → Connectors screen,
 *   masked in REST responses by core, and overridable via the
 *   MASTODON_ACCESS_TOKEN environment variable or PHP constant — exactly
 *   like core's AI provider keys.
 * - The instance URL (not a secret) is a plain setting with a field on
 *   Settings → General.
 *
 * @package Moment_Mastodon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the connector into WordPress and Moment.
 */
class Moment_Mastodon_Integration {

	/**
	 * Environment variable / constant name for the access token.
	 *
	 * @var string
	 */
	private const TOKEN_OVERRIDE_NAME = 'MASTODON_ACCESS_TOKEN';

	/**
	 * Hook everything up.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_connectors_init', array( __CLASS__, 'register_wp_connector' ) );
		add_action( 'init', array( __CLASS__, 'register_settings' ), 10 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings_field' ) );
		add_filter( 'moment_import_network_responses', array( __CLASS__, 'import_responses' ), 10, 5 );
	}

	/**
	 * Register the Mastodon connector with the WP 7.0 Connectors API.
	 *
	 * Core then handles the Settings → Connectors UI, key masking in REST
	 * responses, and env/constant key-source resolution.
	 *
	 * @param WP_Connector_Registry $registry Core connector registry.
	 * @return void
	 */
	public static function register_wp_connector( $registry ): void {
		$instance = self::get_instance_url();

		$registry->register(
			'mastodon',
			array(
				'name'           => __( 'Mastodon', 'moment-connector-mastodon' ),
				'description'    => __( 'Publish Moments to Mastodon and pull replies back into WordPress.', 'moment-connector-mastodon' ),
				'type'           => 'social_network',
				'plugin'         => array(
					'file'      => 'moment-connector-mastodon/moment-connector-mastodon.php',
					'is_active' => static function (): bool {
						return defined( 'MOMENT_MASTODON_VERSION' );
					},
				),
				'authentication' => array(
					'method'          => 'api_key',
					// Access tokens come from the user's own instance.
					'credentials_url' => ( $instance ? $instance : 'https://mastodon.social' ) . '/settings/applications',
					'setting_name'    => MOMENT_MASTODON_TOKEN_SETTING,
					'constant_name'   => self::TOKEN_OVERRIDE_NAME,
					'env_var_name'    => self::TOKEN_OVERRIDE_NAME,
				),
			)
		);
	}

	/**
	 * Register settings.
	 *
	 * The access token is registered in the `connectors` group at priority
	 * 10 — before core's generic fallback at 20 — so it carries a proper
	 * "Access Token" label instead of "API Key".
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'connectors',
			MOMENT_MASTODON_TOKEN_SETTING,
			array(
				'type'              => 'string',
				'label'             => __( 'Mastodon Access Token', 'moment-connector-mastodon' ),
				'description'       => __( 'Access token for the Mastodon connector (create one on your instance under Preferences → Development → New application, with read and write scopes).', 'moment-connector-mastodon' ),
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'general',
			MOMENT_MASTODON_INSTANCE_SETTING,
			array(
				'type'              => 'string',
				'label'             => __( 'Mastodon Instance', 'moment-connector-mastodon' ),
				'description'       => __( 'The Mastodon instance Moments are published to.', 'moment-connector-mastodon' ),
				'default'           => '',
				'show_in_rest'      => false,
				'sanitize_callback' => array( __CLASS__, 'sanitize_instance_url' ),
			)
		);
	}

	/**
	 * Add the instance URL field to Settings → General.
	 *
	 * @return void
	 */
	public static function register_settings_field(): void {
		add_settings_field(
			MOMENT_MASTODON_INSTANCE_SETTING,
			__( 'Mastodon Instance', 'moment-connector-mastodon' ),
			static function (): void {
				printf(
					'<input name="%1$s" id="%1$s" type="url" value="%2$s" class="regular-text" placeholder="https://mastodon.social" /><p class="description">%3$s</p>',
					esc_attr( MOMENT_MASTODON_INSTANCE_SETTING ),
					esc_attr( (string) get_option( MOMENT_MASTODON_INSTANCE_SETTING, '' ) ),
					esc_html__( 'Used by the Moment Mastodon connector. Pair it with an access token on Settings → Connectors.', 'moment-connector-mastodon' )
				);
			},
			'general'
		);
	}

	/**
	 * Normalize an instance URL: force https, strip trailing slash.
	 *
	 * @param mixed $value Raw input.
	 * @return string
	 */
	public static function sanitize_instance_url( $value ): string {
		$url = untrailingslashit( esc_url_raw( (string) $value ) );

		if ( '' === $url ) {
			return '';
		}

		return set_url_scheme( $url, 'https' );
	}

	/**
	 * The configured instance URL.
	 *
	 * @return string
	 */
	public static function get_instance_url(): string {
		return (string) get_option( MOMENT_MASTODON_INSTANCE_SETTING, '' );
	}

	/**
	 * Resolve the access token: env var → constant → Connectors setting.
	 *
	 * Mirrors core's connector key-source precedence.
	 *
	 * @return string
	 */
	public static function get_access_token(): string {
		$env = getenv( self::TOKEN_OVERRIDE_NAME );

		if ( false !== $env && '' !== $env ) {
			return (string) $env;
		}

		if ( defined( self::TOKEN_OVERRIDE_NAME ) ) {
			$constant = constant( self::TOKEN_OVERRIDE_NAME );

			if ( is_string( $constant ) && '' !== $constant ) {
				return $constant;
			}
		}

		return (string) get_option( MOMENT_MASTODON_TOKEN_SETTING, '' );
	}

	/**
	 * Whether both credentials are present.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		return '' !== self::get_instance_url() && '' !== self::get_access_token();
	}

	/**
	 * A client for the configured instance.
	 *
	 * @return Moment_Mastodon_Client
	 */
	public static function client(): Moment_Mastodon_Client {
		return new Moment_Mastodon_Client( self::get_instance_url(), self::get_access_token() );
	}

	/**
	 * Real backflow: import Mastodon replies for a Moment.
	 *
	 * Hooked to `moment_import_network_responses`. Returns null (falls back
	 * to Moment's mock importer) when this isn't a connected real Mastodon
	 * status; otherwise fetches the thread context and imports each direct
	 * reply as a WordPress comment via Moment's importer, which
	 * deduplicates by external ID — safe to run on every sync.
	 *
	 * @param array<int>|null      $handled       Prior handler result.
	 * @param int                  $post_id       Moment post ID.
	 * @param string               $network       Network ID.
	 * @param array<string, mixed> $reference     External post reference.
	 * @param object               $notifications Moment_Notifications instance.
	 * @return array<int>|null
	 */
	public static function import_responses( $handled, int $post_id, string $network, array $reference, $notifications ) {
		if ( null !== $handled || 'mastodon' !== $network ) {
			return $handled;
		}

		$external_id = isset( $reference['external_id'] ) ? (string) $reference['external_id'] : '';

		// Mock references (mock-mastodon-*) stay with the mock importer.
		if ( ! self::is_configured() || '' === $external_id || str_starts_with( $external_id, 'mock-' ) ) {
			return null;
		}

		$replies = self::client()->get_replies( $external_id );

		if ( is_wp_error( $replies ) ) {
			// Backflow must never break the sync flow; report nothing imported.
			return array();
		}

		$imported = array();

		foreach ( $replies as $reply ) {
			$comment_id = $notifications->import_response(
				$post_id,
				'mastodon',
				array(
					'content'      => $reply['content'],
					'author'       => $reply['author'],
					'source_label' => __( 'Reply from Mastodon', 'moment-connector-mastodon' ),
					'external_id'  => 'mastodon-' . $reply['external_id'],
					'external_url' => $reply['external_url'],
					'created_at'   => $reply['created_at'],
				)
			);

			if ( is_int( $comment_id ) && $comment_id > 0 ) {
				$imported[] = $comment_id;
			}
		}

		return $imported;
	}
}
