<?php
 /*Template Name: New Template
 */

get_header(); ?>
<div id="primary">
    <div id="content" role="main">
    <?php
    $mypost = array( 'post_type' => 'invoicedwp', );
    $loop = new WP_Query( $mypost );
    $iwp_currency = iwp_currency_symbol();
    $iwp_options = get_option( 'iwp_settings' );

    while ( have_posts() ) : the_post();

        $invoiceContent = get_post_meta( get_the_id(), '_invoicedwp', true );

        ob_start();
        include( 'templates/standard.php' );
        echo ob_get_clean();

    endwhile; ?>
    </div>
</div>
<?php wp_reset_query(); ?>
<?php get_footer(); ?>
