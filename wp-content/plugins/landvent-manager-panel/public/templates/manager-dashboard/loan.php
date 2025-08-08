<?php
/**
 * Manager Dashboard - Loan Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

global $wp_query, $loan_id;

$loanData = wpp_get_loan_data_r( $loan_id );
if ( empty( $loanData ) ) {
	$loanData = [];
}
$total = wpp_get_total_loan_amount( $loan_id );

if ( $total === false ) {
	error_log( "wpp_get_total_loan_amount вернул false для loan_id = $loan_id" );
	$total = 0;
}

$loanData['baseAmount'] = $total;
$loanData['LOAN_ID'] = $loan_id;
// Преобразуем в JSON (используем JSON_HEX_APOS и JSON_HEX_QUOT для экранирования)
$loanDataJson = json_encode( $loanData, JSON_HEX_APOS | JSON_HEX_QUOT );
?>
    <script>
        // Передаем данные в JavaScript
        const loanData = JSON.parse('<?php echo $loanDataJson; ?>');
        console.log(loanData); // Проверяем в консоли
    </script>
    <div class="container-fluid">
        <div class="row">

            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="wpp-iside">
					<?php do_action( 'wpp_lmp_nav_side' ) ?>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="loan-status-bar">
                    <div class="loan-status-bar__left">
                        <span class="loan-status-bar__icon"><i class="fas fa-briefcase"></i></span>
                        <span class="loan-status-bar__title">test Standard Loan</span>
                    </div>
                    <div class="loan-status-bar__right">
                        <!--<select name="status" class="loan-status-bar__select">
                            <option value="intake_form_lead">Intake Form Lead</option>
                            <option value="other_status">Other Status</option>
                        </select>
                        <button class="loan-status-bar__button">Ledger</button>
                        <button class="loan-status-bar__button">Fund</button>-->
                        <button class="loan-status-bar__button"><i class="fas fa-trash"></i>Delete</button>
                       <!-- <button class="loan-status-bar__more-actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>-->
                    </div>
                </div>

				<?php
				echo wpp_get_form_messages();
				?>

                <form id="wpp-sl-form" method="post">
                    <div class="info-cards-block">
                        <div class="wpp-loan-block closing-date-block">Closing Date<span>Sep 16, 2024</span></div>
                        <div class="wpp-loan-block total-loan-block">Total Loan<span>-</span></div>
                        <div class="wpp-loan-block advance-at-closing-block">Advance at Closing
                            <span>
                                <?php echo format_usd_price( wpp_get_total_loan_amount() ); ?>
                            </span>
                        </div>
                    </div>

                    <input type="hidden" value="<?php echo $loan_id ?>" name="current_loan_id">
					<?php wp_nonce_field( 'wpp_save_loan_data', 'wpp_loan_nonce' ); ?>

					<?php
					/**
					 * @hooked
					 * wpp_term_applicant - 10
					 * wpp_term_property_details - 20
					 * wpp_term_sheet_details - 30
					 * wpp_term_additional_reserve - 40
					 * wpp_term_fees - 50
					 * wpp_term_milestones - 60
					 * wpp_term_payments - 70
					 * wpp_term_conditions - 80
					 * wpp_term_investors - 90
					 * wpp_term_attorney - 100
					 * wpp_term_title_company - 110
					 * wpp_term_required_documents - 120
					 * wpp_term_required_documents - 130
					 */
					do_action( 'wpp_lmp_loan_content' ) ?>
                </form>

            </main>
        </div>
    </div>
<?php get_footer();