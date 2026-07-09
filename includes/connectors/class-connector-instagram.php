<?php
/**
 * Instagram connector (mocked).
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mocked Instagram destination.
 *
 * Real implementation: authenticate a Business/Creator account through
 * the Instagram Graph API (Facebook Login), create a media container
 * via POST /{ig-user-id}/media, then publish it via
 * POST /{ig-user-id}/media_publish — ideally provided by a WordPress
 * Connector plugin so Moment never stores Meta credentials.
 */
class Moment_Connector_Instagram extends Moment_Connector_Base {

	/**
	 * Connector ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'instagram';
	}

	/**
	 * Connector label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Instagram', 'moment' );
	}

	/**
	 * Supported Moment types.
	 *
	 * @return string[]
	 */
	protected function get_supported_types(): array {
		return array( 'image', 'gallery', 'mixed' );
	}

	/**
	 * Mock external-ID prefix.
	 *
	 * @return string
	 */
	protected function get_mock_id_prefix(): string {
		return 'mock-ig';
	}

	/**
	 * Mock external URL base.
	 *
	 * @return string
	 */
	protected function get_mock_url_base(): string {
		return 'https://instagram.com/p/';
	}
}
