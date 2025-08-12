<?php
// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$broker_id = get_query_var( 'item_id' );

if ( empty( $broker_id ) ) {
	$template = 'list-broker';
} else {
	$template = 'single-broker';
}

require_once $template . '.php';