<?php
use Dompdf\Dompdf;

use Dompdf\Options;

function wpp_generate_loan_summary_pdf($steps_data) {
// Подключаем DomPDF
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Start HTML
$html = '<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; }
    .header { text-align: center; margin-bottom: 20px; }
    .loan-number { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
    .address { margin-bottom: 20px; }
    .section-title { font-size: 16px; font-weight: bold; margin: 15px 0 10px 0; }
    .divider { border-top: 1px solid #ccc; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    td { padding: 5px 0; vertical-align: top; }
    .label { font-weight: bold; width: 50%; }
    .value { text-align: right; }
    .footer { margin-top: 30px; font-size: 12px; color: #666; }
    .note { font-size: 11px; color: #666; margin-top: 20px; }
</style>';

// Header
$html .= '<div class="header">';
	$html .= '<div class="loan-number"># ' . esc_html($steps_data[4]['formData']['loan_number'] ?? 'Yyy') . '</div>';
	$html .= '<div class="address">' . esc_html($steps_data[7]['formData']['address_line_1'] ?? 'N/A') . ', ' .
		esc_html($steps_data[7]['formData']['city'] ?? 'N/A') . ', ' .
		esc_html($steps_data[7]['formData']['state'] ?? 'N/A') . ', ' .
		esc_html($steps_data[7]['formData']['zip_code'] ?? 'N/A') . '</div>';
	$html .= '</div>';

// Date
$html .= '<div style="text-align: right; margin-bottom: 20px;">' . date('F j, Y') . '</div>';

// Loan Details Section
$html .= '<div class="section-title">LOAN DETAILS</div>';
$html .= '<div class="divider"></div>';

// Total Loan Amount
$html .= '<div class="section-title">Total Loan Amount</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Purchase Loan Amount</td><td class="value">$' . number_format(($steps_data[4]['formData']['total_loan_amount'] ?? 0), 2) . '</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">$' . number_format(($steps_data[4]['formData']['purchase_price'] ?? 0), 2) . '</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">$' . number_format(($steps_data[4]['formData']['rehab_cost'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// Monthly Payment
$html .= '<div class="section-title">Monthly Payment</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Interest Rate</td><td class="value">' . esc_html($steps_data[4]['formData']['chosen_rate'] ?? 'N/A') . '%</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">$' . number_format(($steps_data[4]['formData']['monthly_payment'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// Cash Required at Closing
$html .= '<div class="section-title">Cash Required at Closing</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Down Payment</td><td class="value">$' . number_format(($steps_data[4]['formData']['down_payment'] ?? 0), 2) . ' (' .
			esc_html($steps_data[4]['formData']['down_payment_percentage'] ?? '0') . '%)</td></tr>';
	$html .= '</table>';

// Origination Fee
$html .= '<div class="section-title">Origination Fee</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Service Fee</td><td class="value">$' . number_format(($steps_data[4]['formData']['origination_fee'] ?? 0), 2) . ' (' .
			esc_html($steps_data[4]['formData']['origination_fee_percentage'] ?? '0') . '%)</td></tr>';
	$html .= '</table>';

// Pro-rated Interest
$html .= '<div class="section-title">Pro-rated Interest</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Third Party Costs *</td><td class="value">$' . number_format(($steps_data[4]['formData']['third_party_costs'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// Additional Details
$html .= '<div class="section-title">Additional Details</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Loan Term</td><td class="value">' . esc_html($steps_data[4]['formData']['loan_term'] ?? 'N/A') . ' Year</td></tr>';
	$html .= '<tr><td class="label">Loan Type</td><td class="value">' . esc_html($steps_data[4]['formData']['loan_type'] ?? 'N/A') . '</td></tr>';
	$html .= '</table>';

// Interest-Only Period
$html .= '<div class="section-title">Interest-Only Period</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Amortization Period</td><td class="value">' . esc_html($steps_data[4]['formData']['amortization_period'] ?? 'N/A') . ' Year</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">Interest Only</td></tr>';
	$html .= '</table>';

// Preferred Signing Date
$html .= '<div class="section-title">Preferred Signing Date</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Purpose</td><td class="value">' . esc_html($steps_data[4]['formData']['signing_date'] ?? 'N/A') . '</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">New Purchase</td></tr>';
	$html .= '</table>';

// Lien
$html .= '<div class="section-title">Lien</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Property Type</td><td class="value">' . esc_html($steps_data[4]['formData']['lien_position'] ?? 'N/A') . ' Position</td></tr>';
	$html .= '<tr><td class="label"></td><td class="value">Single-Family Home</td></tr>';
	$html .= '</table>';

// Occupancy
$html .= '<div class="section-title">Occupancy</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Purchase Price</td><td class="value">$' . number_format(($steps_data[4]['formData']['purchase_price'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// As-is-Value
$html .= '<div class="section-title">As-is-Value</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Estimated ARV</td><td class="value">$' . number_format(($steps_data[4]['formData']['after_repair_value'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// Loan-to-Cost
$html .= '<div class="section-title">Loan-to-Cost</div>';
$html .= '<table>';
	$html .= '<tr><td class="label">Holdback Amount</td><td class="value">$' . number_format(($steps_data[4]['formData']['holdback_amount'] ?? 0), 2) . '</td></tr>';
	$html .= '</table>';

// Pre-Payment Penalty
$html .= '<div class="section-title">Pre-Payment Penalty</div>';
$html .= '<table>';
	$html .= '<tr><td class="label"></td><td class="value">None</td></tr>';
	$html .= '</table>';

// Footer Notes
$html .= '<div class="note">';
	$html .= 'The total loan amount is an estimate, and may be subject to change. The amount also does not include third party settlement costs that may be required to close your loan. For more details on those potential costs, please contact your settlement agent.<br><br>';
	$html .= 'The loan is also subject to applicable Taxes, Insurance Imposonds and Association fees.<br><br>';
	$html .= '* Some third party costs include: Escrow/title fees, property taxes, and hazard insurance. These fees vary by country so please contact your closing agent to get a full schedule of fees and final cash to close amount.<br><br>';
	$html .= '* For the property located in the state indicated, the borrower must be an entity, not a natural person.';
	$html .= '</div>';

// Footer
$html .= '<div class="footer">';
	$html .= 'Generated by Kiavi Loan Application • ' . date('F j, Y');
	$html .= '</div>';

// Загружаем HTML в DomPDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Сохраняем PDF
$upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/loan-terms/';
$file_url = $upload_dir['baseurl'] . '/loan-terms/';

if (!is_dir($file_path)) {
wp_mkdir_p($file_path);
}

$filename = 'kiavi-loan-summary-' . date('YmdHis') . '.pdf';
$output = $dompdf->output();
file_put_contents($file_path . $filename, $output);

return $file_url . $filename;
}