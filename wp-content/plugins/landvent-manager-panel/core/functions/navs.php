<?php
defined( 'ABSPATH' ) || exit;

/**
 * Renders the manager dashboard sidebar menu with active item highlighting
 *
 * @since 1.0.0
 */
function lmp_render_sidebar_menu() {
	$current_endpoint = wpp_is_manager_dashboard();

	if ( ! $current_endpoint ) {
		return;
	}

	$base_url             = home_url( '/manager-dashboard/' );
	$menu_items           = [];
	$current_loan_section = '';

	// Handle loan page anchor detection
	if ( $current_endpoint === 'loan' ) {
		$current_loan_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : 'title-company';
	}

	// Build menu items based on current endpoint
	if ( $current_endpoint !== 'loan' ) {
		$main_menu_items = [
			'main'             => [ 'title' => 'Home', 'icon' => 'home' ],
			'law-firms-clerks' => [ 'title' => 'Law Firms & Clerks', 'icon' => 'users' ],
			'title-companies'  => [ 'title' => 'Title Companies', 'icon' => 'building' ],
			'brokers'          => [ 'title' => 'Brokers', 'icon' => 'exchange-alt' ],
			'appraisers'       => [ 'title' => 'Appraisers', 'icon' => 'search-dollar' ],
		];

		foreach ( $main_menu_items as $slug => $item ) {

			$menu_items[] = [
				'title'  => $item['title'],
				'icon'   => $item['icon'],
				'url'    => $slug === 'main' ? $base_url : $base_url . $slug,
				'active' => $current_endpoint === $slug
			];


			/*  if( empty($slug) && 'main' === $current_endpoint ) {
				  $menu_items['active'] = true;
			  }*/
		}
	} else {
		$loan_menu_items = [
			'applicant-info'      => [ 'title' => 'Applicant Info' ],
			'property-details'    => [ 'title' => 'Property Details' ],
			'term-sheet-details'  => [ 'title' => 'Term Sheet Details' ],
			'additional-reserves' => [ 'title' => 'Additional Reserves' ],
			'fees'                => [ 'title' => 'Fees' ],
			'milestones'          => [ 'title' => 'Milestones' ],
			'payments'            => [ 'title' => 'Payments' ],
			'conditions'          => [ 'title' => 'Conditions' ],
			'investors'           => [ 'title' => 'Investors' ],
			'attorney'            => [ 'title' => 'Attorney' ],
			'title-company'       => [ 'title' => 'Title Company' ],
			'required-documents'  => [ 'title' => 'Required Documents' ],
			'documents'           => [ 'title' => 'Documents' ],
		];

		foreach ( $loan_menu_items as $slug => $item ) {
			$menu_items[] = [
				'title'  => $item['title'],
				'url'    => '#' . $slug,
				'id'     => 'menu-' . $slug,
				'active' => $current_loan_section === $slug
			];
		}
	}

	// Render the menu
	if ( ! empty( $menu_items ) ) {
		echo '<ul class="nav flex-column">';
		foreach ( $menu_items as $item ) {
			$active_class = $item['active'] ? ' active current-point' : '';
			$icon_html    = isset( $item['icon'] ) ? '<i class="fas fa-' . esc_attr( $item['icon'] ) . ' me-2"></i>' : '';
			?>
            <li class="nav-item">
                <a class="nav-link<?php echo esc_attr( $active_class ); ?>"
                   href="<?php echo esc_url( $item['url'] ); ?>"
					<?php echo isset( $item['id'] ) ? 'id="' . esc_attr( $item['id'] ) . '"' : ''; ?>>
					<?php echo $icon_html . esc_html( $item['title'] ); ?>
                </a>
            </li>
			<?php
		}
		echo '</ul>';
	}
}

add_action( 'wpp_lmp_nav_side', 'lmp_render_sidebar_menu' );

