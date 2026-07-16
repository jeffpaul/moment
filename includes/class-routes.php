<?php
/**
 * Front-end route handling for the Moment app shell.
 *
 * Route strategy (committed): rewrite rules mapping /moment and
 * /moment/notifications to the `moment_app` query var, with a
 * template_include filter that loads templates/app-shell.php.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the /moment rewrite rules and routes to the app shell template.
 */
class Moment_Routes {

	/**
	 * Query var carrying the requested Moment app screen.
	 */
	public const QUERY_VAR = 'moment_app';

	/**
	 * Option storing the resolved app base path ('moment', or 'moment-app'
	 * when existing site content already owns /moment).
	 */
	public const OPTION_APP_BASE = 'moment_app_base';

	/**
	 * Allowed screens for the moment_app query var.
	 *
	 * @var string[]
	 */
	private const SCREENS = array( 'home', 'notifications' );

	/**
	 * Register rewrite rules and hooks. Called on init.
	 *
	 * @return void
	 */
	public function register(): void {
		$base_was_unresolved = '' === (string) get_option( self::OPTION_APP_BASE, '' );
		$base                = self::app_base();

		add_rewrite_rule( '^' . $base . '/?$', 'index.php?' . self::QUERY_VAR . '=home', 'top' );
		add_rewrite_rule( '^' . $base . '/notifications/?$', 'index.php?' . self::QUERY_VAR . '=notifications', 'top' );
		add_rewrite_rule( '^' . $base . '/manifest\.json$', 'index.php?' . self::QUERY_VAR . '=manifest', 'top' );

		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		add_filter( 'template_include', array( $this, 'maybe_load_app_shell' ) );

		// Installs that predate the option just resolved it: persist the
		// rules registered above so the app URL works without a manual
		// permalink flush.
		if ( $base_was_unresolved ) {
			flush_rewrite_rules( false );
		}
	}

	/**
	 * The app's base path. Resolves and persists on first use.
	 *
	 * @return string 'moment', or 'moment-app' when /moment is owned by
	 *                existing site content.
	 */
	public static function app_base(): string {
		$base = get_option( self::OPTION_APP_BASE, '' );

		if ( is_string( $base ) && '' !== $base ) {
			return $base;
		}

		return self::resolve_app_base();
	}

	/**
	 * Resolve which base path the app may claim, and persist it.
	 *
	 * The route is a top rewrite rule, which would silently shadow a page
	 * or post already living at /moment — so when such content exists the
	 * app steps aside to /moment-app. Resolved at activation (and lazily
	 * for older installs), then kept stable: a home-screen-installed app
	 * URL should not move underneath its users.
	 *
	 * @return string The resolved base.
	 */
	public static function resolve_app_base(): string {
		$taken = get_page_by_path( 'moment', OBJECT, array( 'page', 'post' ) ) instanceof WP_Post;
		$base  = $taken ? 'moment-app' : 'moment';

		update_option( self::OPTION_APP_BASE, $base );

		return $base;
	}

	/**
	 * Absolute URL into the Moment app.
	 *
	 * @param string $path Optional path within the app (e.g. 'notifications').
	 * @return string
	 */
	public static function app_url( string $path = '' ): string {
		$url = '/' . self::app_base();

		if ( '' !== $path ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		return home_url( $url );
	}

	/**
	 * The PWA manifest, built against the resolved app base so
	 * home-screen installs open the right URL wherever the app lives.
	 *
	 * @return array<string, mixed>
	 */
	public static function build_manifest(): array {
		return array(
			'name'             => 'Moment',
			'short_name'       => 'Moment',
			'start_url'        => self::app_url(),
			'scope'            => self::app_url(),
			'display'          => 'standalone',
			'background_color' => '#ffffff',
			'theme_color'      => '#7a00df',
			'icons'            => array(
				array(
					'src'   => MOMENT_PLUGIN_URL . 'assets/icon.svg',
					'sizes' => 'any',
					'type'  => 'image/svg+xml',
				),
				array(
					'src'   => MOMENT_PLUGIN_URL . 'assets/icon-192.png',
					'sizes' => '192x192',
					'type'  => 'image/png',
				),
				array(
					'src'   => MOMENT_PLUGIN_URL . 'assets/icon-512.png',
					'sizes' => '512x512',
					'type'  => 'image/png',
				),
			),
		);
	}

	/**
	 * Register the moment_app query var.
	 *
	 * @param string[] $vars Registered query vars.
	 * @return string[]
	 */
	public function register_query_var( array $vars ): array {
		$vars[] = self::QUERY_VAR;

		return $vars;
	}

	/**
	 * Load the Moment app shell template when moment_app is set.
	 *
	 * @param string $template The template WordPress resolved.
	 * @return string
	 */
	public function maybe_load_app_shell( string $template ): string {
		$screen = get_query_var( self::QUERY_VAR );

		if ( ! is_string( $screen ) || '' === $screen ) {
			return $template;
		}

		// The manifest is plain JSON served on the app base (its start_url
		// must track wherever the base resolved to), not an app screen.
		if ( 'manifest' === $screen ) {
			header( 'Content-Type: application/manifest+json; charset=utf-8' );
			echo wp_json_encode( self::build_manifest() );
			exit;
		}

		if ( ! in_array( $screen, self::SCREENS, true ) ) {
			return $template;
		}

		$app_shell = MOMENT_PLUGIN_DIR . 'templates/app-shell.php';

		if ( is_readable( $app_shell ) ) {
			return $app_shell;
		}

		return $template;
	}

	/**
	 * Get the current Moment app screen, if any.
	 *
	 * @return string One of 'home', 'notifications', or '' when not in the app.
	 */
	public function current_screen(): string {
		$screen = get_query_var( self::QUERY_VAR );

		if ( is_string( $screen ) && in_array( $screen, self::SCREENS, true ) ) {
			return $screen;
		}

		return '';
	}
}
