<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Core Class
 *
 * @version 1.6.0
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
	 * @since   1.0.0
	 */
	public $conditions;

	/**
	 * condition_options.
	 *
	 * @since   1.0.0
	 */
	public $condition_options;

	/**
	 * options: do_add_variations.
	 *
	 * @since   1.0.0
	 */
	public $do_add_variations;

	/**
	 * options: validate_all_for_include.
	 *
	 * @since   1.0.0
	 */
	public $validate_all_for_include;

	/**
	 * options: cart_instead_of_package.
	 *
	 * @since   1.0.0
	 */
	public $cart_instead_of_package;

	/**
	 * is_cart_data.
	 *
	 * @since   1.0.0
	 */
	public $is_cart_data;

	/**
	 * cart_or_package_items.
	 *
	 * @since   1.0.0
	 */
	public $cart_or_package_items;

	/**
	 * customer_id.
	 *
	 * @since   1.0.0
	 */
	public $customer_id;

	/**
	 * customer_role.
	 *
	 * @since   1.0.0
	 */
	public $customer_role;

	/**
	 * customer_city
	 *
	 * @since   1.0.0
	 */
	public $customer_city;

	/**
	 * total_in_cart.
	 *
	 * @since   1.0.0
	 */
	public $total_in_cart;

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 *
	 * @todo    [next] [!] (dev) debug: more logging, i.e. other conditions, not only "date/time"
	 * @todo    [next] (dev) cart notice (i.e. similar to "After checkout validation")
	 * @todo    [next] (dev) make "Available shipping methods" optional (i.e. "After checkout validation" validation only)
	 * @todo    [next] (dev) shipping descriptions (especially when we'll make "Available shipping methods" optional)
	 */
	function __construct() {
		$this->init();
		if ( 'yes' === get_option( 'wpjup_wc_cond_shipping_plugin_enabled', 'yes' ) ) {
			// Available shipping methods
			add_filter( 'woocommerce_package_rates', array( $this, 'available_shipping_methods' ), PHP_INT_MAX, 2 );
			if ( $this->is_condition_enabled( 'payment_gateways_incl' ) || $this->is_condition_enabled( 'payment_gateways_excl' ) ) {
				add_action( 'init', array( $this, 'invalidate_stored_shipping_rates' ) );
			}
			// After checkout validation
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'checkout_validation' ), PHP_INT_MAX, 2 );
			// JS
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_update_checkout' ) );
		}
		do_action( 'alg_wc_cond_shipping_core_loaded', $this );
	}

	/**
	 * get_condition_sections.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 *
	 * @todo    [next] (dev) merge this with `$this->conditions`
	 */
	function get_condition_sections() {
		return array(
			'order_amount' => array(
				'title'      => __( 'Order Amount', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'min_order_amount'               => __( 'Minimum Order Amount', 'conditional-shipping-for-woocommerce' ),
					'max_order_amount'               => __( 'Maximum Order Amount', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'city' => array(
				'title'      => __( 'Cities', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'city_incl'                      => __( 'Require Cities', 'conditional-shipping-for-woocommerce' ),
					'city_excl'                      => __( 'Exclude Cities', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'user_role' => array(
				'title'      => __( 'User Roles', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'user_role_incl'                 => __( 'Require User Roles', 'conditional-shipping-for-woocommerce' ),
					'user_role_excl'                 => __( 'Exclude User Roles', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'user_id' => array(
				'title'      => __( 'Users', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'user_id_incl'                   => __( 'Require User IDs', 'conditional-shipping-for-woocommerce' ),
					'user_id_excl'                   => __( 'Exclude User IDs', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'user_membership' => array(
				'title'      => __( 'User Memberships', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'user_membership_incl'           => __( 'Require User Membership Plans', 'conditional-shipping-for-woocommerce' ),
					'user_membership_excl'           => __( 'Exclude User Membership Plans', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'payment_gateways' => array(
				'title'      => __( 'Payment Gateways', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'payment_gateways_incl'          => __( 'Require Payment Gateways', 'conditional-shipping-for-woocommerce' ),
					'payment_gateways_excl'          => __( 'Exclude Payment Gateways', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'product' => array(
				'title'      => __( 'Products', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'product_incl'                   => __( 'Require Products', 'conditional-shipping-for-woocommerce' ),
					'product_excl'                   => __( 'Exclude Products', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'product_cat' => array(
				'title'      => __( 'Product Categories', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'product_cat_incl'               => __( 'Require Product Categories', 'conditional-shipping-for-woocommerce' ),
					'product_cat_excl'               => __( 'Exclude Product Categories', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'product_tag' => array(
				'title'      => __( 'Product Tags', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'product_tag_incl'               => __( 'Require Product Tags', 'conditional-shipping-for-woocommerce' ),
					'product_tag_excl'               => __( 'Exclude Product Tags', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'product_shipping_class' => array(
				'title'      => __( 'Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'product_shipping_class_incl'    => __( 'Require Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
					'product_shipping_class_excl'    => __( 'Exclude Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
				),
			),
			'date_time' => array(
				'title'      => __( 'Date/Time', 'conditional-shipping-for-woocommerce' ),
				'conditions' => array(
					'date_time_incl'                 => __( 'Require Date/Time', 'conditional-shipping-for-woocommerce' ),
					'date_time_excl'                 => __( 'Exclude Date/Time', 'conditional-shipping-for-woocommerce' ),
				),
			),
		);
	}

	/**
	 * init.
	 *
	 * @version 1.5.0
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
	 * invalidate_stored_shipping_rates.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function invalidate_stored_shipping_rates() {
		if ( class_exists( 'WC_Cache_Helper' ) ) {
			WC_Cache_Helper::get_transient_version( 'shipping', true );
		}
	}

	/**
	 * enqueue_scripts_update_checkout.
	 *
	 * @version 1.6.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (dev) make this optional
	 */
	function enqueue_scripts_update_checkout() {
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			// Get selectors
			$selectors = array();
			if ( $this->is_condition_enabled( 'payment_gateways_incl' ) || $this->is_condition_enabled( 'payment_gateways_excl' ) ) {
				$selectors[] = 'payment_method';
			}
			if ( $this->is_condition_enabled( 'city_incl' ) || $this->is_condition_enabled( 'city_excl' ) ) {
				$selectors[] = 'shipping_city';
				$selectors[] = 'billing_city';
			}
			// Enqueue script
			if ( ! empty( $selectors ) ) {
				wp_enqueue_script( 'alg-wc-conditional-shipping-update-checkout-js',
					alg_wc_cond_shipping()->plugin_url() . '/includes/js/alg-wc-cs-update-checkout' . ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min' ) . '.js',
					array( 'jquery' ),
					alg_wc_cond_shipping()->version,
					true
				);
				wp_localize_script( 'alg-wc-conditional-shipping-update-checkout-js',
					'alg_wc_cs_update_checkout',
					array(
						'selectors' => 'input[name="' . implode( '"], input[name="', $selectors ) . '"]',
					)
				);
			}
		}
	}

	/**
	 * is_condition_enabled.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    [next] (dev) use this everywhere
	 */
	function is_condition_enabled( $condition ) {
		return ( 'yes' === get_option( 'wpjup_wc_cond_shipping_' . $condition . '_enabled', 'no' ) );
	}

	/**
	 * validate_shipping_method.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    [next] (dev) use it in `available_shipping_methods()`
	 */
	function validate_shipping_method( $rate, $package ) {
		foreach ( array_keys( $this->conditions ) as $condition ) {
			if ( $this->is_condition_enabled( $condition ) && $this->do_hide( $condition, $this->get_condition_value( $condition, $rate ), $package ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * checkout_validation.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 *
	 * @todo    [next] (dev) `wpjup_wc_cond_shipping_checkout_notice`: per condition
	 * @todo    [maybe] (dev) recheck: `wc_clean()` for `$data['shipping_method']`?
	 * @todo    [maybe] (dev) recheck: `$shipping_method = $shipping_methods[ $i ]`, i.e. are we sure that `$i` from `get_packages()` always matched package number in `$shipping_methods[ $i ]`?
	 * @todo    [maybe] (dev) do we really need to check for `is_array( $data['shipping_method'] )`?
	 */
	function checkout_validation( $data, $errors ) {
		if ( isset( $data['shipping_method'] ) ) {
			$shipping_methods = wc_clean( is_array( $data['shipping_method'] ) ? $data['shipping_method'] : array( $data['shipping_method'] ) );
			foreach ( WC()->shipping()->get_packages() as $i => $package ) {
				if ( isset( $shipping_methods[ $i ] ) ) {
					$shipping_method = $shipping_methods[ $i ];
					if ( ! empty( $package['rates'][ $shipping_method ] ) ) {
						$rate = $package['rates'][ $shipping_method ];
						if ( ! $this->validate_shipping_method( $rate, $package ) ) {
							$message = str_replace( '%shipping_method%', $rate->label,
								get_option( 'wpjup_wc_cond_shipping_checkout_notice', __( '%shipping_method% is not available.', 'conditional-shipping-for-woocommerce' ) ) );
							wc_add_notice( $message, 'error' );
						}
					}
				}
			}
		}
	}

	/**
	 * available_shipping_methods.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 *
	 * @todo    [maybe] (feature) add option to add customer messages on cart and checkout pages, if some shipping method is not available (`wc_add_notice()`)
	 * @todo    [maybe] (feature) conditions priority (was "Advanced Options: Filter Priority")
	 */
	function available_shipping_methods( $rates, $package ) {
		foreach ( $rates as $rate_key => $rate ) {
			foreach ( array_keys( $this->conditions ) as $condition ) {
				if ( $this->is_condition_enabled( $condition ) && $this->do_hide( $condition, $this->get_condition_value( $condition, $rate ), $package ) ) {
					unset( $rates[ $rate_key ] );
					break;
				}
			}
		}
		return $rates;
	}

	/**
	 * get_condition_value.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function get_condition_value( $condition, $rate ) {
		if ( ! isset( $this->condition_options[ $condition ] ) ) {
			$this->condition_options[ $condition ] = get_option( "wpjup_wc_cond_shipping_{$condition}_method", array() );
		}
		$method_id = apply_filters( 'alg_wc_cond_shipping_method_id', $rate->method_id, $rate );
		return ( isset( $this->condition_options[ $condition ][ $method_id ] ) ? $this->condition_options[ $condition ][ $method_id ] : '' );
	}

	/**
	 * is_equal.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 *
	 * @todo    [maybe] (dev) better epsilon value
	 */
	function is_equal( $float1, $float2 ) {
		return ( abs( $float1 - $float2 ) < 0.000001 );
	}

	/**
	 * do_hide.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @todo    [later] (dev) Products: check for `isset( $item['variation_id'] )`, `isset( $item['product_id'] )` and `isset( $item['data'] )` before using it
	 * @todo    [later] (feature) Products: as comma separated list (e.g. for WPML)
	 */
	function do_hide( $condition, $value, $package ) {
		if ( empty( $value ) ) {
			return false;
		}
		switch ( $condition ) {
			case 'min_order_amount':
				return ( $this->check_for_cart_data( $package ) && ( $total_cart_amount = $this->get_total_cart_amount( $package ) ) < $value && ! $this->is_equal( $total_cart_amount, $value ) );
			case 'max_order_amount':
				return ( $this->check_for_cart_data( $package ) && ( $total_cart_amount = $this->get_total_cart_amount( $package ) ) > $value && ! $this->is_equal( $total_cart_amount, $value ) );
			case 'city_incl':
				return ( ! in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ) ) );
			case 'city_excl':
				return (   in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ) ) );
			case 'user_role_incl':
				return ( ! in_array( $this->get_customer_role(), $value ) );
			case 'user_role_excl':
				return (   in_array( $this->get_customer_role(), $value ) );
			case 'user_id_incl':
				return ( ! in_array( $this->get_customer_id(), $value ) );
			case 'user_id_excl':
				return (   in_array( $this->get_customer_id(), $value ) );
			case 'user_membership_incl':
				return ( function_exists( 'wc_memberships_is_user_active_member' ) && ! $this->check_customer_membership_plan( $value ) );
			case 'user_membership_excl':
				return ( function_exists( 'wc_memberships_is_user_active_member' ) &&   $this->check_customer_membership_plan( $value ) );
			case 'payment_gateways_incl':
				return ( ! in_array( $this->get_current_payment_gateway(), $value ) );
			case 'payment_gateways_excl':
				return (   in_array( $this->get_current_payment_gateway(), $value ) );
			case 'product_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_products( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
			case 'product_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_products( $value, $this->get_items( $package ) ) );
			case 'product_cat_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat', $this->validate_all_for_include ) );
			case 'product_cat_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat' ) );
			case 'product_tag_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag', $this->validate_all_for_include ) );
			case 'product_tag_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag' ) );
			case 'product_shipping_class_incl':
				return ( $this->check_for_cart_data( $package ) && ! $this->check_shipping_class( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
			case 'product_shipping_class_excl':
				return ( $this->check_for_cart_data( $package ) &&   $this->check_shipping_class( $value, $this->get_items( $package ) ) );
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
	 * @todo    [now] (dev) debug: shipping method title?
	 * @todo    [next] [!] (dev) optionally "require all" for `date_time_incl`
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
			return WC()->session->chosen_payment_method;
		} elseif ( ! empty( $_REQUEST['payment_method'] ) ) {
			return sanitize_key( $_REQUEST['payment_method'] );
		} elseif ( '' != ( $default_gateway = get_option( 'woocommerce_default_gateway' ) ) ) {
			return $default_gateway;
		} else {
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
			if ( ! empty( $available_gateways ) ) {
				return current( array_keys( $available_gateways ) );
			}
		}
		return false;
	}

	/**
	 * check_products.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    [maybe] (dev) if needed, prepare `$products_variations` earlier (and only once)
	 */
	function check_products( $product_ids, $items, $validate_all_for_include = false ) {
		if ( $this->do_add_variations ) {
			$products_variations = array();
			foreach ( $product_ids as $_product_id ) {
				$_product = wc_get_product( $_product_id );
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
	 * @todo    [later] (dev) check for `if ( is_object( $product ) && is_callable( array( $product, 'get_shipping_class_id' ) ) ) { ... }`
	 * @todo    [later] (feature) product variations?
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
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function check_for_cart_data( $package ) {
		if ( ! isset( $this->is_cart_data ) ) {
			$this->is_cart_data = true;
			if ( $this->cart_instead_of_package ) {
				if ( ! isset( WC()->cart ) || WC()->cart->is_empty() ) {
					$this->is_cart_data = false;
				}
			} else {
				if ( ! isset( $package['contents'] ) ) {
					$this->is_cart_data = false;
				}
			}
		}
		return $this->is_cart_data;
	}

	/**
	 * get_items.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_items( $package ) {
		if ( ! isset( $this->cart_or_package_items ) ) {
			$this->cart_or_package_items = ( $this->cart_instead_of_package ? WC()->cart->get_cart() : $package['contents'] );
		}
		return $this->cart_or_package_items;
	}

	/**
	 * check_customer_membership_plan.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    [later] (dev) add "MemberPress" plugin support
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
	 * @todo    [next] (dev) billing city, session, base city: make it optional || remove?
	 * @todo    [maybe] (dev) do we need `'' !== $_REQUEST[ $key ]` and `'' !== $customer[ $key ]` (i.e. '' vs `get_base_city`)?
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

	/**
	 * get_total_cart_amount.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    [next] (dev) use subtotal?
	 * @todo    [maybe] (feature) add option to include or exclude taxes when calculating cart total
	 */
	function get_total_cart_amount( $package ) {
		if ( ! isset( $this->total_in_cart ) ) {
			$this->total_in_cart = ( $this->cart_instead_of_package ? WC()->cart->cart_contents_total : $package['contents_cost'] );
		}
		return $this->total_in_cart;
	}

}

endif;

return new Alg_WC_Conditional_Shipping_Core();
