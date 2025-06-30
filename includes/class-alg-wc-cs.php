<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Main Class
 *
 * @version 2.1.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping' ) ) :

final class Alg_WC_Conditional_Shipping {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = ALG_WC_CONDITIONAL_SHIPPING_VERSION;

	/**
	 * core.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	public $core;

	/**
	 * @var   Alg_WC_Conditional_Shipping The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Conditional_Shipping Instance.
	 *
	 * Ensures only one instance of Alg_WC_Conditional_Shipping is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @static
	 * @return  Alg_WC_Conditional_Shipping - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Conditional_Shipping Constructor.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @access  public
	 */
	function __construct() {

		// Check for active WooCommerce plugin
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Load libs
		if ( is_admin() ) {
			require_once plugin_dir_path( ALG_WC_CONDITIONAL_SHIPPING_FILE ) . 'vendor/autoload.php';
		}

		// Set up localisation
		add_action( 'init', array( $this, 'localize' ) );

		// Declare compatibility with custom order tables for WooCommerce
		add_action( 'before_woocommerce_init', array( $this, 'wc_declare_compatibility' ) );

		// Pro
		if ( 'conditional-shipping-for-woocommerce-pro.php' === basename( ALG_WC_CONDITIONAL_SHIPPING_FILE ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'pro/class-alg-wc-cs-pro.php';
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}

	}

	/**
	 * localize.
	 *
	 * @version 1.5.0
	 * @since   1.4.0
	 */
	function localize() {
		load_plugin_textdomain(
			'conditional-shipping-for-woocommerce',
			false,
			dirname( plugin_basename( ALG_WC_CONDITIONAL_SHIPPING_FILE ) ) . '/langs/'
		);
	}

	/**
	 * wc_declare_compatibility.
	 *
	 * @version 1.7.4
	 * @since   1.7.4
	 *
	 * @see     https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/
	 */
	function wc_declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			$files = (
				defined( 'ALG_WC_CONDITIONAL_SHIPPING_FILE_FREE' ) ?
				array( ALG_WC_CONDITIONAL_SHIPPING_FILE, ALG_WC_CONDITIONAL_SHIPPING_FILE_FREE ) :
				array( ALG_WC_CONDITIONAL_SHIPPING_FILE )
			);
			foreach ( $files as $file ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
					'custom_order_tables',
					$file,
					true
				);
			}
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-cs-core.php';
	}

	/**
	 * admin.
	 *
	 * @version 2.1.0
	 * @since   1.1.0
	 */
	function admin() {

		// Action links
		add_filter(
			'plugin_action_links_' . plugin_basename( ALG_WC_CONDITIONAL_SHIPPING_FILE ),
			array( $this, 'action_links' )
		);

		// "Recommendations" page
		add_action( 'init', array( $this, 'add_cross_selling_library' ) );

		// WC Settings tab as WPFactory submenu item
		add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

		// Settings
		add_filter(
			'woocommerce_get_settings_pages',
			array( $this, 'add_woocommerce_settings_tab' )
		);

		// Version update
		if ( get_option( 'wpjup_wc_cond_shipping_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_update' ) );
		}

	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 *
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();

		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cond_shipping' ) . '">' .
			__( 'Settings', 'conditional-shipping-for-woocommerce' ) .
		'</a>';

		if ( 'conditional-shipping-for-woocommerce.php' === basename( ALG_WC_CONDITIONAL_SHIPPING_FILE ) ) {
			$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/conditional-shipping-for-woocommerce/">' .
				__( 'Go Pro', 'conditional-shipping-for-woocommerce' ) .
			'</a>';
		}

		return array_merge( $custom_links, $links );
	}

	/**
	 * add_cross_selling_library.
	 *
	 * @version 2.0.0
	 * @since   2.0.0
	 */
	function add_cross_selling_library() {

		if ( ! class_exists( '\WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling' ) ) {
			return;
		}

		$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
		$cross_selling->setup( array( 'plugin_file_path' => ALG_WC_CONDITIONAL_SHIPPING_FILE ) );
		$cross_selling->init();

	}

	/**
	 * move_wc_settings_tab_to_wpfactory_menu.
	 *
	 * @version 2.1.0
	 * @since   2.0.0
	 */
	function move_wc_settings_tab_to_wpfactory_menu() {

		if ( ! class_exists( '\WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu' ) ) {
			return;
		}

		$wpfactory_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();

		if ( ! method_exists( $wpfactory_admin_menu, 'move_wc_settings_tab_to_wpfactory_menu' ) ) {
			return;
		}

		$wpfactory_admin_menu->move_wc_settings_tab_to_wpfactory_menu( array(
			'wc_settings_tab_id' => 'alg_wc_cond_shipping',
			'menu_title'         => __( 'Conditional Shipping', 'conditional-shipping-for-woocommerce' ),
			'page_title'         => __( 'Conditional Shipping for WooCommerce: Restrict Shipping Options by Anything', 'conditional-shipping-for-woocommerce' ),
			'plugin_icon'        => array(
				'get_url_method'    => 'wporg_plugins_api',
				'wporg_plugin_slug' => 'wpfactory-conditional-shipping-for-woocommerce',
			),
		) );

	}

	/**
	 * Add Conditional Shipping settings tab to WooCommerce settings.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once plugin_dir_path( __FILE__ ) . 'settings/class-alg-wc-cs-settings.php';
		return $settings;
	}

	/**
	 * version_update.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function version_update() {
		update_option( 'wpjup_wc_cond_shipping_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( ALG_WC_CONDITIONAL_SHIPPING_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( ALG_WC_CONDITIONAL_SHIPPING_FILE ) );
	}

}

endif;
