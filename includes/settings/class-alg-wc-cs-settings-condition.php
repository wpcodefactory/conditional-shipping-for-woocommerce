<?php
/**
 * WPFactory Conditional Shipping for WooCommerce - Condition Section Settings
 *
 * @version 1.9.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Conditional_Shipping_Settings_Condition' ) ) :

class Alg_WC_Conditional_Shipping_Settings_Condition extends Alg_WC_Conditional_Shipping_Settings_Section {

	/**
	 * conditions.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	public $conditions;

	/**
	 * cached_options.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 */
	public $cached_options;

	/**
	 * Constructor.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 */
	function __construct( $id, $title, $conditions ) {
		$this->id         = $id;
		$this->desc       = $title;
		$this->conditions = $conditions;
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) cache: `$this->shipping_methods`: `$method_id` and `$method_title`
	 * @todo    (dev) restyle condition titles, e.g., add icons
	 * @todo    (dev) `alg_wc_cond_shipping_settings_...`: find better solution?
	 */
	function get_settings() {
		$settings = array();

		$settings = array_merge( $settings, array(
			array(
				'title'    => $this->desc,
				'type'     => 'title',
				'id'       => 'alg_wc_cond_shipping_' . $this->id . '_options',
				'desc'     => $this->get_section_desc(),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_cond_shipping_' . $this->id . '_options',
			),
		) );

		$shipping_methods = apply_filters( 'alg_wc_cond_shipping_settings_shipping_methods',
			WC()->shipping()->load_shipping_methods() );
		foreach ( $this->conditions as $condition ) {

			$settings = array_merge( $settings, array(
				array(
					'title'    => alg_wc_cond_shipping()->core->conditions[ $condition ],
					'type'     => 'title',
					'id'       => 'alg_wc_cond_shipping_' . $condition . '_options',
					'desc'     => $this->get_condition_desc( $condition ),
				),
				array(
					'title'    => alg_wc_cond_shipping()->core->conditions[ $condition ],
					'desc'     => '<strong>' . __( 'Enable condition', 'conditional-shipping-for-woocommerce' ) . '</strong>',
					'id'       => 'wpjup_wc_cond_shipping_' . $condition . '_enabled',
					'type'     => 'checkbox',
					'default'  => 'no',
				),
			) );

			foreach ( $shipping_methods as $method ) {

				$method_id = apply_filters( 'alg_wc_cond_shipping_settings_method_id',
					( is_object( $method ) ? $method->id : '' ), $method );
				$method_title = apply_filters( 'alg_wc_cond_shipping_settings_method_title',
					( is_object( $method ) ? $method->get_method_title() : '' ), $method );
				$id = "wpjup_wc_cond_shipping_{$condition}_method[{$method_id}]";

				$settings = array_merge(
					$settings,
					$this->get_field(
						array( 'title' => $method_title, 'id' => $id ),
						array( 'condition' => $condition, 'method_id' => $method_id )
					)
				);

			}

			$settings = array_merge( $settings, array(
				array(
					'title'    => __( 'Additional notice', 'conditional-shipping-for-woocommerce' ),
					'desc'     => __( 'Enable', 'conditional-shipping-for-woocommerce' ),
					'desc_tip' => __( 'In addition to hiding the shipping method, you can also add extra notice on the cart and checkout pages.', 'conditional-shipping-for-woocommerce' ),
					'id'       => 'wpjup_wc_cond_shipping_' . $condition . '_notice[enabled]',
					'type'     => 'checkbox',
					'default'  => 'no',
				),
				array(
					'desc'     => sprintf( __( 'Available placeholder(s): %s', 'conditional-shipping-for-woocommerce' ),
						'<code>%shipping_method%</code>' ),
					'id'       => 'wpjup_wc_cond_shipping_' . $condition . '_notice[content]',
					'type'     => 'text',
					'default'  => __( '%shipping_method% is not available.', 'conditional-shipping-for-woocommerce' ),
				),
				array(
					'type'     => 'sectionend',
					'id'       => 'alg_wc_cond_shipping_' . $condition . '_options',
				),
			) );

		}

		return $settings;
	}

	/**
	 * get_condition_desc.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function get_condition_desc( $condition ) {
		switch ( $condition ) {

			case 'date_time_incl':
				$examples = array(
					sprintf( __( 'Enable shipping method only before 3:00 PM each day: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>00:00:00-14:59:59</code>' ),
					sprintf( __( 'Enable shipping method only before 3:00 PM each day, or before 5:00 PM on Mondays: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>00:00:00-14:59:59;Monday 00:00:00-Monday 16:59:59</code>' ),
					sprintf( __( 'Enable shipping method for the summer months only: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>first day of June - last day of August 23:59:59</code>' ),
					sprintf( __( 'Enable shipping method for the February only: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>first day of February - last day of February 23:59:59</code>' ),
				);
				$example_icon = '<span class="dashicons dashicons-lightbulb"></span> ';
				$examples = '<p>' . $example_icon . implode( '</p><p>' . $example_icon, $examples ) . '</p>';
				return '<h4>' . __( 'Examples', 'conditional-shipping-for-woocommerce' ) . '</h4>' . $examples;

			case 'date_time_excl':
				$examples = array(
					sprintf( __( 'Disable shipping method each day after 4:00 PM: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>16:00:00-23:59:59</code>' ),
					sprintf( __( 'Disable shipping method each day after 4:00 PM, and for the whole day on weekends: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>16:00:00-23:59:59;Saturday 00:00:00-Sunday 23:59:59</code>' ),
					sprintf( __( 'Disable shipping method for the summer months: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>first day of June - last day of August 23:59:59</code>' ),
					sprintf( __( 'Disable shipping method for the February: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>first day of February - last day of February 23:59:59</code>' ),
				);
				$example_icon = '<span class="dashicons dashicons-lightbulb"></span> ';
				$examples = '<p>' . $example_icon . implode( '</p><p>' . $example_icon, $examples ) . '</p>';
				return '<h4>' . __( 'Examples', 'conditional-shipping-for-woocommerce' ) . '</h4>' . $examples;

		}
	}

	/**
	 * get_section_desc.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 *
	 * @todo    (desc) add "Update the cart after you change the settings" note to all sections?
	 * @todo    (desc) `date_time`: more examples?
	 * @todo    (desc) `date_time`: more notes, e.g., __( 'Dates in ranges are inclusive', 'conditional-shipping-for-woocommerce' )?
	 */
	function get_section_desc() {
		switch ( $this->id ) {

			case 'user_membership':
				return '<span class="dashicons dashicons-info"></span> ' .
					sprintf( __( 'This section requires <a target="_blank" href="%s">WooCommerce Memberships</a> plugin.', 'conditional-shipping-for-woocommerce' ),
						'https://woocommerce.com/products/woocommerce-memberships/' );

			case 'date_time':
				$notes = array(
					sprintf( __( 'Options must be set as date range(s) in %s format, i.e., dates must be separated with the hyphen %s symbol.', 'conditional-shipping-for-woocommerce' ),
						'<code>from-to</code>', '<code>-</code>' ),
					sprintf( __( 'You can add multiple date ranges separated by the semicolon %s symbol, i.e., %s. Algorithm stops on first matching date range.', 'conditional-shipping-for-woocommerce' ),
						'<code>;</code>', '<code>from1-to1;from2-to2;...</code>' ),
					sprintf( __( 'Dates can be set in any format parsed by the PHP %s function.', 'conditional-shipping-for-woocommerce' ),
						'<a target="_blank" href="https://www.php.net/manual/en/function.strtotime.php"><code>strtotime()</code></a>' ),
					sprintf( __( '%s date must always be smaller than %s date. E.g., %s is <strong>not correct</strong>; you need to use %s instead.', 'conditional-shipping-for-woocommerce' ),
						'<code>from</code>', '<code>to</code>', '<code>22:00:00-09:59:59</code>', '<code>22:00:00-23:59:59;00:00:00-09:59:59</code>' ),
					sprintf( __( 'Current date: %s', 'conditional-shipping-for-woocommerce' ),
						'<code>' . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . '</code>' ),
				);
				$note_icon = '<span class="dashicons dashicons-info"></span> ';
				$notes = '<p>' . $note_icon    . implode( '</p><p>' . $note_icon, $notes ) . '</p>';
				return $notes;

		}
		return '';
	}

	/**
	 * get_field.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `date_time`: desc: apply `strtotime()` to the current value (i.e., so user could see the parsed time right away)
	 */
	function get_field( $field, $data = array() ) {
		switch ( $this->id ) {

			case 'order_amount':
				$return = array(
					'desc_tip' => __( 'Ignored if set to zero.', 'conditional-shipping-for-woocommerce' ),
					'type'     => 'number',
					'default'  => 0,
				);
				if ( empty( $field['custom_attributes'] ) ) {
					$return['custom_attributes'] = array( 'min' => '0', 'step' => '0.000001' );
				}
				break;

			case 'city':
				$return = array(
					'desc_tip' => __( 'Enter cities, one per line.', 'conditional-shipping-for-woocommerce' ),
					'type'     => 'textarea',
					'css'      => 'height:200px;',
					'default'  => '',
				);
				break;

			case 'date_time':
				$return = array(
					'type'     => 'text',
					'css'      => 'width:100%;',
					'default'  => '',
				);
				break;

			case 'product':
				$return = array(
					'type'              => 'multiselect',
					'class'             => 'wc-product-search',
					'options'           => $this->get_field_options( $data ),
					'default'           => array(),
					'custom_attributes' => array(
						'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
						'data-allow_clear' => 'true',
						'data-action'      => ( alg_wc_cond_shipping()->core->do_add_variations ?
							'woocommerce_json_search_products_and_variations' :
							'woocommerce_json_search_products'
						),
					),
				);
				break;

			default:
				$return = array(
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => $this->get_field_options(),
					'default'  => array(),
				);

		}
		return array( array_merge( $field, $return ) );
	}

	/**
	 * get_field_options.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `user_id`, etc.: ajax?
	 * @todo    (dev) `product`: `get_option( "wpjup_wc_cond_shipping_{$data['condition']}_method", array() )`: better solution?
	 */
	function get_field_options( $data = array() ) {

		if ( isset( $this->cached_options ) ) {
			return $this->cached_options;
		}

		switch ( $this->id ) {

			case 'user_role':
				global $wp_roles;
				$all_roles = array_merge(
					array(
						'guest' => array(
							'name'         => __( 'Guest', 'conditional-shipping-for-woocommerce' ),
							'capabilities' => array(),
						)
					),
					apply_filters( 'editable_roles',
						( isset( $wp_roles ) && is_object( $wp_roles ) ?
							$wp_roles->roles : array() )
					)
				);
				$return = wp_list_pluck( $all_roles, 'name' );
				break;

			case 'user_id':
				$users = array();
				foreach ( get_users( 'orderby=display_name' ) as $user ) {
					$users[ $user->ID ] = $user->display_name . ' ' . '[ID:' . $user->ID . ']';
				}
				$return = $users;
				break;

			case 'user_membership':
				$membership_plans = array();
				$block_size       = 1024;
				$offset           = 0;
				while ( true ) {
					$args = array(
						'post_type'      => 'wc_membership_plan',
						'post_status'    => 'any',
						'posts_per_page' => $block_size,
						'offset'         => $offset,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'fields'         => 'ids',
					);
					$loop = new WP_Query( $args );
					if ( ! $loop->have_posts() ) {
						break;
					}
					foreach ( $loop->posts as $post_id ) {
						$membership_plans[ $post_id ] = get_the_title( $post_id );
					}
					$offset += $block_size;
				}
				$return = $membership_plans;
				break;

			case 'payment_gateways':
				$payment_gateways    = array();
				$wc_payment_gateways = WC()->payment_gateways->payment_gateways;
				if ( ! empty( $wc_payment_gateways ) ) {
					foreach ( $wc_payment_gateways as $payment_gateway ) {
						$payment_gateways[ $payment_gateway->id ] = $payment_gateway->method_title;
					}
				}
				$return = $payment_gateways;
				break;

			case 'product':
				$return = array();
				if (
					! empty( $data['condition'] ) &&
					! empty( $data['method_id'] )
				) {
					if (
						( $values = get_option( "wpjup_wc_cond_shipping_{$data['condition']}_method", array() ) ) &&
						! empty( $values[ $data['method_id'] ] ) &&
						is_array( $values[ $data['method_id'] ] )
					) {
						foreach ( $values[ $data['method_id'] ] as $product_id ) {
							$return[ $product_id ] = ( ( $product = wc_get_product( $product_id ) ) ?
								wp_strip_all_tags( $product->get_formatted_name() ) :
								sprintf( __( 'Product #%d', 'conditional-shipping-for-woocommerce' ), $product_id ) );
						}
					}
				}
				break;

			case 'product_cat':
			case 'product_tag':
				$args = array(
					'taxonomy'   => $this->id,
					'orderby'    => 'name',
					'hide_empty' => false,
				);
				global $wp_version;
				if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
					$_terms = get_terms( $args );
				} else {
					$_taxonomy = $args['taxonomy'];
					unset( $args['taxonomy'] );
					$_terms = get_terms( $_taxonomy, $args );
				}
				$_terms_options = array();
				if ( ! empty( $_terms ) && ! is_wp_error( $_terms ) ){
					foreach ( $_terms as $_term ) {
						$_terms_options[ $_term->term_id ] = $_term->name;
					}
				}
				$return = $_terms_options;
				break;

			case 'product_shipping_class':
				$wc_shipping              = WC_Shipping::instance();
				$shipping_classes_terms   = $wc_shipping->get_shipping_classes();
				$shipping_classes_options = array( 0 => __( 'No shipping class', 'woocommerce' ) );
				foreach ( $shipping_classes_terms as $shipping_classes_term ) {
					$shipping_classes_options[ $shipping_classes_term->term_id ] = $shipping_classes_term->name;
				}
				$return = $shipping_classes_options;
				break;

		}
		if ( empty( $data ) ) {
			$this->cached_options = $return;
		}
		return $return;
	}

}

endif;
