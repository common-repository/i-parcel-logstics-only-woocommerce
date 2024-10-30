jQuery( document ).ajaxComplete(function() {

	var selectedShippingMethod = jQuery('#order_review td[data-title="Shipping"] li:has(input:checked) input').attr('value');

	//Update Tax label on cart

	if(jQuery('.cart-collaterals').length) {

		if(jQuery('.cart-collaterals tr.shipping input.shipping_method').attr('value').indexOf('ups_iparcel_') === 0 || jQuery('.cart-collaterals tr.shipping li:has(input:checked) input').attr('value').indexOf('ups_iparcel_') === 0) {

			jQuery('.cart-collaterals tr.tax-total th').text('Tax & Duty');

		} else {
			
			jQuery('.cart-collaterals tr.tax-total th').text('Tax & Duty');
			
		}

	}

	//Update Tax label on checkout

	if(jQuery('form.woocommerce-checkout').length && jQuery('table.woocommerce-checkout-review-order-table tr.shipping').length) {

		if(jQuery('table.woocommerce-checkout-review-order-table tr.shipping td input.shipping_method[value*="ups_iparcel_"]').length || jQuery('table.woocommerce-checkout-review-order-table tr.shipping td input:checked[value*="ups_iparcel_"]').length) {

			jQuery('#order_review tfoot tr.tax-total th').text('Tax & Duty');

		} else {

			jQuery('#order_review tfoot tr.tax-total th').text('Tax');

		}

	}

	jQuery('span.woocommerce-Price-amount').each(function() {

		jQuery(this).text(jQuery(this).text());

	});

});