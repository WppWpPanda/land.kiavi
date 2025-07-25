<?php
/**
 * Шорткод: [loan_start_button link="/step/personal" text="Start Application"]
 *
 * Выводит кнопку, центрированную посередине экрана.
 * Занимает всю высоту контейнера, кроме хэдера и футера
 *
 * @package WPP_Loan_Application
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Рендерит шорткод с центрированной кнопкой
 *
 * @param array $atts Атрибуты шорткода
 * @return string HTML output
 */
function wpp_render_loan_start_button($atts) {
	// Получаем параметры из шорткода
	$atts = shortcode_atts(
		array(
			'link' => '/step/personal',
			'text' => 'Start Application',
			'btn_class' => 'btn btn-primary'
		),
		$atts,
		'loan_start_button'
	);

	$link = esc_url($atts['link']);
	$text = esc_html($atts['text']);
	$btn_class = esc_attr($atts['btn_class']);

	ob_start();
	?>
	<div class="loan-start-wrapper d-flex align-items-center justify-content-center">
		<a href="<?php echo $link; ?>" class="<?php echo $btn_class; ?>">
			<?php echo $text; ?>
		</a>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode('loan_start_button', 'wpp_render_loan_start_button');