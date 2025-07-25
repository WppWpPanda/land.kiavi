<?php
/*
Template Name: LandVent — Single Loan
*/
get_header(); ?>
    <style>
        body.page-template-single-loan .navbar {
            position: fixed;
            width: 100vw;
            z-index: 99;
        }

        body.page-template-single-loan nav#sidebar {
            position: fixed;
        }

        body.page-template-single-loan nav#sidebar,
        body.page-template-single-loan main {
            margin-top: 57px;
        }

        .loan-status-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
            padding: 10px 0;
        }

        .loan-status-bar__left,
        .loan-status-bar__right {
            display: flex;
            align-items: center;
        }

        .loan-status-bar__icon {
            margin-right: 5px;
        }

        .loan-status-bar__title {
            font-weight: bold;
        }

        .loan-status-bar__select {
            margin-right: 10px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .loan-status-bar__button {
            margin-left: 5px;
            padding: 5px 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }

        .loan-status-bar__more-actions {
            margin-left: 5px;
            padding: 5px 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <style>
        .page-template-single-loan .wpp-field {
            display: flex;
        }

        .page-template-single-loan .wpp-field label.form-label,
        .page-template-single-loan h3.wpp-content-label,
        .page-template-single-loan .wpp-wpp_datepicker_field label {
            width: 200px;
            min-width: 200px;
            font-size: .8rem;
            font-weight: 300;
        }

        .page-template-single-loan .wpp-wpp_select_field select.form-control {
            height: calc(.8em + 0.75rem + 2px);
            font-size: .8rem;
            padding: .1rem .5rem;
            font-weight: 300;
        }

        .page-template-single-loan .wpp-wpp-text input.form-control,
        .page-template-single-loan .wpp-wpp_datepicker_field input.form-control {
            height: calc(.8em + 0.75rem + 2px);
            font-size: .8rem;
            padding: .1rem .5rem;
        }

        .wpp-field.wpp-wpp_checkbox_field .form-check {
            display: flex;
            flex-direction: row-reverse;
            padding: 0;
            margin-bottom: 0;
        }

        .page-template-single-loan .wpp-field label.form-check-label {
            width: 222px;
            min-width: 200px;
            font-size: .8rem;
            font-weight: 300;
        }

        .page-template-single-loan .wpp-field {
            margin-bottom: .5rem;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_checkbox_field.wpp-no-label-inverse .form-check {
            display: flex;
            flex-direction: row;
            margin-left: 220px;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_checkbox_field.wpp-no-label-inverse.no-left .form-check {
            display: flex;
            flex-direction: row;
            margin-left: 20px;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_checkbox_field.wpp-no-label-inverse .form-check input {
            margin-right: 10px;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_radio_field .form-check {
            margin-left: 200px;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_radio_field {
            display: flex;
            flex-direction: column;
        }

        .page-template-single-loan .wpp-field.wpp-wpp_radio_field {
        }

        .page-template-single-loan .wpp-field.wpp-wpp_radio_field .form-check .form-check-label {
            width: 100%;
        }

        .accordion {
            min-width: 100%;
        }

    </style>

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
                        <select name="status" class="loan-status-bar__select">
                            <option value="intake_form_lead">Intake Form Lead</option>
                            <option value="other_status">Other Status</option>
                        </select>
                        <button class="loan-status-bar__button">Ledger</button>
                        <button class="loan-status-bar__button">Fund</button>
                        <button class="loan-status-bar__button">Terminate</button>
                        <button class="loan-status-bar__more-actions">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>

                <?php
                echo wpp_get_form_messages();
                var_dump($_POST);
                ?>

                <form id="wpp-sl-form" method="post">
                    <input type="hidden" value="<?php echo $_GET['loan'] ?>" name="current_loan_id">
	                <?php wp_nonce_field('wpp_save_loan_data', 'wpp_loan_nonce'); ?>
                    <div class="wpp-save-button-panel">
                        <button class="wpp-save-button">Save</button>
                    </div>
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
<script>
    jQuery(document).ready(function($) {
        // Получаем loan_id из скрытого поля формы
        var loanId = $('input[name="current_loan_id"]').val();

        if (loanId) {
            // Запрашиваем данные с сервера
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'wpp_get_loan_data',
                    loan_id: loanId
                },
                success: function(response) {
                    if (response.success) {
                        // Заполняем все поля формы соответствующими значениями
                        $.each(response.data, function(key, value) {
                            var $field = $('[name="' + key + '"]');

                            if ($field.is(':checkbox, :radio')) {
                                // Для чекбоксов и радиокнопок
                                $field.filter('[value="' + value + '"]').prop('checked', true);
                            } else if ($field.is('select')) {
                                // Для выпадающих списков
                                $field.val(value).trigger('change');
                            } else if ($field.is('input[type="file"]')) {
                                // Для файловых полей ничего не делаем
                                return;
                            } else {
                                // Для обычных полей ввода
                                $field.val(value);
                            }
                        });
                    } else {
                        console.error('Error:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    });
</script>
<?php get_footer(); ?>