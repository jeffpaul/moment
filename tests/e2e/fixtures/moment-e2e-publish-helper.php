<?php
/**
 * Plugin Name: Moment E2E Publish Helper
 * Description: Registers a fake third-party publishing plugin so Moment's
 *              awareness note ("also shared via …") is exercisable in E2E.
 *
 * @package Moment
 */

define( 'MOMENT_E2E_PUBLISH_HELPER', true );

add_filter(
	'moment_publish_helper_plugins',
	static function ( $plugins ) {
		$plugins['e2e-helper'] = array(
			'label'     => 'Test Publicize',
			'constants' => array( 'MOMENT_E2E_PUBLISH_HELPER' ),
		);

		return $plugins;
	}
);
