<?php
/**
 * Scripts
 *
 * @package     iwp\PluginName\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Load admin scripts
 *
 * @since       1.0.0
 * @global      array $iwp_settings_page The slug for the iwp settings page
 * @global      string $post_type The type of post that we are editing
 * @return      void
 */
function iwp_admin_scripts( $hook ) {
    global $iwp_settings_page, $post_type;

    // Use minified libraries if SCRIPT_DEBUG is turned off
    //$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    /**
     * @todo        This block loads styles or scripts explicitly on the
     *              iwp settings page.
     */

    $possibleHooks = apply_filters( 'iwp_hooks', array( 'post.php', 'post-new.php' ) );
    $possiblePostType = apply_filters( 'iwp_posttypes', array( 'invoicedwp', 'invoicedwp_template') );

    if( (in_array($hook , $possibleHooks ) && in_array( $post_type, $possiblePostType )) || ( $hook == 'invoicedwp_page_iwp-display-options' ) ) {
        wp_register_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-style' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        wp_enqueue_style( 'thickbox' ); // call to media files in wp
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_script( 'media-upload' );

        wp_register_script( 'iwp_admin_js', IWP_URL . '/assets/js/admin.js', array( 'jquery' ) );
        wp_enqueue_script( 'iwp_admin_js' );

        wp_register_style( 'iwp_admin_css', IWP_URL . '/assets/css/admin.css' );
        wp_enqueue_style( 'iwp_admin_css' );

        wp_register_script( 'iwp_select2_js', IWP_URL . '/assets/select2/select2.js', array( 'jquery' ) );
        wp_enqueue_script( 'iwp_select2_js' );

        wp_register_style( 'iwp_select2_css', IWP_URL . '/assets/select2/select2.css', array() );
        wp_enqueue_style( 'iwp_select2_css' );
    }
}
add_action( 'admin_enqueue_scripts', 'iwp_admin_scripts', 100 );


/**
 * Load frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function iwp_scripts( $hook ) {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    wp_register_script( 'iwp_js', IWP_URL . '/assets/js/scripts' . $suffix . '.js', array( 'jquery' ) );
    wp_enqueue_script( 'iwp_js' );

    wp_register_style( 'iwp_css', IWP_URL . '/assets/css/styles' . $suffix . '.css' );
    wp_enqueue_style( 'iwp_css' );
}
add_action( 'wp_enqueue_scripts', 'iwp_scripts' );


/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since 1.7
 * @author Daniel J Griffiths
 * @return string $message
 */
function iwp_get_default_sale_notification_email() {
    $iwp_options = get_option( 'iwp_settings' );

    $default_email_body = __( 'Hello', 'iwp-txt' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'iwp-txt' ), iwp_get_label_plural() ) . ".\n\n";
    $default_email_body .= sprintf( __( '%s sold:', 'iwp-txt' ), iwp_get_label_plural() ) . "\n\n";
    $default_email_body .= '{download_list}' . "\n\n";
    $default_email_body .= __( 'Purchased by: ', 'iwp-txt' ) . ' {name}' . "\n";
    $default_email_body .= __( 'Amount: ', 'iwp-txt' ) . ' {price}' . "\n";
    $default_email_body .= __( 'Payment Method: ', 'iwp-txt' ) . ' {payment_method}' . "\n\n";
    $default_email_body .= __( 'Thank you', 'iwp-txt' );

    $message = ( isset( $iwp_options['sale_notification'] ) && !empty( $iwp_options['sale_notification'] ) ) ? $iwp_options['sale_notification'] : $default_email_body;

    return $message;
}

/**
 * Get Default Labels
 *
 * @since 1.0.8.3
 * @return array $defaults Default labels
 */
function iwp_get_default_labels() {
    $defaults = array(
       'singular' => __( 'Download', 'iwp-txt' ),
       'plural'   => __( 'Downloads', 'iwp-txt')
    );
    return apply_filters( 'iwp_default_downloads_name', $defaults );
}

/**
 * Get Singular Label
 *
 * @since 1.0.8.3
 *
 * @param bool $lowercase
 * @return string $defaults['singular'] Singular label
 */
function iwp_get_label_singular( $lowercase = false ) {
    $defaults = iwp_get_default_labels();
    return ($lowercase) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @since 1.0.8.3
 * @return string $defaults['plural'] Plural label
 */
function iwp_get_label_plural( $lowercase = false ) {
    $defaults = iwp_get_default_labels();
    return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.9
 *
 * @return string
 */
function iwp_get_emails_tags_list() {
    // The list
    $list = '';

    // Get all tags
    $email_tags = iwp_get_email_tags();

    // Check
    if ( count( $email_tags ) > 0 ) {

        // Loop
        foreach ( $email_tags as $email_tag ) {

            // Add email tag to list
            $list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

        }

    }

    // Return the list
    return $list;
}

/**
 * Get all email tags
 *
 * @since 1.9
 *
 * @return array
 */
function iwp_get_email_tags() {
    // Setup default tags array
    $email_tags = array(

        array(
            'tag'         => 'file_urls',
            'description' => __( 'A plain-text list of download URLs for each download purchased', 'edd' ),
            'function'    => 'edd_email_tag_file_urls'
        ),
        array(
            'tag'         => 'name',
            'description' => __( "The buyer's first name", 'edd' ),
            'function'    => 'edd_email_tag_first_name'
        ),
        array(
            'tag'         => 'fullname',
            'description' => __( "The buyer's full name, first and last", 'edd' ),
            'function'    => 'edd_email_tag_fullname'
        ),
        array(
            'tag'         => 'username',
            'description' => __( "The buyer's user name on the site, if they registered an account", 'edd' ),
            'function'    => 'edd_email_tag_username'
        ),
        array(
            'tag'         => 'user_email',
            'description' => __( "The buyer's email address", 'edd' ),
            'function'    => 'edd_email_tag_user_email'
        ),
        array(
            'tag'         => 'billing_address',
            'description' => __( 'The buyer\'s billing address', 'edd' ),
            'function'    => 'edd_email_tag_billing_address'
        ),
        array(
            'tag'         => 'date',
            'description' => __( 'The date of the purchase', 'edd' ),
            'function'    => 'edd_email_tag_date'
        ),
        array(
            'tag'         => 'subtotal',
            'description' => __( 'The price of the purchase before taxes', 'edd' ),
            'function'    => 'edd_email_tag_subtotal'
        ),
        array(
            'tag'         => 'tax',
            'description' => __( 'The taxed amount of the purchase', 'edd' ),
            'function'    => 'edd_email_tag_tax'
        ),
        array(
            'tag'         => 'price',
            'description' => __( 'The total price of the purchase', 'edd' ),
            'function'    => 'edd_email_tag_price'
        ),
        array(
            'tag'         => 'payment_id',
            'description' => __( 'The unique ID number for this purchase', 'edd' ),
            'function'    => 'edd_email_tag_payment_id'
        ),
        array(
            'tag'         => 'receipt_id',
            'description' => __( 'The unique ID number for this purchase receipt', 'edd' ),
            'function'    => 'edd_email_tag_receipt_id'
        ),
        array(
            'tag'         => 'payment_method',
            'description' => __( 'The method of payment used for this purchase', 'edd' ),
            'function'    => 'edd_email_tag_payment_method'
        ),
        array(
            'tag'         => 'sitename',
            'description' => __( 'Your site name', 'edd' ),
            'function'    => 'edd_email_tag_sitename'
        ),
        array(
            'tag'         => 'receipt_link',
            'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'edd' ),
            'function'    => 'edd_email_tag_receipt_link'
        ),
        array(
            'tag'         => 'discount_codes',
            'description' => __( 'Adds a list of any discount codes applied to this purchase', 'edd' ),
            'function'    => 'edd_email_tag_discount_codes'
        ),
    );

    // Apply edd_email_tags filter
    return apply_filters( 'edd_email_tags', $email_tags );


}

function prevent_searches() {
    global $post;

    if ( ! is_single() ) {
        return;
    }

    if ( $post->post_type == 'invoicedwp' ) {
        echo "<meta name='robots' content='noindex,follow' />";
    }

}
add_action('wp_head','prevent_searches');


