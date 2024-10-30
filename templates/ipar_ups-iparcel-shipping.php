<?php
if ( ! class_exists( 'WC_UPS_iparcel_method' ) ) {
	class WC_UPS_iparcel_method extends WC_Shipping_Method {
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'ups_iparcel';
			$this->method_title       = __( 'UPS i-parcel', 'woocommerce' );
			$this->method_description = __( 'A shipping option which provides a fully landed cost including Taxes and Duty for your international shoppers.' );
			$this->instance_id 		  = absint( $instance_id );
			$this->enabled			  = "yes";
			$this->title              = "UPS i-parcel";
			$this->supports  = array(
				'shipping-zones',
				'instance-settings',
				'instance-settings-modal',
			);
			/*$this->instance_form_fields = array( 
				'115_label' => array(
				  'title'     => __( 'UPS i-parcel Select' ),
				  'type'       => 'text',
				  'label'     => __( '115 Service' ),
				  'default'     => 'UPS i-parcel Select'
				),
				'207_label' => array(
				  'title'     => __( 'UPS Express' ),
				  'type'       => 'text',
				  'label'     => __( '207 Service' ),
				  'default'     => 'UPS Express'
				),
				'208_label' => array(
				  'title'     => __( 'UPS Expedited' ),
				  'type'       => 'text',
				  'label'     => __( '208 Service' ),
				  'default'     => 'UPS Expedited'
				),
	
				'211_label' => array(
				  'title'     => __( 'UPS Standard' ),
				  'type'       => 'text',
				  'label'     => __( '211 Service' ),
				  'default'     => 'UPS Standard'
				),
				'254_label' => array(
				  'title'     => __( 'UPS Express Plus' ),
				  'type'       => 'text',
				  'label'     => __( 'UPS Express Plus' ),
				  'default'     => 'UPS Saver'
				),
				'265_label' => array(
				  'title'     => __( 'UPS Saver' ),
				  'type'       => 'text',
				  'label'     => __( '265 Service' ),
				  'default'     => 'UPS Saver'
				),
			);*/
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		/**
		 * calculate_shipping function - get cart contents and run quote
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			global $woocommerce;
			$items = $woocommerce->cart->get_cart();
			//get list of SKUs and Quantities from cart
			foreach($items as $item => $values) {
				$product = wc_get_product( $values['product_id'] );
				if ( $product->is_type( 'variable' ) ) {
					$product = wc_get_product( $values['variation_id'] );
					$productSKU = $product->get_sku();
					if ($productSKU === '') {
						$productSKU = $values['variation_id'];
					}
				} else {
					$productSKU = $product->get_sku();
					if ($productSKU === '') {
						$productSKU = $values['product_id'];
					}
				}
				$iparItemList[] = array(
				
					'HTSCode' => null,
					'Quantity' => $values['quantity'],
		
					'Identifiers' => array('ProductId' => $productSKU, 'ScreenId' => null),		
					'ProductValue' => array('Amount' => $values['line_total'] / $values['quantity'], 'Currency' => get_woocommerce_currency()),
					'ProductWeight' => null,
					'ProductDimensions' => null,
					'CountryOfOrigin' => null,
					'CatalogValue' => null,
					'SuppressMerchantMarkup' => 'false',
					'SuppressRevShare' => 'true',
				);
			}
			$ipar_QuoteData = array(
				'key' => get_option("ipar_APIKey"),
				'DDP' => 'true',
				'Insurance' => null,
				'PromotionalCode' => null,
				'ResponseCurrencyCode' => get_woocommerce_currency(),
				'ServiceLevels' => null,		
				'SessionId' => null,
				'Discounts' => null,
				'Facility' => 0,
				'ControlNumber' => null,
				'BillingAddress' => array('FirstName' => "", 'LastName' => "", 'Street1' => "", 'Street2' => "", 'Street3' => "", 'PostCode' => $package[ 'destination' ][ 'postcode' ], 'City' => $package[ 'destination' ][ 'city' ], 'Region' => $package[ 'destination' ][ 'state' ], 'CountryCode' => $package[ 'destination' ][ 'country' ], 'Email' => "", 'Phone' => ""),
				'ShippingAddress' => array('FirstName' => "", 'LastName' => "", 'Street1' => "", 'Street2' => "", 'Street3' => "", 'PostCode' => $package[ 'destination' ][ 'postcode' ], 'City' => $package[ 'destination' ][ 'city' ], 'Region' => $package[ 'destination' ][ 'state' ], 'CountryCode' => $package[ 'destination' ][ 'country' ], 'Email' => "", 'Phone' => ""),
				'Parcels' => array(array('ParcelWeight' => null, 'ParcelDimensions' => array('Length' => null, 'Width' => null, 'Height' => null), 'ProductList' => $iparItemList, 'ParcelValue' => null, 'ProvidedShipping' => null, 'CouponCode' => null, 'TrackingNumber' => null, 'OrderReference' => null)),
				'Options' => null,
				'Transaction' => null
			);
			$ipar_WPrequest = array(
				'body' => json_encode($ipar_QuoteData),
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
				'cookies' => array()
			);
			$ipar_quoteResponse = wp_remote_post('https://webservices.i-parcel.com/v2.0/api/Quote', $ipar_WPrequest);
			$ipar_quoteResponseBody = wp_remote_retrieve_body($ipar_quoteResponse);
			$ipar_jsonQuoteResp = json_decode($ipar_quoteResponseBody, TRUE);
			global $iparQuoteError;
			$iparQuoteError = $ipar_jsonQuoteResp["Message"];
			if(strpos($iparQuoteError, "No parcel contents are eligible for shipping to this destination.")) { //if entire parcel is banned
				add_filter( 'woocommerce_no_shipping_available_html', function() use ($iparQuoteError) { echo( "Contents of this order are ineligible for international shipping to your country." ); } );
				add_filter( 'woocommerce_cart_no_shipping_available_html', function() use ($iparQuoteError) { echo( "Contents of this order are ineligible for international shipping to your country." ); } );
			} else if(strpos($iparQuoteError, "The following items are unclassifed")) { //if parcel contains unclassified
				$iparQuoteBannedItems = explode("The following items are unclassifed and have been removed: ", $iparQuoteError);
				add_filter( 'woocommerce_no_shipping_available_html', function() use ($iparQuoteBannedItems) { echo( "The following items need to be removed from your cart for international shipping to your country: <b>". $iparQuoteBannedItems[1] ."</b>"); } );
				add_filter( 'woocommerce_cart_no_shipping_available_html', function() use ($iparQuoteBannedItems) { echo( "The following items need to be removed from your cart for international shipping to your country: <b>". $iparQuoteBannedItems[1] ."</b>"); } );
			} else if(strpos($iparQuoteError, "The following items are banned")) { //if parcel contains both banned and eligible items
				$iparQuoteBannedItems = explode("The following items are banned and have been removed: ", $iparQuoteError);
				add_filter( 'woocommerce_no_shipping_available_html', function() use ($iparQuoteBannedItems) { echo( "The following items need to be removed from your cart for international shipping to your country: <b>". $iparQuoteBannedItems[1] ."</b>"); } );
				add_filter( 'woocommerce_cart_no_shipping_available_html', function() use ($iparQuoteBannedItems) { echo( "The following items need to be removed from your cart for international shipping to your country: <b>". $iparQuoteBannedItems[1] ."</b>"); } );
			} else { //parcel is good - show some rates
				foreach($ipar_jsonQuoteResp["ServiceLevels"] as $iparService) {

					$iparWCTaxEnabled = wc_tax_enabled();
					
					if($iparWCTaxEnabled == 1) {	
						
						$this->add_rate(array(
							'id' => $this->id .'_'. $iparService["ServiceLevel"]["ID"],
							'label' => $iparService["ServiceLevel"]["Name"],
							'cost' => floatval($iparService["Charges"]["Shipping"]) + floatval($iparService["Charges"]["Handling"]),
							'taxes' => array(floatval($iparService["Charges"]["Tax"]) + floatval($iparService["Charges"]["Duty"])),
							'meta_data' => array('UPS-iparcel-service-ID' => $iparService["ServiceLevel"]["ID"]),
						)) ;
						
					} else {
						
						$this->add_rate(array(
							'id' => $this->id .'_'. $iparService["ServiceLevel"]["ID"],
							'label' => $iparService["ServiceLevel"]["Name"],
							'cost' => floatval($iparService["Charges"]["Shipping"]) + floatval($iparService["Charges"]["Handling"]) + floatval($iparService["Charges"]["Tax"]) + floatval($iparService["Charges"]["Duty"]),
							'meta_data' => array('UPS-iparcel-service-ID' => $iparService["ServiceLevel"]["ID"]),
						)) ;
						
					}
				}
				
			}
		}
	}
}
?>