<?php
// generate-commitment-pdf.php

use Dompdf\Dompdf;
use Dompdf\Options;

// Защита от прямого доступа
if (!defined('ABSPATH')) {
	die('Direct access is not allowed.');
}

function generate_commitment_pdf() {
	$options = new Options();
	$options->set('isHtml5ParserEnabled', true);
	$options->set('isPhpEnabled', true);
	$dompdf = new Dompdf($options);

	global $loan_id;

	// Данные (можно подставлять динамически)
	$data = [
		'loan_number' => $loan_id ?: '567',
		'date' => 'July 17, 2024',
		'expires' => '15 days from date above',
		'property' => '16720 NW 78 Place, Miami Lakes, FL, 33016',
		'borrower' => 'Addiel Group Services Corp',
		'lender' => 'LendVent SPV 1 LLC',
		'total_loan_amount' => '$1,370,000.00',
		'initial_loan' => '$1,170,000.00 ($1,463,000.00 Purchase Price)',
		'construction_reserve' => '$200,000.00 (Total Budget of $200,000.00)',
		'term' => '12 months',
		'interest_rate' => '11.95%',
		'lender_points' => '2.00%',
		'broker_points' => '2.00%',
		'diligence_fee' => '$1,995.00',
		'interest_reserve' => '3 months',
		'due_diligence' => 'Borrower to provide all necessary Due Diligence to Lender. Appraisal, borrower credit and background check.',
		'other_expenses' => 'Lender Legal',
		'guarantor' => 'Addiel Rodriquez Lopez',
		'signer_name' => 'Daniel Ifraimov',
		'signer_title' => 'Managing Member',
		'company' => 'Lendvent LLC',
		'address' => '1160 Kane Concourse, Suite 305 Bay Harbor Islands, FL 33154',
		'phone' => '305.747.7037',
		'website' => 'www.lendvent.com'
	];

	// Путь к логотипу
	$logo_path = WP_PLUGIN_DIR . '/landvent-manager-panel/assets/img/img.png';
	if (!file_exists($logo_path)) {
		die('Logo not found at: ' . $logo_path);
	}
	$imageData = file_get_contents($logo_path);
	$logo_base64 = 'data:image/png;base64,' . base64_encode($imageData);

	// HTML
	$html = '
    <html>
    <head>
        <style>
            @page { margin: 50px; }
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 0;
                line-height: 1.4;
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            .contact {
                text-align: right;
                font-size: 11px;
                line-height: 1.4;
            }
            .logo {
                max-width: 160px;
                height: auto;
            }
            .date {
                font-size: 12px;
                margin-bottom: 8px;
            }
            .expires {
                font-size: 12px;
                font-style: italic;
                margin-bottom: 15px;
            }
            .re {
                font-size: 12px;
                margin-bottom: 20px;
            }
            .main-title {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                text-decoration: underline;
                margin: 20px 0 25px 0;
            }
            .field {
                margin-bottom: 6px;
            }
            .label {
                font-weight: bold;
                display: inline-block;
                width: 180px;
                vertical-align: top;
            }
            .value {
                display: inline-block;
            }
            .footer {
                margin-top: 50px;
            }
            .signature-line {
                display: inline-block;
                width: 300px;
                border-top: 1px solid #000;
            }
            .signature-info {
                display: inline-block;
                margin-left: 20px;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <!-- Шапка: контакты и логотип -->
        <div class="header">
            <div class="contact">
                ' . $data['address'] . '<br>
                Phone: ' . $data['phone'] . ' | ' . $data['website'] . '
            </div>
            <img src="' . $logo_base64 . '" alt="Logo" class="logo" />
        </div>

        <!-- Дата и срок -->
        <div class="date">' . $data['date'] . '</div>
        <div class="expires">This Term Sheet expires ' . $data['expires'] . '.</div>

        <!-- RE -->
        <div class="re">RE: ' . $data['property'] . '</div>

        <!-- Заголовок -->
        <div class="main-title">Term Sheet</div>

        <!-- Поля -->
        <div class="field"><span class="label">Borrower:</span> <span class="value">' . $data['borrower'] . '</span></div>
        <div class="field"><span class="label">Lender:</span> <span class="value">' . $data['lender'] . '</span></div>
        <div class="field"><span class="label">Properties:</span> <span class="value">' . $data['property'] . '</span></div>
        <div class="field"><span class="label">Total Loan Amount:</span> <span class="value">' . $data['total_loan_amount'] . '</span></div>
        <div class="field"><span class="label">Initial Loan:</span> <span class="value">' . $data['initial_loan'] . '</span></div>
        <div class="field"><span class="label">Construction Reserve:</span> <span class="value">' . $data['construction_reserve'] . '</span></div>
        <div class="field"><span class="label">Term:</span> <span class="value">' . $data['term'] . '</span></div>
        <div class="field"><span class="label">Interest Rate:</span> <span class="value">' . $data['interest_rate'] . '</span></div>
        <div class="field"><span class="label">Lender Points:</span> <span class="value">' . $data['lender_points'] . '</span></div>
        <div class="field"><span class="label">Broker Points:</span> <span class="value">' . $data['broker_points'] . '</span></div>
        <div class="field"><span class="label">Diligence Fee:</span> <span class="value">' . $data['diligence_fee'] . '</span></div>
        <div class="field"><span class="label">Interest Reserve:</span> <span class="value">' . $data['interest_reserve'] . '</span></div>
        <div class="field"><span class="label">Due Diligence:</span> <span class="value">' . $data['due_diligence'] . '</span></div>
        <div class="field"><span class="label">Other Expenses:</span> <span class="value">' . $data['other_expenses'] . '</span></div>
        <div class="field"><span class="label">Guarantor:</span> <span class="value">' . $data['guarantor'] . '</span></div>

        <!-- Подпись -->
        <div class="footer">
            Accepted and Agreed:
            <div style="margin-top: 40px;">
                <div class="signature-line"></div>
                <div class="signature-info">
                    As Borrower Name<br>
                    Date: _________________________
                </div>
            </div>
            <div style="margin-top: 40px;">
                <div class="signature-line"></div>
                <div class="signature-info">
                    ' . $data['signer_name'] . '<br>
                    ' . $data['signer_title'] . ' ' . $data['company'] . '
                </div>
            </div>
        </div>
    </body>
    </html>';

	$dompdf->loadHtml($html);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();

	// Имя файла: Commitment_567__2024-07-17.pdf
	$filename = "Commitment_" . $data['loan_number'] . "__2024-07-17.pdf";
	$dompdf->stream($filename, ["Attachment" => true]);
	exit();
}
