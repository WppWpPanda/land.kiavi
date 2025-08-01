<?php
defined( 'ABSPATH' ) || exit;

function lmp_render_sidebar_menu() {

	if ( is_singular( 'page' ) ) {
		global $post;
		$current_template = get_post_meta( $post->ID, '_wp_page_template', true );


		if ( $current_template === 'dashboard-template.php' ) {
			$menu_items = array(
				//array( 'title' => 'Tasks', 'icon' => 'tasks', 'url' => '#' ),
				//array( 'title' => 'Terminated', 'icon' => 'ban', 'url' => '#' ),
				array( 'title' => 'Law Firms & Clerks', 'icon' => 'users', 'url' => '#' ),
				array( 'title' => 'Title Companies', 'icon' => 'building', 'url' => '#' ),
				array( 'title' => 'Brokers', 'icon' => 'exchange-alt', 'url' => '#' ),
				array( 'title' => 'Appraisers', 'icon' => 'search-dollar', 'url' => '#' ),
				//array( 'title' => 'Reports', 'icon' => 'chart-bar', 'url' => '#', 'badge' => 'New' ),
				//array( 'title' => 'Servicing', 'icon' => 'cog', 'url' => '#' ),
				//array( 'title' => 'Map', 'icon' => 'map-marked-alt', 'url' => '#' ),
			);
		} elseif ( $current_template === 'single-loan.php' ) {
			$menu_items = array(
				array(
					'title' => 'Applicant Info',
					'url'   => '#applicant-info',
					'id'    => 'menu-applicant-info',
				),
				array(
					'title' => 'Property Details',
					'url'   => '#property-details',
					'id'    => 'menu-property-details',
				),
				array(
					'title' => 'Term Sheet Details',
					'url'   => '#term-sheet-details',
					'id'    => 'menu-term-sheet-details',
				),
				array(
					'title' => 'Additional Reserves',
					'url'   => '#additional-reserves',
					'id'    => 'menu-additional-reserves',
				),
				array(
					'title' => 'Fees',
					'url'   => '#fees',
					'id'    => 'menu-fees',
				),
				array(
					'title' => 'Milestones',
					'url'   => '#milestones',
					'id'    => 'menu-milestones',
				),
				array(
					'title' => 'Payments',
					'url'   => '#payments',
					'id'    => 'menu-payments',
				),
				array(
					'title' => 'Conditions',
					'url'   => '#conditions',
					'id'    => 'menu-conditions',
				),
				array(
					'title' => 'Investors',
					'url'   => '#investors',
					'id'    => 'menu-investors',
				),
				array(
					'title' => 'Attorney',
					'url'   => '#attorney',
					'id'    => 'menu-attorney',
				),
				array(
					'title'  => 'Title Company',
					'url'    => '#title-company',
					'id'     => 'menu-title-company',
					'active' => true,
				),
				array(
					'title' => 'Required Documents',
					'url'   => '#required-documents',
					'id'    => 'menu-required-documents',
				),
				array(
					'title' => 'Documents',
					'url'   => '#documents',
					'id'    => 'menu-documents',
				),
			);
		}

		if ( ! empty( $menu_items ) ) {
			echo '<ul class="nav flex-column">';
			foreach ( $menu_items as $item ) {
				$active_class = isset( $item['active'] ) ? ' active' : '';
				$badge_html   = isset( $item['badge'] ) ? '<span class="badge bg-success text-white ms-2">' . esc_html( $item['badge'] ) . '</span>' : '';
				?>
                <li class="nav-item">
                    <a class="nav-link<?php echo $active_class; ?>" href="<?php echo esc_url( $item['url'] ); ?>">
                        <i class="fas fa-<?php echo esc_attr( $item['icon'] ) ?? ''; ?>"></i>
						<?php echo esc_html( $item['title'] ); ?>
						<?php echo $badge_html; ?>
                    </a>
                </li>
				<?php
			}
			echo '</ul>';
		}
	}

}

add_action( 'wpp_lmp_nav_side', 'lmp_render_sidebar_menu' );