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
		add_rewrite_rule( '^moment/?$', 'index.php?' . self::QUERY_VAR . '=home', 'top' );
		add_rewrite_rule( '^moment/notifications/?$', 'index.php?' . self::QUERY_VAR . '=notifications', 'top' );

		add_filter( 'query_vars', array( $this, 'register_query_var' ) );
		add_filter( 'template_include', array( $this, 'maybe_load_app_shell' ) );
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
