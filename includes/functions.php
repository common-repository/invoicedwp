<?php
/**
 * Helper Functions
 *
 * @package     InvoicedWP
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;
function invoicedwp_translation_mangler($translation, $text, $domain) {
        global $post;
    if( isset( $post->post_type ) ) {
        if ( $post->post_type == 'invoicedwp') {
            $screen = get_current_screen();
            if ( $screen->parent_base == 'edit' ) {
                $translations = get_translations_for_domain( $domain);
                if ( $text == 'Scheduled for: <b>%1$s</b>') {
                    return $translations->translate( 'Send On: <b>%1$s</b>' );
                }
                if ( $text == 'Published on: <b>%1$s</b>') {
                    return $translations->translate( 'Sent On: <b>%1$s</b>' );
                }
                if ( $text == 'Publish <b>immediately</b>') {
                    return $translations->translate( 'Send <b>immediately</b>' );
                }
                if ( $text == 'Schedule') {
                    return $translations->translate( 'Schedule send' );
                }
                if ( $text == 'Publish') {
                    return $translations->translate( 'Send Invoice' );
                }
                if ( $text == 'Update') {
                    return $translations->translate( 'Update and Send' );
                }
            }
        }
    }
    return $translation;
}
//add_filter('gettext', 'invoicedwp_translation_mangler', 10, 4);'
//
/**
 * Returns a nicely formatted amount.
 *
 * @since 1.0
 *
 * @param string $amount   Price amount to format
 * @param string $decimals Whether or not to use decimals.  Useful when set to false for non-currency numbers.
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function iwp_format_amount( $amount, $decimals = true ) {
    $thousands_sep = iwp_get_option( 'thousands_separator', ',' );
    $decimal_sep   = iwp_get_option( 'decimal_separator', '.' );
    // Format the amount
    if ( $decimal_sep == ',' && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) {
        $whole = substr( $amount, 0, $sep_found );
        $part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
        $amount = $whole . '.' . $part;
    }
    // Strip , from the amount (if set as the thousands separator)
    if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
        $amount = str_replace( ',', '', $amount );
    }
    // Strip ' ' from the amount (if set as the thousands separator)
    if ( $thousands_sep == ' ' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
        $amount = str_replace( ' ', '', $amount );
    }
    if ( empty( $amount ) ) {
        $amount = 0;
    }
    $amount = (double) $amount;
    $decimals  = apply_filters( 'iwp_format_amount_decimals', $decimals ? 2 : 0, $amount );
    $formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );
    return apply_filters( 'iwp_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
}
function iwp_get_business_information() {
    $defaults = array(
        'business_logo'         => '',
        'business_name'         => '',
        'business_address1'     => '',
        'business_address2'     => '',
        'business_city'         => '',
        'business_state'        => '',
        'business_phone_number' => '',
        'business_email'        => '',
        'business_zip_code'     => '',
        'business_country'      => '',
    );
    $options = get_option( 'iwp_settings' );
    if ( empty( $options ) || $options == '' ) {
        $options = array();
    }
    $return = array();
    foreach ( $defaults as $key => $value ) {
        if ( ! empty( $options[ $key ] ) ) {
            $return_value = $options[ $key ];
        } else {
            $return_value = $defaults[ $key ];
        }
        $return[ $key ] = $return_value;
    }
    return apply_filters( 'iwp_get_business_information', $return );
}

function iwp_add_paypal_button(){
    $iwp_options = get_option( 'iwp_settings' );
    $invoiceContent = get_post_meta( get_the_id(), '_invoicedwp', true );

    ?>
    <div id="iwp_payments">

        <?php

        if( $iwp_options["iwp_enable_paypal"] == 1 ) {
            if ( $invoiceContent['minPayment'] == 1 ) {
                echo sprintf( __( 'Please make at minimum payment of at least %s%s', 'iwp-txt'), iwp_currency_symbol(), iwp_format_amount( $invoiceContent['minPaymentText'] ) );
            }

            ?>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="<?php echo $iwp_options["iwp_paypal_email"]; ?>">
                <input type="hidden" name="item_name" value="<?php echo get_the_title(); ?>">
                <input type="hidden" name="currency_code" value="<?php echo $iwp_options["currency"]; ?>">
                <input type="number" placeholder="Amount" name="amount" value="" class="iwp_paypal_amount" min="<?php echo $invoiceContent['minPaymentText']; ?>">
                <input type="submit" value="Submit" name="submit" class="iwp_paypal_submit">
            </form>

            <?php
        }
        ?>
    </div>
    <?php

}
add_action( 'iwp_payment_methods', 'iwp_add_paypal_button' );

