<?php
/**
 * App route base resolution tests.
 *
 * @package Moment
 */

/**
 * The /moment route must step aside (to /moment-app) when existing site
 * content already owns that path, instead of silently shadowing it.
 */
class Test_Routes extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		delete_option( Moment_Routes::OPTION_APP_BASE );
	}

	private function registered_rule_patterns(): array {
		global $wp_rewrite;
		// Top rules accumulate on the shared WP_Rewrite across in-process
		// tests; start from a clean slate for this registration.
		$wp_rewrite->extra_rules_top = array();

		$routes = new Moment_Routes();
		$routes->register();

		return array_keys( $wp_rewrite->extra_rules_top );
	}

	/** Default: no content at /moment, the app claims it. */
	public function test_default_base_is_moment() {
		$this->assertSame( 'moment', Moment_Routes::resolve_app_base() );
		$this->assertSame( home_url( '/moment' ), Moment_Routes::app_url() );
		$this->assertSame( home_url( '/moment/notifications' ), Moment_Routes::app_url( 'notifications' ) );
		$this->assertContains( '^moment/?$', $this->registered_rule_patterns() );
	}

	/** A page at /moment pushes the app to /moment-app. */
	public function test_existing_page_moves_app_to_fallback_base() {
		self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_name'    => 'moment',
				'post_title'   => 'A Moment in Time',
				'post_content' => 'User content that must stay reachable.',
			)
		);

		$this->assertSame( 'moment-app', Moment_Routes::resolve_app_base() );
		$this->assertSame( home_url( '/moment-app' ), Moment_Routes::app_url() );

		$patterns = $this->registered_rule_patterns();
		$this->assertContains( '^moment-app/?$', $patterns );
		$this->assertNotContains( '^moment/?$', $patterns, 'The user page URL must not be shadowed' );

		// The Plugins-page link follows the resolved base.
		$links = apply_filters(
			'plugin_action_links_' . plugin_basename( MOMENT_PLUGIN_FILE ),
			array()
		);
		$this->assertStringContainsString( home_url( '/moment-app' ), $links['open-moment'] );
	}

	/** A post (not page) at /moment also counts as taken. */
	public function test_existing_post_also_moves_app() {
		self::factory()->post->create( array( 'post_name' => 'moment', 'post_title' => 'Moment' ) );

		$this->assertSame( 'moment-app', Moment_Routes::resolve_app_base() );
	}

	/** Once resolved, the base is stable until explicitly re-resolved. */
	public function test_base_is_sticky_between_resolutions() {
		$this->assertSame( 'moment', Moment_Routes::resolve_app_base() );

		self::factory()->post->create( array( 'post_type' => 'page', 'post_name' => 'moment' ) );

		$this->assertSame( 'moment', Moment_Routes::app_base(), 'app_base() must not move once persisted' );
		$this->assertSame( 'moment-app', Moment_Routes::resolve_app_base(), 'Re-activation re-resolves' );
	}

	/** The manifest tracks the resolved base and uses plugin-URL icons. */
	public function test_manifest_tracks_base() {
		self::factory()->post->create( array( 'post_type' => 'page', 'post_name' => 'moment' ) );
		Moment_Routes::resolve_app_base();

		$manifest = Moment_Routes::build_manifest();

		$this->assertSame( home_url( '/moment-app' ), $manifest['start_url'] );
		$this->assertSame( home_url( '/moment-app' ), $manifest['scope'] );
		$this->assertStringStartsWith( MOMENT_PLUGIN_URL, $manifest['icons'][0]['src'] );
	}
}
