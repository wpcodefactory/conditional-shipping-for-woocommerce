<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - General Section Settings
 *
 * @version 1.9.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Settings_General' ) ) :

class Alg_WC_Conditional_Shipping_Settings_General extends Alg_WC_Conditional_Shipping_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id   = '';
		$this->desc = __( 'General', 'conditional-shipping-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_affected_conditions_message.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) add links to sections
	 */
	function get_affected_conditions_message( $conditions ) {
		return sprintf( __( 'This option affects only these conditions: %s.', 'conditional-shipping-for-woocommerce' ), '"' . implode( '", "', $conditions ) . '"' );
	}

	/**
	 * add_style.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function add_style() {
		$ids = array();
		foreach ( alg_wc_cond_shipping()->core->conditions as $condition_id => $condition_desc ) {
			$ids[] = '.form-table td fieldset label[for=wpjup_wc_cond_shipping_' . $condition_id . '_enabled]';
		}
		echo '<style> ' . implode( ', ', $ids ) . ' { margin-top: 0 !important; margin-bottom: 0 !important; line-height: 1 !important; } </style>';
	}

	/**
	 * get_settings.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `wpjup_wc_cond_shipping_cart_not_package`: remove the option (i.e., always use packages, not cart)
	 * @todo    (dev) Available Conditions: link to each condition separately, e.g., `admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cond_shipping&section=' . $section_id . '#' . 'wpjup_wc_cond_shipping_' . $condition_id . '_enabled' )`
	 * @todo    (desc) Checkout notice: better desc?
	 * @todo    (desc) Debug: better desc?
	 */
	function get_settings() {

		add_action( 'admin_footer', array( $this, 'add_style' ), PHP_INT_MAX );

		$conditions = alg_wc_cond_shipping()->core->conditions;

		$plugin_settings = array(
			array(
				'title'    => __( 'Conditional Shipping Options', 'conditional-shipping-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cond_shipping_plugin_options',
			),
			array(
				'title'    => __( 'Conditional Shipping', 'conditional-shipping-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable plugin', 'conditional-shipping-for-woocommerce' ) . '</strong>',
				'desc_tip' => __( 'Set conditions for WooCommerce shipping methods to show up.', 'conditional-shipping-for-woocommerce' ),
				'id'       => 'wpjup_wc_cond_shipping_plugin_enabled',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cond_shipping_plugin_options',
			),
		);

		$general_settings = array(
			array(
				'title'    => __( 'General Options', 'conditional-shipping-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cond_shipping_general_options',
			),
			array(
				'title'    => __( 'Logical operator', 'conditional-shipping-for-woocommerce' ),
				'desc'     => sprintf( __( 'Logical operator used when multiple conditions are enabled, for example: %s', 'conditional-shipping-for-woocommerce' ),
					sprintf( '<br><em>* %s</em><br><em>* %s</em>',
						__( 'Enable free shipping if an order is over a certain amount AND if a product is in a "Free" shipping class.', 'conditional-shipping-for-woocommerce' ),
						__( 'Enable free shipping if an order is over a certain amount OR if a product is in a "Free" shipping class.', 'conditional-shipping-for-woocommerce' )
					) ),
				'id'       => 'alg_wc_cond_shipping_logical_operator',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'default'  => 'and',
				'options'  => array(
					'and' => __( 'AND', 'conditional-shipping-for-woocommerce' ),
					'or'  => __( 'OR', 'conditional-shipping-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Add products variations', 'conditional-shipping-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you want to add products variations to the products list.', 'conditional-shipping-for-woocommerce' ) . '<br>' .
					$this->get_affected_conditions_message( array(
						$conditions['product_incl'],
						$conditions['product_excl'],
					) ),
				'desc'     => __( 'Add', 'conditional-shipping-for-woocommerce' ),
				'id'       => 'wpjup_wc_cond_shipping_add_variations',
				'type'     => 'checkbox',
				'default'  => 'yes',
			),
			array(
				'title'    => __( 'Require all', 'conditional-shipping-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you want all products in cart to be valid (instead of at least one).', 'conditional-shipping-for-woocommerce' ) . '<br>' .
					$this->get_affected_conditions_message( array(
						$conditions['product_incl'],
						$conditions['product_cat_incl'],
						$conditions['product_tag_incl'],
						$conditions['product_shipping_class_incl'],
					) ),
				'desc'     => __( 'Enable', 'conditional-shipping-for-woocommerce' ),
				'id'       => 'wpjup_wc_cond_shipping_validate_all',
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Cart instead of package', 'conditional-shipping-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you want to check all cart products instead of package.', 'conditional-shipping-for-woocommerce' ) . '<br>' .
					$this->get_affected_conditions_message( array(
						$conditions['min_order_amount'],
						$conditions['max_order_amount'],
						$conditions['product_incl'],
						$conditions['product_excl'],
						$conditions['product_cat_incl'],
						$conditions['product_cat_excl'],
						$conditions['product_tag_incl'],
						$conditions['product_tag_excl'],
						$conditions['product_shipping_class_incl'],
						$conditions['product_shipping_class_excl'],
					) ),
				'desc'     => __( 'Enable', 'conditional-shipping-for-woocommerce' ),
				'id'       => 'wpjup_wc_cond_shipping_cart_not_package',
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			array(
				'title'    => __( 'Checkout notice', 'conditional-shipping-for-woocommerce' ),
				'desc'     => sprintf( __( 'Available placeholder(s): %s', 'conditional-shipping-for-woocommerce' ), '<code>%shipping_method%</code>' ),
				'desc_tip' => __( 'This will be displayed if customer will select the shipping method which became unavailable during the checkout process.', 'conditional-shipping-for-woocommerce' ) . ' ' .
					__( 'For example, when using "Require/Exclude Date/Time" sections, shipping method availability may change because of the time passed since the checkout page was (re)loaded.', 'conditional-shipping-for-woocommerce' ),
				'type'     => 'text',
				'id'       => 'wpjup_wc_cond_shipping_checkout_notice',
				'default'  => __( '%shipping_method% is not available.', 'conditional-shipping-for-woocommerce' ),
				'css'      => 'width:100%;',
			),
			array(
				'title'    => __( 'Use shipping instances', 'conditional-shipping-for-woocommerce' ),
				'desc'     => __( 'Enable', 'conditional-shipping-for-woocommerce' ),
				'desc_tip' => __( 'Enable this if you want to use shipping methods instances instead of shipping methods.', 'conditional-shipping-for-woocommerce' ) . ' ' .
					__( 'For example if you want to set different conditions for different "Flat rate" method instances in different or same shipping zones.', 'conditional-shipping-for-woocommerce' ) .
					apply_filters( 'alg_wc_cond_shipping_settings', '<br>' . sprintf( 'You will need %s plugin to enable this option.',
						'<a target="_blank" href="https://wpfactory.com/item/conditional-shipping-for-woocommerce/">' . 'WPFactory Conditional Shipping for WooCommerce Pro' . '</a>' ) ),
				'type'     => 'checkbox',
				'id'       => 'wpjup_wc_cond_shipping_use_instances',
				'default'  => 'no',
				'custom_attributes' => apply_filters( 'alg_wc_cond_shipping_settings', array( 'disabled' => 'disabled' ) ),
			),
			array(
				'title'    => __( 'Debug', 'conditional-shipping-for-woocommerce' ),
				'desc'     => __( 'Enable', 'conditional-shipping-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Will add a log to %s.', 'conditional-shipping-for-woocommerce' ),
						'<a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' . __( 'WooCommerce > Status > Logs', 'conditional-shipping-for-woocommerce' ) . '</a>' ) . '<br>' .
					sprintf( __( 'Currently this option affects only these conditions: %s.', 'conditional-shipping-for-woocommerce' ),
						'"' . implode( '", "', array(
							$conditions['city_incl'],
							$conditions['city_excl'],
							$conditions['date_time_incl'],
							$conditions['date_time_excl'],
						) ) . '"'
					),
				'type'     => 'checkbox',
				'id'       => 'wpjup_wc_cond_shipping_debug',
				'default'  => 'no',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cond_shipping_general_options',
			),
		);

		$condition_settings = array(
			array(
				'title'    => __( 'Available Conditions', 'conditional-shipping-for-woocommerce' ),
				'type'     => 'title',
				'id'       => 'alg_wc_cond_shipping_conditions_options',
			),
		);
		$condition_index = 0;
		$condition_count = count( $conditions );
		foreach ( alg_wc_cond_shipping()->core->get_condition_sections() as $section_id => $section ) {
			foreach ( $section['conditions'] as $condition_id => $condition_desc ) {
				$condition_settings = array_merge( $condition_settings, array(
					array(
						'title'    => ( 0 === $condition_index ? __( 'Conditions', 'conditional-shipping-for-woocommerce' ) : '' ),
						'desc'     => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_cond_shipping&section=' . $section_id ) . '">' . $condition_desc . '</a>',
						'id'       => 'wpjup_wc_cond_shipping_' . $condition_id . '_enabled',
						'default'  => 'no',
						'type'     => 'checkbox',
						'checkboxgroup' => ( 0 === $condition_index ? 'start' : ( ( $condition_count - 1 ) === $condition_index ? 'end' : '' ) ),
					),
				) );
				$condition_index++;
			}
		}
		$condition_settings = array_merge( $condition_settings, array(
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cond_shipping_conditions_options',
			),
		) );

		return array_merge( $plugin_settings, $general_settings, $condition_settings );
	}

}

endif;

return new Alg_WC_Conditional_Shipping_Settings_General();
