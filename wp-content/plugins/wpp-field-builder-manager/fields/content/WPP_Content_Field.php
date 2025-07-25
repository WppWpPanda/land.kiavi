<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPP_Form_Field' ) ) {
	return;
}

/**
 * Class WPP_Content_Field
 *
 * Выводит произвольный HTML-контент внутри формы.
 * Полезен для разделов, описаний и инфо-блоков.
 */
class WPP_Content_Field extends WPP_Form_Field {

	public function render() {
		$this->render_wrapper_start();

		$name    = esc_attr( $this->get_name() );
		$label   = ! empty( $this->args['label'] ) ? '<h3 class="wpp-content-label">' . esc_html( $this->args['label'] ) . '</h3>' : '';
		$content = ! empty( $this->args['content'] ) ? $this->args['content'] : '';

		echo $label;
		echo '<div class="wpp-content-body">';
		echo wp_kses_post( $content );
		echo '</div>';

		$this->render_wrapper_end();
	}
}