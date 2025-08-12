<?php
/**
 * Brokers Management Page Template
 *
 * Displays the admin interface for managing brokerages in the LandVent Manager Panel.
 * Includes:
 * - A header and container layout.
 * - An "Add Brokerage" button that opens a modal form.
 * - A modal form for adding new brokerages via AJAX.
 * - Form configuration array with field definitions.
 * - Security nonce for AJAX submission.
 *
 * This file is typically included within an admin menu page.
 *
 * @package LandVent_Manager_Panel
 * @subpackage Admin/UI
 * @since 1.0.0
 */

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

landvent_manager_header_main();

$broker_data = get_broker_by_id( $broker_id );

if ( function_exists( '_wpp_console_log' ) ) {
	_wpp_console_log( $broker_data );
}

extract( $broker_data );

$brokerage_form_config = [
	'form_title'    => 'New Brokerage',
	'form_id'       => 'wpp-brokerage-form',
	'fields'        => [
		'brok_brokerage_name'   => [
			'type'        => 'text',
			'label'       => 'Brokerage:',
			'placeholder' => 'Enter brokerage name',
			'width'       => 'full',
			'default'     => $brok_brokerage_name ?? '',
		],
		'brok_parent_brokerage' => [
			'type'        => 'text',
			'label'       => 'Parent Brokerage:',
			'placeholder' => 'Enter parent brokerage name',
			'width'       => 'full',
			'default'     => $brok_parent_brokerage ?? '',
		],
		'brok_address'          => [
			'type'        => 'text',
			'label'       => 'Address:',
			'placeholder' => 'Street address',
			'width'       => 'full',
			'default'     => $brok_address ?? '',
		],
		'brok_city'             => [
			'type'        => 'text',
			'label'       => 'City:',
			'placeholder' => 'City',
			'width'       => '1/2',
			'default'     => $brok_city ?? '',
		],
		'brok_county'           => [
			'type'        => 'text',
			'label'       => 'County:',
			'placeholder' => 'County',
			'width'       => '1/2',
			'default'     => $brok_county ?? '',
		],
		'brok_state'            => [
			'type'        => 'text',
			'label'       => 'State:',
			'placeholder' => 'State',
			'width'       => '1/2',
			'default'     => $brok_state ?? '',
		],
		'brok_zip_code'         => [
			'type'        => 'text',
			'label'       => 'Zip Code:',
			'placeholder' => 'ZIP code',
			'width'       => '1/2',
			'default'     => $brok_zip_code ?? '',
		],
		'brok_broker_bdm'       => [
			'type'        => 'text',
			'label'       => 'Broker BDM:',
			'placeholder' => 'Enter broker BDM',
			'width'       => 'full',
			'edit_button' => true,
			'default'     => $brok_broker_bdm ?? '',
		],
	],
	'submit_button' => [
		'text'    => 'Save Broker',
		'classes' => [ 'wpp-button', 'wpp-button-primary' ],
	],
];

?>


    <div class="wpp-lk-wrap">

        <div class="wpp-manager-container">
            <h1 class="wpp-sl-h">
				<?php esc_html_e( 'Broker', 'landvent-manager' ); ?>
				<?php echo ! empty( $brok_brokerage_name ) ? ' - ' . $brok_brokerage_name : ''; ?>
            </h1>
            <div class="wpp-action-panel"></div>
        </div>

        <div class="wpp-lk-form-wrap">
            <form id="<?php echo esc_attr( $brokerage_form_config['form_id'] ); ?>" class="wpp-min-form" method="post">
                <div class="wpp-form row">
					<?php foreach ( $brokerage_form_config['fields'] as $name => $config ): ?>
						<?php
						// Dynamically instantiate field class (e.g., WPP_Text_Field)
						$field_class = 'WPP_' . ucfirst( $config['type'] ) . '_Field';

						if ( class_exists( $field_class ) ) {
							$field = new $field_class( array_merge( $config, [ 'name' => $name ] ) );
							$field->render();
						} else {
							// Fallback: render simple input if class not found
							printf(
								'<div class="wpp-form-field wpp-width-%s">
                                    <label>%s</label>
                                    <input type="text" name="%s" placeholder="%s" %s />
                                </div>',
								esc_attr( $config['width'] ),
								esc_html( $config['label'] ),
								esc_attr( $name ),
								esc_attr( $config['placeholder'] ),
								! empty( $config['required'] ) ? 'required' : ''
							);
						}
						?>
					<?php endforeach; ?>
                </div>

                <!-- Security: Nonce field for AJAX verification -->
				<?php wp_nonce_field( 'wpp_brokerage_nonce', '_ajax_nonce', false ); ?>

                <!-- Submit Button -->
                <div class="wpp-form-actions">
                    <button type="submit"
                            class="<?php echo esc_attr( implode( ' ', $brokerage_form_config['submit_button']['classes'] ) ); ?>">
						<?php echo esc_html( $brokerage_form_config['submit_button']['text'] ); ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="wpp-data-table"></div>

    </div>
<?php
// -------------------------------
// 4. Render Page Footer
// -------------------------------
// Outputs the main admin footer (e.g., scripts, closing tags)
// Defined in another file (likely core/templates.php)
landvent_manager_footer_main();