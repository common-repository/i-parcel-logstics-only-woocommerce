<?php

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

if(isset($_GET['sync-status']) && $_GET['sync-status'] === 'syncing') { ?>

	<div class="wrap">

		<h2>i-parcel Global Access Catalog Sync</h2>

		<p>We are syncing your catalog in the background - hang tight.</p>

	</div>

	<?php

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

		echo('<p>Total Products: '. count($WooCatalogSize) .'</p>');

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

			if( $ipar_synced < count($WooCatalogSize) ) {

				echo('<p>'. $ipar_synced .' out of '. count($WooCatalogSize) .' synced!</p>');

			} else {

				echo('All Done!');

			}

		}

	

		wp_redirect( admin_url( '/admin.php?page=ipar_catalog.php&sync-status=success' ) );

	} else { ?>

	<script>

		//scripts to select dropdowns with values from DB

		jQuery(document).ready(function() {

			var ipar_catalogConfiglVal = "<?php echo esc_attr( get_option('ipar_catalogConfig') ); ?>";

			var ipar_cat_attribute1Val = "<?php echo esc_attr( get_option('ipar_cat_attribute1') ); ?>";

			var ipar_cat_attribute2Val = "<?php echo esc_attr( get_option('ipar_cat_attribute2') ); ?>";

			var ipar_cat_attribute3Val = "<?php echo esc_attr( get_option('ipar_cat_attribute3') ); ?>";

			var ipar_cat_attribute4Val = "<?php echo esc_attr( get_option('ipar_cat_attribute4') ); ?>";

			var ipar_cat_CountryOfOriginVal = "<?php echo esc_attr( get_option('ipar_cat_CountryOfOrigin') ); ?>"

			var ipar_cat_HTSCodesVal = "<?php echo esc_attr( get_option('ipar_cat_HTSCodes') ); ?>";

			var ipar_cat_shipAloneVal = "<?php echo esc_attr( get_option('ipar_cat_shipAlone') ); ?>";

			var ipar_cat_priceTypeVal = "<?php echo esc_attr( get_option('ipar_cat_priceType') ); ?>";

			jQuery('select#ipar_catalogConfig').find('option[value="'+ ipar_catalogConfiglVal +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_attribute1').find('option[value="'+ ipar_cat_attribute1Val +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_attribute2').find('option[value="'+ ipar_cat_attribute2Val +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_attribute3').find('option[value="'+ ipar_cat_attribute3Val +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_attribute4').find('option[value="'+ ipar_cat_attribute4Val +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_CountryOfOrigin').find('option[value="'+ ipar_cat_CountryOfOriginVal +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_HTSCodes').find('option[value="'+ ipar_cat_HTSCodesVal +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_shipAlone').find('option[value="'+ ipar_cat_shipAloneVal +'"]').attr('selected','selected');

			jQuery('select#ipar_cat_priceType').find('option[value="'+ ipar_cat_priceTypeVal +'"]').attr('selected','selected');

			//catalog mapping validation

			jQuery('form#ipar_cat_upload_form').on('submit', function() {

				// do validation here

				if(jQuery('form#ipar_form_catalog select#ipar_cat_CountryOfOrigin').val() == 'none' && jQuery('form#ipar_form_catalog input#ipar_cat_OverrideCountryOfOrigin').val() == '') {

					alert('The UPS i-parcel catalog needs a country of origin for each product.');

					return false;

				}

			});

		});

	</script>

	<?php

	global $wpdb;

	$ProductOptions = $wpdb->get_results( 

		"SELECT DISTINCT attribute_name, attribute_label FROM ". $wpdb->prefix . 'woocommerce_attribute_taxonomies'

	);

	?>

	<div class="wrap">

	<h2>i-parcel Global Access Catalog Sync</h2>

	<p>In order for i-parcel to calcuate Tax, Duty and Shipping costs, we need a copy of your catalog.  We also use your catalog for to determine shipping ineligibility and other Global Access features.</p>

	<p>You can feed us a catalog directly from your WordPress admin.  Configure your feed below, save your changes an submit your catalog to i-parcel.</p>

	<?php

	if( isset($_GET['settings-updated']) ) { ?>

		<div id="message" class="updated">

			<p><strong><?php _e('Settings saved.') ?></strong></p>

		</div>

	<?php } ?>

	<?php

	if(isset($_GET['sync-status']) && $_GET['sync-status'] === 'success' ) { ?>

		<div id="message" class="updated">

			<p><strong>Catalog has been uploaded.</strong></p>

		</div>

	<?php } ?>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">

				<form name="ipar_form_catalog" id="ipar_form_catalog" method="post" action="options.php">

				<?php settings_fields( 'iparcatalog-group' ); ?>

				<?php do_settings_sections( 'iparcatalog-group' ); ?>

					<!-- Catalog Config -->

					<div class="postbox">

						<h3 class="hndle"><span>Configuration</span></h3>

						<div class="inside">

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_catalogConfig" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Auto Catalog Updates:</label><select name="ipar_catalogConfig" id="ipar_catalogConfig" style="display:inline-block;"><option value="0">Disabled</option><option value="1">On product save</option><option value="2">Daily Catalog Sync</option></select>

							</div>

						</div>

					</div>

					<!-- Catalog Mapping -->

					<div class="postbox">

						<h3 class="hndle"><span>Catalog Mapping Attributes</span></h3>

						<div class="inside">

							<p>We automatically pull in all of the required fields which include <strong>Product SKU</strong>, <strong>Product Name</strong>, <strong>Product Categories</strong>, <strong>Product Price</strong>, <strong>Product Description</strong>, <strong>Product Dimensions</strong>, <strong>Product Weight</strong> and <strong>Product URL</strong>.  Any additional info you can feed to our catalog will help our classification department apply the proper HTS Codes to your products to accurately calcuate tax, duty and shipping costs.</p>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_attribute1" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Attribute 2</label>

								<select name="ipar_cat_attribute1" id="ipar_cat_attribute1" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  //echo $value->meta_key."<br/>";

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_attribute2" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Attribute 3</label>

								<select name="ipar_cat_attribute2" id="ipar_cat_attribute2" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_attribute3" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Attribute 4</label>

								<select name="ipar_cat_attribute3" id="ipar_cat_attribute3" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_attribute4" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Attribute 5</label>

								<select name="ipar_cat_attribute4" id="ipar_cat_attribute4" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_CountryOfOrigin" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Country of Origin</label>

								<select name="ipar_cat_CountryOfOrigin" id="ipar_cat_CountryOfOrigin" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

						   <div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_OverrideCountryOfOrigin" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Country of Origin (Override)</label>

								<input type="text" maxlength="2" value="<?php echo esc_attr( get_option('ipar_cat_OverrideCountryOfOrigin') ); ?>" name="ipar_cat_OverrideCountryOfOrigin" id="ipar_cat_OverrideCountryOfOrigin" style="display:inline-block;width:232px;" />

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_HTSCodes" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">HTS Codes</label>

								<select name="ipar_cat_HTSCodes" id="ipar_cat_HTSCodes" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

							<div style="padding:0px 0px 10px 0px;">

								<label for="ipar_cat_shipAlone" style="text-align:left;min-width:175px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Ship Alone</label>

								<select name="ipar_cat_shipAlone" id="ipar_cat_shipAlone" style="display:inline-block;">

									<option value="none">none</option>

									<?php

									foreach($ProductOptions as $value)

									{

									  echo '<option value="pa_'.$value->attribute_name.'">'.$value->attribute_label.'</option>';

									}

									?>

								</select>

							</div>

						</div>

					</div>

					<!-- Save Changes Button -->

					<div>

						<?php submit_button('Save Changes', 'primary', '', false); ?>

						<?php

							$submitCatalogURL = wp_nonce_url('admin.php?page=ipar_catalog.php&sync-status=syncing');

						?>

					   </form>

						<form action="<?php echo($submitCatalogURL); ?>" id="ipar_cat_upload_form" method="post" style="display:inline-block;margin-left:10px;">

							<?php submit_button('Upload Catalog', 'secondary', '', false); ?>

						</form>

					</div>

			</div>

			<div id="postbox-container-1">

				<div class="postbox">

					<h3 class="hndle"><span>Need Help?</span></h3>

					<div class="inside">

						<p>If you need help with any part of the boarding process or are running into issues, please contact us.</p>

						<p>You can log into your UPS i-parcel Dashboard at anytime at <a href="https://globalaccess.i-parcel.com/" target="_blank">globalaccess.i-parcel.com</a>.</p>

						<p>You can contact our IT department by visiting <a href="https://www.i-parcel.com/en/it-helpdesk/" target="_blank">i-parcel.com/en/it-helpdesk</a></p>

					</div>

				</div>

			</div>

		</div>

	</div>

<?php } ?>