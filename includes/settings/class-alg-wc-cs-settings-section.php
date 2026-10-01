<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Section Settings
 *
 * @version 1.8.0
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Conditional_Shipping\Settings
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Settings_Section' ) ) :

	/**
	 * Alg_WC_Conditional_Shipping_Settings_Section class.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	class Alg_WC_Conditional_Shipping_Settings_Section {

		/**
		 * ID.
		 *
		 * @version 1.8.0
		 * @since   1.8.0
		 *
		 * @var string The ID of the settings section.
		 */
		public $id;

		/**
		 * Description.
		 *
		 * @version 1.8.0
		 * @since   1.8.0
		 *
		 * @var string The description of the settings section.
		 */
		public $desc;

		/**
		 * Constructor.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_filter(
				'woocommerce_get_sections_alg_wc_cond_shipping',
				array( $this, 'settings_section' )
			);
			add_filter(
				'woocommerce_get_settings_alg_wc_cond_shipping_' . $this->id,
				array( $this, 'get_settings' ),
				PHP_INT_MAX
			);
		}

		/**
		 * Settings section.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $sections Array of WooCommerce settings sections.
		 *
		 * @return array Modified array of WooCommerce settings sections.
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}
	}

endif;
