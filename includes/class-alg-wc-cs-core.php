<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Core Class
 *
 * @version 1.9.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Core' ) ) :

class Alg_WC_Conditional_Shipping_Core {

	/**
	 * conditions.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $conditions;

	/**
	 * condition_options.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $condition_options;

	/**
	 * options: do_add_variations.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $do_add_variations;

	/**
	 * options: validate_all_for_include.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $validate_all_for_include;

	/**
	 * options: cart_instead_of_package.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $cart_instead_of_package;

	/**
	 * customer_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $customer_id;

	/**
	 * customer_role.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $customer_role;

	/**
	 * customer_city
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	public $customer_city;

	/**
	 * logical_operator.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	public $logical_operator;

	/**
	 * do_debug.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	public $do_debug;

	/**
	 * condition_sections.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	public $condition_sections;

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 *
	 * @todo    (feature) "Shipping by shipping", e.g., show flat rate only if free shipping is not available?
	 * @todo    (dev) debug: more logging, i.e., other conditions, not only "date/time"
	 */
	function __construct() {

		$this->init();

		if ( 'yes' === get_option( 'wpjup_wc_cond_shipping_plugin_enabled', 'yes' ) ) {
			require_once( 'class-alg-wc-cs-hooks.php' );
		}

		do_action( 'alg_wc_cond_shipping_core_loaded', $this );

	}

	/**
	 * get_condition_sections.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 *
	 * @todo    (dev) merge this with `$this->conditions`
	 */
	function get_condition_sections() {
		if ( ! isset( $this->condition_sections ) ) {
			$this->condition_sections = require_once( 'alg-wc-cs-condition-sections.php' );
		}
		return $this->condition_sections;
	}

	/**
	 * init.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function init() {

		$this->conditions = array();
		foreach ( $this->get_condition_sections() as $section_id => $section ) {
			$this->conditions = array_merge( $this->conditions, $section['conditions'] );
		}

		$this->do_add_variations        = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_add_variations',   'yes' ) );
		$this->validate_all_for_include = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_validate_all',     'no' ) );
		$this->cart_instead_of_package  = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_cart_not_package', 'no' ) );
		$this->do_debug                 = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_debug',            'no' ) );
		$this->logical_operator         = strtoupper( get_option( 'alg_wc_cond_shipping_logical_operator', 'AND' ) );

	}

	/**
	 * debug.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function debug( $message ) {
		if ( $this->do_debug ) {
			$this->add_to_log( $message );
		}
	}

	/**
	 * add_to_log.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function add_to_log( $message ) {
		if ( function_exists( 'wc_get_logger' ) && ( $log = wc_get_logger() ) ) {
			$log->log( 'info', $message, array( 'source' => 'conditional-shipping-for-woocommerce' ) );
		}
	}

	/**
	 * is_condition_enabled.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    (dev) use this everywhere?
	 */
	function is_condition_enabled( $condition ) {
		return ( 'yes' === get_option( 'wpjup_wc_cond_shipping_' . $condition . '_enabled', 'no' ) );
	}

	/**
	 * validate_shipping_method.
	 *
	 * @version 1.9.0
	 * @since   1.4.0
	 *
	 * @see     https://github.com/woocommerce/woocommerce/blob/7.7.0/plugins/woocommerce/includes/class-wc-shipping-rate.php
	 */
	function validate_shipping_method( $rate, $package ) {

		$logical_operator = apply_filters( 'alg_wc_cond_shipping_logical_operator', $this->logical_operator, $rate, $package );

		switch ( $logical_operator ) {

			case 'OR':
				$do_show        = true;
				$hide_condition = false;
				foreach ( array_keys( $this->conditions ) as $condition ) {
					if (
						$this->is_condition_enabled( $condition ) &&
						( $value = $this->get_condition_value( $condition, $rate ) ) && ! empty( $value ) &&
						! $this->do_hide( $condition, $value, $package )
					) {
						return array( 'res' => true, 'hide_condition' => false );
					} elseif ( $this->is_condition_enabled( $condition ) && ! empty( $value ) ) {
						$hide_condition = $condition;
						$do_show = false;
					}
				}
				return array( 'res' => $do_show, 'hide_condition' => $hide_condition );

			default: // 'AND'
				foreach ( array_keys( $this->conditions ) as $condition ) {
					if (
						$this->is_condition_enabled( $condition ) &&
						( $value = $this->get_condition_value( $condition, $rate ) ) && ! empty( $value ) &&
						$this->do_hide( $condition, $value, $package )
					) {
						return array( 'res' => false, 'hide_condition' => $condition );
					}
				}
				return array( 'res' => true, 'hide_condition' => false );

		}

	}

	/**
	 * get_condition_value.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 */
	function get_condition_value( $condition, $rate ) {
		if ( ! isset( $this->condition_options[ $condition ] ) ) {
			$this->condition_options[ $condition ] = get_option( "wpjup_wc_cond_shipping_{$condition}_method", array() );
		}
		$method_id = apply_filters( 'alg_wc_cond_shipping_method_id', $rate->method_id, $rate );
		return ( $this->condition_options[ $condition ][ $method_id ] ?? '' );
	}

	/**
	 * is_equal.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 *
	 * @todo    (dev) better epsilon value
	 */
	function is_equal( $float1, $float2 ) {
		return ( abs( $float1 - $float2 ) < 0.000001 );
	}

	/**
	 * do_hide.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) Products: check for `isset( $item['variation_id'] )`, `isset( $item['product_id'] )` and `isset( $item['data'] )` before using it
	 * @todo    (feature) Products: as comma separated list (e.g., for WPML)
	 */
	function do_hide( $condition, $value, $package ) {
		switch ( $condition ) {

			// Order Amount
			case 'min_order_amount':
				return ( $this->check_for_cart_data( $package ) && ( $total_cart_amount = $this->get_total_cart_amount( $package ) ) < $value && ! $this->is_equal( $total_cart_amount, $value ) );
			case 'max_order_amount':
				return ( $this->check_for_cart_data( $package ) && ( $total_cart_amount = $this->get_total_cart_amount( $package ) ) > $value && ! $this->is_equal( $total_cart_amount, $value ) );

			// Cities
			case 'city_incl':
				return ( ! in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ) ) );
			case 'city_excl':
				return (   in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ) ) );

			// User Roles
			case 'user_role_incl':
				return ( ! in_array( $this->get_customer_role(), $value ) );
			case 'user_role_excl':
				return (   in_array( $this->get_customer_role(), $value ) );

			// Users
			case 'user_id_incl':
				return ( ! in_array( $this->get_customer_id(), $value ) );
			case 'user_id_excl':
				return (   in_array( $this->get_customer_id(), $value ) );

			// User Memberships
			case 'user_membership_incl':
				return ( function_exists( 'wc_memberships_is_user_active_member' ) && ! $this->check_customer_membership_plan( $value ) );
			case 'user_membership_excl':
				return ( function_exists( 'wc_memberships_is_user_active_member' ) &&   $this->check_customer_membership_plan( $value ) );

			// Payment Gateways
			case 'payment_gateways_incl':
				return ( ! in_array( $this->get_current_payment_gateway(), $value ) );
			case 'payment_gateways_excl':
				return (   in_array( $this->get_current_payment_gateway(), $value ) );

			// Products
			case 'product_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_products( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
			case 'product_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_products( $value, $this->get_items( $package ) ) );

			// Product Categories
			case 'product_cat_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat', $this->validate_all_for_include ) );
			case 'product_cat_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat' ) );

			// Product Tags
			case 'product_tag_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag', $this->validate_all_for_include ) );
			case 'product_tag_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag' ) );

			// Product Shipping Classes
			case 'product_shipping_class_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_shipping_class( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
			case 'product_shipping_class_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_shipping_class( $value, $this->get_items( $package ) ) );

			// Date/Time
			case 'date_time_incl':
				return ! $this->check_date_time( $value );
			case 'date_time_excl':
				return   $this->check_date_time( $value );

		}
	}

	/**
	 * check_date_time.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    (dev) debug: shipping method title?
	 * @todo    (dev) optionally "require all" for `date_time_incl`
	 */
	function check_date_time( $value ) {
		$current_time = current_time( 'timestamp' );
		$value        = array_map( 'trim', explode( ';', $value ) );
		foreach ( $value as $_value ) {
			$_value = array_map( 'trim', explode( '-', $_value ) );
			if ( 2 == count( $_value ) ) {
				$start_time  = strtotime( $_value[0], $current_time );
				$end_time    = strtotime( $_value[1], $current_time );
				$is_in_range = ( $current_time >= $start_time && $current_time <= $end_time );
				$this->debug( sprintf( __( 'Date/time range: from %s to %s; current time: %s; result: %s', 'conditional-shipping-for-woocommerce' ),
					date( 'Y-m-d H:i:s', $start_time ), date( 'Y-m-d H:i:s', $end_time ), date( 'Y-m-d H:i:s', $current_time ), ( $is_in_range ? 'yes' : 'no' ) ) );
				if ( $is_in_range ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * get_current_payment_gateway.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function get_current_payment_gateway() {

		if ( isset( WC()->session->chosen_payment_method ) ) {

			// Session
			return WC()->session->chosen_payment_method;

		} elseif ( ! empty( $_REQUEST['payment_method'] ) ) {

			// Submitted data
			return sanitize_key( $_REQUEST['payment_method'] );

		} elseif ( '' != ( $default_gateway = get_option( 'woocommerce_default_gateway' ) ) ) {

			// Default gateway
			return $default_gateway;

		} else {

			// First available gateway
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			if ( ! empty( $available_gateways ) ) {
				return current( array_keys( $available_gateways ) );
			}

		}

		// No current gateway
		return false;

	}

	/**
	 * check_products.
	 *
	 * @version 1.9.2
	 * @since   1.0.0
	 *
	 * @todo    (dev) if needed, prepare `$products_variations` earlier (and only once)
	 */
	function check_products( $product_ids, $items, $validate_all_for_include = false ) {

		if ( $this->do_add_variations ) {
			$products_variations = array();
			foreach ( $product_ids as $_product_id ) {
				if ( ! ( $_product = wc_get_product( $_product_id ) ) ) {
					continue;
				}
				if ( $_product->is_type( 'variable' ) ) {
					$products_variations = array_merge( $products_variations, $_product->get_children() );
				} else {
					$products_variations[] = $_product_id;
				}
			}
			$product_ids = array_unique( $products_variations );
		}

		foreach ( $items as $item ) {
			$_product_id = ( $this->do_add_variations && 0 != $item['variation_id'] ? $item['variation_id'] : $item['product_id'] );
			if ( $validate_all_for_include && ! in_array( $_product_id, $product_ids ) ) {
				return false;
			} elseif ( ! $validate_all_for_include && in_array( $_product_id, $product_ids ) ) {
				return true;
			}
		}

		return $validate_all_for_include;
	}

	/**
	 * check_taxonomy.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function check_taxonomy( $product_ids, $items, $taxonomy, $validate_all_for_include = false ) {

		foreach ( $items as $item ) {

			$product_terms = get_the_terms( $item['product_id'], $taxonomy );
			if ( empty( $product_terms ) ) {
				if ( $validate_all_for_include ) {
					return false;
				} else {
					continue;
				}
			}

			foreach( $product_terms as $product_term ) {
				if ( $validate_all_for_include && ! in_array( $product_term->term_id, $product_ids ) ) {
					return false;
				} elseif ( ! $validate_all_for_include && in_array( $product_term->term_id, $product_ids ) ) {
					return true;
				}
			}

		}

		return $validate_all_for_include;
	}

	/**
	 * check_shipping_class.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) check for `if ( is_object( $product ) && is_callable( array( $product, 'get_shipping_class_id' ) ) ) { ... }`
	 * @todo    (feature) product variations?
	 */
	function check_shipping_class( $product_ids, $items, $validate_all_for_include = false ) {
		foreach ( $items as $item ) {
			$product = $item['data'];
			$product_shipping_class = $product->get_shipping_class_id();
			if ( $validate_all_for_include && ! in_array( $product_shipping_class, $product_ids ) ) {
				return false;
			} elseif ( ! $validate_all_for_include && in_array( $product_shipping_class, $product_ids ) ) {
				return true;
			}
		}
		return $validate_all_for_include;
	}

	/**
	 * check_for_cart_data.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function check_for_cart_data( $package ) {
		return ( $this->cart_instead_of_package ?
			( isset( WC()->cart ) && ! WC()->cart->is_empty() ) :
			! empty( $package['contents'] )
		);
	}

	/**
	 * get_items.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function get_items( $package ) {
		return ( $this->cart_instead_of_package ?
			WC()->cart->get_cart() :
			$package['contents']
		);
	}

	/**
	 * get_total_cart_amount.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) use subtotal?
	 * @todo    (feature) add option to include or exclude taxes when calculating cart total
	 */
	function get_total_cart_amount( $package ) {
		return ( $this->cart_instead_of_package ?
			WC()->cart->cart_contents_total :
			$package['contents_cost']
		);
	}

	/**
	 * check_customer_membership_plan.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) add "MemberPress" plugin support
	 */
	function check_customer_membership_plan( $membership_plans ) {
		foreach ( $membership_plans as $membership_plan ) {
			if ( wc_memberships_is_user_active_member( $this->get_customer_id(), $membership_plan ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * get_customer_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_customer_id() {
		if ( ! isset( $this->customer_id ) ) {
			$this->customer_id = get_current_user_id();
		}
		return $this->customer_id;
	}

	/**
	 * get_customer_role.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_customer_role() {
		if ( ! isset( $this->customer_role ) ) {
			$current_user = wp_get_current_user();
			$first_role   = ( isset( $current_user->roles ) && is_array( $current_user->roles ) && ! empty( $current_user->roles ) ? reset( $current_user->roles ) : 'guest' );
			$this->customer_role = ( '' != $first_role ? $first_role : 'guest' );
		}
		return $this->customer_role;
	}

	/**
	 * get_customer_city.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) billing city, session, base city: make it optional || remove?
	 * @todo    (dev) do we need `'' !== $_REQUEST[ $key ]` and `'' !== $customer[ $key ]` (i.e., '' vs `get_base_city`)?
	 */
	function get_customer_city() {

		if ( ! isset( $this->customer_city ) ) {
			$source_for_debug = '';

			// Try to get it from `$_REQUEST`
			$keys = array( 's_city', 'shipping_city', 'city', 'billing_city' );
			foreach ( $keys as $key ) {
				if ( isset( $_REQUEST[ $key ] ) && '' !== $_REQUEST[ $key ] ) {
					$this->customer_city = sanitize_text_field( $_REQUEST[ $key ] );
					$source_for_debug = 'REQUEST[' . $key . ']';
					break;
				}
			}

			// Try to get it from session
			if ( ! isset( $this->customer_city ) && isset( WC()->session ) && ( $customer = WC()->session->get( 'customer' ) ) ) {
				$keys = array( 'shipping_city', 'city' );
				foreach ( $keys as $key ) {
					if ( isset( $customer[ $key ] ) && '' !== $customer[ $key ] ) {
						$this->customer_city = sanitize_text_field( $customer[ $key ] );
						$source_for_debug = 'SESSION[' . $key . ']';
						break;
					}
				}
			}

			// Get it from `get_base_city()` (fallback)
			if ( ! isset( $this->customer_city ) ) {
				$this->customer_city = WC()->countries->get_base_city();
				$source_for_debug = 'get_base_city';
			}

			// To upper
			$this->customer_city = strtoupper( $this->customer_city );

			// Debug
			$this->debug( sprintf( __( 'Customer city: %s', 'conditional-shipping-for-woocommerce' ), $this->customer_city . ' (' . $source_for_debug . ')' ) );

		}

		return $this->customer_city;
	}

}

endif;

return new Alg_WC_Conditional_Shipping_Core();
