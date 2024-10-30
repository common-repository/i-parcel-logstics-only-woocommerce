<?php
/**
 * Plugin Name: i-parcel Logstics Only (WooCommerce)
 * Description: A plug-in to enable UPS i-parcel Shipping Services for your international shoppers
 * Version: 1.3.9
 * Author: UPS i-parcel
 * Author URI: http://www.i-parcel.com/
 * Text Domain: i-parcel
 * @package i-parcel
 */
/* Check if WooCommerce is active */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if (! function_exists('write_log')) {
   function write_log ( $log )  {
	  if (is_array($log) || is_object($log)) {
		 error_log(print_r($log, true));
	  } else {
		 error_log($log);
	  }
   }
}
//admin notices to check for required items
function ipar_admin_reqiurements() {
	//do check to see if Tax are enabled.
	$ipar_taxEnabled = get_option('woocommerce_calc_taxes');
	if($ipar_taxEnabled === 'no') {
		echo('<div class="notice notice-error""><p><b>Taxes need to be enabled inside WooCommerce for UPS i-parcel to show Tax and Duty to your international shoppers during checkout.</b></p></div>');
	}
	
	$ipar_taxDisplay = get_option('woocommerce_tax_total_display');
	if($ipar_taxDisplay != 'single') {
		echo('<div class="notice notice-error"><p><b>"Display tax totals" must be set as "As a single total" to show Tax and Duty to your international shoppers during checkout.</b></p></div>');
	}
}
add_action( 'admin_notices', 'ipar_admin_reqiurements' );
add_action('admin_head', 'ipar_css');
function ipar_css() {
echo '<style>
.ipartooltip {position:relative;display:inline-block;float:right;}
.ipartooltip .ipartooltiptext {visibility:hidden;width:200px;background-color:#555;color:#fff;text-align:center;border-radius:6px;padding:5px;position:absolute;z-index:1;bottom:125%;left:50%;margin-left:-105px;opacity:0;transition:opacity 0.3s;font-size:11px;}
.ipartooltip .ipartooltiptext::after {content: "";position: absolute;top: 100%;left: 50%;margin-left: -5px;border-width: 5px;border-style: solid;border-color: #555 transparent transparent transparent;}
.ipartooltip:hover .ipartooltiptext {visibility:visible;opacity:1;}
</style>';
}
//Add i-parcel pages to WordPress admin	
function ipar_config() { include('templates/ipar_config.php');}
function ipar_catalogSync() { include('templates/ipar_catalog.php'); }
function ipar_catalogSync_GO() { include('templates/ipar_catalog_upload.php'); }
function ipar_externalAPI() { include('templates/ipar_external.php'); }
add_action('admin_menu', 'ipar_config_admin_actions');
function ipar_config_admin_actions() {
    add_menu_page( 'i-parcel Global Access', 'i-parcel', 'manage_options', 'ipar_config.php', 'ipar_config', 'https://www.i-parcel.com/wp-content/themes/i-parcel/images/favicon/favicon-16x16.png' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Config', 'Configuration', 'manage_options', 'ipar_config.php', 'ipar_config' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Catalog Sync', 'Catalog Sync', 'manage_options', 'ipar_catalog.php', 'ipar_catalogSync' );
	add_submenu_page( 'ipar_config.php', 'i-parcel Shipping Methods', 'Shipping Methods', 'manage_options', 'admin.php?page=wc-settings&tab=shipping', '' );
	//add_submenu_page( 'https://globalaccess.i-parcel.com/', 'i-parcel Dashboard', 'i-parcel Dashboard', 'manage_options', 'https://globalaccess.i-parcel.com/', '' );
}
//Register options to save Config settings
add_action( 'admin_init', 'register_config_settings' );
function register_config_settings() {
	register_setting( 'iparconfig-group', 'ipar_APIKey' );
	register_setting( 'iparconfig-group', 'ipar_APIPrivateKey' );
	register_setting( 'iparconfig-group', 'ipar_LandedCostOnly' );
}
//Register options to save Catalog settings
add_action( 'admin_init', 'register_catalog_settings' );
function register_catalog_settings() {
	register_setting( 'iparcatalog-group', 'ipar_catalogConfig' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute1' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute2' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute3' );
	register_setting( 'iparcatalog-group', 'ipar_cat_attribute4' );
	register_setting( 'iparcatalog-group', 'ipar_cat_CountryOfOrigin' );
	register_setting( 'iparcatalog-group', 'ipar_cat_HTSCodes' );
	register_setting( 'iparcatalog-group', 'ipar_cat_shipAlone' );
	register_setting( 'iparcatalog-group', 'ipar_cat_OverrideCountryOfOrigin' );
}
//Add Action to feed updated product details to i-parcel catalog on product save
function ipar_CH_ipar_update_catalog_price($post_id) {
	$iparSyncCheck = esc_attr(get_option('ipar_catalogConfig'));
	if($iparSyncCheck == 1) {
		global $post;
		$slug = 'product';
		$variantSlug = 'product_variation';
		$iparPostType = get_post_type($post);
		if ( $slug != $iparPostType ) {
			return;
		}
		$iparCatArgs = array( 'taxonomy' => 'product_cat',);
		$iparCatTerms = wp_get_post_terms($post->ID,'product_cat', $iparCatArgs);
		$ipar_catArray = array();
		$count = count($iparCatTerms); 
		if ($count > 0) {
			foreach ($iparCatTerms as $iparCatTerm) {
				$ipar_catArray[] = $iparCatTerm->name;
			}
		}
		$ipar_prodCats = implode(",", $ipar_catArray);
		// Check if product is variable and send variant SKU data
		$iparProduct = new WC_Product_Variable($post_id);
		$iparProdVars = $iparProduct->get_available_variations();
		if(!empty($iparProdVars)) {
			// get IDs of main product and variants
			foreach ( $iparProdVars as $iparProdVar ) {
				if( get_post_type($iparProdVar['variation_id']) == $variantSlug ) {
					//get variation ID if needed for parent
					$iparVariation = wc_get_product($iparProdVar['variation_id']);
					$WooSKU = get_post_meta($iparProdVar['variation_id'], '_sku', true);
					if($WooSKU == '') {
						$WooSKU = $iparProdVar['variation_id'];
					}
					// Product Name
					$iparProdName = get_the_title($iparProdVar['variation_id']);
					if(strpos($iparProdName, 'Variation') !== false) {
						$iparProdName = explode (' ', $iparProdName, 4);
						$iparProdName = $iparProdName[3];
					}
					// Get Country of Origin
					$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
					if( $iparCountryOfOrigin == '') {
						$iparCountryOfOrigin = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_CountryOfOrigin"));
						$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
					}
					if( get_option("ipar_cat_attribute1") != 'none' ) {
						$iparAttribute2 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute1"));
						$iparAttribute2 = $iparAttribute2[0]->name;
					} else {
						$iparAttribute2 = "";
					}
					if( get_option("ipar_cat_attribute2") != 'none' ) {
						$iparAttribute3 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute2"));
						$iparAttribute3 = $iparAttribute3[0]->name;
					} else {
						$iparAttribute3 = "";
					}
					if( get_option("ipar_cat_attribute3") != 'none' ) {
						$iparAttribute4 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute3"));
						$iparAttribute4 = $iparAttribute4[0]->name;
					} else {
						$iparAttribute4 = "";
					}
					if( get_option("ipar_cat_attribute4") != 'none' ) {
						$iparAttribute5 = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_attribute4"));
						$iparAttribute5 = $iparAttribute5[0]->name;
					} else {
						$iparAttribute5 = "";
					}
					if( get_option("ipar_cat_HTSCodes") != 'none' ) {
						$iparHTSCode = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_HTSCodes"));
						$iparHTSCode = $iparHTSCode[0]->name;
					} else {
						$iparHTSCode = NULL;
					}
					if( get_option("ipar_cat_shipAlone") != 'none' ) {
						$iparShipAlone = get_the_terms($iparProdVar['variation_id'], get_option("ipar_cat_shipAlone"));
						
						$iparShipAlone = $iparShipAlone[0]->name;
					
					} else {
					
						$iparShipAlone = NULL;
					}
					$iparSKUWeight = get_post_meta($iparProdVar['variation_id'], '_weight', true);
					if($iparSKUWeight === '') {
						$iparSKUWeight = get_post_meta($iparVariation->get_parent_id(), '_weight', true);
					}
					$iparSKUHeight = get_post_meta($iparProdVar['variation_id'], '_height', true);
					if($iparSKUHeight === '') {
						$iparSKUHeight = get_post_meta($iparVariation->get_parent_id(), '_height', true);
					}
					$iparSKUWidth = get_post_meta($iparProdVar['variation_id'], '_width', true);
					if($iparSKUWidth === '') {
						$iparSKUWidth = get_post_meta($iparVariation->get_parent_id(), '_width', true);
					}
					$iparSKULength = get_post_meta($iparProdVar['variation_id'], '_length', true);
					if($iparSKULength === '') {
						$iparSKULength = get_post_meta($iparVariation->get_parent_id(), '_length', true);
					}
					
					//get Weight unit and convert based on selected value
					$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
					if($iparMerchantWeightSetting === 'oz') {
						//convert oz to lbs
						$iparSKUWeight = $iparSKUWeight / 16;
					} else if($iparMerchantWeightSetting === 'g') {
						//convert g to kg
						$iparSKUWeight = $iparSKUWeight / 1000;
					}
					$ipar_SKUs[] = array(
						'SKU' => $WooSKU,
						'ProductName' => $iparProdName,
						'Attribute2' => $iparAttribute2,
						'Attribute3' => $iparAttribute3,
						'Attribute4' => $iparAttribute4,
						'Attribute5' => $iparAttribute5,
						'Attribute6' => $post->post_content,
						'HSCodeUS' => $iparHTSCode,
						'HSCodeCA' => NULL,
						'CountryOfOrigin' => $iparCountryOfOrigin,
						'CurrentPrice' => get_post_meta($iparProdVar['variation_id'], '_price', true),
						'ProductURL' => get_permalink($iparProdVar['variation_id']),
						'Weight' => $iparSKUWeight,
						'SKN' => NULL,
						'Length' => $iparSKULength,
						'Width' => $iparSKUWidth,
						'Height' => $iparSKUHeight,
						'ShipAlone' => $iparShipAlone,
						'Delete' => false,
						'WeightInKilos' => NULL
					);
				}
				
			}
		}
		//Send original product data
		$WooSKU = get_post_meta($post->ID, '_sku', true);
		if($WooSKU == '') {
			$WooSKU = $post->ID;
		}
		// Get Country of Origin
		$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
		if( $iparCountryOfOrigin == '') {
			//$iparCountryOfOrigin = get_post_meta($WooProdID, get_option("ipar_cat_CountryOfOrigin"), true);
			$iparCountryOfOrigin = get_the_terms($post->ID, get_option("ipar_cat_CountryOfOrigin"));
			$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
		}
		if( get_option("ipar_cat_attribute1") != 'none' ) {
			$iparAttribute2 = get_the_terms($post->ID, get_option("ipar_cat_attribute1"));		
			$iparAttribute2 = $iparAttribute2[0]->name;
		} else {
			$iparAttribute2 = "";
		}
		if( get_option("ipar_cat_attribute2") != 'none' ) {
			$iparAttribute3 = get_the_terms($post->ID, get_option("ipar_cat_attribute2"));
			$iparAttribute3 = $iparAttribute3[0]->name;
		} else {
			$iparAttribute3 = "";
		}
		if( get_option("ipar_cat_attribute3") != 'none' ) {
			$iparAttribute4 = get_the_terms($post->ID, get_option("ipar_cat_attribute3"));
			$iparAttribute4 = $iparAttribute4[0]->name;
		} else {
			$iparAttribute4 = "";
		}
		if( get_option("ipar_cat_attribute4") != 'none' ) {
			$iparAttribute5 = get_the_terms($post->ID, get_option("ipar_cat_attribute4"));
			$iparAttribute5 = $iparAttribute5[0]->name;
		} else {
			$iparAttribute5 = "";
		}
		if( get_option("ipar_cat_HTSCodes") != 'none' ) {
			$iparHTSCode = get_the_terms($post->ID, get_option("ipar_cat_HTSCodes"));
			$iparHTSCode = $iparHTSCode[0]->name;
		} else {
			$iparHTSCode = NULL;
		}
		
		if( get_option("ipar_cat_shipAlone") != 'none' ) {
			$iparShipAlone = get_the_terms($post->ID, get_option("ipar_cat_shipAlone"));
			$iparShipAlone = $iparShipAlone[0]->name;
		} else {
			$iparShipAlone = NULL;
		}
		//get Weight unit and convert based on selected value
		$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
		$iparSKUWeight = $post->_weight;
		if($iparMerchantWeightSetting === 'oz') {
			//convert oz to lbs
			$iparSKUWeight = $iparSKUWeight / 16;
		} else if($iparMerchantWeightSetting === 'g') {
			//convert g to kg
			$iparSKUWeight = $iparSKUWeight / 1000;
		}
		$ipar_SKUs[] = array(
			'SKU' => $WooSKU,
			'ProductName' => get_the_title($post->ID),
			'Attribute1' => $ipar_prodCats,
			'Attribute2' => $iparAttribute2,
			'Attribute3' => $iparAttribute3,
			'Attribute4' => $iparAttribute4,
			'Attribute5' => $iparAttribute5,
			'Attribute6' => $post->post_content,
			'HSCodeUS' => $iparHTSCode,
			'HSCodeCA' => NULL,
			'CountryOfOrigin' => $iparCountryOfOrigin,
			'CurrentPrice' => $post->_price,
			'ProductURL' => get_permalink($post->ID),
			'Weight' => $iparSKUWeight,
			'SKN' => NULL,
			'Length' => $post->_length,
			'Width' => $post->_width,
			'Height' => $post->_height,
			'ShipAlone' => $iparShipAlone,
			'Delete' => false,
			'WeightInKilos' => NULL
		);
		// Put data in array for UPS i-parcel;		
		$ipar_SKUData = array(
			'key' => get_option("ipar_APIPrivateKey"),
			'SKUs' => $ipar_SKUs
		);
		$ipar_WPrequest = array(
			'body' => json_encode($ipar_SKUData),
			'timeout' => '5',
			'redirection' => '5',
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
			'cookies' => array()
		);
		$ipar_catalogResponse = wp_remote_post( 'https://webservices.i-parcel.com/api/SubmitCatalog', $ipar_WPrequest );
		$ipar_catalogResponseBody = wp_remote_retrieve_body($ipar_quoteResponse);
		$ipar_responseData = json_decode($ipar_catalogResponseBody, TRUE);
	}
}
	
add_action('woocommerce_update_product', 'ipar_CH_ipar_update_catalog_price');

//option for WP_Cron catalog sync
$iparSyncCheck = esc_attr(get_option('ipar_catalogConfig'));
if($iparSyncCheck == 2) {
	if ( ! wp_next_scheduled( 'ipar_catalog_sync_cron' ) ) {
		wp_schedule_event( time(), 'daily', 'ipar_catalog_sync_cron' );
	}
} else {
	$iparCronTimestamp = wp_next_scheduled( 'ipar_catalog_sync_cron' );
	wp_unschedule_event( $iparCronTimestamp, 'ipar_catalog_sync_cron' );
}
function ipar_CH_ipar_catalog_sync_cron() {
	
	$ipar_SKUs = array();
	global $wpdb;
	$ipar_synced = 0;
	$ipar_syncOffset = 0;
	$ipar_syncLoadSize = 25;
	$WooCatalogSize = $wpdb->get_results(
		"
		SELECT ID, post_title, post_content, post_name
		FROM $wpdb->posts
		WHERE post_type = 'product' OR post_type = 'product_variation'
		"
	);
	while ($ipar_synced <= count($WooCatalogSize)) {
		//place bulk sync function here...
		$WooProducts = $wpdb->get_results( 
		"
		SELECT ID, post_title, post_content, post_name
		FROM $wpdb->posts
		WHERE post_type = 'product' OR post_type = 'product_variation'
		ORDER BY ID ASC
		LIMIT ". $ipar_syncOffset .",". $ipar_syncLoadSize
		);
		foreach ( $WooProducts as $WooProduct ) {
			$WooProdID = $WooProduct->ID;
			$WooCategory = $wpdb->get_results(
			"
			SELECT name
			FROM $wpdb->terms
			LEFT JOIN $wpdb->term_relationships ON ($wpdb->terms.term_ID = $wpdb->term_relationships.term_taxonomy_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->terms.term_ID = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE $wpdb->term_relationships.object_id = '$WooProdID'
			AND $wpdb->term_taxonomy.taxonomy = 'product_cat'
			AND $wpdb->terms.name NOT LIKE 'simple'
			AND $wpdb->terms.name NOT LIKE 'group'
			AND $wpdb->terms.name NOT LIKE 'variable'
			AND $wpdb->terms.name NOT LIKE 'external'
			"
			);
			$ipar_catArray = array();
			foreach ($WooCategory as $WooCatName) {
				$ipar_catArray[] = $WooCatName->name;
			}
			$ipar_prodCats = implode(",", $ipar_catArray);
			// SKU Data in array
			$WooSKU = get_post_meta($WooProdID, '_sku', true);
			if($WooSKU == '') {
				$WooSKU = $WooProdID;
			}
			// Product Name
			$iparProdName = $WooProduct->post_title;
			if(strpos($iparProdName, 'Variation') !== false) {
				$iparProdName = explode (' ', $iparProdName, 4);
				$iparProdName = $iparProdName[3];
			}
			// Product Description
			$iparProdDescription = $WooProduct->post_content;
			if($iparProdDescription == '') {
				$iparProdDescription = get_post_meta($WooProdID, '_variation_description', true);
			}
			// Get Country of Origin
			$iparCountryOfOrigin = get_option("ipar_cat_OverrideCountryOfOrigin");
			if( $iparCountryOfOrigin == '') {
				$iparCountryOfOrigin = get_the_terms($WooProdID, get_option("ipar_cat_CountryOfOrigin"));
				$iparCountryOfOrigin = $iparCountryOfOrigin[0]->name;
			}
			if( get_option("ipar_cat_attribute1") != 'none' ) {
				$iparAttribute2 = get_the_terms($WooProdID, get_option("ipar_cat_attribute1"));
				$iparAttribute2 = $iparAttribute2[0]->name;
			} else {
				$iparAttribute2 = "";
			}
			if( get_option("ipar_cat_attribute2") != 'none' ) {
				$iparAttribute3 = get_the_terms($WooProdID, get_option("ipar_cat_attribute2"));
				$iparAttribute3 = $iparAttribute3[0]->name;
			} else {
				$iparAttribute3 = "";
			}
			if( get_option("ipar_cat_attribute3") != 'none' ) {
				$iparAttribute4 = get_the_terms($WooProdID, get_option("ipar_cat_attribute3"));
				$iparAttribute4 = $iparAttribute4[0]->name;
			} else {
				$iparAttribute4 = "";
			}
			if( get_option("ipar_cat_attribute4") != 'none' ) {
				$iparAttribute5 = get_the_terms($WooProdID, get_option("ipar_cat_attribute4"));
				$iparAttribute5 = $iparAttribute5[0]->name;
			} else {
				$iparAttribute5 = "";
			}
			if( get_option("ipar_cat_HTSCodes") != 'none' ) {
				$iparHTSCode = get_the_terms($WooProdID, get_option("ipar_cat_HTSCodes"));
				$iparHTSCode = $iparHTSCode[0]->name;
			} else {
				$iparHTSCode = NULL;
			}
			if( get_option("ipar_cat_shipAlone") != 'none' ) {
				$iparShipAlone = get_the_terms($WooProdID, get_option("ipar_cat_shipAlone"));
				$iparShipAlone = $iparShipAlone[0]->name;
			} else {
				$iparShipAlone = NULL;
			}
			$iparSKUWeight = get_post_meta($WooProdID, '_weight', true);
			if($iparSKUWeight === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUWeight = get_post_meta($iparVariation->get_parent_id(), '_weight', true);
				if($iparSKUWeight === false) {
					$iparSKUWeight = "0";	
				}
			}
			$iparSKUHeight = get_post_meta($WooProdID, '_height', true);
			if($iparSKUHeight === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUHeight = get_post_meta($iparVariation->get_parent_id(), '_height', true);
				if($iparSKUHeight === false) {
					$iparSKUHeight = "0";	
				}
			}
			$iparSKUWidth = get_post_meta($WooProdID, '_width', true);
			if($iparSKUWidth === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKUWidth = get_post_meta($iparVariation->get_parent_id(), '_width', true);
				if($iparSKUWidth === false) {
					$iparSKUWidth = "0";	
				}
			}
			$iparSKULength = get_post_meta($WooProdID, '_length', true);
			if($iparSKULength === '') {
				$iparVariation = wc_get_product($WooProdID);
				$iparSKULength = get_post_meta($iparVariation->get_parent_id(), '_length', true);
				if($iparSKULength === false) {
					$iparSKULength = "0";	
				}
			}
			
			//get Weight unit and convert based on selected value
			$iparMerchantWeightSetting = esc_attr( get_option('woocommerce_weight_unit') );
			if($iparMerchantWeightSetting === 'oz') {
				//convert oz to lbs
				$iparSKUWeight = $iparSKUWeight / 16;
			} else if($iparMerchantWeightSetting === 'g') {
				//convert g to kg
				$iparSKUWeight = $iparSKUWeight / 1000;
			}
			$ipar_SKUs[] = array(
				'SKU' => $WooSKU,
				'ProductName' => $iparProdName,
				'Attribute1' => $ipar_prodCats,
				'Attribute2' => $iparAttribute2,
				'Attribute3' => $iparAttribute3,
				'Attribute4' => $iparAttribute4,
				'Attribute5' => $iparAttribute5,
				//'Attribute6' => $post->post_content,
				'HSCodeUS' => $iparHTSCode,
				'HSCodeCA' => NULL,
				'CountryOfOrigin' => $iparCountryOfOrigin,
				'CurrentPrice' => get_post_meta($WooProdID, '_price', true),
				'ProductURL' => get_permalink($WooProduct->ID),
				'Weight' => $iparSKUWeight,
				'SKN' => null,
				'Length' => $iparSKULength,
				'Width' => $iparSKUWidth,
				'Height' => $iparSKUHeight,
				'ShipAlone' => $iparShipAlone,
				'Delete' => false,
				'WeightInKilos' => null,
				'WeightInKilos' => false,
				'WeightUnit' => null,
				'DimensionUnit' => null
			);
			}
			$ipar_SKUData = array(
				'key' => get_option("ipar_APIPrivateKey"),
				'SKUs' => $ipar_SKUs
			);
			$ipar_WPrequest = array(
				'body' => json_encode($ipar_SKUData),
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
				'cookies' => array()
			);
			$ipar_catalogResponse = wp_remote_post( 'https://webservices.i-parcel.com/api/SubmitCatalog', $ipar_WPrequest );
			$ipar_catalogResponseBody = wp_remote_retrieve_body($ipar_catalogResponse);
			$ipar_responseData = json_decode($ipar_catalogResponseBody, TRUE);
			unset($ipar_SKUs);
			$ipar_SKUs = array();
		$ipar_synced = $ipar_synced + $ipar_syncLoadSize;
		$ipar_syncOffset = $ipar_syncOffset + $ipar_syncLoadSize;
	}
	
}
	
add_action( 'ipar_catalog_sync_cron',  'ipar_CH_ipar_catalog_sync_cron' );
//Add UPS i-parcel Shipping Method
add_filter('woocommerce_shipping_methods', 'add_ups_iparcel_method');
function add_ups_iparcel_method( $methods ) {
	$methods['ups_iparcel'] = 'WC_UPS_iparcel_method';
	return $methods;
}
add_action( 'woocommerce_shipping_init', 'ups_iparcel_method' );
function ups_iparcel_method() {
	require_once 'templates/ipar_ups-iparcel-shipping.php';
}
//Fire SubmitParcel if order is completed and is using ups_iparcel as shipping method.
add_action('woocommerce_order_status_pending_to_processing', 'ipar_getOrderAndSubmitParcel', 10, 1);
//Get All Order Details
function ipar_getOrderAndSubmitParcel($order_id){
	if(esc_attr( get_option('ipar_LandedCostOnly') ) === 'false' || esc_attr( get_option('ipar_LandedCostOnly') ) === '') {
		//execute call
		$iparOrder = new WC_Order( $order_id );
		//to get shopper selected shipping method and service
		$iparShipping = $iparOrder->get_items( 'shipping' );	
		foreach( $iparOrder->get_items( 'tax' ) as $item_id => $item_tax ){
			$tax_data = $item_tax->get_data();
			$iparOrderTaxID = $tax_data['id'];
		}
		wc_update_order_item_meta($iparOrderTaxID, 'label', 'Tax & Duty');
		$serviceLevelOrderID = array_keys($iparShipping);
		$iparServiceLevelID = wc_get_order_item_meta($serviceLevelOrderID[0], 'UPS-iparcel-service-ID', $single);
		if($iparServiceLevelID[0] != '') {
			//get shoppers shipping/billing information
			$iparOrder_meta = get_post_meta($order_id);
			if ( !isset($iparOrder_meta["_shipping_email"][0]) ) {
				$iparOrderEmail = $iparOrder_meta["_billing_email"][0];
			} else {
				$iparOrderEmail = $iparOrder_meta["_shipping_email"][0];
			}
			//to get shopper's cart contents - SKU and Qty
			$items = $iparOrder->get_items();
			//create array of items for submitParcel call
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
			$submitParcelRequest = array(
				'Quote' => array(
					'key' => get_option("ipar_APIPrivateKey"),
					'DDP' => 'true',
					'Insurance' => null,
					'PromotionalCode' => null,
					'ResponseCurrencyCode' => get_woocommerce_currency(),
					'ServiceLevels' => array($iparServiceLevelID[0]),
					'SessionId' => null,
					'Discounts' => null,
					'Facility' => 0,
					'ControlNumber' => null,
					'BillingAddress' => array('FirstName' => $iparOrder_meta["_billing_first_name"][0], 'LastName' => $iparOrder_meta["_billing_last_name"][0], 'Street1' => $iparOrder_meta["_billing_address_1"][0], 'Street2' => $iparOrder_meta["_billing_address_2"][0], 'Street3' => "", 'PostCode' => $iparOrder_meta["_billing_postcode"][0], 'City' => $iparOrder_meta["_billing_city"][0], 'Region' => $iparOrder_meta["_shipping_state"][0], 'CountryCode' => $iparOrder_meta["_billing_country"][0], 'Email' => $iparOrderEmail, 'Phone' => $iparOrder_meta["_billing_phone"][0]),
					'ShippingAddress' => array('FirstName' => $iparOrder_meta["_shipping_first_name"][0], 'LastName' => $iparOrder_meta["_shipping_last_name"][0], 'Street1' => $iparOrder_meta["_shipping_address_1"][0], 'Street2' => $iparOrder_meta["_shipping_address_2"][0], 'Street3' => "", 'PostCode' => $iparOrder_meta["_shipping_postcode"][0], 'City' => $iparOrder_meta["_shipping_city"][0], 'Region' => $iparOrder_meta["_shipping_state"][0], 'CountryCode' => $iparOrder_meta["_shipping_country"][0], 'Email' => $iparOrderEmail, 'Phone' => $iparOrder_meta["_billing_phone"][0]),
					'Parcels' => array(array('ParcelWeight' => null, 'ParcelDimensions' => array('Length' => null, 'Width' => null, 'Height' => null), 'ProductList' => $iparItemList, 'ParcelValue' => null, 'ProvidedShipping' => null, 'CouponCode' => null, 'TrackingNumber' => null, 'OrderReference' => $order_id)),
					'Options' => null,
					'Transaction' => null
				),
				'ControlNumber' => null,
				'RespondWithLabel' => false,
				'IsTest' => false
			);
			$ipar_WPparcelRequest = array(
				'body' => json_encode($submitParcelRequest),
				'timeout' => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array('Content-Type' => 'application/json;charset=UTF-8'),
				'cookies' => array()
			);
			$ipar_submitParcelResponse = wp_remote_post( 'https://webservices.i-parcel.com/v2.0/api/SubmitParcel', $ipar_WPparcelRequest );
			$ipar_submitParcelResponseBody = wp_remote_retrieve_body($ipar_submitParcelResponse);
			$ipar_jsonSubmitParcelResp = json_decode($ipar_submitParcelResponseBody, TRUE);
			$iparTrackingNum = $ipar_jsonSubmitParcelResp['TrackingNumber'];
			$iparOrderTax = (float)$ipar_jsonSubmitParcelResp['Charges']['Tax'];
			$iparOrderDuty = (float)$ipar_jsonSubmitParcelResp['Charges']['Duty'];
			$iparTotalTaxAndDuty = $iparOrderTax + $iparOrderDuty;
			//Set tax value from submitParcel call
			$iparOrder->add_tax('', $tax_amount = $iparTotalTaxAndDuty, $shipping_tax_amount = 0, '', 'Tax & Duty', '');
			//update order with a note with tracking number and barcode
			$iparOrder->add_order_note('Order Submitted to UPS i-parcel<br/><br/>UPS i-parcel Tracking Number: '. $iparTrackingNum .'<br/><br/><a href="https://webservices.i-parcel.com/api/Barcode?key='. get_option('ipar_APIPrivateKey') .'&trackingnumber='. $iparTrackingNum .'" target="_blank">Click here</a> to generate your barcode<br/><br/><a href="https://webservices.i-parcel.com/api/GetLabel?key='. get_option('ipar_APIPrivateKey') .'&trackingnumber='. $iparTrackingNum .'" target="_blank">Click here</a> to generate your label');
		}
	} else {
		//merchant selected to not submit parcel.
	}	
}
//front-end JS to hide payment methods based on shipping selection
add_action( 'wp_enqueue_scripts', 'include_iparJS' );
function include_iparJS()
{
    // Register the script like this for a plugin:
    wp_register_script( 'ipar-frontend-script', plugins_url( '/js/iparscript.js', __FILE__ ), array( 'jquery' ) );
    // Register the script like this for a theme:
    wp_register_script( 'ipar-frontend-script', get_template_directory_uri() . '/js/iparscript.js', array( 'jquery' ) );
    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'ipar-frontend-script' );
}
/* Close WooCommerce Check */
}
?>