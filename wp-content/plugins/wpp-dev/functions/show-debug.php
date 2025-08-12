<?php
/**
 * Created by PhpStorm.
 * User: WP_PANDA
 * Date: 09.03.2019
 * Time: 13:09
 */

if ( ! function_exists( 'wpp_dump' ) ) :

	/**
	 * var_dump for wp-panda
	 *
	 * @since 0.0.1
	 *
	 * @param $data
	 */
	function wpp_dump( $data ) {
		if ( is_wpp_panda() ) {
			echo '<pre>';
			var_dump( $data );
			echo '</pre>';
		}
	}

endif;

if ( ! function_exists( 'wpp_d_log' ) ) :
	/**
	 * echo log in file
	 *
	 * @since 0.0.1
	 *
	 * @param $log
	 */
	function wpp_d_log( $log ) {
		//if ( is_wpp_panda() ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
		//}
	}
endif;



if ( ! function_exists( '_wpp_console_log' ) ) :
	/**
	 * Выводит данные в консоль браузера (аналог console.log в JavaScript)
	 *
	 * @since 0.0.1
	 * @param mixed $data Данные для вывода
	 */
	function _wpp_console_log( $data ) {
		if ( ! is_wpp_panda() ) {
			return; // Проверка окружения (аналогично вашей логике)
		}

		// Преобразуем данные в JSON для передачи в JavaScript
		$json_data = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		// Выводим JavaScript-код, который передаст данные в console.log
		echo "<script>console.log('PHP Debug:', $json_data);</script>";
	}
endif;