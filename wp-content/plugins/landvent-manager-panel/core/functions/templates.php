<?php
defined( 'ABSPATH' ) || exit;

function landvent_manager_header_main() {
	get_header(); ?>
	<div class="container-fluid">
	<div class="row">

	<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
		<div class="wpp-iside">
			<?php do_action( 'wpp_lmp_nav_side' ) ?>
		</div>
	</nav>

	<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
<?php }


function landvent_manager_footer_main() { ?>
	</main>
        </div>
    </div>

<?php get_footer();
}