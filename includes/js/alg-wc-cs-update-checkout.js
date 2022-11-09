/**
 * alg-wc-cs-update-checkout.js
 *
 * @version 1.6.0
 * @since   1.2.0
 *
 * @author  Algoritmika Ltd.
 */

jQuery( document ).ready( function() {
	/**
	 * Triggers `update_checkout`.
	 *
	 * @version 1.6.0
	 * @since   1.2.0
	 */
	jQuery( 'body' ).on( 'change input', alg_wc_cs_update_checkout.selectors, function() {
		jQuery( 'body' ).trigger( 'update_checkout' );
	} );
} );
