<?php
/**
 * WP-Invoice AJAX Handler
 */
class IWP_Ajax {

  /**
   * Search user for invoice page metabox
   * @global object $wpdb
   */
    static function search_email() {
        global $wpdb, $blog_id;

        $theRequest = iwp_sanitize( $_REQUEST['s'] );

        $users_found = $wpdb->get_results( "SELECT `u`.`user_email` as `id`, `u`.`user_email` as `title`
                FROM `{$wpdb->users}` as `u` INNER JOIN `{$wpdb->usermeta}` as `m`
                ON `u`.`ID` = `m`.`user_id`
                WHERE (`u`.`display_name` LIKE '%{$theRequest}%'
                OR `u`.`user_email` LIKE '%{$theRequest}%')
                AND `u`.`user_email` != ''
                AND `m`.`meta_key` = '{$wpdb->get_blog_prefix( $blog_id )}capabilities'
                GROUP BY `u`.`ID`
                LIMIT 10" );

            die( json_encode( $users_found ) );
    }

  /**
   * Search users for filter invoice section
   * @global object $wpdb
   */
  static function search_recipient() {
    global $wpdb, $blog_id;

    $theRequest = iwp_sanitize( $_REQUEST['s'] );

    $users_found = $wpdb->get_results( "SELECT `u`.`ID` as `id`, CONCAT(`u`.`display_name`, ' (', `u`.`user_email`, ')') as `title`
          FROM `{$wpdb->users}` as `u` INNER JOIN `{$wpdb->usermeta}` as `m`
          ON `u`.`ID` = `m`.`user_id`
          WHERE (`u`.`display_name` LIKE '%{$theRequest}%'
          OR `u`.`user_email` LIKE '%{$theRequest}%')
          AND `u`.`user_email` != ''
          AND `m`.`meta_key` = '{$wpdb->get_blog_prefix( $blog_id )}capabilities'
          GROUP BY `u`.`ID`
          LIMIT 10" );
      die( json_encode( $users_found ) );
    }

  /**
   * Return user data in JSON format
   *
   * @todo add hooks to accomodate different user values
   * @since 3.0
   *
   */
  static function get_user_data( $user_email = false ) {
    if ( ! $user_email ) {
      return;
    }

    $user_id = email_exists( $user_email );

    if ( !$user_id ) {
      return;
    }

    $user_data = array();

    $user_data[ 'first_name' ] = get_user_meta( $user_id, 'first_name', true );
    $user_data[ 'last_name' ] = get_user_meta( $user_id, 'last_name', true );
    $user_data[ 'company_name' ] = get_user_meta( $user_id, 'iwp_company_name', true );
    $user_data[ 'phonenumber' ] = get_user_meta( $user_id, 'iwp_phonenumber', true );
    $user_data[ 'streetaddress' ] = get_user_meta( $user_id, 'iwp_streetaddress', true );
    $user_data[ 'city' ] = get_user_meta( $user_id, 'iwp_city', true );
    $user_data[ 'state' ] = get_user_meta( $user_id, 'iwp_state', true );
    $user_data[ 'zip' ] = get_user_meta( $user_id, 'iwp_zip', true );

    if ( $user_data ) {
      echo json_encode( array( 'success' => 'true', 'user_data' => $user_data ) );
    }
    wp_die( );
  }


  public function add_row() {
    $post_data = iwp_sanitize( $_POST['version'] );
    ob_start();
    include( IWP_DIR . 'admin/templates/meta-content.php' );
    echo ob_get_clean();
    wp_die( );
  }

  public function add_template_row() {
    $post_data = iwp_sanitize( $_POST['version'] );
    $template = iwp_sanitize( $_POST['template'] );
    ob_start();
    include( IWP_DIR . 'admin/templates/meta-template-content.php' );
    echo ob_get_clean();
    wp_die( );
  }

}
