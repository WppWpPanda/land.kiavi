<?php
/**
 * Manager Dashboard - Loan Template
 */

if (!defined('ABSPATH')) exit;

global $wp_query;
$loan_id = isset($wp_query->query_vars['loan_id']) ? $wp_query->query_vars['loan_id'] : 0;
?>

<div class="manager-dashboard-loan">
	<h1>Loan Details: <?php echo esc_html($loan_id); ?></h1>

	<!-- Ваш контент для отображения информации о займе -->

	<?php
	// Здесь можно добавить логику для работы с вашей таблицей wpp_loans_full_data
	global $wpdb;
	$loan_data = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}wpp_loans_full_data WHERE loan_id = %s",
		$loan_id
	));

	if ($loan_data) {
		echo '<div class="loan-details">';
		// Отображаем данные о займе
		echo '</div>';
	} else {
		echo '<p>Loan not found</p>';
	}
	?>
</div>