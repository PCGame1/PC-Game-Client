<?php
/**
 * Settings class file.
 *
 * @package WordPress Plugin Template/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings class.
 */
class PCGame_Client_Settings {

	/**
	 * The single instance of PCGame_Client_Settings.
	 *
	 * @var     object
	 * @access  private
	 * @since   1.0.0
	 */
	private static $_instance = null; //phpcs:ignore

	/**
	 * The main plugin object.
	 *
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 *
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Constructor function.
	 *
	 * @param object $parent Parent object.
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		$this->base = 'pcgclient_';

		// Initialise settings.
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Add settings page to menu.
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page.
		add_filter(
			'plugin_action_links_' . plugin_basename( $this->parent->file ),
			array(
				$this,
				'add_settings_link',
			)
		);

		// Configure placement of plugin settings page. See readme for implementation.
		add_filter( $this->base . 'menu_settings', array( $this, 'configure_settings' ) );
	}

	/**
	 * Initialise settings
	 *
	 * @return void
	 */
	public function init_settings() {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 *
	 * @return void
	 */
	public function add_menu_item() {

		$args = $this->menu_settings();

		// Do nothing if wrong location key is set.
		if ( is_array( $args ) && isset( $args['location'] ) && function_exists( 'add_' . $args['location'] . '_page' ) ) {
			switch ( $args['location'] ) {
				case 'options':
				case 'submenu':
					$page = add_submenu_page( $args['parent_slug'], $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'] );
					break;
				case 'menu':
					$page = add_menu_page( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], $args['function'], $args['icon_url'], $args['position'] );
					break;
				default:
					return;
			}
			add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
		}
	}

	/**
	 * Prepare default settings page arguments
	 *
	 * @return mixed|void
	 */
	private function menu_settings() {
		return apply_filters(
			$this->base . 'menu_settings',
			array(
				'location'    => 'menu',
				'parent_slug' => 'options-general.php',
				'page_title'  => __( 'PC Game Client', 'pcgame-client' ),
				'menu_title'  => __( 'PC Game Client', 'pcgame-client' ),
				'capability'  => 'manage_options',
				'menu_slug'   => $this->parent->_token . '_settings',
				'function'    => array( $this, 'settings_page' ),
				'icon_url'    => 'dashicons-admin-site-alt2',
				'position'    => 2,
			)
		);
	}

	/**
	 * Container for settings page arguments
	 *
	 * @param array $settings Settings array.
	 *
	 * @return array
	 */
	public function configure_settings( $settings = array() ) {
		return $settings;
	}

	/**
	 * Load settings JS & CSS
	 *
	 * @return void
	 */
	public function settings_assets() {

	}

	/**
	 * Add settings link to plugin list table
	 *
	 * @param  array $links Existing links.
	 * @return array        Modified links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'pcgame-client' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}

	/**
	 * Generate tokens.
	 *
	 * @param [type] $chars random seed.
	 * @return string secret tokens.
	 */
	public function maketokens( $chars ) {
		return wp_generate_password( $chars, false );
	}


	/**
	 * Build settings fields
	 *
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields() {

		$settings['standard'] = array(
			'description' => __( 'This enables the site to connect to PC Game Server site', 'pcgame-client' ),
			'fields'      => array(
				array(
					'id'          => 'enable_access',
					'label'       => __( 'Enable Connection', 'pcgame-client' ),
					'description' => __( 'Enable remote connection to site', 'pcgame-client' ),
					'type'        => 'checkbox',
					'default'     => 'on',
				),
				array(
					'id'          => 'auth_id',
					'label'       => __( 'Authentication ID', 'pcgame-client' ),
					'description' => __( 'Unique random generated ID to access the site.', 'pcgame-client' ),
					'type'        => 'text',
					'default'     => $this->maketokens( 12 ),
					'placeholder' => __( 'Do not leave blank', 'pcgame-client' ),
				),
				array(
					'id'          => 'auth_key',
					'label'       => __( 'Authentication Key', 'pcgame-client' ),
					'description' => __( 'Unique random generated KEY to access the site.', 'pcgame-client' ),
					'type'        => 'text',
					'default'     => $this->maketokens( 20 ),
					'placeholder' => __( 'Do not leave blank', 'pcgame-client' ),
				),
				array(
					'id'          => 'allowed_ips',
					'label'       => __( 'Allowed IPs', 'pcgame-client' ),
					'description' => __( 'When left blank, it is open for any IP connection', 'pcgame-client' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'x.x.x.x', 'pcgame-client' ),
				),
			),
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab.
			//phpcs:disable
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}
			//phpcs:enable

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section !== $section ) {
					continue;
				}

				// Add section to page.
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field.
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field.
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page.
					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this->parent->admin, 'display_field' ),
						$this->parent->_token . '_settings',
						$section,
						array(
							'field'  => $field,
							'prefix' => $this->base,
						)
					);
				}

				if ( ! $current_section ) {
					break;
				}
			}
		}
	}

	/**
	 * Settings section.
	 *
	 * @param array $section Array of section ids.
	 * @return void
	 */
	public function settings_section( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo wp_kses( $html, $this->allowed_htmls );
	}

	/**
	 * Get authentication hash.
	 *
	 * @return string Authentication hash.
	 */
	public function get_auth_hash() {
		$authid  = get_option( 'pcgclient_auth_id' );
		$authkey = get_option( 'pcgclient_auth_key' );

		$authentication_hash = base64_encode( $authid . ':' . $authkey ); //phpcs:ignore
		return $authentication_hash;
	}

	/**
	 * Initialize values.
	 *
	 * @return string Authentication output.
	 */
	public function init_values() {
		$authid  = get_option( 'pcgclient_auth_id' );
		$authkey = get_option( 'pcgclient_auth_key' );

		if ( ( empty( $authid ) ) || ( empty( $authkey ) ) ) {
			return 'Do not leave Authentication ID or KEY empty. Make sure you save the random seed when the plugin is ran for the first time.';
		} else {
			$key  = base64_encode( $authid . ':' . $authkey ); //phpcs:ignore
			$url  = 'Use this to PCGame Admin Quick add: <code>' . site_url() . '|' . $key . '</code><br><br>OR Add new Site individually using:<br><br>';
			$url .= 'Site URL: <code>' . site_url() . '</code><br>';
			$url .= 'Site Passkey: <code>' . $key . '</code>';

			return $url;
		}
	}


	/**
	 * Load settings page content.
	 *
	 * @return void
	 */
	public function settings_page() {

		$datareturn = $this->init_values();

		// Build page HTML.
		$html      = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'PC Game Client Settings', 'pcgame-client' ) . '</h2>' . "\n";

			$tab = '';
		//phpcs:disable
		if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
			$tab .= $_GET['tab'];
		}
		//phpcs:enable

		// Show page tabs.
		if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

			$html .= '<h2 class="nav-tab-wrapper">' . "\n";

			$c = 0;
			foreach ( $this->settings as $section => $data ) {

				// Set tab class.
				$class = 'nav-tab';
				if ( ! isset( $_GET['tab'] ) ) { //phpcs:ignore
					if ( 0 === $c ) {
						$class .= ' nav-tab-active';
					}
				} else {
					if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) { //phpcs:ignore
						$class .= ' nav-tab-active';
					}
				}

				// Set tab link.
				$tab_link = add_query_arg( array( 'tab' => $section ) );
				if ( isset( $_GET['settings-updated'] ) ) { //phpcs:ignore
					$tab_link = remove_query_arg( 'settings-updated', $tab_link );
				}

				// Output tab.
				$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

				++$c;
			}

			$html .= '</h2>' . "\n";
		}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields.
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html     .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings', 'pcgame-client' ) ) . '" />' . "\n";
				$html     .= '</p>' . "\n";
				$html     .= $datareturn . "\n";
			$html         .= '</form>' . "\n";
		$html             .= '</div>' . "\n";

		echo wp_kses( $html, $this->allowed_htmls );
	}


	/**
	 * Main PCGame_Client_Settings Instance
	 *
	 * Ensures only one instance of PCGame_Client_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see PCGame_Client()
	 * @param object $parent Object instance.
	 * @return object PCGame_Client_Settings instance
	 */
	public static function instance( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning of PCGame_Client_API is forbidden.' ) ), esc_attr( $this->parent->_version ) );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of PCGame_Client_API is forbidden.' ) ), esc_attr( $this->parent->_version ) );
	} // End __wakeup()

	/**
	 * Allowed html for output.
	 *
	 * @var array
	 */
	public $allowed_htmls = [
		'a'        => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'h1'       => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'h2'       => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'h3'       => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'h4'       => [
			'href'  => [],
			'title' => [],
			'class' => [],
		],
		'input'    => [
			'id'                  => [],
			'type'                => [],
			'name'                => [],
			'placeholder'         => [],
			'value'               => [],
			'class'               => [],
			'checked'             => [],
			'style'               => [],
			'data-uploader_title' => [],
			'data-uploader_text'  => [],
			'tabindex'            => [],
		],
		'select'   => [
			'id'          => [],
			'type'        => [],
			'name'        => [],
			'placeholder' => [],
			'value'       => [],
			'multiple'    => [],
			'style'       => [],
		],
		'option'   => [
			'id'          => [],
			'type'        => [],
			'name'        => [],
			'placeholder' => [],
			'value'       => [],
			'multiple'    => [],
			'selected'    => [],
		],
		'label'    => [
			'for'   => [],
			'title' => [],
		],
		'span'     => [
			'class' => [],
			'title' => [],
		],
		'table'    => [
			'scope' => [],
			'title' => [],
			'class' => [],
			'role'  => [],
		],
		'tbody'    => [
			'scope' => [],
			'title' => [],
			'class' => [],
			'role'  => [],
		],
		'th'       => [
			'scope' => [],
			'title' => [],
		],
		'form'     => [
			'method'      => [],
			'type'        => [],
			'name'        => [],
			'placeholder' => [],
			'value'       => [],
			'multiple'    => [],
			'selected'    => [],
			'action'      => [],
			'enctype'     => [],
		],
		'div'      => [
			'class' => [],
			'id'    => [],
		],
		'img'      => [
			'class' => [],
			'id'    => [],
			'src'   => [],
		],
		'textarea' => [
			'class'       => [],
			'id'          => [],
			'rows'        => [],
			'cols'        => [],
			'name'        => [],
			'placeholder' => [],
			'spellcheck'  => [],
		],
		'tr'       => [],
		'td'       => [],
		'p'        => [],
		'br'       => [],
		'em'       => [],
		'strong'   => [],
		'th'       => [],
		'code'     => [],
	];

}
