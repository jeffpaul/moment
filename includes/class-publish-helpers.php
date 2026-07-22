<?php
/**
 * Detection of third-party publishing plugins.
 *
 * A Moment is a standard post, so any active "publicize"-style plugin
 * (Jetpack Social, Share on Mastodon, XPoster, …) already syndicates
 * Moments on publish through its own hooks — Moment neither drives nor
 * blocks them. This class only *detects* those plugins so the publish
 * screen can tell the user their Moment will also go out that way. It
 * does not call them, configure them, or change their behavior.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects popular social-publishing plugins for awareness-only surfacing.
 */
class Moment_Publish_Helpers {

	/**
	 * Known publishing-helper plugins and how to detect each.
	 *
	 * A plugin is detected if any of its `slugs` (plugin folder) is active,
	 * or any of its `classes`/`functions`/`constants` exists at runtime —
	 * the latter being more precise (e.g. Jetpack's Publicize class only
	 * loads when that module is on).
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private const PLUGINS = array(
		'jetpack'               => array(
			'label'   => 'Jetpack Social',
			'classes' => array( 'Automattic\\Jetpack\\Publicize\\Publicize' ),
			'slugs'   => array( 'jetpack-social' ),
		),
		'atmosphere'            => array(
			'label'     => 'ATmosphere',
			'slugs'     => array( 'wordpress-atmosphere' ),
			'classes'   => array( 'Atmosphere\\Publisher' ),
			'constants' => array( 'ATMOSPHERE_VERSION' ),
		),
		'autoblue'              => array(
			'label' => 'Autoblue',
			'slugs' => array( 'autoblue' ),
		),
		'share-on-mastodon'     => array(
			'label' => 'Share on Mastodon',
			'slugs' => array( 'share-on-mastodon' ),
		),
		'xposter'               => array(
			'label'     => 'XPoster',
			'slugs'     => array( 'wp-to-twitter' ),
			'functions' => array( 'wpt_post_to_service' ),
		),
		'autoshare-for-twitter' => array(
			'label' => 'Autoshare for Twitter',
			'slugs' => array( 'autoshare-for-twitter' ),
		),
	);

	/**
	 * The detectable plugin definitions, filterable so other publishing
	 * plugins can make themselves known to Moment's awareness note.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function definitions(): array {
		/**
		 * Filter the map of detectable publishing-helper plugins.
		 *
		 * @param array<string, array<string, mixed>> $plugins id => definition,
		 *        where a definition may carry `label`, `slugs`, `classes`,
		 *        `functions`, and `constants`.
		 */
		$defs = apply_filters( 'moment_publish_helper_plugins', self::PLUGINS );

		return is_array( $defs ) ? $defs : array();
	}

	/**
	 * Active publishing-helper plugins as [{id, label}], for surfacing on
	 * the publish screen. Awareness only.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function detect(): array {
		$active = self::active_plugin_slugs();
		$found  = array();

		foreach ( self::definitions() as $id => $def ) {
			if ( is_array( $def ) && self::matches( $def, $active ) ) {
				$found[] = array(
					'id'    => sanitize_key( (string) $id ),
					'label' => sanitize_text_field( (string) ( $def['label'] ?? $id ) ),
				);
			}
		}

		return $found;
	}

	/**
	 * Whether a definition matches the current site.
	 *
	 * @param array<string, mixed> $def          Plugin definition.
	 * @param string[]             $active_slugs Active plugin folder slugs.
	 * @return bool
	 */
	private static function matches( array $def, array $active_slugs ): bool {
		foreach ( (array) ( $def['slugs'] ?? array() ) as $slug ) {
			if ( in_array( $slug, $active_slugs, true ) ) {
				return true;
			}
		}
		foreach ( (array) ( $def['classes'] ?? array() ) as $class ) {
			if ( class_exists( (string) $class ) ) {
				return true;
			}
		}
		foreach ( (array) ( $def['functions'] ?? array() ) as $fn ) {
			if ( function_exists( (string) $fn ) ) {
				return true;
			}
		}
		foreach ( (array) ( $def['constants'] ?? array() ) as $const ) {
			if ( defined( (string) $const ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Folder slugs of all active plugins (site + network).
	 *
	 * @return string[]
	 */
	private static function active_plugin_slugs(): array {
		$paths = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$paths = array_merge( $paths, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
		}

		return array_map(
			static function ( $path ) {
				return strtok( (string) $path, '/' );
			},
			$paths
		);
	}
}
