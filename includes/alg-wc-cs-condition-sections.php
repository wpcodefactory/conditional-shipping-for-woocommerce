<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Condition Sections
 *
 * @version 1.7.0
 * @since   1.7.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

return array(

	// Order Amount
	'order_amount' => array(
		'title'      => __( 'Order Amount', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'min_order_amount'               => __( 'Minimum Order Amount', 'conditional-shipping-for-woocommerce' ),
			'max_order_amount'               => __( 'Maximum Order Amount', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Cities
	'city' => array(
		'title'      => __( 'Cities', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'city_incl'                      => __( 'Require Cities', 'conditional-shipping-for-woocommerce' ),
			'city_excl'                      => __( 'Exclude Cities', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// User Roles
	'user_role' => array(
		'title'      => __( 'User Roles', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'user_role_incl'                 => __( 'Require User Roles', 'conditional-shipping-for-woocommerce' ),
			'user_role_excl'                 => __( 'Exclude User Roles', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Users
	'user_id' => array(
		'title'      => __( 'Users', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'user_id_incl'                   => __( 'Require User IDs', 'conditional-shipping-for-woocommerce' ),
			'user_id_excl'                   => __( 'Exclude User IDs', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// User Memberships
	'user_membership' => array(
		'title'      => __( 'User Memberships', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'user_membership_incl'           => __( 'Require User Membership Plans', 'conditional-shipping-for-woocommerce' ),
			'user_membership_excl'           => __( 'Exclude User Membership Plans', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Payment Gateways
	'payment_gateways' => array(
		'title'      => __( 'Payment Gateways', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'payment_gateways_incl'          => __( 'Require Payment Gateways', 'conditional-shipping-for-woocommerce' ),
			'payment_gateways_excl'          => __( 'Exclude Payment Gateways', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Products
	'product' => array(
		'title'      => __( 'Products', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'product_incl'                   => __( 'Require Products', 'conditional-shipping-for-woocommerce' ),
			'product_excl'                   => __( 'Exclude Products', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Product Categories
	'product_cat' => array(
		'title'      => __( 'Product Categories', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'product_cat_incl'               => __( 'Require Product Categories', 'conditional-shipping-for-woocommerce' ),
			'product_cat_excl'               => __( 'Exclude Product Categories', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Product Tags
	'product_tag' => array(
		'title'      => __( 'Product Tags', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'product_tag_incl'               => __( 'Require Product Tags', 'conditional-shipping-for-woocommerce' ),
			'product_tag_excl'               => __( 'Exclude Product Tags', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Product Shipping Classes
	'product_shipping_class' => array(
		'title'      => __( 'Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'product_shipping_class_incl'    => __( 'Require Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
			'product_shipping_class_excl'    => __( 'Exclude Product Shipping Classes', 'conditional-shipping-for-woocommerce' ),
		),
	),

	// Date/Time
	'date_time' => array(
		'title'      => __( 'Date/Time', 'conditional-shipping-for-woocommerce' ),
		'conditions' => array(
			'date_time_incl'                 => __( 'Require Date/Time', 'conditional-shipping-for-woocommerce' ),
			'date_time_excl'                 => __( 'Exclude Date/Time', 'conditional-shipping-for-woocommerce' ),
		),
	),

);
