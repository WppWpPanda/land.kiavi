<?php
/*
Plugin Name: Lendvent Manager
*/

require_once 'core/init.php';



function _wpp_loan_form()
{
    ob_start();
    require_once 'templates/form/form-step-2.php';
   return ob_get_clean();
}

add_shortcode('wpp_loan_form', '_wpp_loan_form');