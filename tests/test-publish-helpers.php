<?php
/**
 * Third-party publishing-plugin detection tests.
 *
 * @package Moment
 */

/**
 * Tests Moment_Publish_Helpers detection (awareness only).
 */
class Test_Publish_Helpers extends WP_UnitTestCase {

	/** With no syndication plugins active, nothing is detected. */
	public function test_detects_nothing_by_default() {
		$this->assertSame( array(), Moment_Publish_Helpers::detect() );
	}

	/** An active plugin slug is detected and reported with its label. */
	public function test_detects_by_active_plugin_slug() {
		$filter = static function ( $plugins ) {
			$plugins['share-on-mastodon'] = array(
				'label' => 'Share on Mastodon',
				'slugs' => array( 'share-on-mastodon' ),
			);

			return $plugins;
		};

		// Simulate the plugin being active.
		add_filter(
			'option_active_plugins',
			static function ( $value ) {
				$value   = (array) $value;
				$value[] = 'share-on-mastodon/share-on-mastodon.php';

				return $value;
			}
		);

		$found = Moment_Publish_Helpers::detect();
		$ids   = wp_list_pluck( $found, 'id' );

		$this->assertContains( 'share-on-mastodon', $ids );
		$label = $found[ array_search( 'share-on-mastodon', $ids, true ) ]['label'];
		$this->assertSame( 'Share on Mastodon', $label );

		unset( $filter );
	}

	/** A runtime class signature is detected (module-precise plugins). */
	public function test_detects_by_class_signature() {
		// A dummy plugin definition pointing at a class we define here.
		if ( ! class_exists( 'Moment_Test_Publicize' ) ) {
			// phpcs:ignore Squiz.Commenting.ClassComment.Missing, Generic.CodeAnalysis.EmptyStatement
			eval( 'class Moment_Test_Publicize {}' );
		}

		$filter = static function ( $plugins ) {
			$plugins['dummy'] = array(
				'label'   => 'Dummy Publicize',
				'classes' => array( 'Moment_Test_Publicize' ),
			);

			return $plugins;
		};
		add_filter( 'moment_publish_helper_plugins', $filter );

		$ids = wp_list_pluck( Moment_Publish_Helpers::detect(), 'id' );
		$this->assertContains( 'dummy', $ids );

		remove_filter( 'moment_publish_helper_plugins', $filter );
		$this->assertNotContains( 'dummy', wp_list_pluck( Moment_Publish_Helpers::detect(), 'id' ) );
	}

	/** Third parties can register their own plugin via the filter. */
	public function test_definitions_are_filterable() {
		$filter = static function ( $plugins ) {
			$plugins['custom'] = array(
				'label'     => 'Custom',
				'constants' => array( 'MOMENT_TEST_CUSTOM_HELPER' ),
			);

			return $plugins;
		};
		add_filter( 'moment_publish_helper_plugins', $filter );

		$this->assertArrayHasKey( 'custom', Moment_Publish_Helpers::definitions() );

		remove_filter( 'moment_publish_helper_plugins', $filter );
	}
}
