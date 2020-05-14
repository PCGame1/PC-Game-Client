<?php
/**
 * Plugin Name: PCGame client
 * Version: 1.0.0
 * Plugin URI: http://www.hughlashbrooke.com/
 * Description: This is your starter template for your next WordPress plugin.
 * Author: Hugh Lashbrooke
 * Author URI: http://www.hughlashbrooke.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: pcgame-client
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Hugh Lashbrooke
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load plugin class files.
require_once 'includes/class-pcgame-client.php';
require_once 'includes/class-pcgame-client-settings.php';

// Load plugin libraries.
require_once 'includes/lib/class-pcgame-client-admin-api.php';
require_once 'includes/lib/class-pcgame-client-post-type.php';
require_once 'includes/lib/class-pcgame-client-taxonomy.php';

/**
 * Returns the main instance of PCGame_client to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object PCGame_client
 */
function pcgame_client() {
	$instance = PCGame_client::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = PCGame_client_Settings::instance( $instance );
	}

	return $instance;
}

pcgame_client();
