<?php
/**
 * Block and shortcode registration.
 *
 * Registers the moment/* dynamic blocks from the blocks/ directory and
 * the matching moment_* shortcodes. Both delegate to Moment_Renderer so
 * blocks and shortcodes produce identical markup.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers Moment blocks and shortcode fallbacks.
 */
class Moment_Blocks {

	/**
	 * View keys shared by blocks and shortcodes.
	 *
	 * Blocks: moment/{view}. Shortcodes: [moment_{view}].
	 *
	 * @var array<int, string>
	 */
	private const VIEWS = array( 'timeline', 'images', 'videos', 'audio', 'notes' );

	/**
	 * Shared view renderer.
	 *
	 * @var Moment_Renderer
	 */
	private Moment_Renderer $renderer;

	/**
	 * Constructor.
	 *
	 * @param Moment_Renderer|null $renderer Shared renderer instance.
	 */
	public function __construct( ?Moment_Renderer $renderer = null ) {
		$this->renderer = $renderer ?? new Moment_Renderer();
	}

	/**
	 * Register blocks and shortcodes. Called on init.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( self::VIEWS as $view ) {
			add_shortcode(
				'moment_' . $view,
				function ( $atts ) use ( $view ): string {
					return $this->render_shortcode( $view, $atts );
				}
			);

			$block_json = MOMENT_PLUGIN_DIR . 'blocks/' . $view . '/block.json';

			if ( file_exists( $block_json ) ) {
				register_block_type( $block_json );
			}
		}
	}

	/**
	 * Shortcode callback for a Moment view.
	 *
	 * @param string       $view Validated view key from self::VIEWS.
	 * @param array|string $atts Raw shortcode attributes.
	 * @return string Escaped HTML.
	 */
	private function render_shortcode( string $view, $atts ): string {
		$atts = shortcode_atts(
			array(
				'count' => 10,
			),
			(array) $atts,
			'moment_' . $view
		);

		return $this->renderer->render(
			$view,
			array(
				'count' => absint( $atts['count'] ),
			)
		);
	}
}
