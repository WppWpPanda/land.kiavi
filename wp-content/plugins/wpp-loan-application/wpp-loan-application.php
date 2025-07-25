<?php
/**
 * Plugin Name: WPP Loan Application
 * Description: Многошаговая форма заявки на кредит по недвижимости
 * Version: 1.0.0
 * Author: WebAndAd Team
 * Developer: WP_Panda
 */

define( 'WPP_LOAN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPP_LOAN_URL', plugin_dir_url( __FILE__ ) );

require_once 'dew-tools.php';

// Режим разработки (включается/выключается вручную)
define( 'WPP_LOAN_DEV_MODE', 0 );

// Конфигурация шагов
define( 'WPP_LOAN_STEPS', json_encode( [
	'1' => [
		'slug'  => 'personal',
		'title' => 'Step 1',
		'short' => '[wpp_loan_application_step_1]'
	],
	'2' => [
		'slug'  => 'borrower',
		'title' => 'Step 2',
		'short' => '[wpp_loan_application_step_2]'
	],
	'3' => [
		'slug'  => 'address',
		'title' => 'Step 3',
		'short' => '[wpp_loan_application_step_3]'
	],
	'4' => [
		'slug'  => 'estimate-rate',
		'title' => 'Step 4',
		'short' => '[wpp_loan_application_step_4]'
	],
	'5' => [
		'slug'  => 'eligibility-confirmations',
		'title' => 'Step 5',
		'short' => '[wpp_loan_application_step_5]'
	],
	'6' => [
		'slug'  => 'property-address',
		'title' => 'Step 6',
		'short' => '[wpp_loan_application_step_6]'
	],
	'7' => [
		'slug'  => 'preferred-signing-date',
		'title' => 'Step 7',
		'short' => '[wpp_loan_application_step_7]'
	],
	'8' => [
		'slug'  => 'combined-confirmations',
		'title' => 'Step 8',
		'short' => '[wpp_loan_application_step_7]'
	],
	'9' => [
		'slug'  => 'completed',
		'title' => 'thanks',
		'short' => '[wpp_render_loan_thanks]'
	]
] ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/install.php';
register_activation_hook( __FILE__, 'wpp_create_loan_application_table' );
register_activation_hook( __FILE__, 'wpp_loan_create_pages' );

// Подключение классов
require_once WPP_LOAN_PATH . 'includes/class-wpp-loan-assets.php';
require_once WPP_LOAN_PATH . 'includes/class-wpp-loan-session.php';
require_once WPP_LOAN_PATH . 'includes/functions/wpp-helper.php';

add_action( 'init', [ 'WPP_Loan_Session_Handler', 'start_session' ], 1 );

require_once WPP_LOAN_PATH . 'includes/pdf-generator.php';

$i = 1;
while ( $i <= 8 ) {
	// Шорткоды
	require_once WPP_LOAN_PATH . 'shortcodes/step-' . $i . '.php';
	$i ++;
}

require_once WPP_LOAN_PATH . 'shortcodes/home.php';

require_once WPP_LOAN_PATH . 'shortcodes/thanks.php';

require_once WPP_LOAN_PATH . 'includes/functions/loader.php';

// Гарант
require_once WPP_LOAN_PATH . 'includes/functions/guarantor.php';
// AJAX обработчики
require_once WPP_LOAN_PATH . 'ajax/handle-step.php';