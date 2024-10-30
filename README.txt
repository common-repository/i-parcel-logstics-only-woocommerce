=== UPS i-parcel Logistics Only ===

Contributors: upsiparcel
Tags: UPS Shipping International Shipping, i-parcel, UPS i-parcel, Shipping Method, International Shipping
Requires at least: 4.0
Tested up to: 5.3
Requires PHP: 5.6.30
Stable tag: 1.3.9
Version: 1.3.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable UPS i-parcel shipping method in your checkout for your international shoppers.

== Description ==

Integrate the UPS i-parcel Logistics Only plug-in into your WooCommerce website to offer a shipping option for your international shoppers.  Requires a UPS i-parcel Global Access account.  Shipping quotes include shipping costs, tax and duty for exporting.

For the merchant, orders have links to generate UPS i-parcel labels and barcodes from the order details.

== Installation ==

1. Install plug-in from WordPress.org or upload extracted .zip into your "wp-content/plugins" directory.

2. Activate the plugin through the "Plugins" menu in WordPress.

3. Navigate to the "i-parcel" option in your admin's navigation

4. Enter your UPS i-parcel Global Access username and password to fetch your API keys.  Save your settings

5. Navigate to i-parcel > Catalog Sync settings.  Populate your catalog sync options and save your settings.

6. Click the "Upload Catalog" button to sync your WooCommerce catalog to your UPS i-parcel Dashboard.

7. Navigate to i-parcel > Shipping Methods and add the UPS i-parcel shipping method to the zones you want UPS i-parcel to be available in.

== Frequently Asked Questions ==

No FAQs yet.  Questions? Visit https://www.i-parcel.com/en/contact-us/

== Screenshots ==

1. Plug-in configuration

2. Catalog syncing configurations

3. WooCommerce Order Details

== Changelog ==

1.0 - initial plug-in

1.0.1 - update to include all UPS service levels and support custom labels.

1.2.0 - Updated to use new 2.0 APIs.  Included error messaging if items in order are ineligible or banned.

1.2.1 - Updated Catalog Sync to use WooCommerce Product Attributes

1.2.2 - Updated quote to including Handling Fee into Shipping cost

1.2.3 – Added Catalog Sync validation

1.2.5 - Updated catalog sync to break up requests into chunks of 25 SKUs per request

1.2.6 - Created Daily Catalog Sync option in Catalog settings.  Also fixed missing character issue in iparscript.js file

1.2.7 - Updated Tax and Duty statement in iparscript.js file

1.2.8 - Removed excessive white space in code.  Updated SubmitParcel to use different API Key.  Added conversion of OZ to LBs and G to KGs.

1.2.9 - Updated Quote and SubmitParcel calls to get Variant SKUs if products are variable products.

1.3.1 - Updated to use Store Currency in Quote and SubmitParcel calls instead of just USD.

1.3.2 - Removed function is JS to suppress payment methods if UPS i-parcel Shipping Service is selected.

1.3.3 - Added support to use product parent weights and dims if varient weight and dims are left empty

1.3.4 - Applied fix to not pass "false" for weights/dims but instead pass "0" if woocommerce weight/dim inputs are empty

1.3.5 - Updated to use WooCommerce product attributes for Ship Alone values

1.3.6 - Updated Quote call to include "handling" value from Quote response into shipping cost.

1.3.7 - Updated plug-in to use service level names returned from Quote API response.

1.3.8 - Added option to suppress SubmitParcel API call.  If set to "true", merchant will have to find another way to get order data into the UPS i-parcel Dashboard.

1.3.9 - Applied fix to correct conflict between plug-in and product variation creation on product editor in admin.

== Upgrade Notice ==

A new version of UPS i-parcel Logistics Only is available