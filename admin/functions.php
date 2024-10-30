<?php
/**
 * Helper Functions
 *
 * @package     InvoicedWP
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


function myInvoiceSettings() {
    global $post;

    wp_nonce_field('iwp_extra_nonce', 'iwp_extra_nonce' );

    if ( ( get_post_type($post) == 'invoicedwp' ) || ( get_post_type($post) == 'invoicedwp_template' ) ) {
        $custom = get_post_custom();
        
        $iwp = array( 'isQuote' => 0, 'reoccuringPayment' => 0, 'minPayment' => 0, 'minPaymentText' => '' );

        if( ! empty($custom['_invoicedwp']) ){
            $newiwp = maybe_unserialize( $custom['_invoicedwp'][0] );
            $iwp = array_merge( $iwp, $newiwp );
        }

        if ( get_post_type($post) == 'invoicedwp') {

            $display = '<input type="checkbox" name="isQuote" id="isQuote" value="' . $iwp['isQuote'] . '" ' . checked( $iwp['isQuote'], 1, false ) .' /> <label for="isQuote" class="">Quote</label><br />';

            $display .= '<input style="display: none;" type="checkbox" name="reoccuringPayment" id="reoccuringPayment" value="' . $iwp['reoccuringPayment'] . '" ' . checked( $iwp['reoccuringPayment'], 1, false ) .' /> <label for="reoccuringPayment" class="" style="display: none;">' . __( 'Reoccuring Bill', 'iwp-txt') . '</label>';
            $reoccuringPaymentText = isset( $iwp['reoccuringPaymentText'] ) ? $iwp['reoccuringPaymentText'] : '';
            $display .= '<input type="text" name="reoccuringPaymentText" id="reoccuringPaymentText" value="' . $reoccuringPaymentText . '" placeholder="' . __( 'Number of Days to Next Payment', 'iwp-txt') . '"  style="width: 100%; display: none;" /><br />'; // Need to add jQuery to update the place holder to be the invoice total.
            // Need to add jQuery to update this section to slide open when the box is checked.     
            
            $minPayment = 0;
            $displayMinPayment = 'display: none;';
            if( isset( $iwp['minPayment'] ) ) {
                if( $iwp['minPayment'] == 1 ){
                    //!  && ( 
                    $minPayment = $iwp['minPayment'];
                    $displayMinPayment = '';
                }
            }

            $display .= '<input type="checkbox" name="minPayment" id="minPayment" value="' . $minPayment . '" ' . checked( $minPayment, 1, false ) .' /> <label for="minPayment" class="">' . __( 'Minimum Payment', 'iwp-txt') . '</label>';    
            $minPaymentText = isset( $iwp['minPaymentText'] ) ? $iwp['minPaymentText'] : '';
            $display .= '<input type="text" name="minPaymentText" id="minPaymentText" value="' . $minPaymentText . '" placeholder="' . __( 'Minimum Payment', 'iwp-txt') . '"  style="width: 100%; ' . $displayMinPayment . '" /><br />'; // Need to add jQuery to update the place holder to be the invoice total.
            
            $paymentDueDate = '';
            $displayDueDate = 'display: none;';
            if( isset( $iwp['paymentDueDate'] ) ) {
                if( $iwp['paymentDueDate'] == 1 ) {
                    //isset( $iwp['paymentDueDate'] ) && ( 
                    $paymentDueDate = $iwp['paymentDueDate'];
                    $displayDueDate = '';
                }
            }

            $display .= '<input type="checkbox" name="paymentDueDate" id="dueDate" value="' . $paymentDueDate . '" ' . checked( $paymentDueDate, 1, false ) .' /> <label for="paymentDueDate" class="">' . __( 'Set Due Date', 'iwp-txt') . '</label>';    
            $paymentDueDateText = isset ( $iwp['paymentDueDateText'] ) ? $iwp['paymentDueDateText'] : '';
            $display .= '<input type="text" name="paymentDueDateText" id="dueDateText" value="' . $paymentDueDateText . '" placeholder="' . __( 'Due Date', 'iwp-txt') . '"  style="width: 100%; ' . $displayDueDate . ' " class="iwp-date-picker" /><br />'; // Need to add jQuery to update the place holder to be the invoice total.

            echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
            echo apply_filters( 'iwp_extra_options', $display );
            echo '</div>';


        }
    }
}
add_action( 'post_submitbox_misc_actions', 'myInvoiceSettings' );



function save_myInvoiceSettings($post_id) {
    
    if (!isset( $_POST['post_type'] ) )
        return;

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
        return;

    if ( ! ('invoicedwp' == $_POST['post_type'] ) || ! ( 'invoicedwp_template' == $_POST['post_type'] ) && ! current_user_can( 'edit_post', $post_id ) )
        return;

    if ( ! wp_verify_nonce( $_POST['iwp_extra_nonce'], 'iwp_extra_nonce' ) )
        return;

    $iwp = get_post_meta( $post_id, '_invoicedwp', true );
    
    if ( 'invoicedwp' == iwp_sanitize( $_POST['post_type'] ) ) {
        if (isset( $_POST['isQuote'] ) ) {
            $iwp['isQuote'] = 1;
        } else {
            $iwp['isQuote'] = 0;
        }

        if (isset( $_POST['makeAccount'] ) ) {
            $iwp['makeAccount'] = 1;
        } else {
            $iwp['makeAccount'] = 0;
        }
        

        if (isset( $_POST['reoccuringPayment'] ) ) {
            $iwp['reoccuringPayment'] = 1;
            $iwp['reoccuringPaymentText'] = iwp_sanitize( $_POST['reoccuringPaymentText'] );
        } else {
            $iwp['reoccuringPayment'] = 0;
            $iwp['reoccuringPaymentText'] = '';
        }
        
        if (isset( $_POST['minPayment'] ) ) {
            $iwp['minPayment'] = 1;
            $iwp['minPaymentText'] = iwp_sanitize( $_POST['minPaymentText'] );
        } else {
            $iwp['minPayment'] = 0;
            $iwp['minPaymentText'] = '';
        }   
        
        if (isset( $_POST['paymentDueDate'] ) ) {
            $iwp['paymentDueDate'] = 1;
            $iwp['paymentDueDateText'] = iwp_sanitize( $_POST['paymentDueDateText'] );
        } else {
            $iwp['paymentDueDate'] = 0;
            $iwp['paymentDueDateText'] = '';
        }
        
    }

    $count = 0;
    $i = 0;
    $reportOptions = array();

    $totalLines = count( iwp_sanitize( $_POST["iwp_invoice_price"] ) );

    // Handles the Invoice fields
    $invoicePost["iwp_invoice_name"]            = iwp_sanitize( $_POST["iwp_invoice_name"] );
    $invoicePost["iwp_invoice_description"]     = iwp_sanitize( $_POST["iwp_invoice_description"] );
    $invoicePost["iwp_invoice_qty"]             = iwp_sanitize( $_POST["iwp_invoice_qty"] );
    $invoicePost["iwp_invoice_price"]           = iwp_sanitize( $_POST["iwp_invoice_price"] );
    $invoicePost["iwp_invoice_total"]           = iwp_sanitize( $_POST["iwp_invoice_total"] );

    // Handles the Invoice Discounts
    $iwp["iwp_invoice_discount"]["name"]        = isset( $_POST["iwp_invoice_discount_name"] ) ? iwp_sanitize( $_POST["iwp_invoice_discount_name"] ) : '';
    $iwp["iwp_invoice_discount"]["type"]        = isset( $_POST["iwp_invoice_discountType"] ) ? iwp_sanitize( $_POST["iwp_invoice_discountType"] ) : '';
    $iwp["iwp_invoice_discount"]["discount"]    = isset( $_POST["iwp_invoice_discount"] ) ? iwp_sanitize( $_POST["iwp_invoice_discount"] ) : '';

    foreach ( $invoicePost as $key => $value)
        foreach( $value as $lines )
            $reportOptions[$key][] = $lines;

    $iwp['lineItems']       = $reportOptions;
    $iwp['invoice_notice']  = iwp_sanitize( $_POST["iwp_notice"] );
    $iwp['invoice_totals']  = iwp_sanitize( $_POST["iwp_totals"] );
    $iwp['user_data']       = iwp_sanitize( $_POST["iwp_invoice"]["user_data"] );

    // Handles Payments
    $payment = 0;

    if( ! empty( $_POST["iwp_payment_amount"] ) ) {
        $iwp["iwp_invoice_payment"]["amount"][] = iwp_sanitize( $_POST["iwp_payment_amount"] );
        $iwp["iwp_invoice_payment"]["method"][] = iwp_sanitize( $_POST["iwp_payment_method"] );
    
          
    }

    foreach ($iwp["iwp_invoice_payment"]["amount"] as $key => $value)
        $payment += $value;  
    
    $iwp['invoice_totals']["payments"]  = $payment;

    update_post_meta( $post_id, '_invoicedwp', $iwp ); // Saves if the invoice is only a quote



    // Add User information for new user
    if( isset( $_POST['makeAccount'] ) ) {

        $user_name = $iwp['user_data']['first_name'][0] . $iwp['user_data']['last_name'];

        $user_id = username_exists( $user_name );
        if ( !$user_id and email_exists( $iwp['user_data']['user_email'] ) == false ) {
            
            $user_email = $iwp['user_data']['user_email'];
            $random_password = wp_generate_password( $length=12, $include_standard_special_chars=true );
            $user_id = wp_create_user( $user_name, $random_password, $user_email );

            // Send email with password information 
        }

        add_user_meta( $user_id, 'iwp_first_name', $iwp['user_data']['first_name'] );
        add_user_meta( $user_id, 'iwp_last_name', $iwp['user_data']['last_name'] );
        add_user_meta( $user_id, 'iwp_company_name', $iwp['user_data']['company_name'] );
        add_user_meta( $user_id, 'iwp_phonenumber', $iwp['user_data']['phonenumber'] );
        add_user_meta( $user_id, 'iwp_streetaddress', $iwp['user_data']['streetaddress'] );
        add_user_meta( $user_id, 'iwp_streetaddress2', $iwp['user_data']['streetaddress2'] );
        add_user_meta( $user_id, 'iwp_city', $iwp['user_data']['city'] );
        add_user_meta( $user_id, 'iwp_state', $iwp['user_data']['state'] );
        add_user_meta( $user_id, 'iwp_zip', $iwp['user_data']['zip'] );

    }


    // Add email functions here

}
add_action( 'save_post', 'save_myInvoiceSettings' );


function secureLink( $post_ID, $post, $update ) {
    global $wpdb;

    $where = array( 'ID' => $post_ID );
    $postNameMD5 = md5( $post->post_name );

    $wpdb->update( $wpdb->posts, array( 'post_name' => $postNameMD5 ), $where );

}
add_action( 'save_post_invoicedwp', 'secureLink', 10, 3 );



function iwp_sanitize( $items ) {
    if( ! is_array( $items ) )
        return sanitize_text_field( $items );

    $newItem = array(); 
    foreach( $items as $key => $item )
        $newItem[$key] = sanitize_text_field( $item );

    return $newItem;
}

function iwp_get_roles() {
    global $wp_roles;

    $list = array();
    $all_roles = $wp_roles->roles;
    foreach($all_roles as $role)
        $list[] = $role["name"];

    return apply_filters('iwp_get_roles', $list);
}



/**
 * Include admin files conditionally
 */
function iwp_conditonal_includes() {

    $screen = get_current_screen();

    switch ( $screen->id ) {
        case 'dashboard' :
            //include( 'class-wc-admin-dashboard.php' );
        break;
        case 'options-permalink' :
            include( 'permalink-settings.php' );
        break;
        case 'users' :
        case 'user' :
        case 'profile' :
        case 'user-edit' :
            //include( 'class-wc-admin-profile.php' );
        break;
    }
}

