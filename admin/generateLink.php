<?php
/**
 * Generate Link Functions
 *
 * @package     InvoicedWP
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;






add_action( 'init', 'wpse26869_add_rewrites' );
function wpse26869_add_rewrites()
{
	add_rewrite_rule( '^invoice/(\d+)$', 'index.php?invoice=$matches[1]', 'top' );
}

add_filter( 'query_vars', 'wpse26869_query_vars', 10, 1 );
function wpse26869_query_vars( $vars )
{
	$vars[] = 'invoice';
	return $vars;
}

add_action( 'template_redirect', 'wpse26869_shortlink_redirect' );
function wpse26869_shortlink_redirect()
{
	// bail if this isn't a short link
	if( ! get_query_var( 'invoice' ) ) return;
	global $wp_query;

	$id = absint( get_query_var( 'invoice' ) );
	if( ! $id )
	{
		$wp_query->is_404 = true;
		return;
	}

	$link = get_permalink( $id );
	if( ! $link )
	{
		$wp_query->is_404 = true;
		return;
	}

	wp_redirect( esc_url( $link ), 301 );
	exit();
}

add_filter( 'get_shortlink', 'wpse26869_get_shortlink', 10, 3 );
function wpse26869_get_shortlink( $link, $id, $context )
{
	if( 'query' == $context && is_single() )
	{
		$id = get_queried_object_id();
	}

	//$id = md5( $id );
	return home_url( 'invoice/' . $id );
}

