<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<script>
jQuery(document).ready(function() {		
	jQuery("#GetKey").click(function () {
		var DashRequest = new Object();
		DashRequest.username = jQuery('input#ipar_DashUsername').val();
		DashRequest.password = jQuery('input#ipar_DashPassword').val();
		jQuery.ajax({
			url: 'https://globalaccess.i-parcel.com/api/PublicKeys',
			type: 'POST',
			dataType: 'json',
			data: DashRequest,
			success: function (data, textStatus, xhr) {
				jQuery('#ipar_form_config').show();
				jQuery('.APIusernamePassword').hide();
				jQuery('#ipar_form_config input#ipar_APIKey').val(data['Results'][0]['PublicKey']);
				jQuery('#ipar_form_config input#ipar_APIPrivateKey').val(data['Results'][0]['PrivateKey']);
				jQuery('.iparResponse').html('<div class="updated notice"><p><b>SUCCESS!</b>  Your keys have been added to your settings.  Be sure to click <b>Save Changes!</b></p></div>');
			},
			error: function (xhr, textStatus, errorThrown) {
				jQuery('.iparResponse').html('<div class="error notice"><p>'+ xhr['responseJSON']['Error'] +'</p></div>');
			}
		});
	});
});
</script>
<div class="wrap">
<h2>i-parcel Global Access Configuration</h2>
<?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.') ?></strong></p>
    </div>
<?php } ?>
	<div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
				<?php settings_fields( 'iparconfig-group' ); ?>
                <?php do_settings_sections( 'iparconfig-group' ); ?>
                <?php if ( esc_attr( get_option('ipar_APIKey') ) === '') { ?>
					<div class="iparResponse"></div>
					<!-- Dashboard Username and Password -->
					<div class="notice notice-info is-dismissible">
						<p><?php _e( 'Enter your UPS i-parcel Global Access Username and Password to fetch your API Keys.', '' ); ?></p>
					</div>
					<div class="postbox APIusernamePassword">
						<h3 class="hndle"><span>i-parcel Dashboard Credentials</span></h3>
						<div class="inside">
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Username: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Your UPS i-parcel Dashboard Username.</span></div></label><input name="ipar_DashUsername" id="ipar_DashUsername" value="" type="text" style="display:inline-block;width:300px;" />
							</div>
							<div style="padding:0px 0px 10px 0px;">
								<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Password: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Your UPS i-parcel Dashboard Password.</span></div></label><input name="ipar_DashPassword" id="ipar_DashPassword" value="" type="password" style="display:inline-block;width:300px;" />
							</div>
							<div>
								<div class="button button-primary" id="GetKey">Get Your API Key</div>
							</div>
						</div>
					</div>
					<form name="ipar_form_config" id="ipar_form_config" method="post" action="options.php" style="display:none;">
						<?php settings_fields( 'iparconfig-group' ); ?>
          				<?php do_settings_sections( 'iparconfig-group' ); ?>
						<!-- API Key -->
						<div class="postbox">
							<h3 class="hndle"><span>API Key</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Public API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Private API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIKey" id="ipar_APIKey" value="" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIPrivateKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Private API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Public API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIPrivateKey" id="ipar_APIPrivateKey" value="" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
						</div>
						<!-- Save Changes Button -->
						<div>
							<?php submit_button('Save Changes', 'primary', '', false); ?>
						</div>
					</form>
					<!-- Fire off quest to get API key -->
				<?php } else { ?>
					<?php
						if ( get_option('ipar_catalogConfig') === '' ) { ?>
							<div class="notice notice-info is-dismissible">
								<p><?php _e( 'Great!  Your API Keys are set - now lets sync your catalog!', '' ); ?></p>
							</div>						
					<?php } ?>
					<form name="ipar_form_config" id="ipar_form_config" method="post" action="options.php">
						<?php settings_fields( 'iparconfig-group' ); ?>
          				<?php do_settings_sections( 'iparconfig-group' ); ?>
						<!-- API Key -->
						<div class="postbox">
							<h3 class="hndle"><span>API Keys and Script ID</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Public API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Private API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIKey" id="ipar_APIKey" value="<?php echo esc_attr( get_option('ipar_APIKey') ); ?>" type="text" style="display:inline-block;width:300px;" />
								</div>
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_APIPrivateKey" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Private API Key: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Public API Key from the UPS i-parcel Dashboard.</span></div></label><input name="ipar_APIPrivateKey" id="ipar_APIPrivateKey" value="<?php echo esc_attr( get_option('ipar_APIPrivateKey') ); ?>" type="text" style="display:inline-block;width:300px;" />
								</div>
							</div>
						</div>
						<div class="postbox">
							<h3 class="hndle"><span>Options</span></h3>
							<div class="inside">
								<div style="padding:0px 0px 10px 0px;">
									<label for="ipar_LandedCostOnly" style="text-align:left;min-width:210px;margin-right:10px;display:inline-block;vertical-align:initial!important;">Landed Cost Calculation Only: <div class="ipartooltip"><span class="dashicons dashicons-editor-help"></span><span class="ipartooltiptext">Setting to "True" will supress the API call whichs sends data to your UPS i-parcel Dashboard.</span></div></label>
									<select name="ipar_LandedCostOnly" id="ipar_LandedCostOnly" style="display:inline-block;">
										<?php
											if(esc_attr( get_option('ipar_LandedCostOnly') ) === 'false' || esc_attr( get_option('ipar_LandedCostOnly') === '') ) {
												echo('<option value="true">True</option><option value="false" selected="selected">False</option>');
											} else {
												echo('<option value="true" selected="selected">True</option><option value="false">False</option>');
											}
										?>
									</select>
								</div>
							</div>
						</div>
						<!-- Save Changes Button -->
						<div>
							<?php submit_button('Save Changes', 'primary', '', false); ?>
						</div>
					</form>
				<?php } ?>
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
			<?php //ipar_getOrderAndSubmitParcel(364); ?>
   		</div>        
    </div>
</div>