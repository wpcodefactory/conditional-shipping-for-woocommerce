<?php
/*
Plugin Name: WPFactory Conditional Shipping for WooCommerce
Plugin URI: https://wpfactory.com/item/conditional-shipping-for-woocommerce/
Description: Set conditions for WooCommerce shipping methods to show up.
Version: 1.9.2
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: conditional-shipping-for-woocommerce
Domain Path: /langs
WC tested up to: 9.3
Requires Plugins: woocommerce
*/

defined( 'ABSPATH' ) || exit;

if ( 'conditional-shipping-for-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 1.7.4
	 * @since   1.5.0
	 */
	$plugin = 'conditional-shipping-for-woocommerce-pro/conditional-shipping-for-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		defined( 'ALG_WC_CONDITIONAL_SHIPPING_FILE_FREE' ) || define( 'ALG_WC_CONDITIONAL_SHIPPING_FILE_FREE', __FILE__ );
		return;
	}
}

defined( 'ALG_WC_CONDITIONAL_SHIPPING_VERSION' ) || define( 'ALG_WC_CONDITIONAL_SHIPPING_VERSION', '1.9.2' );

defined( 'ALG_WC_CONDITIONAL_SHIPPING_FILE' ) || define( 'ALG_WC_CONDITIONAL_SHIPPING_FILE', __FILE__ );

require_once( 'includes/class-alg-wc-cs.php' );

if ( ! function_exists( 'alg_wc_cond_shipping' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Conditional_Shipping to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_cond_shipping() {
		return Alg_WC_Conditional_Shipping::instance();
	}
}

add_action( 'plugins_loaded', 'alg_wc_cond_shipping' );
