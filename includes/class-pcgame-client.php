<?php
/**
 * Main plugin class file.
 *
 * @package PCGame Client/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
class PCGame_Client {

	/**
	 * The single instance of PCGame_Client.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * Local instance of PCGame_Client_Admin_API
	 *
	 * @var PCGame_Client_Admin_API|null
	 */
	public $admin = null;

	/**
	 * Settings class object
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version; //phpcs:ignore

	/**
	 * The token.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token; //phpcs:ignore

	/**
	 * The main plugin file.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for JavaScripts.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor funtion.
	 *
	 * @param string $file File constructor.
	 * @param string $version Plugin version.
	 */
	public function __construct( $file = '', $version = '1.0.1' ) {
		$this->_version = $version;
		$this->_token   = 'pcgame_client';

		// Load plugin environment variables.
		$this->file       = $file;
		$this->dir        = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		if ( is_admin() ) {
			$this->admin = new PCGame_Client_Admin_API();
		}

		// Handle localisation.
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		if ( $this->isthis_enabled() ) {
			add_filter( 'determine_current_user', array( $this, 'json_basic_auth_handler' ), 20 );

			add_filter( 'rest_authentication_errors', array( $this, 'json_basic_auth_error' ) );

			add_filter( 'rest_authentication_errors', array( $this, 'filter_incoming_connections' ) );
		}

	} // End __construct ()



	/**
	 * Register post type function.
	 *
	 * @param string $post_type Post Type.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param string $description Description.
	 * @param array  $options Options array.
	 *
	 * @return bool|string|PCGame_Client_Post_Type
	 */
	public function register_post_type( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) {
			return false;
		}

		$post_type = new PCGame_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param string $plural Plural Label.
	 * @param string $single Single Label.
	 * @param array  $post_types Post types to register this taxonomy for.
	 * @param array  $taxonomy_args Taxonomy arguments.
	 *
	 * @return bool|string|PCGame_Client_Taxonomy
	 */
	public function register_taxonomy( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) {
			return false;
		}

		$taxonomy = new PCGame_Client_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 *
	 * @access  public
	 * @return void
	 * @since   1.0.0
	 */
	public function enqueue_styles() {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Admin enqueue style.
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return void
	 */
	public function admin_enqueue_styles( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 *
	 * @access  public
	 *
	 * @param string $hook Hook parameter.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_localisation() {
		load_plugin_textdomain( 'pcgame-client', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = 'pcgame-client';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main PCGame_Client Instance
	 *
	 * Ensures only one instance of PCGame_Client is loaded or can be loaded.
	 *
	 * @param string $file File instance.
	 * @param string $version Version parameter.
	 *
	 * @return Object PCGame_Client instance
	 * @see PCGame_Client()
	 * @since 1.0.0
	 * @static
	 */
	public static function instance( $file = '', $version = '1.0.1' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning of PCGame_Client is forbidden' ) ), esc_attr( $this->_version ) );

	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of PCGame_Client is forbidden' ) ), esc_attr( $this->_version ) );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	public function install() {
		$this->_log_version_number();

	} // End install ()

	/**
	 * Log the plugin version number.
	 *
	 * @access  public
	 * @return  void
	 * @since   1.0.0
	 */
	private function _log_version_number() { //phpcs:ignore
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * JSON basic auth.
	 *
	 * @param [type] $user user to auth.
	 * @return int user id.
	 */
	public function json_basic_auth_handler( $user ) {
		global $wp_json_basic_auth_error;

		$wp_json_basic_auth_error = null;

		// Don't authenticate twice.
		if ( ! empty( $user ) ) {
			return $user;
		}

		// Check that we're trying to authenticate.
		if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ) {
			return $user;
			// secondary auth.
			// if ( $this->secondary_auth() ) {
			// 	$user = $this->get_admin_userid();
			// 	return $user->ID;
			// }
		}

		remove_filter( 'determine_current_user', array( $this, 'json_basic_auth_handler' ), 20 );

		// Create ID and token.
		if ( $this->verify_cred() ) {
			$user = $this->get_admin_userid();
		}

		add_filter( 'determine_current_user', array( $this, 'json_basic_auth_handler' ), 20 );

		if ( is_wp_error( $user ) ) {
			$wp_json_basic_auth_error = $user;
			return null;
		}

		$wp_json_basic_auth_error = true;

		return $user->ID;
	}

	/**
	 * Secondary authentication.
	 *
	 * @return boolean
	 */
	public function secondary_auth() {

		if ( ( ! isset( $_REQUEST['authkey'] ) ) ) {
			return false;
		}
//var_dump( $_REQUEST );
		$authkeybase64 = sanitize_text_field( wp_unslash( $_REQUEST['authkey'] ) );

		$keys     = explode( ':', base64_decode( $authkeybase64 ) );
		$username = $keys[0];
		$password = $keys[1];

		$authid  = get_option( 'pcgclient_auth_id' );
		$authkey = get_option( 'pcgclient_auth_key' );

		if ( ( $authid === $username ) && ( $authkey === $password ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * JSON error return.
	 *
	 * @param [type] $error Error message.
	 * @return string error message.
	 */
	public function json_basic_auth_error( $error ) {
		if ( ! empty( $error ) ) {
			return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ) );
		}

		global $wp_json_basic_auth_error;

		return $wp_json_basic_auth_error;
	}

	/**
	 * Filter incoming connections.
	 *
	 * @param [type] $errors Error message.
	 * @return boolean Status if allowed.
	 */
	public function filter_incoming_connections( $errors ) {
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ) );
		}
		$allowedaddress = get_option( 'pcgclient_allowed_ips' );
		$request_server = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );

		if ( ! ( empty( $allowedaddress ) ) ) {
			$csv = explode( ',', $allowedaddress );

			if ( ! in_array( $request_server, $csv, true ) ) {
				return new WP_Error( 'forbidden_access', 'Access denied', array( 'status' => 403 ) );
			} else {
				return true;
			}
		} else {
			return true;
		}

	}

	/**
	 * Roles allowed to connect.
	 *
	 * @return int User Id.
	 */
	public function get_admin_userid() {
		$adminuser = get_users( [ 'role__in' => [ 'administrator' ] ] );
		foreach ( $adminuser as $user ) {
			return $user;
		}
	}

	/**
	 * Enabled plugin.
	 *
	 * @return boolean State of the plugin.
	 */
	public function isthis_enabled() {
		$allowed = get_option( 'pcgclient_enable_access' );
		if ( 'on' === $allowed ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verify user.
	 *
	 * @return boolean Check if authenticated.
	 */
	public function verify_cred() {
		if ( ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ) || ( ! isset( $_SERVER['PHP_AUTH_PW'] ) ) ) {
			return false;
		}
		$username = sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) );
		$password = sanitize_text_field( wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );

		$authid  = get_option( 'pcgclient_auth_id' );
		$authkey = get_option( 'pcgclient_auth_key' );

		if ( ( $authid === $username ) && ( $authkey === $password ) ) {
			return true;
		} else {
			return false;
		}
	}

}
