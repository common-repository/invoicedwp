<?php
/**
 * Plugin Name:     Invoiced WP
 * Plugin URI:      invoicedwp.com
 * Description:     The Most effective way to Get Paid by your clients.  Create it directly from your website.
 * Version:         1.3.0
 * Author:          Plugin Poets
 * Author URI:      pluginpoets.com
 * Text Domain:     iwp-txt
 *
 * @package         Invoiced WP
 * @author          Ryan Pletcher
 * @copyright       Copyright (c) 2015
 *
 *
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'IWP' ) ) {

    /**
     * Main IWP class
     *
     * @since       1.0.0
     */
    class IWP {


        /**
         * @var         IWP $instance The one true IWP
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true IWP
         */
        public static function instance() {

            if( !self::$instance ) {
                self::$instance = new IWP();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            define( 'IWP_PATH', plugin_dir_path( __FILE__ ) );
            define( 'IWP_DIR', plugin_dir_path( __FILE__ ) );
            define( 'IWP_VERSION', '1.3.0' );
            define( 'IWP_FILE', plugin_basename( __FILE__ ) );
            define( 'IWP_URL', plugins_url( '', IWP_FILE ) );

        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            $iwp_options = get_option( 'iwp_settings' );

            require_once IWP_PATH . 'admin/settings.php';
            require_once IWP_PATH . 'admin/generateLink.php';
            //$iwp_options = iwp_get_settings();

            // Include scripts
            require_once IWP_PATH . 'includes/scripts.php';
            require_once IWP_PATH . 'includes/post-type.php';
            require_once IWP_PATH . 'admin/ajax-functions.php';
            require_once IWP_PATH . 'includes/functions.php';

            require_once IWP_PATH . 'admin/functions.php';


            require_once IWP_PATH . 'admin/admin-pages.php';
            require_once IWP_PATH . 'admin/form-callbacks.php';
            require_once IWP_PATH . 'admin/meta.php';

        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         */
        private function hooks() {

            add_action( 'init', 'iwp_setup_init' );
            add_action( 'current_screen', 'iwp_conditonal_includes' );
            add_filter( 'template_include', 'include_invoice_template_function', 1 );

            if( is_admin() ) {
                add_action( 'admin_menu', array( $this, 'iwp_setup_admin_menu' ), 1000, 0 );
                add_action( 'add_meta_boxes', array( $this, 'iwp_setup_admin_meta' ) );

                add_filter( 'manage_invoicedwp_posts_columns', 'iwp_custom_columns' );
                add_action( 'manage_invoicedwp_posts_custom_column', 'iwp_display_custom_columns' );

                // AJAX Functions
                add_action( 'wp_ajax_iwp_search_email', array( 'IWP_Ajax', 'search_email' ) );
                add_action( 'wp_ajax_iwp_search_recipient', array( 'IWP_Ajax', 'search_recipient' ) );
                add_action( 'wp_ajax_iwp_add_row', array( 'IWP_Ajax', 'add_row' ) );
                add_action( 'wp_ajax_iwp_add_template_row', array( 'IWP_Ajax', 'add_template_row' ) );
                add_action( 'wp_ajax_iwp_get_user_data', create_function( '', 'die(IWP_Ajax::get_user_data( iwp_sanitize( $_REQUEST["user_email"] ) ) );' ) );
            }


        }



        /**
         * Add the Pushover Notifications item to the Settings menu
         * @return void
         * @access public
         */
        public function iwp_setup_admin_menu() {
            global $iwp_dashboard_page, $iwp_settings_page,$iwp_sysinfo_page;

            //$iwp_dashboard_page = add_submenu_page( 'edit.php?post_type=invoicedwp', __( 'Dashboard', 'iwp-txt' ), __( 'Dashboard', 'iwp-txt' ), 'manage_options', 'iwp-display-dashboard', 'iwp_display_dashboard' );
            $iwp_settings_page = add_submenu_page( 'edit.php?post_type=invoicedwp', __( 'Options', 'iwp-txt' ), __( 'Options', 'iwp-txt' ), 'manage_options', 'iwp-display-options', 'iwp_display_options' );
            $iwp_sysinfo_page = add_submenu_page( 'edit.php?post_type=invoicedwp', __( 'System Info', 'iwp-txt' ), __( 'System Info', 'iwp-txt' ), 'manage_options', 'iwp-system-info', 'iwp_display_sysinfo' );
        }


        /**
             * Add the Pushover Notifications item to the Settings menu
             * @return void
             * @access public
             */
            public function iwp_setup_admin_meta() {
                global $post;
                // Meta Boxes for the Invoice Page

                $iwp = get_post_meta( $post->ID, '_invoicedwp', true );
                if ( isset( $iwp['isQuote'] ) )
                    if ( $iwp['isQuote'] != 1 )
                        add_meta_box( 'iwp_payment', __( 'Payment Information', 'iwp-txt' ), 'iwp_payment', 'invoicedwp', 'side' );


                add_meta_box( 'iwp_client', __( 'Client Information', 'iwp-txt' ), 'iwp_client', 'invoicedwp', 'side', 'low' );

                add_meta_box( 'iwp_details', __( 'Billing Details', 'iwp-txt' ), 'iwp_details', 'invoicedwp', 'normal', 'high' );
                add_meta_box( 'iwp_notice', __( 'Invoice Notice', 'iwp-txt' ), 'iwp_notice', 'invoicedwp', 'normal', 'high' );
                // Meta Boxes for the Line Item Page
                add_meta_box( 'iwp_line_details', __( 'Line Item Details', 'iwp-txt' ), 'iwp_details', 'invoicedwp_template', 'normal', 'low' );

                remove_meta_box( 'commentstatusdiv', 'invoicedwp_template' , 'normal' );
                remove_meta_box( 'commentsdiv', 'invoicedwp_template' , 'normal' );
                remove_meta_box( 'slugdiv', 'invoicedwp_template' , 'normal' );

            }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = IWP_PATH . '/languages/';
            $lang_dir = apply_filters( 'iwp_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'invoicedwp' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'invoicedwp', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/invoicedwp/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/invoicedwp/ folder
                load_textdomain( 'invoicedwp', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/invoicedwp/languages/ folder
                load_textdomain( 'invoicedwp', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'invoicedwp', false, $lang_dir );
            }
        }


        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         */
        public function settings( $settings ) {

        }


        /*
    	 * Activation function fires when the plugin is activated.
    	 *
    	 * This function is fired when the activation hook is called by WordPress,
    	 *
    	 */
    	public static function activation() {
        /*Activation functions here*/

        }

        /**
         * Register/Whitelist our settings on the settings page, allow extensions and other plugins to hook into this
         * @return void
         * @access public
         */
        public function iwp_register_settings() {
            register_setting( 'iwp-options', 'iwp_options' );

            do_action( 'iwp_register_additional_settings' );
        }

    }
}


/**
 * The main function responsible for returning the one true IWP
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      IWP The one true IWP
 *
 * @todo        Inclusion of the activation code below isn't mandatory, but
 *              can prevent any number of errors, including fatal errors, in
 *              situations where your extension is activated but EDD is not
 *              present.
 */
function IWP_load() {
        return IWP::instance();
}
add_action('plugins_loaded', 'IWP_load' );


