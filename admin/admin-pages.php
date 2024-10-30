<?php

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.8.4
 * @return mixed
 */
function iwp_get_option( $key = '', $default = false ) {
	$iwp_options = get_option( 'iwp_settings' );
	$value = ! empty( $iwp_options[ $key ] ) ? $iwp_options[ $key ] : $default;
	$value = apply_filters( 'iwp_get_option', $value, $key, $default );
	return apply_filters( 'iwp_get_option_' . $key, $value, $key, $default );
}

/**
 * Update an option
 *
 * Updates an edd setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the iwp_options array.
 *
 * @since 2.3
 * @param string $key The Key to update
 * @param string|bool|int $value The value to set the key to
 * @return boolean True if updated, false if not.
 */
function iwp_update_option( $key = '', $value = false ) {

	// If no key, exit
	if ( empty( $key ) ){
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = iwp_delete_option( $key );
		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'iwp_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'iwp_update_option', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update = update_option( 'iwp_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ){
		global $iwp_options;
		$iwp_options[ $key ] = $value;

	}

	return $did_update;
}


/**
 * Display the General settings tab
 * @return void
 */
function iwp_display_options() {
	$iwp_options = get_option( 'iwp_settings' );
	//$license 	= get_option( '_iwp_license_key' );
	//$status 	= get_option( '_iwp_license_key_status' );

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], iwp_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( iwp_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'iwp_settings' );
				do_settings_fields( 'iwp_settings_' . $active_tab, 'iwp_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();

	do_action( 'iwp_general_settings_after' );

}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array EDD settings
 */
function iwp_get_settings() {

	$iwp_options = get_option( 'iwp_settings' );

	if( empty( $settings ) ) {
		// Update old settings with new single option

		$general_settings 	= is_array( get_option( 'iwp_settings_general' ) )    ? get_option( 'iwp_settings_general' )  	: array();
		$business_settings 	= is_array( get_option( 'iwp_settings_business' ) )   ? get_option( 'iwp_settings_buisness' ) 	: array();
		//$email_settings   	= is_array( get_option( 'iwp_settings_emails' ) )     ? get_option( 'iwp_settings_emails' )   	: array();
		$ext_settings     	= is_array( get_option( 'iwp_settings_extensions' ) ) ? get_option( 'iwp_settings_extensions' )	: array();
		$license_settings 	= is_array( get_option( 'iwp_settings_licenses' ) )   ? get_option( 'iwp_settings_licenses' )	: array();
		//$misc_settings    	= is_array( get_option( 'iwp_settings_misc' ) )       ? get_option( 'iwp_settings_misc' )		: array();

		$settings = array_merge( $general_settings, $business_settings, $email_settings, $ext_settings, $license_settings, $misc_settings );

		update_option( 'iwp_settings', $settings );

	}
	return apply_filters( 'iwp_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
*/
function iwp_register_settings() {

	if ( false == get_option( 'iwp_settings' ) ) {
		add_option( 'iwp_settings' );
	}

	foreach( iwp_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'iwp_settings_' . $tab,
			__return_null(),
			'__return_false',
			'iwp_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'iwp_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'iwp_' . $option['type'] . '_callback' ) ? 'iwp_' . $option['type'] . '_callback' : 'iwp_missing_callback',
				'iwp_settings_' . $tab,
				'iwp_settings_' . $tab,
				array(
					'section'     => $tab,
					'id'          => isset( $option['id'] )          ? $option['id']      : null,
					'desc'        => ! empty( $option['desc'] )      ? $option['desc']    : '',
					'name'        => isset( $option['name'] )        ? $option['name']    : null,
					'size'        => isset( $option['size'] )        ? $option['size']    : null,
					'options'     => isset( $option['options'] )     ? $option['options'] : '',
					'std'         => isset( $option['std'] )         ? $option['std']     : '',
					'min'         => isset( $option['min'] )         ? $option['min']     : null,
					'max'         => isset( $option['max'] )         ? $option['max']     : null,
                    'step'        => isset( $option['step'] )        ? $option['step']    : null,
                    'select2'     => isset( $option['select2'] )     ? $option['select2'] : null,
                    'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'iwp_settings', 'iwp_settings', 'iwp_settings_sanitize' );

}
add_action('admin_init', 'iwp_register_settings');

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function iwp_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'iwp-txt' ), $args['id'] );
}


/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @return array $tabs
 */
function iwp_get_settings_tabs() {

	$settings = iwp_get_registered_settings();

	$tabs = array();
	$tabs['general']  	= __( 'General', 'iwp-txt' );
	$tabs['business'] 	= __( 'Business Info', 'iwp-txt' );
	$tabs['taxes'] 		= __( 'Taxes', 'iwp-txt' );
	//$tabs['emails']   	= __( 'Emails', 'iwp-txt' );
	$tabs['payments']   = __( 'Payments', 'iwp-txt' );

	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'iwp-txt' );
	}
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'iwp-txt' );
	}

	$tabs['misc']      = __( 'Misc', 'iwp-txt' );

	return apply_filters( 'iwp_settings_tabs', $tabs );
}

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function iwp_get_registered_settings() {

	/**
	 * 'Whitelisted' EDD settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$iwp_settings = array(
		//* General Settings */
		'general' => apply_filters( 'iwp_settings_general',
			array(

				/*'partial_payments' => array(
					'id' => 'partial_payments',
					'name' => __( 'Allow partial payments', 'iwp-txt' ),
					'desc' => __( '', 'iwp-txt' ),
					'type' => 'checkbox'
				),
				'partial_payment_default' => array(
					'id' => 'partial_payment_default',
					'name' => __( 'Partial payments allowed by default', 'iwp-txt' ),
					'desc' => __( '', 'iwp-txt' ),
					'type' => 'checkbox'
				),
				'show_recurring_billing' => array(
					'id' => 'show_recurring_billing',
					'name' => __( 'Show recurring billing options', 'iwp-txt' ),
					'desc' => __( '', 'iwp-txt' ),
					'type' => 'checkbox'
				),
				'enforce_https' => array(
					'id' => 'enforce_https',
					'name' => __( 'Enforce HTTPS on invoice pages', 'iwp-txt' ),
					'desc' => __( '', 'iwp-txt' ),
					'type' => 'checkbox'
				),
				'minimum_level' => array(
					'id' => 'minimum_level',
					'name' => __( 'Minimum user level to manage Invoices', 'iwp-txt' ),
					'desc' => __( '', 'iwp-txt' ),
					'type' => 'select',
					'options' => iwp_get_roles()
				),
				'currency_settings' => array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __( 'Currency Settings', 'iwp-txt' ) . '</strong>',
					'desc' => __( 'Configure the currency options', 'iwp-txt' ),
					'type' => 'header'
				),*/
				'currency' => array(
					'id' => 'currency',
					'name' => __( 'Currency', 'iwp-txt' ),
					'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'iwp-txt' ),
					'type' => 'select',
                    'options' => iwp_get_currencies(),
                    'select2' => true
				),
				'currency_position' => array(
					'id' => 'currency_position',
					'name' => __( 'Currency Position', 'iwp-txt' ),
					'desc' => __( 'Choose the location of the currency sign.', 'iwp-txt' ),
					'type' => 'select',
					'options' => array(
						'before' => __( 'Before - $10', 'iwp-txt' ),
						'after' => __( 'After - 10$', 'iwp-txt' )
					)
				),
				'thousands_separator' => array(
					'id' => 'thousands_separator',
					'name' => __( 'Thousands Separator', 'iwp-txt' ),
					'desc' => __( 'The symbol (usually , or .) to separate thousands', 'iwp-txt' ),
					'type' => 'text',
					'size' => 'small',
					'std' => ','
				),
				'decimal_separator' => array(
					'id' => 'decimal_separator',
					'name' => __( 'Decimal Separator', 'iwp-txt' ),
					'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'iwp-txt' ),
					'type' => 'text',
					'size' => 'small',
					'std' => '.'
				),

			)
		),
		//* Business Settings */
		'business' => apply_filters('iwp_settings_business',
			array(
				'business_logo' => array(
					'id' => 'business_logo',
					'name' => __( 'Logo', 'iwp-txt' ),
					'desc' => __( 'Upload or choose a logo to be displayed at the top of your invoices.', 'iwp-txt' ),
					'type' => 'upload'
				),
				'business_info' => array(
					'id' => 'business_info',
					'name' => '<strong>' . __( 'Business Information', 'iwp-txt' ) . '</strong>',
					'desc' => __( 'Enter your businesses information below', 'iwp-txt' ),
					'type' => 'header'
				),
				'business_name' => array(
					'id' => 'business_name',
					'name' => __( 'Business Name', 'iwp-txt' ),
					'type' => 'text',
				),
				'business_address1' => array(
					'id' => 'business_address1',
					'name' => __( 'Address Line 1', 'iwp-txt' ),
					'type' => 'text',
				),
				'business_address2' => array(
					'id' => 'business_address2',
					'name' => __( 'Address Line 2', 'iwp-txt' ),
					'type' => 'text',
				),
				'business_city' => array(
					'id' => 'business_city',
					'name' => __( 'City', 'iwp-txt' ),
					'type' => 'text',
				),
				'business_country' => array(
					'id' => 'business_country',
					'name' => __( 'Country', 'iwp-txt' ),
					'desc' => __( 'Where does you operate from?', 'iwp-txt' ),
					'type' => 'select',
                    'options' => iwp_get_country_list(),
                    'select2' => true,
                    'placeholder' => __( 'Select a country', 'iwp-txt' )
				),
				'business_state' => array(
					'id' => 'business_state',
					'name' => __( 'State / Province', 'iwp-txt' ),
					'desc' => __( 'What state / province does your store operate from?', 'iwp-txt' ),
					'type' => 'shop_states',
                    'select2' => true,
                    'placeholder' => __( 'Select a state', 'iwp-txt' )
				),
				'business_zip_code' => array(
					'id' => 'business_zip_code',
					'name' => __( 'Zip / Postal Code', 'iwp-txt' ),
					'type' => 'number',
					'size' => 'small'
				),
				'business_email' => array(
					'id' => 'business_email',
					'name' => __( 'Email Adress', 'iwp-txt' ),
					'type' => 'text'
				),
				'business_phone_number' => array(
					'id' => 'business_phone_number',
					'name' => __( 'Phone Number', 'iwp-txt' ),
					'type' => 'text'
				),
				/*'registration_number' => array(
					'id' => 'registration_number',
					'name' => __( 'Registration Number', 'iwp-txt' ),
					'type' => 'text',
				),
				'taxvat_number' => array(
					'id' => 'taxvat_number',
					'name' => __( 'Tax/VAT Number', 'iwp-txt' ),
					'type' => 'text',
				),*/
				'invoice_global_notice' => array(
					'id' => 'invoice_global_notice',
					'name' => __( 'Global notice on invoces', 'iwp-txt' ),
					'type' => 'rich_editor',
				),
			)
		),
		/** Taxes Settings */
		'taxes' => apply_filters('iwp_settings_taxes',
			array(
				'iwp_enable_taxes' => array(
					'id' => 'iwp_enable_taxes',
					'name' => __( 'Enable Taxes', 'iwp-txt' ),
					'desc' => __( 'Check this to enable taxes on purchases.', 'iwp-txt' ),
					'type' => 'checkbox',
				),
				'tax_rate' => array(
					'id' => 'tax_rate',
					'name' => __( 'Tax Rate', 'iwp-txt' ),
					'desc' => __( 'Enter a percentage, such as 6.5. This is the default rate charged.', 'iwp-txt' ),
					'type' => 'text',
					'size' => 'small'
				),
				'change_tax_on_invoice' => array(
					'id' => 'change_tax_on_invoice',
					'name' => __( 'Change Tax Rate on Invoice', 'iwp-txt' ),
					'desc' => __( 'Default tax rate will be displayed on the invoice when invoice is created but site owner can modify it.', 'iwp-txt' ),
					'type' => 'radio',
					'std' => 'no',
					'options' => array(
						'yes' => __( 'Yes, I want to adjust tax rate on invoice', 'iwp-txt' ),
						'no'  => __( 'No, I will set a single tax rate for all invoices', 'iwp-txt' )
					)
				)
			)
		),
		/** Email Settings */
		'emails' => apply_filters('iwp_settings_email',
			array(
				'email_logo' => array(
					'id' => 'email_logo',
					'name' => __( 'Logo', 'iwp-txt' ),
					'desc' => __( 'Upload or choose a logo to be displayed at the top of the purchase receipt emails. Displayed on HTML emails only.', 'iwp-txt' ),
					'type' => 'upload'
				),
				'email_settings' => array(
					'id' => 'email_settings',
					'name' => '',
					'desc' => '',
					'type' => 'hook'
				),
				'from_name' => array(
					'id' => 'from_name',
					'name' => __( 'From Name', 'iwp-txt' ),
					'desc' => __( 'The name purchase receipts are said to come from. This should probably be your site or shop name.', 'iwp-txt' ),
					'type' => 'text',
					'std'  => get_bloginfo( 'name' )
				),
				'from_email' => array(
					'id' => 'from_email',
					'name' => __( 'From Email', 'iwp-txt' ),
					'desc' => __( 'Email to send purchase receipts from. This will act as the "from" and "reply-to" address.', 'iwp-txt' ),
					'type' => 'text',
					'std'  => get_bloginfo( 'admin_email' )
				),

				'new_invoice_header' => array(
					'id' => 'new_invoice_header',
					'name' => '<strong>' . __('New Invoice Email Template', 'iwp-txt') . '</strong>',
					'desc' => __('Configure new invoice notification emails', 'iwp-txt'),
					'type' => 'header'
				),
				'new_invoice_subject' => array(
					'id' => 'new_invoice_subject',
					'name' => __( 'New Invoice Subject', 'iwp-txt' ),
					'desc' => __( 'Enter the subject line for the new invoice email', 'iwp-txt' ),
					'type' => 'text',
					'std'  => __( '[New Invoice] {subject}', 'iwp-txt' )
				),
				'new_invoice' => array(
					'id' => 'new_invoice',
					'name' => __( 'New Invoice', 'iwp-txt' ),
					'desc' => __('Enter the email that is sent to users after completing an invoice is created. HTML is accepted. Available template tags:', 'iwp-txt') . '<br/>' . iwp_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std'  => __( "Dear", "iwp" ) . " {name},\n\n" . __( "Thank you for your purchase. Please click on the link(s) below to download your files.", "iwp" ) . "\n\n{download_list}\n\n{sitename}"
				),
				'reminder_email_header' => array(
					'id' => 'reminder_email_header',
					'name' => '<strong>' . __('Reminder Email Template', 'iwp-txt') . '</strong>',
					'desc' => __('Configure reminder email', 'iwp-txt'),
					'type' => 'header'
				),
				'reminder_notification_subject' => array(
					'id' => 'reminder_notification_subject',
					'name' => __( 'Reminder Notification Subject', 'iwp-txt' ),
					'desc' => __( 'Enter the subject line for the reminder notification email', 'iwp-txt' ),
					'type' => 'text',
					'std' => '[Reminder] {subject}'
				),
				'reminder_notification' => array(
					'id' => 'reminder_notification',
					'name' => __( 'Reminder Notification', 'iwp-txt' ),
					'desc' => __( 'Enter the email that is sent to reminder notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'iwp-txt' ) . '<br/>' . iwp_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std' => iwp_get_default_sale_notification_email()
				),

				'receipt_email_header' => array(
					'id' => 'receipt_email_header',
					'name' => '<strong>' . __('Receipt Template', 'iwp-txt') . '</strong>',
					'desc' => __('Configure receipt email', 'iwp-txt'),
					'type' => 'header'
				),
				'receipt_email_subject' => array(
					'id' => 'receipt_email_subject',
					'name' => __( 'Receipt Email Subject', 'iwp-txt' ),
					'desc' => __( 'Enter the subject line for the receipt email', 'iwp-txt' ),
					'type' => 'text',
					'std' => '[Payment Received] {subject}'
				),
				'receipt_notification' => array(
					'id' => 'receipt_notification',
					'name' => __( 'Receipt Notification', 'iwp-txt' ),
					'desc' => __( 'Enter the email that is sent to receipt notification emails after completion of a payment. HTML is accepted. Available template tags:', 'iwp-txt' ) . '<br/>' . iwp_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std' => iwp_get_default_sale_notification_email()
				),
				'quote_email_header' => array(
					'id' => 'quote_email_header',
					'name' => '<strong>' . __('Quote Template', 'iwp-txt') . '</strong>',
					'desc' => __('Configure quote email', 'iwp-txt'),
					'type' => 'header'
				),
				'quote_email_subject' => array(
					'id' => 'quote_email_subject',
					'name' => __( 'Quote Email Subject', 'iwp-txt' ),
					'desc' => __( 'Enter the subject line for the quote email', 'iwp-txt' ),
					'type' => 'text',
					'std' => '[Quote] {subject}'
				),
				'quote_notification' => array(
					'id' => 'quote_notification',
					'name' => __( 'Quote', 'iwp-txt' ),
					'desc' => __( 'Enter the email that is sent to quote notification emails. HTML is accepted. Available template tags:', 'iwp-txt' ) . '<br/>' . iwp_get_emails_tags_list(),
					'type' => 'rich_editor',
					'std' => iwp_get_default_sale_notification_email()
				),

				'disable_admin_notices' => array(
					'id' => 'disable_admin_notices',
					'name' => __( 'Disable Admin Notifications', 'iwp-txt' ),
					'desc' => __( 'Check this box if you do not want to receive emails when new sales are made.', 'iwp-txt' ),
					'type' => 'checkbox'
				)
			)
		),
		'payments' => apply_filters('iwp_settings_payments',
			array(
				'iwp_enable_paypal' => array(
					'id' => 'iwp_enable_paypal',
					'name' => __( 'Enable Paypal', 'iwp-txt' ),
					'desc' => __( 'Check this to enable Paypal as a payment method.', 'iwp-txt' ),
					'type' => 'checkbox',
				),
				'iwp_paypal_email' => array(
					'id' => 'iwp_paypal_email',
					'name' => __( 'Email Address', 'iwp-txt' ),
					'desc' => __( 'Enter the email address of your paypal account', 'iwp-txt' ),
					'type' => 'text',
					'std' => ''
				),


			)
		),

		/** Extension Settings */
		'extensions' => apply_filters('iwp_settings_extensions',
			array()
		),
		/** License Settings */
		'licenses' => apply_filters('iwp_settings_licenses',
			array()
		),
		/** Misc Settings */
		'misc' => apply_filters('iwp_settings_misc',
			array(

			)
		)
	);

	return apply_filters( 'iwp_registered_settings', $iwp_settings );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.9.5
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function iwp_get_pages( $force = false ) {

	$pages_options = array( '' => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'iwp-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}



/**
 * Display the System Info Tab
 * @return void
 */
function iwp_display_sysinfo() {
	global $wpdb;
	$iwp_options = get_option( 'iwp_settings' );
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"></div><h2><?php _e( 'InvoicedWP - System Info', 'iwp-txt' ); ?></h2>
		<textarea style="font-family: Menlo, Monaco, monospace; white-space: pre" onclick="this.focus();this.select()" readonly cols="150" rows="35">
	SITE_URL:                 <?php echo site_url() . "\n"; ?>
	HOME_URL:                 <?php echo home_url() . "\n"; ?>

	IWP Version:             <?php echo IWP_VER . "\n"; ?>
	WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

	IWP SETTINGS:
	<?php
	foreach ( $iwp_options as $name => $value ) {
	if ( $value == false )
		$value = 'false';

	if ( $value == '1' )
		$value = 'true';

	echo $name . ': ' . maybe_serialize( $value ) . "\n";
	}
	?>

	ACTIVE PLUGINS:
	<?php
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ) {
		// If the plugin isn't active, don't show it.
		if ( ! in_array( $plugin_path, $active_plugins ) )
			continue;

	echo $plugin['Name']; ?>: <?php echo $plugin['Version'] ."\n";

	}
	?>

	CURRENT THEME:
	<?php
	if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		echo $theme_data['Name'] . ': ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		echo $theme_data->Name . ': ' . $theme_data->Version;
	}
	?>


	Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

	ADVANCED INFO:
	PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
	MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
	Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

	PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
	PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
	PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>

	WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

	WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

	Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
	Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>
	Page For Posts:           <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>

	Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
	Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
	Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
	Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
	Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
	Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

	UPLOAD_MAX_FILESIZE:      <?php if ( function_exists( 'phpversion' ) ) echo ini_get( 'upload_max_filesize' ); ?><?php echo "\n"; ?>
	POST_MAX_SIZE:            <?php if ( function_exists( 'phpversion' ) ) echo ini_get( 'post_max_size' ); ?><?php echo "\n"; ?>
	WordPress Memory Limit:   <?php echo WP_MEMORY_LIMIT; ?><?php echo "\n"; ?>
	DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
	FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'iwp-txt' ) : __( 'Your server does not support fsockopen.', 'iwp-txt' ); ?><?php echo "\n"; ?>
		</textarea>
	</div>
	<?php
}
