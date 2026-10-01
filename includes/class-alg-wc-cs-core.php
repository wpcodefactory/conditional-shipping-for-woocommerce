<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Core Class
 *
 * @version 2.2.0
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Conditional_Shipping
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Core' ) ) :

	/**
	 * Alg_WC_Conditional_Shipping_Core class.
	 *
	 * @version 2.2.0
	 * @since   1.0.0
	 */
	class Alg_WC_Conditional_Shipping_Core {

		/**
		 * Conditions.
		 *
		 * @version 2.1.2
		 * @since   1.0.0
		 *
		 * @var array
		 */
		public $conditions = array();

		/**
		 * Condition options.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var array
		 */
		public $condition_options;

		/**
		 * Options: do add variations.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var bool
		 */
		public $do_add_variations;

		/**
		 * Options: validate all for include.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var bool
		 */
		public $validate_all_for_include;

		/**
		 * Options: cart instead of package.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var bool
		 */
		public $cart_instead_of_package;

		/**
		 * Customer ID.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var int
		 */
		public $customer_id;

		/**
		 * Customer role.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var string
		 */
		public $customer_role;

		/**
		 * Customer city.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @var string
		 */
		public $customer_city;

		/**
		 * Logical operator.
		 *
		 * @version 1.7.0
		 * @since   1.7.0
		 *
		 * @var string
		 */
		public $logical_operator;

		/**
		 * Do debug.
		 *
		 * @version 1.7.0
		 * @since   1.7.0
		 *
		 * @var bool
		 */
		public $do_debug;

		/**
		 * Condition sections.
		 *
		 * @version 1.7.0
		 * @since   1.7.0
		 *
		 * @var array
		 */
		public $condition_sections;

		/**
		 * Constructor.
		 *
		 * @version 2.1.0
		 * @since   1.0.0
		 *
		 * @todo (feature) "Shipping by shipping", e.g., show flat rate only if free shipping is not available?
		 * @todo (dev) Debug: More logging, i.e., other conditions, not only "date/time".
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );

			if ( 'yes' === get_option( 'wpjup_wc_cond_shipping_plugin_enabled', 'yes' ) ) {
				require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-cs-hooks.php';
			}

			do_action( 'alg_wc_cond_shipping_core_loaded', $this );
		}

		/**
		 * Get condition sections.
		 *
		 * @version 2.1.0
		 * @since   1.5.0
		 *
		 * @todo (dev) Merge this with `$this->conditions`.
		 */
		public function get_condition_sections() {
			if ( ! isset( $this->condition_sections ) ) {
				$this->condition_sections = require_once plugin_dir_path( __FILE__ ) . 'alg-wc-cs-condition-sections.php';
			}
			return $this->condition_sections;
		}

		/**
		 * Init.
		 *
		 * @version 1.7.0
		 * @since   1.0.0
		 */
		public function init() {
			$this->conditions = array();
			foreach ( $this->get_condition_sections() as $section_id => $section ) {
				$this->conditions = array_merge( $this->conditions, $section['conditions'] );
			}

			$this->do_add_variations        = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_add_variations', 'yes' ) );
			$this->validate_all_for_include = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_validate_all', 'no' ) );
			$this->cart_instead_of_package  = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_cart_not_package', 'no' ) );
			$this->do_debug                 = ( 'yes' === get_option( 'wpjup_wc_cond_shipping_debug', 'no' ) );
			$this->logical_operator         = strtoupper( get_option( 'alg_wc_cond_shipping_logical_operator', 'AND' ) );
		}

		/**
		 * Debug.
		 *
		 * @version 1.4.0
		 * @since   1.4.0
		 *
		 * @param string $message Message.
		 */
		public function debug( $message ) {
			if ( $this->do_debug ) {
				$this->add_to_log( $message );
			}
		}

		/**
		 * Add to log.
		 *
		 * @version 2.2.0
		 * @since   1.4.0
		 *
		 * @param string $message Message.
		 */
		public function add_to_log( $message ) {
			if ( ! function_exists( 'wc_get_logger' ) ) {
				return;
			}
			$log = wc_get_logger();
			if ( $log ) {
				$log->log(
					'info',
					$message,
					array( 'source' => 'conditional-shipping-for-woocommerce' )
				);
			}
		}

		/**
		 * Is condition enabled.
		 *
		 * @version 1.2.0
		 * @since   1.2.0
		 *
		 * @param string $condition Condition.
		 *
		 * @return bool
		 *
		 * @todo (dev) Use this everywhere?
		 */
		public function is_condition_enabled( $condition ) {
			return ( 'yes' === get_option( 'wpjup_wc_cond_shipping_' . $condition . '_enabled', 'no' ) );
		}

		/**
		 * Validate shipping method.
		 *
		 * @version 1.9.0
		 * @since   1.4.0
		 *
		 * @param object $rate Rate.
		 * @param array  $package Package.
		 *
		 * @see https://github.com/woocommerce/woocommerce/blob/7.7.0/plugins/woocommerce/includes/class-wc-shipping-rate.php
		 */
		public function validate_shipping_method( $rate, $package ) {
			$logical_operator = apply_filters( 'alg_wc_cond_shipping_logical_operator', $this->logical_operator, $rate, $package );

			switch ( $logical_operator ) {

				case 'OR':
					$do_show        = true;
					$hide_condition = false;
					foreach ( array_keys( $this->conditions ) as $condition ) {
						if (
							$this->is_condition_enabled( $condition ) &&
							( $value = $this->get_condition_value( $condition, $rate ) ) && // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
							! empty( $value ) &&
							! $this->do_hide( $condition, $value, $package )
						) {
							return array(
								'res'            => true,
								'hide_condition' => false,
							);
						} elseif ( $this->is_condition_enabled( $condition ) && ! empty( $value ) ) {
							$hide_condition = $condition;
							$do_show        = false;
						}
					}
					return array(
						'res'            => $do_show,
						'hide_condition' => $hide_condition,
					);

				default: // 'AND'
					foreach ( array_keys( $this->conditions ) as $condition ) {
						if (
							$this->is_condition_enabled( $condition ) &&
							( $value = $this->get_condition_value( $condition, $rate ) ) && // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
							! empty( $value ) &&
							$this->do_hide( $condition, $value, $package )
						) {
							return array(
								'res'            => false,
								'hide_condition' => $condition,
							);
						}
					}
					return array(
						'res'            => true,
						'hide_condition' => false,
					);

			}
		}

		/**
		 * Get condition value.
		 *
		 * @version 1.9.0
		 * @since   1.0.0
		 *
		 * @param string $condition Condition.
		 * @param object $rate Rate.
		 */
		public function get_condition_value( $condition, $rate ) {
			if ( ! isset( $this->condition_options[ $condition ] ) ) {
				$this->condition_options[ $condition ] = get_option( "wpjup_wc_cond_shipping_{$condition}_method", array() );
			}
			$method_id = apply_filters( 'alg_wc_cond_shipping_method_id', $rate->method_id, $rate );
			return ( $this->condition_options[ $condition ][ $method_id ] ?? '' );
		}

		/**
		 * Is equal.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 *
		 * @param float $float1 First float.
		 * @param float $float2 Second float.
		 *
		 * @todo (dev) Better epsilon value.
		 */
		public function is_equal( $float1, $float2 ) {
			return ( abs( $float1 - $float2 ) < 0.000001 );
		}

		/**
		 * Do hide.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 *
		 * @param string $condition Condition.
		 * @param mixed  $value     Value.
		 * @param array  $package   Package.
		 *
		 * @todo (dev) Products: Check for `isset( $item['variation_id'] )`, `isset( $item['product_id'] )` and `isset( $item['data'] )` before using it.
		 * @todo (feature) Products: As comma separated list (e.g., for WPML).
		 */
		public function do_hide( $condition, $value, $package ) {
			switch ( $condition ) {

				// Order Amount.
				case 'min_order_amount':
					return (
						$this->check_for_cart_data( $package ) &&
						( $total_cart_amount = $this->get_total_cart_amount( $package ) ) < $value && // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
						! $this->is_equal( $total_cart_amount, $value )
					);
				case 'max_order_amount':
					return (
						$this->check_for_cart_data( $package ) &&
						( $total_cart_amount = $this->get_total_cart_amount( $package ) ) > $value && // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
						! $this->is_equal( $total_cart_amount, $value )
					);

				// Cities.
				case 'city_incl':
					return ( ! in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ), true ) );
				case 'city_excl':
					return ( in_array( $this->get_customer_city(), array_map( 'strtoupper', array_map( 'trim', explode( PHP_EOL, $value ) ) ), true ) );

				// User Roles.
				case 'user_role_incl':
					return ( ! in_array( $this->get_customer_role(), $value, true ) );
				case 'user_role_excl':
					return ( in_array( $this->get_customer_role(), $value, true ) );

				// Users.
				case 'user_id_incl':
					return ( ! in_array( (int) $this->get_customer_id(), array_map( 'intval', $value ), true ) );
				case 'user_id_excl':
					return ( in_array( (int) $this->get_customer_id(), array_map( 'intval', $value ), true ) );

				// User Memberships.
				case 'user_membership_incl':
					return ( function_exists( 'wc_memberships_is_user_active_member' ) && ! $this->check_customer_membership_plan( $value ) );
				case 'user_membership_excl':
					return ( function_exists( 'wc_memberships_is_user_active_member' ) && $this->check_customer_membership_plan( $value ) );

				// Payment Gateways.
				case 'payment_gateways_incl':
					return ( ! in_array( $this->get_current_payment_gateway(), $value, true ) );
				case 'payment_gateways_excl':
					return ( in_array( $this->get_current_payment_gateway(), $value, true ) );

				// Products.
				case 'product_incl':
					return ( $this->check_for_cart_data( $package ) && ! $this->check_products( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
				case 'product_excl':
					return ( $this->check_for_cart_data( $package ) && $this->check_products( $value, $this->get_items( $package ) ) );

				// Product Categories.
				case 'product_cat_incl':
					return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat', $this->validate_all_for_include ) );
				case 'product_cat_excl':
					return ( $this->check_for_cart_data( $package ) && $this->check_taxonomy( $value, $this->get_items( $package ), 'product_cat' ) );

				// Product Tags.
				case 'product_tag_incl':
					return ( $this->check_for_cart_data( $package ) && ! $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag', $this->validate_all_for_include ) );
				case 'product_tag_excl':
					return ( $this->check_for_cart_data( $package ) && $this->check_taxonomy( $value, $this->get_items( $package ), 'product_tag' ) );

				// Product Shipping Classes.
				case 'product_shipping_class_incl':
					return ( $this->check_for_cart_data( $package ) && ! $this->check_shipping_class( $value, $this->get_items( $package ), $this->validate_all_for_include ) );
				case 'product_shipping_class_excl':
					return ( $this->check_for_cart_data( $package ) && $this->check_shipping_class( $value, $this->get_items( $package ) ) );

				// Date/Time.
				case 'date_time_incl':
					return ! $this->check_date_time( $value );
				case 'date_time_excl':
					return $this->check_date_time( $value );

			}
		}

		/**
		 * Check date time.
		 *
		 * @version 2.2.0
		 * @since   1.4.0
		 *
		 * @param string $value Value.
		 *
		 * @return bool
		 *
		 * @todo (dev) Debug: Shipping method title?
		 * @todo (dev) Optionally "require all" for `date_time_incl`.
		 */
		public function check_date_time( $value ) {
			$current_time = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
			$value        = array_map( 'trim', explode( ';', $value ) );
			foreach ( $value as $_value ) {
				$_value = array_map( 'trim', explode( '-', $_value ) );
				if ( 2 === count( $_value ) ) {
					$start_time  = strtotime( $_value[0], $current_time );
					$end_time    = strtotime( $_value[1], $current_time );
					$is_in_range = ( $current_time >= $start_time && $current_time <= $end_time );
					$this->debug(
						sprintf(
							/* Translators: %1$s: Date and time, %2$s: Date and time, %3$s: Date and time, %4$s: Result (yes or no). */
							__( 'Date/time range: from %1$s to %2$s; current time: %3$s; result: %4$s', 'conditional-shipping-for-woocommerce' ),
							date( 'Y-m-d H:i:s', $start_time ),   // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
							date( 'Y-m-d H:i:s', $end_time ),     // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
							date( 'Y-m-d H:i:s', $current_time ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
							( $is_in_range ? 'yes' : 'no' )
						)
					);
					if ( $is_in_range ) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Get current payment gateway.
		 *
		 * @version 2.2.0
		 * @since   1.2.0
		 */
		public function get_current_payment_gateway() {
			if ( isset( WC()->session->chosen_payment_method ) ) {

				// Session.
				return WC()->session->chosen_payment_method;

			} elseif ( ! empty( $_REQUEST['payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Submitted data.
				return sanitize_key( $_REQUEST['payment_method'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			} elseif ( '' !== (string) ( $default_gateway = get_option( 'woocommerce_default_gateway' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure

				// Default gateway.
				return $default_gateway;

			} else {

				// First available gateway.
				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
				if ( ! empty( $available_gateways ) ) {
					return current( array_keys( $available_gateways ) );
				}
			}

			// No current gateway.
			return false;
		}

		/**
		 * Check products.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 *
		 * @param array $product_ids              Product IDs.
		 * @param array $items                    Items.
		 * @param bool  $validate_all_for_include Whether to validate all items for include.
		 *
		 * @return bool
		 *
		 * @todo (dev) If needed, prepare `$products_variations` earlier (and only once).
		 */
		public function check_products( $product_ids, $items, $validate_all_for_include = false ) {
			if ( $this->do_add_variations ) {
				$products_variations = array();
				foreach ( $product_ids as $_product_id ) {
					$_product = wc_get_product( $_product_id );
					if ( ! $_product ) {
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
				$_product_id = (
					$this->do_add_variations && ! empty( $item['variation_id'] ) ?
					$item['variation_id'] :
					$item['product_id']
				);
				if (
					$validate_all_for_include &&
					! in_array( (int) $_product_id, array_map( 'intval', $product_ids ), true )
				) {
					return false;
				} elseif (
					! $validate_all_for_include &&
					in_array( (int) $_product_id, array_map( 'intval', $product_ids ), true )
				) {
					return true;
				}
			}

			return $validate_all_for_include;
		}

		/**
		 * Check taxonomy.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 *
		 * @param array  $product_ids              Product IDs.
		 * @param array  $items                    Items.
		 * @param string $taxonomy                 Taxonomy.
		 * @param bool   $validate_all_for_include Whether to validate all items for include.
		 *
		 * @return bool
		 */
		public function check_taxonomy( $product_ids, $items, $taxonomy, $validate_all_for_include = false ) {
			foreach ( $items as $item ) {

				$product_terms = get_the_terms( $item['product_id'], $taxonomy );
				if ( empty( $product_terms ) ) {
					if ( $validate_all_for_include ) {
						return false;
					} else {
						continue;
					}
				}

				foreach ( $product_terms as $product_term ) {
					if (
						$validate_all_for_include &&
						! in_array( (int) $product_term->term_id, array_map( 'intval', $product_ids ), true )
					) {
						return false;
					} elseif (
						! $validate_all_for_include &&
						in_array( (int) $product_term->term_id, array_map( 'intval', $product_ids ), true )
					) {
						return true;
					}
				}
			}

			return $validate_all_for_include;
		}

		/**
		 * Check shipping class.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 *
		 * @param array $product_ids              Product IDs.
		 * @param array $items                    Items.
		 * @param bool  $validate_all_for_include Whether to validate all items for include.
		 *
		 * @return bool
		 *
		 * @todo (dev) Check for `if ( is_object( $product ) && is_callable( array( $product, 'get_shipping_class_id' ) ) ) { ... }`.
		 * @todo (feature) Product variations?
		 */
		public function check_shipping_class( $product_ids, $items, $validate_all_for_include = false ) {
			foreach ( $items as $item ) {
				$product                   = $item['data'];
				$product_shipping_class_id = $product->get_shipping_class_id();
				if (
					$validate_all_for_include &&
					! in_array( (int) $product_shipping_class_id, array_map( 'intval', $product_ids ), true )
				) {
					return false;
				} elseif (
					! $validate_all_for_include &&
					in_array( (int) $product_shipping_class_id, array_map( 'intval', $product_ids ), true )
				) {
					return true;
				}
			}
			return $validate_all_for_include;
		}

		/**
		 * Check for cart data.
		 *
		 * @version 1.8.0
		 * @since   1.0.0
		 *
		 * @param array $package Package.
		 *
		 * @return bool
		 */
		public function check_for_cart_data( $package ) {
			return (
				$this->cart_instead_of_package ?
				( isset( WC()->cart ) && ! WC()->cart->is_empty() ) :
				! empty( $package['contents'] )
			);
		}

		/**
		 * Get items.
		 *
		 * @version 1.8.0
		 * @since   1.0.0
		 *
		 * @param array $package Package.
		 */
		public function get_items( $package ) {
			return (
				$this->cart_instead_of_package ?
				WC()->cart->get_cart() :
				$package['contents']
			);
		}

		/**
		 * Get total cart amount.
		 *
		 * @version 1.8.0
		 * @since   1.0.0
		 *
		 * @param array $package Package.
		 *
		 * @todo (dev) Use subtotal?
		 * @todo (feature) Add option to include or exclude taxes when calculating cart total.
		 */
		public function get_total_cart_amount( $package ) {
			return (
				$this->cart_instead_of_package ?
				WC()->cart->cart_contents_total :
				$package['contents_cost']
			);
		}

		/**
		 * Check customer membership plan.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $membership_plans Membership plans.
		 *
		 * @return bool
		 *
		 * @todo (dev) add "MemberPress" plugin support.
		 */
		public function check_customer_membership_plan( $membership_plans ) {
			foreach ( $membership_plans as $membership_plan ) {
				if ( wc_memberships_is_user_active_member( $this->get_customer_id(), $membership_plan ) ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get customer ID.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_customer_id() {
			if ( ! isset( $this->customer_id ) ) {
				$this->customer_id = get_current_user_id();
			}
			return $this->customer_id;
		}

		/**
		 * Get customer role.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 */
		public function get_customer_role() {
			if ( ! isset( $this->customer_role ) ) {
				$current_user        = wp_get_current_user();
				$first_role          = (
					(
						isset( $current_user->roles ) &&
						is_array( $current_user->roles ) &&
						! empty( $current_user->roles )
					) ?
					reset( $current_user->roles ) :
					'guest'
				);
				$this->customer_role = ( '' !== (string) $first_role ? $first_role : 'guest' );
			}
			return $this->customer_role;
		}

		/**
		 * Get customer city.
		 *
		 * @version 2.2.0
		 * @since   1.0.0
		 *
		 * @todo (dev) Billing city, session, base city: make it optional || remove?
		 * @todo (dev) Do we need `'' !== $_REQUEST[ $key ]` and `'' !== $customer[ $key ]` (i.e., '' vs `get_base_city`)?
		 */
		public function get_customer_city() {
			if ( ! isset( $this->customer_city ) ) {
				$source_for_debug = '';

				// Try to get it from `$_REQUEST`.
				$keys = array( 's_city', 'shipping_city', 'city', 'billing_city' );
				foreach ( $keys as $key ) {
					if ( isset( $_REQUEST[ $key ] ) && '' !== $_REQUEST[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$this->customer_city = sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$source_for_debug    = 'REQUEST[' . $key . ']';
						break;
					}
				}

				// Try to get it from session.
				if (
					! isset( $this->customer_city ) &&
					isset( WC()->session )
				) {
					$customer = WC()->session->get( 'customer' );
					if ( $customer ) {
						$keys = array( 'shipping_city', 'city' );
						foreach ( $keys as $key ) {
							if ( isset( $customer[ $key ] ) && '' !== $customer[ $key ] ) {
								$this->customer_city = sanitize_text_field( $customer[ $key ] );
								$source_for_debug    = 'SESSION[' . $key . ']';
								break;
							}
						}
					}
				}

				// Get it from `get_base_city()` (fallback).
				if ( ! isset( $this->customer_city ) ) {
					$this->customer_city = WC()->countries->get_base_city();
					$source_for_debug    = 'get_base_city';
				}

				// To upper.
				$this->customer_city = strtoupper( $this->customer_city );

				// Debug.
				$this->debug(
					sprintf(
						/* Translators: %s: City name. */
						__( 'Customer city: %s', 'conditional-shipping-for-woocommerce' ),
						$this->customer_city . ' (' . $source_for_debug . ')'
					)
				);

			}

			return $this->customer_city;
		}
	}

endif;

return new Alg_WC_Conditional_Shipping_Core();
