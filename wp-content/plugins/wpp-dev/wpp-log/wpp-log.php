<?php
/**
 * Описание файлв
 *
 * @package wp.dev
 * @version 1.0.0
 * @author WP_Panda
 */

defined( 'ABSPATH' ) || exit;


function wpp_log_data( $data = null, $dir = null ) {
	$dir = ! empty( $dir ) ? "/{$dir}" : '';

	$upload_dir = wp_upload_dir();

	$log_dir = $upload_dir['basedir'] . '/wpp-logs' . $dir;

	wp_mkdir_p( $log_dir );

	if ( is_array( $data ) || is_object( $data ) ) {
		$data = print_r( $data, true );
	}

	$log = "User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date( "d.m.Y - H:i:s" ) . PHP_EOL;
	$log .= $data . PHP_EOL;
	$log .= '---------------------------------------------------------------------------' . PHP_EOL;

	$file_name = is_multisite() ? '/' . home_url() . '_'. date( "d.m.Y" ) . '.log' : '/' . date( "d.m.Y" ) . '.log' ;

	file_put_contents( $log_dir . $file_name, $log, FILE_APPEND );
}