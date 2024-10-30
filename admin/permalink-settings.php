<?php
/**
 * Adds settings to the permalinks admin settings page.
 *
 * @class       InvoicedWP_Permalink_Settings
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'InvoicedWP_Permalink_Settings' ) ) :

/**
 * InvoicedWP_Permalink_Settings Class
 */
class InvoicedWP_Permalink_Settings {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		$this->settings_init();
		$this->settings_save();
	}

	/**
	 * Init our settings.
	 */
	public function settings_init() {
		// Add a section to the permalinks page
		//add_settings_section( 'invoicedwp-permalink', __( 'Product permalink base', 'invoicedwp' ), array( $this, 'settings' ), 'permalink' );

		// Add our settings
		add_settings_field(
			'invoicedwp_slug',            // id
			__( 'Invoice base', 'invoicedwp' ),   // setting title
			array( $this, 'invoicedwp_slug_input' ),  // display callback
			'permalink',                                    // settings page
			'optional'                                      // settings section
		);
	}

	/**
	 * Show a slug input box.
	 */
	public function invoicedwp_slug_input() {
		$permalinks = get_option( 'invoicedwp_permalinks' );
		?>
		<input name="invoicedwp_slug" type="text" class="regular-text code" value="<?php if ( isset( $permalinks['invoicedwp_slug'] ) ) echo esc_attr( $permalinks['invoicedwp_slug'] ); ?>" placeholder="<?php echo _x('invoicedwp', 'slug', 'invoicedwp') ?>" />
		<?php
	}

	/**
	 * Save the settings.
	 */
	public function settings_save() {

		if ( ! is_admin() ) {
			return;
		}

		// We need to save the options ourselves; settings api does not trigger save for the permalinks page
		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['invoicedwp_slug'] ) ) {
			// Cat and tag bases
			$invoicedwp_slug  = iwp_sanitize( $_POST['invoicedwp_slug'] );

			$permalinks = get_option( 'invoicedwp_permalinks' );

			if ( ! $permalinks ) {
				$permalinks = array();
			}

			$permalinks['invoicedwp_slug'] = untrailingslashit( $invoicedwp_slug );

			update_option( 'invoicedwp_permalinks', $permalinks );
		}
		
	}

}

endif;

return new InvoicedWP_Permalink_Settings();
