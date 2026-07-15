<?php
/**
 * App shell template output tests.
 *
 * @package Moment
 */

/**
 * The shell must load its JS/CSS through the script/style API (wp.org
 * review requirement) while staying free of theme/admin chrome.
 */
class Test_App_Shell extends WP_UnitTestCase {

	private function render_shell(): string {
		$user_id = self::factory()->user->create( array( 'role' => 'author' ) );
		wp_set_current_user( $user_id );

		$this->go_to( '/' );
		set_query_var( Moment_Routes::QUERY_VAR, 'home' );

		ob_start();
		include MOMENT_PLUGIN_DIR . 'templates/app-shell.php';

		return (string) ob_get_clean();
	}

	/** Assets are emitted by the enqueue API, deferred, with inline config. */
	public function test_assets_are_enqueued_via_api() {
		$html = $this->render_shell();

		$this->assertStringContainsString( "id='moment-app-css'", $html, 'Stylesheet must be printed by the style API' );
		$this->assertStringContainsString( 'id="moment-app-js"', $html, 'Script must be printed by the script API' );
		$this->assertStringContainsString( 'defer', $html, 'App script must use the defer strategy' );
		$this->assertStringContainsString( 'window.momentApp = {', $html, 'Inline config must precede the app script' );
		$this->assertStringContainsString( 'ver=' . MOMENT_VERSION, $html, 'Assets must carry the plugin version' );

		// Config must be printed before the deferred app script executes.
		$this->assertLessThan(
			strpos( $html, 'assets/app.js' ),
			strpos( $html, 'window.momentApp' ),
			'Inline config must come before the app script tag'
		);
	}

	/** The shell stays hermetic: no admin bar, no theme head/footer output. */
	public function test_shell_has_no_admin_chrome() {
		$html = $this->render_shell();

		$this->assertStringNotContainsString( 'wpadminbar', $html );
		$this->assertStringNotContainsString( 'adminmenu', $html );
	}
}
