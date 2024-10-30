<?php
/**
 * Helper Functions
 *
 * @package     InvoicedWP
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.0
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitizied value
 */
function iwp_settings_sanitize( $input = array() ) {

	$iwp_options = get_option( 'iwp_settings' );

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = iwp_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'iwp_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'iwp_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'iwp_settings_sanitize', $input[$key], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			//if ( empty( $input[$key] ) ) {
			//	unset( $iwp_options[$key] );
			//}

		}
	}

	// Merge our new settings with the existing
	//
	if( is_array( $iwp_options ) ) {
		$output = array_merge( $iwp_options, $input );
	} else {
		$output = $input;
	}

	add_settings_error( 'iwp-notices', '', __( 'Settings updated.', 'iwp-txt' ), 'updated' );

	return $output;
}

/**
 * Misc Settings Sanitization
 *
 * @since 1.0.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function iwp_settings_sanitize_misc( $input ) {

	$iwp_options = get_option( 'iwp_settings' );

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if( iwp_get_file_download_method() != $input['download_method'] || ! iwp_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		iwp_create_protection_files( true, $input['download_method'] );
	}

	if( ! empty( $input['enable_sequential'] ) && ! iwp_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		EDD()->session->set( 'upgrade_sequential', '1' );

	}

	return $input;
}
add_filter( 'iwp_settings_misc_sanitize', 'iwp_settings_sanitize_misc' );



/**
 * Sanitize text fields
 *
 * @since 1.0.0
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function iwp_sanitize_text_field( $input ) {
	return trim( $input );
}
add_filter( 'iwp_settings_sanitize_text', 'iwp_sanitize_text_field' );



/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function iwp_header_callback( $args ) {
	echo '<hr/>';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_checkbox_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	$checked = isset( $iwp_options[ $args[ 'id' ] ] ) ? checked( 1, $iwp_options[ $args[ 'id' ] ], false ) : '';
	$html = '<input type="checkbox" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_multicheck_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $iwp_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="iwp_settings[' . $args['id'] . '][' . $key . ']" id="iwp_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="iwp_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description">' . $args['desc'] . '</p>';
	}
}

/**
 * Payment method icons callback
 *
 * @since 2.1
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_payment_icons_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ) {

			if( isset( $iwp_options[$args['id']][$key] ) ) {
				$enabled = $option;
			} else {
				$enabled = NULL;
			}

			echo '<label for="iwp_settings[' . $args['id'] . '][' . $key . ']" style="margin-right:10px;line-height:16px;height:16px;display:inline-block;">';

				echo '<input name="iwp_settings[' . $args['id'] . '][' . $key . ']" id="iwp_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . esc_attr( $option ) . '" ' . checked( $option, $enabled, false ) . '/>&nbsp;';

				if( iwp_string_is_image_url( $key ) ) {

					echo '<img class="payment-icon" src="' . esc_url( $key ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';

				} else {

					$card = strtolower( str_replace( ' ', '', $option ) );

					if( has_filter( 'iwp_accepted_payment_' . $card . '_image' ) ) {

						$image = apply_filters( 'iwp_accepted_payment_' . $card . '_image', '' );

					} else {

						$image       = iwp_locate_template( 'images' . DIRECTORY_SEPARATOR . 'icons' . DIRECTORY_SEPARATOR . $card . '.gif', false );
						$content_dir = WP_CONTENT_DIR;

						if( function_exists( 'wp_normalize_path' ) ) {

							// Replaces backslashes with forward slashes for Windows systems
							$image = wp_normalize_path( $image );
							$content_dir = wp_normalize_path( $content_dir );

						}

						$image = str_replace( $content_dir, WP_CONTENT_URL, $image );

					}

					echo '<img class="payment-icon" src="' . esc_url( $image ) . '" style="width:32px;height:24px;position:relative;top:6px;margin-right:5px;"/>';
				}


			echo $option . '</label>';

		}
		echo '<p class="description" style="margin-top:16px;">' . $args['desc'] . '</p>';
	}
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_radio_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $iwp_options[ $args['id'] ] ) && $iwp_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $iwp_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="iwp_settings[' . $args['id'] . ']"" id="iwp_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="iwp_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;

	echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_gateways_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $iwp_options['gateways'][ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		echo '<input name="iwp_settings[' . $args['id'] . '][' . $key . ']"" id="iwp_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="iwp_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_gateway_select_callback($args) {
	$iwp_options = get_option( 'iwp_settings' );

	echo '<select name="iwp_settings[' . $args['id'] . ']"" id="iwp_settings[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $iwp_options[ $args['id'] ] ) ? selected( $key, $iwp_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_text_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_number_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

    if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_textarea_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<textarea class="large-text" cols="50" rows="5" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_password_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_select_callback($args) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

    if ( isset( $args['placeholder'] ) )
        $placeholder = $args['placeholder'];
    else
        $placeholder = '';

    $html = '<select id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" ' . ( $args['select2'] ? 'class="iwp-select2"' : '' ) . 'data-placeholder="' . $placeholder . '" />';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_color_select_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @global $wp_version WordPress Version
 */
function iwp_rich_editor_callback( $args ) {
	global $wp_version;
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) ) {
		$value = $iwp_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$rows = isset( $args['size'] ) ? $args['size'] : 20;

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'iwp_settings_' . $args['id'], array( 'textarea_name' => 'iwp_settings[' . $args['id'] . ']', 'textarea_rows' => $rows ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text" rows="10" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_upload_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="iwp_settings_upload_button button-secondary" value="' . __( 'Upload File', 'iwp-txt' ) . '"/></span>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_color_callback( $args ) {
	$iwp_options = get_option( 'iwp_settings' );

	if ( isset( $iwp_options[ $args['id'] ] ) )
		$value = $iwp_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="iwp-color-picker" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Shop States Callback
 *
 * Renders states drop down based on the currently selected country
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_shop_states_callback($args) {
	$iwp_options = get_option( 'iwp_settings' );

    if ( isset( $args['placeholder'] ) )
        $placeholder = $args['placeholder'];
    else
        $placeholder = '';

	$states = iwp_get_shop_states();

    $select2= ( $args['select2'] ? ' iwp-select2' : '' );
    $class = empty( $states ) ? ' class="iwp-no-states' . $select2 . '"' : 'class="' . $select2 . '"';
    $html = '<select id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']"' . $class . 'data-placeholder="' . $placeholder . '"/>';

	foreach ( $states as $option => $name ) :
		$selected = isset( $iwp_options[ $args['id'] ] ) ? selected( $option, $iwp_options[$args['id']], false ) : '';
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Tax Rates Callback
 *
 * Renders tax rates table
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
function iwp_tax_rates_callback($args) {
	$iwp_options = get_option( 'iwp_settings' );
	$rates = iwp_get_tax_rates();
	ob_start(); ?>
	<p><?php echo $args['desc']; ?></p>
	<table id="iwp_tax_rates" class="wp-list-table widefat fixed posts">
		<thead>
			<tr>
				<th scope="col" class="iwp_tax_country"><?php _e( 'Country', 'iwp-txt' ); ?></th>
				<th scope="col" class="iwp_tax_state"><?php _e( 'State / Province', 'iwp-txt' ); ?></th>
				<th scope="col" class="iwp_tax_global" title="<?php _e( 'Apply rate to whole country, regardless of state / province', 'iwp-txt' ); ?>"><?php _e( 'Country Wide', 'iwp-txt' ); ?></th>
				<th scope="col" class="iwp_tax_rate"><?php _e( 'Rate', 'iwp-txt' ); ?></th>
				<th scope="col"><?php _e( 'Remove', 'iwp-txt' ); ?></th>
			</tr>
		</thead>
		<?php if( ! empty( $rates ) ) : ?>
			<?php foreach( $rates as $key => $rate ) : ?>
			<tr>
				<td class="iwp_tax_country">
					<?php
					echo EDD()->html->select( array(
						'options'          => iwp_get_country_list(),
						'name'             => 'tax_rates[' . $key . '][country]',
						'selected'         => $rate['country'],
						'show_option_all'  => false,
						'show_option_none' => false,
                        'class'            => 'iwp-select iwp-tax-country',
                        'select2' => true,
                        'placeholder' => __( 'Choose a country', 'iwp-txt' )
					) );
					?>
				</td>
				<td class="iwp_tax_state">
					<?php
					$states = iwp_get_shop_states( $rate['country'] );
					if( ! empty( $states ) ) {
						echo EDD()->html->select( array(
							'options'          => $states,
							'name'             => 'tax_rates[' . $key . '][state]',
							'selected'         => $rate['state'],
							'show_option_all'  => false,
                            'show_option_none' => false,
                            'select2' => true,
                            'placeholder' => __( 'Choose a state', 'iwp-txt' )
						) );
					} else {
						echo EDD()->html->text( array(
							'name'             => 'tax_rates[' . $key . '][state]', $rate['state']
						) );
					}
					?>
				</td>
				<td class="iwp_tax_global">
					<input type="checkbox" name="tax_rates[<?php echo $key; ?>][global]" id="tax_rates[<?php echo $key; ?>][global]" value="1"<?php checked( true, ! empty( $rate['global'] ) ); ?>/>
					<label for="tax_rates[<?php echo $key; ?>][global]"><?php _e( 'Apply to whole country', 'iwp-txt' ); ?></label>
				</td>
				<td class="iwp_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" max="99" name="tax_rates[<?php echo $key; ?>][rate]" value="<?php echo $rate['rate']; ?>"/></td>
				<td><span class="iwp_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'iwp-txt' ); ?></span></td>
			</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td class="iwp_tax_country">
					<?php
					echo EDD()->html->select( array(
						'options'          => iwp_get_country_list(),
						'name'             => 'tax_rates[0][country]',
						'show_option_all'  => false,
						'show_option_none' => false,
                        'class'            => 'iwp-select iwp-tax-country',
                        'select2' => true,
                        'placeholder' => __( 'Choose a country', 'iwp-txt' )
					) ); ?>
				</td>
				<td class="iwp_tax_state">
					<?php echo EDD()->html->text( array(
						'name'             => 'tax_rates[0][state]'
					) ); ?>
				</td>
				<td class="iwp_tax_global">
					<input type="checkbox" name="tax_rates[0][global]" value="1"/>
					<label for="tax_rates[0][global]"><?php _e( 'Apply to whole country', 'iwp-txt' ); ?></label>
				</td>
				<td class="iwp_tax_rate"><input type="number" class="small-text" step="0.0001" min="0.0" name="tax_rates[0][rate]" value=""/></td>
				<td><span class="iwp_remove_tax_rate button-secondary"><?php _e( 'Remove Rate', 'iwp-txt' ); ?></span></td>
			</tr>
		<?php endif; ?>
	</table>
	<p>
		<span class="button-secondary" id="iwp_add_tax_rate"><?php _e( 'Add Tax Rate', 'iwp-txt' ); ?></span>
	</p>
	<?php
	echo ob_get_clean();
}

/**
 * Descriptive text callback.
 *
 * Renders descriptive text onto the settings field.
 *
 * @since 2.1.3
 * @param array $args Arguments passed by the setting
 * @return void
 */
function iwp_descriptive_text_callback( $args ) {
	echo esc_html( $args['desc'] );
}

/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $iwp_options Array of all the EDD Options
 * @return void
 */
if ( ! function_exists( 'iwp_license_key_callback' ) ) {
	function iwp_license_key_callback( $args ) {
		$iwp_options = get_option( 'iwp_settings' );

		if ( isset( $iwp_options[ $args['id'] ] ) )
			$value = $iwp_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="iwp_settings[' . $args['id'] . ']" name="iwp_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'iwp-txt' ) . '"/>';
		}
		$html .= '<label for="iwp_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );

		echo $html;
	}
}

/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function iwp_hook_callback( $args ) {
	do_action( 'iwp_' . $args['id'] );
}
