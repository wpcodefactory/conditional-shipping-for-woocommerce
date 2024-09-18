=== WPFactory Conditional Shipping for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder, karzin, omardabbas, kousikmukherjeeli
Tags: woocommerce, shipping, woocommerce shipping, conditional shipping, shipping method
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 1.9.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Set conditions for WooCommerce shipping methods to show up.

== Description ==

**WPFactory Conditional Shipping for WooCommerce** plugin lets you set conditions for WooCommerce shipping methods to show up.

### &#9989; Shipping Method Conditions ###

You can set these conditions for shipping methods:

* Minimum or Maximum **Order Amount**
* Require or Exclude **Cities**
* Require or Exclude **User Roles**
* Require or Exclude **User IDs**
* Require or Exclude **User Membership Plans**
* Require or Exclude **Payment Gateways**
* Require or Exclude **Products**
* Require or Exclude **Product Categories**
* Require or Exclude **Product Tags**
* Require or Exclude **Product Shipping Classes**
* Require or Exclude **Date/Time**

### &#127942; Premium Version ###

[WPFactory Conditional Shipping for WooCommerce Pro](https://wpfactory.com/item/conditional-shipping-for-woocommerce/) allows you to set conditions on **per shipping instance** basis. For example, if you want to set different conditions for different "Flat rate" method instances in different or same shipping zones.

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/conditional-shipping-for-woocommerce/).

### &#8505; More ###

* The plugin is **"High-Performance Order Storage (HPOS)"** compatible.

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Conditional Shipping".

== Changelog ==

= 1.9.2 - 18/09/2024 =
* Fix - Possible "Call to a member function is_type() on bool ..." PHP error fixed.
* WC tested up to: 9.3.

= 1.9.1 - 31/07/2024 =
* WC tested up to: 9.1.
* Tested up to: 6.6.

= 1.9.0 - 24/05/2024 =
* Dev - "Additional notice" options added (to all conditions).
* Dev - Products - Admin - Product dropdowns use AJAX now.
* Dev - Date/Time - Admin - Section description updated.
* Dev - General - Admin settings rearranged.
* Dev - Code refactoring.
* WC tested up to: 8.9.
* Tested up to: 6.5.
* `woocommerce` added to the "Requires Plugins" (plugin header).

= 1.8.0 - 15/03/2024 =
* Fix - Cart instead of package - Bug fixed (multiple packages).
* Dev - PHP 8.2 compatibility - "Creation of dynamic property is deprecated" notice fixed.
* WC tested up to: 8.6.
* Readme.txt - Tags updated.

= 1.7.4 - 20/11/2023 =
* Dev – "High-Performance Order Storage (HPOS)" compatibility.
* WC tested up to: 8.3.
* Tested up to: 6.4.

= 1.7.3 - 24/09/2023 =
* WC tested up to: 8.1.
* Tested up to: 6.3.
* Plugin icon, banner updated.

= 1.7.2 - 18/06/2023 =
* WC tested up to: 7.8.

= 1.7.1 - 25/05/2023 =
* Dev - Developers - `alg_wc_cond_shipping_logical_operator` filter added.

= 1.7.0 - 19/05/2023 =
* Dev - General - "Logical operator" option added (defaults to "AND").
* Dev - Code refactoring.
* Tested up to: 6.2.
* WC tested up to: 7.7.

= 1.6.2 - 14/11/2022 =
* Tested up to: 6.1.
* WC tested up to: 7.1.
* Readme.txt updated.
* Deploy script added.

= 1.6.1 - 13/04/2022 =
* Dev - Date/Time - Admin settings notes updated.
* Tested up to: 5.9.
* WC tested up to: 6.4.

= 1.6.0 - 29/12/2021 =
* Fix - Cities - Algorithm for retrieving the current customer city fixed.
* Dev - Cities - Trying to get the current customer city from the session as well now.
* Dev - Cities - Added to the debug.
* Dev - JS - `update_checkout` trigger - "Cities" module added.
* Dev - JS - `update_checkout` trigger - Improved (`input` event added; waiting for `document.ready` now).
* WC tested up to: 6.0.

= 1.5.0 - 08/09/2021 =
* Dev - Admin settings rearranged: sections merged, e.g., "Minimum Order Amount" and "Maximum Order Amount" to "Order Amount", etc.
* Dev - Admin settings descriptions updated.
* Dev - Plugin is initialized on the `plugins_loaded` action now.
* Dev - Code refactoring.
* Tested up to: 5.8.
* WC tested up to: 5.6.

= 1.4.0 - 04/01/2021 =
* Fix - Settings - Pro plugin message fixed.
* Dev - "Require/Exclude Date/Time" sections added.
* Dev - General - "Checkout notice" option added. Re-checking shipping methods on "after checkout validation" now.
* Dev - General - "Debug" option added.
* Dev - Localization - `load_plugin_textdomain` moved to the `init` hook.
* WC tested up to: 4.8.
* Tested up to: 5.6.

= 1.3.0 - 28/08/2020 =
* Dev - General - "Use shipping instances" defaults to `no` now.
* Dev - JS files minified.
* Dev - All input sanitized now.
* Dev - Code refactoring.
* Dev - Free plugin version created.
* Dev - Admin settings descriptions updated.
* Plugin renamed.
* WC tested up to: 4.4.
* Tested up to: 5.5.

= 1.2.0 - 06/02/2020 =
* Dev - "Require/Exclude Payment Gateways" sections added.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* WC tested up to: 3.9.

= 1.1.0 - 13/11/2019 =
* Fix - Minimum/Maximum Order Amount - Comparing float values properly now (with epsilon).
* Fix - Minimum/Maximum Order Amount - Decimal values are now allowed in settings.
* Dev - Admin settings restyled.
* Dev - Code refactoring.
* Plugin URI updated.
* Tested up to: 5.3.
* WC tested up to: 3.8.

= 1.0.0 - 06/06/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
