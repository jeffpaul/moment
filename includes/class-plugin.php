<?php
/**
 * Core plugin loader.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Singleton loader that wires up all Moment components.
 */
final class Moment_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Moment_Plugin|null
	 */
	private static ?Moment_Plugin $instance = null;

	/**
	 * Route handler.
	 *
	 * @var Moment_Routes
	 */
	public Moment_Routes $routes;

	/**
	 * REST controller.
	 *
	 * @var Moment_REST_Controller
	 */
	public Moment_REST_Controller $rest_controller;

	/**
	 * Moment publisher.
	 *
	 * @var Moment_Publisher
	 */
	public Moment_Publisher $publisher;

	/**
	 * Syndication links (u-syndication markup on Moment posts).
	 *
	 * @var Moment_Syndication_Links
	 */
	public Moment_Syndication_Links $syndication_links;

	/**
	 * AI Assist adapter.
	 *
	 * @var Moment_AI_Assist
	 */
	public Moment_AI_Assist $ai_assist;

	/**
	 * Block registrar.
	 *
	 * @var Moment_Blocks
	 */
	public Moment_Blocks $blocks;

	/**
	 * View renderer.
	 *
	 * @var Moment_Renderer
	 */
	public Moment_Renderer $renderer;

	/**
	 * Syndication connector registry.
	 *
	 * @var Moment_Syndication_Registry
	 */
	public Moment_Syndication_Registry $syndication_registry;

	/**
	 * Notifications provider.
	 *
	 * @var Moment_Notifications
	 */
	public Moment_Notifications $notifications;

	/**
	 * Pages created on activation: slug => shortcode.
	 *
	 * @var array<string, string>
	 */
	private const ACTIVATION_PAGES = array(
		'timeline' => 'moment_timeline',
		'images'   => 'moment_images',
		'videos'   => 'moment_videos',
		'audio'    => 'moment_audio',
		'notes'    => 'moment_notes',
	);

	/**
	 * Get the singleton instance.
	 *
	 * @return Moment_Plugin
	 */
	public static function instance(): Moment_Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Private constructor. Use instance().
	 */
	private function __construct() {}

	/**
	 * Instantiate components and register hooks.
	 *
	 * @return void
	 */
	private function setup(): void {
		$this->routes               = new Moment_Routes();
		$this->rest_controller      = new Moment_REST_Controller();
		$this->publisher            = new Moment_Publisher();
		$this->ai_assist            = new Moment_AI_Assist();
		$this->renderer             = new Moment_Renderer();
		$this->blocks               = new Moment_Blocks( $this->renderer );
		$this->syndication_registry = Moment_Syndication_Registry::instance();
		$this->notifications        = new Moment_Notifications();
		$this->syndication_links    = new Moment_Syndication_Links();

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'rest_api_init', array( $this->rest_controller, 'register_routes' ) );
	}

	/**
	 * Runs on plugins_loaded.
	 *
	 * @return void
	 */
	public function on_plugins_loaded(): void {
		// Reserved for load-order-sensitive wiring (translations load automatically for WP >= 4.6).
	}

	/**
	 * Runs on init. Registers routes, blocks, and connectors.
	 *
	 * @return void
	 */
	public function on_init(): void {
		$this->routes->register();
		$this->blocks->register();
		$this->syndication_links->register();

		/**
		 * Fires after built-in Moment connectors are registered.
		 *
		 * Third-party connector plugins, WordPress Connector plugins,
		 * or existing social publishing plugins can hook here to register
		 * their own Moment_Syndication_Connector implementations via
		 * $registry->register_connector( $connector ).
		 *
		 * @param Moment_Syndication_Registry $registry The connector registry.
		 */
		do_action( 'moment_register_connectors', $this->syndication_registry );
	}

	/**
	 * Plugin activation callback.
	 *
	 * Registers rewrite rules, creates the Moment view pages, flushes
	 * rewrite rules, and stores activation flags. Never deletes content.
	 *
	 * @return void
	 */
	public static function activate(): void {
		// Register rewrite rules so the flush below picks them up.
		$routes = new Moment_Routes();
		$routes->register();

		self::create_pages();

		flush_rewrite_rules();

		update_option( 'moment_activated', time() );
		update_option( 'moment_version', MOMENT_VERSION );
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * Flushes rewrite rules only. Content, pages, and meta are preserved
	 * by design — Moments must remain standard WordPress content.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Create the Moment view pages if pages with those slugs do not exist.
	 *
	 * @return void
	 */
	private static function create_pages(): void {
		foreach ( self::ACTIVATION_PAGES as $slug => $shortcode ) {
			$existing = get_page_by_path( $slug, OBJECT, 'page' );

			if ( $existing instanceof WP_Post ) {
				continue;
			}

			wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_name'    => $slug,
					'post_title'   => ucfirst( $slug ),
					'post_content' => '<!-- wp:shortcode -->[' . $shortcode . ']<!-- /wp:shortcode -->',
				)
			);
		}
	}
}
