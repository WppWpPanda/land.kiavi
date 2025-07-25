<?php
/**
 * Функция выводит HTML-прелоадер в footer сайта
 */
function wpp_render_global_loader() {
	?>
	<div id="wpp-loader-screen" class="wpp-loader-overlay" style="display: none;">
		<div class="wpp-loader-content text-center">
			<div class="spinner-border text-primary" role="status">
				<span class="visually-hidden">Loading...</span>
			</div>
			<p class="mt-3">Please wait while we process your data</p>
		</div>
	</div>
	<?php
}
add_action('wp_footer', 'wpp_render_global_loader');