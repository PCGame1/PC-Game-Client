<?php
/**
 * Plugin Name: PCGame client
 * Version: 1.0.0
 * Plugin URI: http://pcgame.lol/
 * Description: PC Game Client to connect to Server
 * Author: Carl Alberto
 * Author URI: https://carlalberto.code.blog
 * Requires at least: 4.8
 * Tested up to: 5.4.1
 *
 * Text Domain: pcgame-client
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Carl Alberto
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
