<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Hooks Class
 *
 * @version 2.2.0
 * @since   1.7.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Conditional_Shipping
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Hooks' ) ) :

	/**
	 * Alg_WC_Conditional_Shipping_Hooks class.
	 *
	 * @version 2.2.0
	 * @since   1.7.0
	 */
	class Alg_WC_Conditional_Shipping_Hooks {

		/**
		 * Constructor.
		 *
		 * @version 2.2.0
		 * @since   1.7.0
		 *
		 * @todo (dev) Notices: `alg_wc_conditional_shipping_notice_hooks`: Add admin option?
		 * @todo (dev) Notices: Enable/disable globally (i.e., not per condition).
		 * @todo (dev) Make "Available shipping methods" optional (i.e., "After checkout validation" validation only).
		 * @todo (dev) Shipping descriptions (especially when we'll make "Available shipping methods" optional).
		 */
		public function __construct() {
			// Available shipping methods.
			add_filter( 'woocommerce_package_rates', array( $this, 'available_shipping_methods' ), PHP_INT_MAX, 2 );
			add_action( 'init', array( $this, 'maybe_invalidate_stored_shipping_rates' ) );

			// After checkout validation.
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validation' ), PHP_INT_MAX );

			// JS.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_update_checkout' ) );

			// Notices.
			$notice_hooks = apply_filters(
				'alg_wc_conditional_shipping_notice_hooks',
				array(
					'woocommerce_before_cart',
					'woocommerce_before_checkout_form',
				)
			);
			foreach ( $notice_hooks as $notice_hook ) {
				add_action( $notice_hook, array( $this, 'notices' ), 9 );
			}
		}

		/**
		 * Notices.
		 *
		 * @version 1.9.0
		 * @since   1.9.0
		 */
		public function notices() {
			$session_data = WC()->session->get( 'alg_wc_conditional_shipping_data', array() );

			if ( ! empty( $session_data['unset'] ) ) {

				foreach ( $session_data['unset'] as $rate_key => $rate_data ) {

					if (
					empty( $rate_data['rate'] ) ||
					empty( $rate_data['hide_condition'] )
					) {
						continue;
					}

					$notice = array_replace(
						array(
							'enabled' => 'no',
							'content' => __( '%shipping_method% is not available.', 'conditional-shipping-for-woocommerce' ), // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
						),
						get_option( 'wpjup_wc_cond_shipping_' . $rate_data['hide_condition'] . '_notice', array() )
					);

					if ( 'yes' === $notice['enabled'] ) {

						$placeholders = array(
							'%shipping_method%' => ( $rate_data['rate']->label ?? '' ),
						);

						$message = str_replace( array_keys( $placeholders ), $placeholders, $notice['content'] );

						wc_add_notice( $message, 'notice' );

					}
				}
			}
		}

		/**
		 * Checkout validation.
		 *
		 * @version 2.2.0
		 * @since   1.4.0
		 *
		 * @param array $data Data.
		 *
		 * @todo (dev) `wpjup_wc_cond_shipping_checkout_notice`: Per condition?
		 * @todo (dev) Recheck: `wc_clean()` for `$data['shipping_method']`?
		 * @todo (dev) Recheck: `$shipping_method = $shipping_methods[ $i ]`, i.e., are we sure that `$i` from `get_packages()` always matched package number in `$shipping_methods[ $i ]`?
		 * @todo (dev) Do we really need to check for `is_array( $data['shipping_method'] )`?
		 */
		public function checkout_validation( $data ) {
			if ( ! isset( $data['shipping_method'] ) ) {
				return;
			}

			$shipping_methods = wc_clean(
				is_array( $data['shipping_method'] ) ?
				$data['shipping_method'] :
				array( $data['shipping_method'] )
			);

			foreach ( WC()->shipping()->get_packages() as $i => $package ) {

				if ( ! isset( $shipping_methods[ $i ] ) ) {
					continue;
				}

				$shipping_method = $shipping_methods[ $i ];

				if ( empty( $package['rates'][ $shipping_method ] ) ) {
					continue;
				}

				$rate = $package['rates'][ $shipping_method ];

				$validate = alg_wc_cond_shipping()->core->validate_shipping_method( $rate, $package );

				if ( ! $validate['res'] ) {
					$notice = get_option(
						'wpjup_wc_cond_shipping_checkout_notice',
						__( '%shipping_method% is not available.', 'conditional-shipping-for-woocommerce' ) // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					);
					$notice = str_replace( '%shipping_method%', $rate->label, $notice );
					wc_add_notice( $notice, 'error' );
				}
			}
		}

		/**
		 * Available shipping methods.
		 *
		 * @version 1.9.0
		 * @since   1.0.0
		 *
		 * @param array $rates   Rates.
		 * @param array $package Package.
		 *
		 * @todo (feature) Conditions priority (was "Advanced Options: Filter Priority").
		 */
		public function available_shipping_methods( $rates, $package ) {
			$unset = array();
			foreach ( $rates as $rate_key => $rate ) {
				$validate = alg_wc_cond_shipping()->core->validate_shipping_method( $rate, $package );
				if ( ! $validate['res'] ) {
					$unset[ $rate_key ] = array(
						'rate'           => $rate,
						'hide_condition' => $validate['hide_condition'],
					);
					unset( $rates[ $rate_key ] );
				}
			}

			$session_data          = WC()->session->get( 'alg_wc_conditional_shipping_data', array() );
			$session_data['unset'] = $unset;
			WC()->session->set( 'alg_wc_conditional_shipping_data', $session_data );

			return $rates;
		}

		/**
		 * Maybe invalidate stored shipping rates.
		 *
		 * @version 1.7.0
		 * @since   1.2.0
		 */
		public function maybe_invalidate_stored_shipping_rates() {
			if (
				(
					alg_wc_cond_shipping()->core->is_condition_enabled( 'payment_gateways_incl' ) ||
					alg_wc_cond_shipping()->core->is_condition_enabled( 'payment_gateways_excl' )
				) &&
				class_exists( 'WC_Cache_Helper' )
			) {
				WC_Cache_Helper::get_transient_version( 'shipping', true );
			}
		}

		/**
		 * Enqueue scripts update checkout.
		 *
		 * @version 2.2.0
		 * @since   1.2.0
		 *
		 * @todo    (dev) make it optional
		 */
		public function enqueue_scripts_update_checkout() {
			if ( function_exists( 'is_checkout' ) && is_checkout() ) {

				// Get selectors.
				$selectors = array();
				if (
					alg_wc_cond_shipping()->core->is_condition_enabled( 'payment_gateways_incl' ) ||
					alg_wc_cond_shipping()->core->is_condition_enabled( 'payment_gateways_excl' )
				) {
					$selectors[] = 'payment_method';
				}
				if (
					alg_wc_cond_shipping()->core->is_condition_enabled( 'city_incl' ) ||
					alg_wc_cond_shipping()->core->is_condition_enabled( 'city_excl' )
				) {
					$selectors[] = 'shipping_city';
					$selectors[] = 'billing_city';
				}

				// Enqueue script.
				if ( ! empty( $selectors ) ) {

					$minify = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min' );
					wp_enqueue_script(
						'alg-wc-conditional-shipping-update-checkout-js',
						alg_wc_cond_shipping()->plugin_url() . '/assets/js/alg-wc-cs-update-checkout' . $minify . '.js',
						array( 'jquery' ),
						alg_wc_cond_shipping()->version,
						true
					);

					wp_localize_script(
						'alg-wc-conditional-shipping-update-checkout-js',
						'alg_wc_cs_update_checkout',
						array(
							'selectors' => 'input[name="' . implode( '"], input[name="', $selectors ) . '"]',
						)
					);

				}
			}
		}
	}

endif;

return new Alg_WC_Conditional_Shipping_Hooks();
