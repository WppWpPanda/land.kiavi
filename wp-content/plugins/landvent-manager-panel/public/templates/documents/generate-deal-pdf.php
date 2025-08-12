<?php

use Dompdf\Dompdf;
use Dompdf\Options;

// Запрещаем прямой доступ
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function generate_deal_worksheet_pdf() {
	$options = new Options();
	$options->set('isHtml5ParserEnabled', true);
	$options->set('isPhpEnabled', true);
	$dompdf = new Dompdf($options);

	global $loan_id;
	$formData = wpp_get_loan_data_r( $loan_id );
	// Данные
	$data = [
		'loan_number' => $loan_id,
		'borrower'=> $formData['bower_name'],
		'credit_score' => '700',
		'property' =>  $formData['property_street'] . ', ' . $formData['property_city'] . ', ' . $formData['property_state'] . ', ' . $formData['property_zip'],
		'lot_size' => '0',
		'loan_type' =>  $formData['loan_type'],
		'property_value' => '$0.00',
		'loan_amount' => $formData['total_loan_amount'],
		'term' => $formData['term'] . ' months',
		'ltv' => '0.00%',
		'interest_rate' => $formData['interest_rate'] . '%',
		'monthly_interest' => $formData['interest_reserve_months'],
		'placement_fee' => '$0.00(0%)',
		'broker_fee' => $formData['fee_broker_fee'],
		'broker' => 'Mariia Khilko - Mariia<br>Khilko<br>email: mariia@lendvent.com',
		'prepayment_penalty' => '0.00 Months',
		'holdback' => '$0.00',
		'closing_date' => $formData['closing_date'],
		'phone' => '833-352-4100',
		'website' => 'lendvent.com'
	];

	// Путь к логотипу
	$logo_path = WP_PLUGIN_DIR . '/landvent-manager-panel/assets/img/img.png';
	$imageData = file_get_contents($logo_path);
	$base64 = 'data:image/jpeg;base64,' . base64_encode($imageData);

	// HTML (оптимизирован под DomPDF)
	$html = '
    <html>
    <head>
        <style>
            @page { margin: 40px; }
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 20px;
            }
            .logo-container {
                text-align: center;
                margin-bottom: 15px;
            }
            .logo {
                max-width: 220px;
                height: auto;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
                table-layout: fixed;
            }
            th, td {
                border: 1px solid #000;
                padding: 6px 8px;
                line-height: 1.4;
            }
            th {
                background-color: #f2f2f2;
                font-weight: bold;
                text-align: center;
            }
            .bb-none {
                border-bottom: none;
            }
            .bt-none {
                border-top: none;
            }
            .footer {
                text-align: center;
                margin-top: 25px;
                font-size: 14px;
                line-height: 1.6;
            }
            img.logo {
    max-width: 180px;
    position: absolute;
    right: 0;
    display: inline-block;
}

.logo-container {
    position: relative;
    width: 100%;
    height: 72px;
    margin-bottom: 50px;
}

.logo-bg {
    width: 600px;
    background-color: #8c7947;
    height: 100%;
    float: left;
    position: relative;
}

.logo-bg:after {
    content: '.';
    height: 0px;
    width: 0px;
    position: absolute;
    background-color: transparent;
    right: -72px;
    border: 36px solid #ffffff00;
    border-left: 36px solid #8c7947;
    border-top: 36px solid #8c7947;
    z-index:999
}
            
        </style>
    </head>
    <body>
        <!-- Логотип -->
        <div class="logo-container">
        
        
       
            <img src="' . $base64 . '" alt="Company Logo" class="logo" />
 
      
        </div>

        <!-- Таблица -->
        <table>
            <tr>
                <th colspan="4" style="font-size: 16px; padding: 10px;">
                    LOAN #' . $data['loan_number'] . ' DETAILS
                </th>
            </tr>
            <tr>
                <th colspan="2">Security</th>
                <th colspan="2">Loan Terms</th>
            </tr>
            <tr>
                <td>Borrower(s):</td>
                <td>' . $data['borrower'] . '</td>
                <td>Loan Amount:</td>
                <td>' . $data['loan_amount'] . '</td>
            </tr>
            <tr>
                <td>Guarantor(s):</td>
                <td></td>
                <td>Term:</td>
                <td>' . $data['term'] . '</td>
            </tr>
            <tr>
                <td>Credit Score:</td>
                <td>' . $data['credit_score'] . '</td>
                <td>LTV:</td>
                <td>' . $data['ltv'] . '</td>
            </tr>
            <tr>
                <td class="bb-none">Subject Property:</td>
                <td class="bb-none">' . $data['property'] . '</td>
                <td>Interest Rate:</td>
                <td>' . $data['interest_rate'] . '</td>
            </tr>
            <tr>
                <td class="bt-none"></td>
                <td class="bt-none"></td>
                <td>Monthly Interest:</td>
                <td>' . $data['monthly_interest'] . '</td>
            </tr>
            <tr>
                <td>Lot Size:</td>
                <td>' . $data['lot_size'] . '</td>
                <td>Placement Fee:</td>
                <td>' . $data['placement_fee'] . '</td>
            </tr>
            <tr>
                <td>Loan Type:</td>
                <td>' . $data['loan_type'] . '</td>
                <td>Broker Fee:</td>
                <td>' . $data['broker_fee'] . '</td>
            </tr>
            <tr>
                <td>Property Value:</td>
                <td>' . $data['property_value'] . '</td>
                <td>Broker:</td>
                <td>' . $data['broker'] . '</td>
            </tr>
            <tr>
                <td class="bb-none">Blanket Mortgages:</td>
                <td class="bb-none"></td>
                <td>Prepayment Penalty:</td>
                <td>' . $data['prepayment_penalty'] . '</td>
            </tr>
            <tr>
                <td class="bt-none"></td>
                <td class="bt-none"></td>
                <td>Holdback:</td>
                <td>' . $data['holdback'] . '</td>
            </tr>
            <tr>
                <td>Closing Date:</td>
                <td colspan="3">' . $data['closing_date'] . '</td>
            </tr>
            <tr>
                <td>Approved By:</td>
                <td colspan="3"></td>
            </tr>
        </table>

        <!-- Футер -->
        <div class="footer">
            ' . $data['phone'] . '<br>
            ' . $data['website'] . '
        </div>
    </body>
    </html>';

	$dompdf->loadHtml($html);
	$dompdf->setPaper('A4', 'portrait');
	$dompdf->render();

	// Имя файла: DealWorksheet_600_Moree_2025-08-06.pdf
	$filename = "DealWorksheet_" . $data['loan_number'] . "_Moree_2025-08-06.pdf";
	$dompdf->stream($filename, ["Attachment" => true]);
	exit();
}