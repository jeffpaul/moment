<?php
/**
 * Server render for the moment/timeline dynamic block.
 *
 * Delegates to the shared Moment_Renderer so the block and the
 * [moment_timeline] shortcode produce identical markup.
 *
 * @package Moment
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$moment_view_count = isset( $attributes['count'] ) ? absint( $attributes['count'] ) : 10;

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Moment_Renderer output is fully escaped at build time.
echo Moment_Plugin::instance()->renderer->render( 'timeline', array( 'count' => $moment_view_count ) );
