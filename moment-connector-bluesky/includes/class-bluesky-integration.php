<?php
/**
 * WordPress integration: Connectors API registration, settings, backflow.
 *
 * Credentials model (WP 7.0 native):
 * - The app password (the secret) lives in the Connectors API: registered
 *   on `wp_connectors_init`, managed on the Settings → Connectors screen,
 *   masked in REST responses by core, and overridable via the
 *   BLUESKY_APP_PASSWORD environment variable or PHP constant — exactly
 *   like core's AI provider keys.
 * - The handle (not a secret) is a plain setting with a field on
 *   Settings → General.
 *
 * @package Moment_Bluesky
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the connector into WordPress and Moment.
 */
class Moment_Bluesky_Integration {

	/**
	 * Environment variable / constant name for the app password.
	 *
	 * @var string
	 */
	private const PASSWORD_OVERRIDE_NAME = 'BLUESKY_APP_PASSWORD';

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
	 * Register the Bluesky connector with the WP 7.0 Connectors API.
	 *
	 * Core then handles the Settings → Connectors UI, key masking in REST
	 * responses, and env/constant key-source resolution.
	 *
	 * @param WP_Connector_Registry $registry Core connector registry.
	 * @return void
	 */
	public static function register_wp_connector( $registry ): void {
		$registry->register(
			'bluesky',
			array(
				'name'           => __( 'Bluesky', 'moment-connector-bluesky' ),
				'description'    => __( 'Publish Moments to Bluesky and pull replies back into WordPress.', 'moment-connector-bluesky' ),
				'type'           => 'social_network',
				'plugin'         => array(
					'file'      => 'moment-connector-bluesky/moment-connector-bluesky.php',
					'is_active' => static function (): bool {
						return defined( 'MOMENT_BLUESKY_VERSION' );
					},
				),
				'authentication' => array(
					'method'          => 'api_key',
					'credentials_url' => 'https://bsky.app/settings/app-passwords',
					'setting_name'    => MOMENT_BLUESKY_PASSWORD_SETTING,
					'constant_name'   => self::PASSWORD_OVERRIDE_NAME,
					'env_var_name'    => self::PASSWORD_OVERRIDE_NAME,
				),
			)
		);
	}

	/**
	 * Register settings.
	 *
	 * The app password is registered in the `connectors` group at priority
	 * 10 — before core's generic fallback at 20 — so it carries a proper
	 * "App Password" label instead of "API Key".
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'connectors',
			MOMENT_BLUESKY_PASSWORD_SETTING,
			array(
				'type'              => 'string',
				'label'             => __( 'Bluesky App Password', 'moment-connector-bluesky' ),
				'description'       => __( 'App password for the Bluesky connector (create one under Bluesky Settings → App Passwords — never your main account password).', 'moment-connector-bluesky' ),
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_setting(
			'general',
			MOMENT_BLUESKY_HANDLE_SETTING,
			array(
				'type'              => 'string',
				'label'             => __( 'Bluesky Handle', 'moment-connector-bluesky' ),
				'description'       => __( 'The Bluesky handle Moments are published as.', 'moment-connector-bluesky' ),
				'default'           => '',
				'show_in_rest'      => false,
				'sanitize_callback' => array( __CLASS__, 'sanitize_handle' ),
			)
		);
	}

	/**
	 * Add the handle field to Settings → General.
	 *
	 * @return void
	 */
	public static function register_settings_field(): void {
		add_settings_field(
			MOMENT_BLUESKY_HANDLE_SETTING,
			__( 'Bluesky Handle', 'moment-connector-bluesky' ),
			static function (): void {
				printf(
					'<input name="%1$s" id="%1$s" type="text" value="%2$s" class="regular-text" placeholder="you.bsky.social" /><p class="description">%3$s</p>',
					esc_attr( MOMENT_BLUESKY_HANDLE_SETTING ),
					esc_attr( (string) get_option( MOMENT_BLUESKY_HANDLE_SETTING, '' ) ),
					esc_html__( 'Used by the Moment Bluesky connector. Pair it with an app password on Settings → Connectors.', 'moment-connector-bluesky' )
				);
			},
			'general'
		);
	}

	/**
	 * Normalize a handle: strip @ and whitespace.
	 *
	 * @param mixed $value Raw input.
	 * @return string
	 */
	public static function sanitize_handle( $value ): string {
		return ltrim( sanitize_text_field( (string) $value ), '@' );
	}

	/**
	 * The configured handle.
	 *
	 * @return string
	 */
	public static function get_handle(): string {
		return (string) get_option( MOMENT_BLUESKY_HANDLE_SETTING, '' );
	}

	/**
	 * Resolve the app password: env var → constant → Connectors setting.
	 *
	 * Mirrors core's connector key-source precedence.
	 *
	 * @return string
	 */
	public static function get_app_password(): string {
		$env = getenv( self::PASSWORD_OVERRIDE_NAME );

		if ( false !== $env && '' !== $env ) {
			return (string) $env;
		}

		if ( defined( self::PASSWORD_OVERRIDE_NAME ) ) {
			$constant = constant( self::PASSWORD_OVERRIDE_NAME );

			if ( is_string( $constant ) && '' !== $constant ) {
				return $constant;
			}
		}

		return (string) get_option( MOMENT_BLUESKY_PASSWORD_SETTING, '' );
	}

	/**
	 * Whether both credentials are present.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		return '' !== self::get_handle() && '' !== self::get_app_password();
	}

	/**
	 * A client for the configured account.
	 *
	 * @return Moment_Bluesky_Client
	 */
	public static function client(): Moment_Bluesky_Client {
		return new Moment_Bluesky_Client( self::get_handle(), self::get_app_password() );
	}

	/**
	 * Real backflow: import Bluesky replies for a Moment.
	 *
	 * Hooked to `moment_import_network_responses`. Returns null (falls back
	 * to Moment's mock importer) when this isn't a connected real Bluesky
	 * post; otherwise fetches the thread and imports each reply as a
	 * WordPress comment via Moment's importer, which deduplicates by
	 * external ID — safe to run on every sync.
	 *
	 * @param array<int>|null      $handled       Prior handler result.
	 * @param int                  $post_id       Moment post ID.
	 * @param string               $network       Network ID.
	 * @param array<string, mixed> $reference     External post reference.
	 * @param object               $notifications Moment_Notifications instance.
	 * @return array<int>|null
	 */
	public static function import_responses( $handled, int $post_id, string $network, array $reference, $notifications ) {
		if ( null !== $handled || 'bluesky' !== $network ) {
			return $handled;
		}

		$external_id = isset( $reference['external_id'] ) ? (string) $reference['external_id'] : '';

		// Mock references (mock-bsky-*) stay with the mock importer.
		if ( ! self::is_configured() || ! str_starts_with( $external_id, 'at://' ) ) {
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
				'bluesky',
				array(
					'content'      => $reply['content'],
					'author'       => $reply['author'],
					'source_label' => __( 'Reply from Bluesky', 'moment-connector-bluesky' ),
					'external_id'  => $reply['external_id'],
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
