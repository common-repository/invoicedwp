<?php
/**
 * Post-Type Setup
 *
 * @package     InvoicedWP
 * @since       1.0.0
 */




/**
 * [ryno_setup_staff_init description]
 * @return [type] [description]
 */
function iwp_setup_init() {
	$permalinks = get_option( 'invoicedwp_permalinks' );
	$invoice_permalink = empty( $permalinks['invoicedwp_slug'] ) ? _x( 'invoiced', 'slug' ) : $permalinks['invoicedwp_slug'];

	$labels = array(
		'name' 					=> _x('Invoices', 'post type general name', 'iwp-txt' ),
		'singular_name' 		=> _x('Invoice', 'post type singular name', 'iwp-txt' ),
		'add_new' 				=> _x('Add Invoice', 'Invoice', 'iwp-txt' ),
		'add_new_item' 			=> __('Add Invoice', 'iwp-txt' ),
		'edit_item' 			=> __('Edit Invoice', 'iwp-txt' ),
		'new_item' 				=> __('New Invoice', 'iwp-txt' ),
		'view_item' 			=> __('View Invoice', 'iwp-txt' ),
		'search_items' 			=> __('Search Invoices', 'iwp-txt' ),
		'exclude_from_search' 	=> true,
		'not_found' 			=> __('No invoices found', 'iwp-txt' ),
		'not_found_in_trash' 	=> __('No invoices found in Trash', 'iwp-txt' ),
		'parent_item_colon' 	=> '',
		'all_items' 			=> 'Invoiced WP',
		'menu_name' 			=> 'Invoiced WP'

	);

	$args = array(
		'description'         	=> __( 'This is where you can add invoices to your site.', 'iwp-txt' ),
		'labels' 				=> $labels,
		'public' 				=> true,
		'show_ui' 				=> true,
		'capability_type' 		=> 'page',
		'map_meta_cap'        	=> true,
		'publicly_queryable' 	=> true,
		'exclude_from_search' 	=> false,
		'show_in_menu' 			=> true,
		'show_in_nav_menus'   	=> true,
		'hierarchical' 			=> false,
		'rewrite'             	=> $invoice_permalink ? array( 'slug' => untrailingslashit( $invoice_permalink ), 'with_front' => false, 'feeds' => true ) : false,
		'query_var' 			=> true,
		'has_archive' 			=> true,
		'menu_icon'				=> 'dashicons-media-text',
		'menu_position' 		=> 20,				
		'supports' 				=> array( 'title', 'editor' )

	);

	register_post_type( 'invoicedwp', $args );

	$show_in_menu = 'invoicedwp';

	$temp_labels = array(
		'name'      			=> __( 'Templates', 'iwp-txt' ),
		'singular_name'			=> __( 'Template', 'iwp-txt' ),
		'menu_name'    			=> _x( 'Templates', 'Admin menu name', 'iwp-txt' ),
		'add_new'     			=> __( 'Add Template', 'iwp-txt' ),
		'add_new_item'    		=> __( 'Add New Templates', 'iwp-txt' ),
		'edit'      			=> __( 'Edit', 'iwp-txt' ),
		'edit_item'    			=> __( 'Edit Templates', 'iwp-txt' ),
		'new_item'     			=> __( 'New Templates', 'iwp-txt' ),
		'view'      			=> __( 'View Templatess', 'iwp-txt' ),
		'view_item'    			=> __( 'View Templates', 'iwp-txt' ),
		'search_items'    		=> __( 'Search Templatess', 'iwp-txt' ),
		'not_found'    			=> __( 'No Templatess found', 'iwp-txt' ),
		'not_found_in_trash'	=> __( 'No Templatess found in trash', 'iwp-txt' ),
		'parent'     			=> __( 'Parent Templates', 'iwp-txt' )

	);


	$args = array(
		'labels' 				=> $temp_labels,
		'public' 				=> false,
		'publicly_queryable' 	=> false,
		'show_ui' 				=> true,
		'query_var' 			=> true,
		'rewrite' 				=> true,
		'capability_type' 		=> 'page',
		'has_archive' 			=> false,
		'menu_position' 		=> 20,
		'show_in_menu' 	 		=> 'edit.php?post_type=invoicedwp',
		'hierarchical' 			=> false,
		'supports'   			=> array( 'title', 'comments' )

	);

	register_post_type( 'invoicedWP_template', $args );


	register_post_status( 'quote', array(
		'label'                     => __( 'Quotes', 'iwp-txt' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Quote <span class="count">(%s)</span>', 'Quote <span class="count">(%s)</span>', 'iwp-txt' )
	) );
	register_post_status( 'needPay', array(
		'label'                     => __( 'Needs Payment', 'iwp-txt' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Needs Payment <span class="count">(%s)</span>', 'Needs Payment <span class="count">(%s)</span>', 'iwp-txt' )
	) );
	register_post_status( 'paid', array(
		'label'                     => __( 'Paid', 'iwp-txt' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Paid <span class="count">(%s)</span>', 'Paid <span class="count">(%s)</span>', 'iwp-txt' )
	) );

}

/**
 * Adds custom columns for staff-member CPT admin display
 *
 * @param    array    $cols    New column titles
 * @return   array             Column titles
 */
function iwp_custom_columns( $cols ) {
	$cols = array(
		'cb'			=> '<input type="checkbox" />',
		'title'			=> __( 'Name', 'iwp-txt' ),
		'paid'			=> __( 'Amount Paid', 'iwp-txt' ),
		'recipient'		=> __( 'Recipient', 'iwp-txt' ),
		//'invoiceID'		=> __( 'Invoice ID', 'iwp-txt' ),
		'dueDate'		=> __( 'Due Date', 'iwp-txt' ),
		'date'			=> __( 'Creation Date', 'iwp-txt' ),

	);

	return $cols;

}

/**
 * [ryno_staff_display_custom_columns description]
 * @param  [type] $column [description]
 * @return [type]         [description]
 */
function iwp_display_custom_columns( $column ) {
	global $post;

	$iwp = get_post_meta( $post->ID, '_invoicedwp', true );
	$curencyPos = iwp_get_option( 'currency_position', 'before' );

	switch ( $column ) {
		case "paid":
			$payments 	= isset( $iwp['invoice_totals']["payments"] ) ? $iwp['invoice_totals']["payments"] : '0';
			$total 		= isset( $iwp['invoice_totals']["total"] ) ? $iwp['invoice_totals']["total"] : '0';
			if ( $curencyPos == "before" ) {
				echo iwp_currency_symbol() . iwp_format_amount( $payments ) . ' / ' . iwp_currency_symbol() . iwp_format_amount( $total );

			} else {
				echo iwp_format_amount( $payments ) . iwp_currency_symbol() . ' / ' . iwp_format_amount( $total ) . iwp_currency_symbol();
			}
			$unpaid = $total - $payments;
			if( $total > 0 )
				echo '<br />Unpaid: <strong>' . iwp_currency_symbol() . iwp_format_amount( $unpaid ) . '</strong>';

		break;
		case "recipient":
			$firstName 	= isset( $iwp["user_data"]["first_name"] ) ? $iwp["user_data"]["first_name"] : '-';
			$lastName 	= isset( $iwp["user_data"]["last_name"] ) ? $iwp["user_data"]["last_name"] : '-';

			echo $firstName . ' ' . $lastName;
		break;
		case "invoiceID":
			echo "-";
		break;
		case "dueDate":
			$dueDate = isset( $iwp["paymentDueDate"] ) ? $iwp["paymentDueDate"] : '-';

			if( $dueDate == 0 ) {
				echo "-";
			} else {
				echo $iwp["paymentDueDateText"];
			}
		break;

	}
}


function include_invoice_template_function( $template_path ) {

    if ( get_post_type() == 'invoicedwp' ) {

        if ( is_single() ) {
            if ( $theme_file = locate_template( array ( 'single-invoicedwp.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = plugin_dir_path( __FILE__ ) . 'single-invoicedwp.php';
            }
        }
    }
    return $template_path;
}





