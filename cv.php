<?
add_action('init', 'my_nav_menus');
add_action( 'init', 'disable_emojis' );
add_action('wp_enqueue_scripts', 'jquery_init');
// Регистрируем топ меню
function my_nav_menus() {
	register_nav_menus(array(
		'nav-menu' => 'Header Menu',
		'footer-menu' => 'Footer Menu',
		'footer-menu1' => 'Темы',
		'footer-menu2' => 'Рубрики',
		'footer-menu3' => 'Интересное',
	));
}
function disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}
function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}
function jquery_init() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('slick', get_template_directory_uri() . '/js/slick.js');
		wp_enqueue_script('nicescroll', get_template_directory_uri() . '/js/jquery.nicescroll.min.js');
		wp_enqueue_script('prettyphoto', get_template_directory_uri() . '/js/jquery.prettyPhoto.js');
		wp_enqueue_script('smooth', get_template_directory_uri() . '/js/smoothscroll.js');
	}
}
function adn_post_view_counter($postID) {
	$count_key = 'view_counter';
	$count = get_post_meta($postID, $count_key, true);
	if($count==''){
		$count = 0;
		delete_post_meta($postID, $count_key);
		add_post_meta($postID, $count_key, '0');
	}else{
		$count++;
		update_post_meta($postID, $count_key, $count);
	}
}
function kama_drussify_months( $date, $req_format ){
	// в формате есть "строковые" неделя или месяц
	if( ! preg_match('~[FMlS]~', $req_format ) ) return $date;

	$replace = array (
		"январь" => "января", "Февраль" => "февраля", "Март" => "марта", "Апрель" => "апреля", "Май" => "мая", "Июнь" => "июня", "Июль" => "июля", "Август" => "августа", "Сентябрь" => "сентября", "Октябрь" => "октября", "Ноябрь" => "ноября", "Декабрь" => "декабря",

		"January" => "января", "February" => "февраля", "March" => "марта", "April" => "апреля", "May" => "мая", "June" => "июня", "July" => "июля", "August" => "августа", "September" => "сентября", "October" => "октября", "November" => "ноября", "December" => "декабря",

		"Jan" => "янв.", "Feb" => "фев.", "Mar" => "март.", "Apr" => "апр.", "May" => "мая", "Jun" => "июня", "Jul" => "июля", "Aug" => "авг.", "Sep" => "сен.", "Oct" => "окт.", "Nov" => "нояб.", "Dec" => "дек.",

		"Sunday" => "воскресенье", "Monday" => "понедельник", "Tuesday" => "вторник", "Wednesday" => "среда", "Thursday" => "четверг", "Friday" => "пятница", "Saturday" => "суббота",

		"Sun" => "вос.", "Mon" => "пон.", "Tue" => "вт.", "Wed" => "ср.", "Thu" => "чет.", "Fri" => "пят.", "Sat" => "суб.", "th" => "", "st" => "", "nd" => "", "rd" => "",
	);

	return strtr( $date, $replace );
}
function rustime($format = 'd/m/Y', $time) {
	$replace = array (
		"январь" => "января", "Февраль" => "февраля", "Март" => "марта", "Апрель" => "апреля", "Май" => "мая", "Июнь" => "июня", "Июль" => "июля", "Август" => "августа", "Сентябрь" => "сентября", "Октябрь" => "октября", "Ноябрь" => "ноября", "Декабрь" => "декабря",

		"January" => "января", "February" => "февраля", "March" => "марта", "April" => "апреля", "May" => "мая", "June" => "июня", "July" => "июля", "August" => "августа", "September" => "сентября", "October" => "октября", "November" => "ноября", "December" => "декабря",

		"Jan" => "янв.", "Feb" => "фев.", "Mar" => "март.", "Apr" => "апр.", "May" => "мая", "Jun" => "июня", "Jul" => "июля", "Aug" => "авг.", "Sep" => "сен.", "Oct" => "окт.", "Nov" => "нояб.", "Dec" => "дек.",

		"Sunday" => "воскресенье", "Monday" => "понедельник", "Tuesday" => "вторник", "Wednesday" => "среда", "Thursday" => "четверг", "Friday" => "пятница", "Saturday" => "суббота",

		"Sun" => "вс.", "Mon" => "пн.", "Tue" => "вт.", "Wed" => "ср.", "Thu" => "чт.", "Fri" => "пт.", "Sat" => "суб.", "th" => "", "st" => "", "nd" => "", "rd" => "",
	);
	return strtr( date($format, $time), $replace );
}

function monday($offset = "0") {
	if (rustime('l', time())== 'понедельник') {
		return strtotime("now ".$offset." day");
	}
	return strtotime("last Monday ".$offset." day");
}

add_filter('date_i18n', 'kama_drussify_months', 11, 2);

add_action( 'restrict_manage_posts', 'wpse45436_admin_posts_filter_restrict_manage_posts' );
/**
 * First create the dropdown
 * make sure to change POST_TYPE to the name of your custom post type
 *
 * @author Ohad Raz
 *
 * @return void
 */
function wpse45436_admin_posts_filter_restrict_manage_posts(){
	$type = 'tv';
	if (isset($_GET['post_type'])) {
		$type = $_GET['post_type'];
	}

	//only add filter to post type you want
	if ('tv' == $type){?>
		<input type="text" placeholder="Дата выпуска" name="day" value="<?= isset($_GET['day'])? $_GET['day']:''?>">
		<?php
	}
}

add_filter( 'parse_query', 'wpse45436_posts_filter' );
/**
 * if submitted filter by post meta
 *
 * make sure to change META_KEY to the actual meta key
 * and POST_TYPE to the name of your custom post type
 * @author Ohad Raz
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function wpse45436_posts_filter( $query ){
	global $pagenow;
	$type = 'tv';
	if (isset($_GET['post_type'])) {
		$type = $_GET['post_type'];
	}
	if ( 'tv' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['day']) && $_GET['day'] != '') {
		// $query->query_vars['meta_key'] = 'day';
		$query->query_vars['meta_value'] = $_GET['day'];
		$query->query_vars['day'] = $_GET['day'];
		$query->query_vars['meta_compare'] = '=';
	}
	// var_dump($query);
	// die();
}

function my_enqueue($hook) {
	if ( 'edit.php' != $hook ) {
		return;
	}
	// wp_enqueue_script('jquery');
	wp_enqueue_script( 'datepicker', get_template_directory_uri() . '/js/datepicker.min.js' );
	wp_enqueue_script( 'calendarinit', get_template_directory_uri() . '/js/admincalendar.js' );
	wp_enqueue_style( 'prefix-style', get_template_directory_uri() . '/css/datepicker.min.css' );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue' );
add_theme_support( 'post-thumbnails' );
?>