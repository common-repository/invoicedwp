<?php
if ( isset( $_POST['version'] ) ) {
  $post_data = iwp_sanitize( $_POST['version'] );
}

$values = get_post_meta( $template, '_invoicedwp', true );
$values = $values['lineItems'];
$count = count( $values["iwp_invoice_name"] );

for( $i = 0; $i < $count; $i++ ) { ?>

	<script>

	 		var rowNumber = jQuery('#invoicedDisplay tbody tr').length;

			lastTR = jQuery( '#invoicedDisplay tbody#invoiced_rows' ).find("tr:last"),
			trNew = lastTR.clone()
						  .find("input:text").val("").end();

			lastTR.after(trNew);

			nameID 	= 'iwp_invoice_name[' + rowNumber + ']';
			descId 	= 'iwp_invoice_description[' + rowNumber + ']';
			qtyId 	= 'iwp_invoice_qty[' + rowNumber + ']';
			priceId = 'iwp_invoice_price[' + rowNumber + ']';
			totalId = 'iwp_invoice_total[' + rowNumber + ']';

			jQuery( '#invoicedDisplay tbody tr:last').find('.input_name').attr('id', nameID ).attr('name', nameID).val("<?php echo $values["iwp_invoice_name"][$i]; ?>");
			jQuery( '#invoicedDisplay tbody tr:last').find('.input_description').attr('id', descId ).attr('name', descId ).val("<?php echo $values["iwp_invoice_description"][$i]; ?>");
			jQuery( '#invoicedDisplay tbody tr:last').find('.input_qty').attr('id', qtyId ).attr('name', qtyId ).val("<?php echo $values["iwp_invoice_qty"][$i]; ?>");
			jQuery( '#invoicedDisplay tbody tr:last').find('.input_price').attr('id', priceId ).attr('name', priceId ).val("<?php echo $values["iwp_invoice_price"][$i]; ?>");
			jQuery( '#invoicedDisplay tbody tr:last').find('.input_total').attr('id', totalId ).attr('name', totalId ).val("<?php echo $values["iwp_invoice_total"][$i]; ?>");

	</script>

<?php } ?>
