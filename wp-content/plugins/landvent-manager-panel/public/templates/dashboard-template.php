<?php
/*
Template Name: LandVent — Dashboard Template
*/
get_header(); ?>
    <style>


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
                    <div class="add-column">
                        <button id="add-column-btn">
                            <svg class="icon icon-plus" viewBox="0 0 24 24" width="24" height="24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                    </div>
                    <div id="kanban-stats">
                        <div class="tl int-adv-tp">Initial Advance:
                            <span>0</span>
                        </div>
                        <div class="tl int-tot-tp">
                            Total Facility:
                            <span>0</span>
                        </div>
                        <div class="tl int-loan-count">
                            <span>0</span>loans
                        </div>
                    </div>
                </div>
				<?php
				$data  = get_colums_data();
				$array = wpp_get_all_trello_columns(); // колонки трело


				$all_card_ids = array();

				foreach ( $array as $item ) {
					// Декодируем JSON строку в массив
					$card_ids = json_decode( $item->card_ids, true );

					// Объединяем с общим массивом
					if ( is_array( $card_ids ) ) {
						$all_card_ids = array_merge( $all_card_ids, $card_ids );
					}
				}

				// Теперь $all_card_ids содержит все ID карт


				//debugPanel( $data );
				?>

                <div class="trello-board">


                    <div class="columns-container">
                        <div class="column" data-column-id="New">
                            <div class="column-header">
                                <div class="colum-header-wrap">
                                    <div class="column-header-left">
                                        <h3 contenteditable="true">New</h3>
                                        <div class="wpp-loan-count"><span>loans</span><b></b></div>
                                    </div>
                                    <div class="column-header-right">
                                        <div class="wpp-nit"><span>nit. Adv.:</span><b></b></div>
                                        <div class="wpp-total"><span>Total:</span><b></b></div>
                                    </div>
                                </div>
                            </div>
                            <div class="cards-container ui-sortable">

								<?php foreach ( $data as $key => $value ) :
									if ( in_array( $key, $all_card_ids ) ) {
										continue;
									}
									?>
                                    <a href="<?php echo get_home_url(); ?>/manager-dashboard/loan/<?php echo $key ?>" class="card"
                                       data-card-id="<?php echo $key ?>">
                                        <div class="card-content" contenteditable="true">
                                            <div class="h-top">
                                                #<?php echo $key; ?> | <?php echo $value['raw_data']->s2_entity_name; ?>
                                            </div>
                                        </div>
                                        <div class="address">
											<?php echo $value['raw_data']->s3_address_line_1; ?>
											<?php if ( ! empty( $value['raw_data']->s3_address_line_2 ) ) {
												echo ', ' . $value['raw_data']->s3_address_line_2;
											} ?><br>
											<?php echo $value['raw_data']->s3_city; ?>
                                            , <?php echo $value['raw_data']->s3_state; ?>
                                            , <?php echo $value['raw_data']->s3_zip; ?>
                                        </div>
                                        <div class="money-data">
                                            <div class="amount-info">
                                                <div class="st-1">
                                                    <b>$<?php echo number_format( $value['raw_data']->s4_total_loan_amount_sum, 2 ); ?></b>
                                                </div>
                                                <div class="st-2">@</div>
                                                <div class="st-3">
													<?php echo $value['raw_data']->s4_chosen_rate; ?>%
                                                </div>
                                            </div>
                                            <div class="payment-info">
                                                <div class="st-1">
                                                    $<?php
													echo number_format( $value['raw_data']->s4_chosen_monthly_payment, 2 );
													?>
                                                </div>
                                                <div class="st-2">|</div>
                                                <div class="st-3">
													<?php echo $value['raw_data']->s4_chosen_rate_type; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wcard-footer">
                                            <div class="agent-info">
												<?php
												// Здесь должно быть имя агента, но в данных его нет
												echo "Agent Name"; // Замените на реальное поле, если оно есть
												?>
                                            </div>
                                            <div class="days-info">
												<?php
												echo wpp_time_ago( $value['date'] );
												?>
                                            </div>
                                        </div>
                                    </a>
								<?php endforeach; ?>

                            </div>
                        </div>
						<?php


						foreach ( $array as $item ) {
							if ( ! empty( $item->card_ids ) ) {
								$ids = json_decode( $item->card_ids );


							}
							?>
                        <div class="column" data-column-id="<?php echo $item->id; ?>">
                            <div class="column-header">
                                <div class="colum-header-wrap">
                                    <div class="column-header-left">
                                        <h3 contenteditable="true">
											<?php echo $item->title; ?>
                                        </h3>
                                        <div class="wpp-loan-count">
                                            <spam>loans</spam>
                                            <b>0</b></div>
                                    </div>
                                    <div class="column-header-right">
                                        <div class="wpp-nit"><span>nit. Adv.:</span><b></b></div>
                                        <div class="wpp-total"><span>Total:</span><b></b></div>
                                    </div>

                                </div>
                                <button class="delete-column" title="Remove Column"></button>
                            </div>
                            <div class="cards-container ui-sortable">
								<?php foreach ( $ids as $key ) :
									$value = $data[ $key ];
									?>
                                    <a href="<?php echo get_home_url(); ?>/manager-dashboard/loan/<?php echo $key ?>" class="card"
                                       data-card-id="<?php echo $key ?>">
                                        <div class="card-content" contenteditable="true">
                                            <div class="h-top">
                                                #<?php echo $key; ?> | <?php echo $value['raw_data']->s2_entity_name; ?>
                                            </div>
                                        </div>
                                        <div class="address">
											<?php echo $value['raw_data']->s3_address_line_1; ?>
											<?php if ( ! empty( $value['raw_data']->s3_address_line_2 ) ) {
												echo ', ' . $value['raw_data']->s3_address_line_2;
											} ?><br>
											<?php echo $value['raw_data']->s3_city; ?>
                                            , <?php echo $value['raw_data']->s3_state; ?>
                                            , <?php echo $value['raw_data']->s3_zip; ?>
                                        </div>
                                        <div class="money-data">
                                            <div class="amount-info">
                                                <div class="st-1">
                                                    <b>$<?php echo number_format( $value['raw_data']->s4_total_loan_amount_sum, 2 ); ?></b>
                                                </div>
                                                <div class="st-2">@</div>
                                                <div class="st-3">
													<?php echo $value['raw_data']->s4_chosen_rate; ?>%
                                                </div>
                                            </div>
                                            <div class="payment-info">
                                                <div class="st-1">
                                                    $<?php
													echo number_format( $value['raw_data']->s4_chosen_monthly_payment, 2 );
													?>
                                                </div>
                                                <div class="st-2">|</div>
                                                <div class="st-3">
													<?php echo $value['raw_data']->s4_chosen_rate_type; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wcard-footer">
                                            <div class="agent-info">
												<?php
												// Здесь должно быть имя агента, но в данных его нет
												echo "Agent Name"; // Замените на реальное поле, если оно есть
												?>
                                            </div>
                                            <div class="days-info">
												<?php
												echo wpp_time_ago( $value['date'] );
												?>
                                            </div>
                                        </div>
                                    </a>
								<?php endforeach; ?>

                            </div>
                            </div><?php
						}

						?>

                    </div>
                </div>

				<?php
				/**
				 * @hooked
				 */
				do_action( 'wpp_lmp_dash_loan_content' ) ?>
            </main>
        </div>
    </div>

<?php get_footer(); ?>