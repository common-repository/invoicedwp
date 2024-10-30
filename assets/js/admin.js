jQuery(document).ready(function( $ ) {

	if ( ! window.console ) {
		window.console = {
			log : function(str) {
				// alert(str);
			}
		};
	}

	setInterval(function(){ calculateTotal(); }, 250);

	$( document ).on( 'click', '.add_row', function( e ) {
		e.preventDefault();

 		var rowNumber = $('#invoicedDisplay #invoiced_rows tr').length;

		lastTR = $( '#invoicedDisplay tbody#invoiced_rows' ).find("tr:last"),
		trNew = lastTR.clone()
					  .find("input, textarea").val("").end();

		lastTR.after(trNew);

		nameID 	= 'iwp_invoice_name[' + rowNumber + ']';
		descId 	= 'iwp_invoice_description[' + rowNumber + ']';
		qtyId 	= 'iwp_invoice_qty[' + rowNumber + ']';
		priceId = 'iwp_invoice_price[' + rowNumber + ']';
		totalId = 'iwp_invoice_total[' + rowNumber + ']';

		$( '#invoicedDisplay tbody tr:last').find('.input_name').attr('id', nameID ).attr('name', nameID);
		$( '#invoicedDisplay tbody tr:last').find('.input_description').attr('id', descId ).attr('name', descId );
		$( '#invoicedDisplay tbody tr:last').find('.input_qty').attr('id', qtyId ).attr('name', qtyId );
		$( '#invoicedDisplay tbody tr:last').find('.input_price').attr('id', priceId ).attr('name', priceId );
		$( '#invoicedDisplay tbody tr:last').find('.input_total').attr('id', totalId ).attr('name', totalId );

		$( '#invoicedDisplay tbody tr:last').find('.input_description').hide();
		$( '#invoicedDisplay tbody tr:last').find('.toggleDescription').show();
    });


	$( 'body' ).on( 'change', '.selectTemplate', function( e ) {
 		var template = $(this).val();
 		var rowNumber = $('#invoicedDisplay tbody tr').length;

	    var data = {
			'action': 'iwp_add_template_row',
			'version': rowNumber,
			'template': template
		};

		$.post(ajaxurl, data, function(response) {
			$("tbody#invoiced_rows").append( response );
		});
    });



	$( 'body' ).on( 'change keyup blur', '#discountType, #discountAmount', function( e ) {
 		var discountType 	= $( '#discountType' ).val();

 		if( discountType == "percent" ){
 			$( ".percentSymbol").show();
 			$( ".currencySymbol").hide();
 		}

 		if( discountType == "amount" ){
 			$( ".percentSymbol").hide();
 			$( ".currencySymbol").show();
 		}

 		if( ! $( '#discountAmount' ).val() ) {
			$( '#discountAmount' ).val( 0 );
		}

    });

	// If the remove button is clicked
    $('body').on('click', 'td.remove', function(){
		$(this).closest('tr').remove();
		return false;
	});

	// If the remove discount button is clicked
	$('body').on('click', 'td.remove_discount', function(){
		$( '.add_discount' ).show();
		$( '.column-invoice-details-discounts' ).hide();
		return false;
	});

	// The Add Discount button is clicked
	$( 'body' ).on('click', '.add_discount', function(){
		$(this).closest('table').find('tfoot').prepend( $( this ).data( 'row' ) );
		$('body').trigger('row_added');
		$('.column-invoice-details-discounts').show();
		$(this).hide();
		return false;
	});

	// The toggle Description button is clicked
	$('body').on('click', '.toggleDescription', function(){
		$(this).closest('td').find('.input_description').show();
		$(this).hide();
		return false;
	});

	// Sort the rows in the inventory
	$('#invoiced_rows, #pricing_rows').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: '.sort',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65,
		placeholder: 'wc-metabox-sortable-placeholder',
		start:function(event,ui){
			ui.item.css('background-color','#f6f6f6');
		},
		stop:function(event,ui){
			ui.item.removeAttr('style');
		}
	});

	// Function to get the user data if the account exists
	$( ".iwp_email_selection" ).change( function () {

	    $.post( ajaxurl, {
	      action: 'iwp_get_user_data',
	      user_email: $( this ).val()
	    }, function ( result ) {
	      if ( result ) {
	        user_data = result.user_data;

	        for ( var field in user_data ) {
	          $( '.iwp_newUser .iwp_' + field ).val( user_data[field] );

	        }
	        $( '.makeNewAccount' ).hide();
	      }
	    }, 'json' );

	  } );




	function calculateTotal( ) {
		// Calculate total for the invoice
		var sum = 0;

		$( 'tbody#invoiced_rows tr' ).each( function() {
			if( !isNaN( $( this ).find('.input_qty').val() ) ) {
				var qty = $( this ).find('.input_qty').val();
			} else {
				var qty = 0;
			}

			if( !isNaN( $( this ).find('.input_price').val() ) ) {
				var price = $( this ).find('.input_price').val();
			} else {
				var price = 0;
			}

			var total = $( this ).find('.input_qty').val() * $( this ).find('.input_price').val();

			$( this ).find('.input_total').val( parseFloat( total ).toFixed(2) );
			$( this ).find('.hidden_total').val( $( this ).find('.input_total').val() );

			sum += parseFloat(total);
		});

		$(".calculate_invoice_subtotal").val( parseFloat( sum ).toFixed(2) );
		$(".hidden_subtotal").val( parseFloat( sum ).toFixed(2) );

		var subTotal 		= $( '.calculate_invoice_subtotal' ).val();
		var taxAmount		= 0;
		var discountAmount 	= $( '#discountAmount' ).val();
 		var discountType 	= $( '#discountType' ).val();
 		var totalDiscount	= 0;

		if ( $( '#iwp_tax_rate' ).val() > 0 ) {
			taxAmount = subTotal * ( $( '#iwp_tax_rate' ).val() / 100 );
		}

 		if( discountType == "percent" ){
 			var totalSubtotal = parseFloat( subTotal ) + parseFloat( taxAmount );
 			var discount = discountAmount / 100 ;

 			totalDiscount = totalSubtotal * discount;
 		}

 		if( discountType == "amount" ){
 			totalDiscount = discountAmount;
 		}

 		$( ".calculate_discount_total").val( parseFloat( totalDiscount ).toFixed(2) );
 		$( ".calculate_invoice_tax " ).val( parseFloat( taxAmount ).toFixed(2) );

		var grandTotal = (parseFloat( subTotal ) + parseFloat( taxAmount )) - parseFloat( totalDiscount );

		$( '.calculate_invoice_grandtotal').val( parseFloat( grandTotal ).toFixed(2) );
	}

	// Invoice Setup functions

	$("#reoccuringPayment").change(function() {
	    if(this.checked) {
	        $("#reoccuringPaymentText").show();
	    } else {
	    	$("#reoccuringPaymentText").hide();
	    }
	});


	$("#minPayment").change(function() {
	    if(this.checked) {
	        $("#minPaymentText").show();
	    } else {
	    	$("#minPaymentText").hide();
	    }
	});

	$("#dueDate").change(function() {
	    if(this.checked) {
	        $("#dueDateText").show();
	    } else {
	    	$("#dueDateText").hide();
	    }
	});


	$( '#makeAccount' ).change(function() {
	    if(this.checked) {
	        $("#iwp_email_selection").show();
	        $("#iwp_email_selection").val( '' );
	        $("#s2id_autogen1").hide();
	        $(".makeNewAccount").hide();
	    } else {
	    	$("#makeAccountText").hide();
	    }
	});

	/**
	 * Settings screen JS
	 */
	var IWP_Settings = {

		init : function() {
			this.general();
		},

		general : function() {

			if( $('.iwp-color-picker').length ) {
				$('.iwp-color-picker').wpColorPicker();
			}
			// WP 3.5+ uploader
			var file_frame;
			window.formfield = '';

			$('body').on('click', '.iwp_settings_upload_button', function(e) {

				e.preventDefault();

				var button = $(this);

				window.formfield = $(this).parent().prev();

				// If the media frame already exists, reopen it.
				if ( file_frame ) {
					//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
					file_frame.open();
					return;
				}

				// Create the media frame.
				file_frame = wp.media.frames.file_frame = wp.media({
					frame: 'post',
					state: 'insert',
					title: button.data( 'uploader_title' ),
					button: {
						text: button.data( 'uploader_button_text' )
					},
					multiple: false
				});

				file_frame.on( 'menu:render:default', function( view ) {
					// Store our views in an object.
					var views = {};

					// Unset default menu items
					view.unset( 'library-separator' );
					view.unset( 'gallery' );
					view.unset( 'featured-image' );
					view.unset( 'embed' );

					// Initialize the views in our view object.
					view.set( views );
				} );

				// When an image is selected, run a callback.
				file_frame.on( 'insert', function() {

					var selection = file_frame.state().get('selection');
					selection.each( function( attachment, index ) {
						attachment = attachment.toJSON();
						window.formfield.val(attachment.url);
					});
				});

				// Finally, open the modal
				file_frame.open();
			});


			// WP 3.5+ uploader
			var file_frame;
			window.formfield = '';
		}

	}
	IWP_Settings.init();

});
