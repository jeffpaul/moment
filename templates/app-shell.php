<?php
/**
 * Moment app shell template.
 *
 * Loaded by Moment_Routes via template_include when the moment_app query
 * var is set (/moment, /moment/notifications). Renders a full standalone
 * HTML document — the active theme is intentionally not loaded and
 * wp_head()/wp_footer() are intentionally not called so no theme or admin
 * chrome leaks into the app shell.
 *
 * @package Moment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$moment_screen = get_query_var( Moment_Routes::QUERY_VAR );
$moment_screen = ( is_string( $moment_screen ) && '' !== $moment_screen ) ? $moment_screen : 'home';

if ( ! is_user_logged_in() ) {
	$moment_return_url = 'notifications' === $moment_screen
		? home_url( '/moment/notifications' )
		: home_url( '/moment' );
	wp_safe_redirect( wp_login_url( $moment_return_url ) );
	exit;
}

if ( ! current_user_can( 'edit_posts' ) ) {
	wp_die(
		esc_html__( 'You need permission to create posts to use Moment.', 'moment' ),
		esc_html__( 'Moment', 'moment' ),
		array( 'response' => 403 )
	);
}

$moment_user = wp_get_current_user();

/*
 * Connector list and per-type destination defaults, from the
 * Moment_Syndication_Registry (the source of truth) — so real connector
 * plugins registered via `moment_register_connectors` appear here with
 * their live connection status.
 */
$moment_registry   = Moment_Syndication_Registry::instance();
$moment_connectors = array();

foreach ( $moment_registry->get_connectors() as $moment_connector ) {
	$moment_connectors[] = array(
		'id'           => $moment_connector->get_id(),
		'label'        => $moment_connector->get_label(),
		'connected'    => $moment_connector->is_connected(),
		'status'       => $moment_connector->is_connected() ? 'connected' : 'mocked',
		'status_label' => $moment_connector->get_status_label(),
	);
}

$moment_type_defaults = array();

foreach ( array( 'note', 'image', 'gallery', 'video', 'audio', 'podcast', 'mixed' ) as $moment_type ) {
	$moment_type_defaults[ $moment_type ] = $moment_registry->get_defaults_for_type( $moment_type );
}

$moment_config = array(
	'restUrl'     => esc_url_raw( rest_url( 'moment/v1/' ) ),
	'assetsUrl'   => esc_url_raw( MOMENT_PLUGIN_URL . 'assets/' ),
	'nonce'       => wp_create_nonce( 'wp_rest' ),
	'siteUrl'     => esc_url_raw( home_url( '/' ) ),
	'screen'      => $moment_screen,
	'connectors'  => $moment_connectors,
	'defaults'    => $moment_type_defaults,
	'currentUser' => array(
		'id'          => (int) $moment_user->ID,
		'displayName' => $moment_user->display_name,
	),
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="theme-color" content="#7a00df" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="default" />
	<meta name="apple-mobile-web-app-title" content="Moment" />
	<title><?php esc_html_e( 'Moment', 'moment' ); ?></title>
	<link rel="manifest" href="<?php echo esc_url( MOMENT_PLUGIN_URL . 'assets/manifest.json' ); ?>" />
	<?php /* iOS ignores SVG here; icon-192.png is generated from assets/icon.svg (see README). */ ?>
	<link rel="apple-touch-icon" href="<?php echo esc_url( MOMENT_PLUGIN_URL . 'assets/icon-192.png' ); ?>" />
	<link rel="icon" href="<?php echo esc_url( MOMENT_PLUGIN_URL . 'assets/icon.svg' ); ?>" type="image/svg+xml" />
	<link rel="stylesheet" href="<?php echo esc_url( MOMENT_PLUGIN_URL . 'assets/app.css?ver=' . MOMENT_VERSION ); ?>" />
</head>
<body class="moment-app moment-app--<?php echo esc_attr( $moment_screen ); ?>">
	<div id="moment-app" class="moment-shell">
		<p class="moment-boot"><?php esc_html_e( 'Loading Moment…', 'moment' ); ?></p>
	</div>
	<noscript>
		<p class="moment-noscript"><?php esc_html_e( 'Moment needs JavaScript. Please enable it and reload.', 'moment' ); ?></p>
	</noscript>
	<script>
		window.momentApp = <?php echo wp_json_encode( $moment_config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;
	</script>
	<script src="<?php echo esc_url( MOMENT_PLUGIN_URL . 'assets/app.js?ver=' . MOMENT_VERSION ); ?>" defer></script>
</body>
</html>
