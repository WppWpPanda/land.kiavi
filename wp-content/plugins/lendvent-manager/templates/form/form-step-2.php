<?php
defined('ABSPATH') || exit;


$formFieldsManager = FormFieldsManager::getInstance();
$formFieldsManager->init();
?>
    <style>
        .wpp-sub-group,
        .wpp-sub-sub-group{
            display: none;
        }
    </style>

<?php


$out = '<div class="wpp-step-form-2">';

$out .= $formFieldsManager->renderField('heading', [
    'type' => 'h2',
    'text' => 'Start a New Loan',
    'wrapper' => false
]);

$out .= '<p>Get started by creating a new entity and guarantor or select from existing profiles.</p>';

$out .= $formFieldsManager->renderField('select', [
    'name' => 'program',
    'label' => 'Borrower Entities',
    'required' => true,
    'class'=>'conditional-select',
    'conditional'=>['#wpp-new-entry','#wpp-guarantor'],
    'placeholder' => 'Search for an Entity',
    'options' => [
        'new-entry' => '-- Create New Entity --',
        'hard_money_infill' => 'John second (Entity) | 23 Loans | Profile created: 03/18/2025',
        'not_sure' => 'John (Entity) | 20 Loans | Profile created: 01/23/2025'
    ]
]);

$out .= '<div id="wpp-new-entry" class="wpp-sub-group" data-show="new-entry">';

$out .= $formFieldsManager->renderField('text', [
    'name' => 'new_entity_name',
    'value' => '',
    'placeholder' => 'Entity Name',
    'label' => false,
    'required' => true
]);

$out .= $formFieldsManager->renderField('select', [
    'name' => 'new_entity_type',
    'label' => false,
    'required' => true,
    'placeholder' => 'Entity Type',
    'options' => [
        'Limited Liability Company',
        'Limited Partnership',
        'Corporation',
        'Statutory Trust',
        'Common Law Trust',
        'Sole Proprietor / Natural Person',
        'General Partnership',
        'Other'
    ],
    'button_style' => 'outline',
    'color' => '#FF5722'
]);


$out .= $formFieldsManager->renderField('heading', [
    'type' => 'h3',
    'text' => 'Experience',
    'wrapper' => false
]);

$out .= '<p>How many flips have been completed under this entity in the last 2 years?</p>';

$out .= $formFieldsManager->renderField('radio', [
    'name' => 'exits_last_24',
    'value' => '',
    'options' => [
        'none' => 'None',
        '1-4' => '1-4 properties',
        '5+' => '5 or more properties',
    ],
    'label' => false,
    'required' => false,
    'inline' => false,
]);
$out .= '</div>';// end id="wpp-new-entry";

$out .= '<div id="wpp-guarantor" class="wpp-sub-sub-group" data-show="all">';

$out .= $formFieldsManager->renderField('heading', [
    'type' => 'h3',
    'text' => 'Guarantor',
    'wrapper' => false
]);

$out .= '<p>If you plan to have no guarantor for the loan, please select a credit qualifying individual from this selected entity.</p>';
$out .= '<p>You can choose your personal guarantee status for this loan on the rate calculator.</p>';



$out .= $formFieldsManager->renderField('select', [
    'name' => 'guarantor',
    'label' => false,
    'required' => true,
    'placeholder' => 'Search for an Guarantor',
    'options' => [
        'hard_money_infill' => 'John second (Entity) | 23 Loans | Profile created: 03/18/2025',
        'not_sure' => 'John (Entity) | 20 Loans | Profile created: 01/23/2025'
    ]
]);



$out .= $formFieldsManager->renderField('checkbox', [
    'name' => 'search_all_profiles',
    'value' => [],
    'option_label' => 'Search All Profiles', // Метка для одиночного чекбокса
    'required' => false,
    'inline' => false,
    'single' => true, // Одиночный чекбокс
]);

//$out .= '<div class="wpp-sub-sub-group" data-show="search_all_profiles_none" style="display:block;">';

$out .= $formFieldsManager->renderField('text', [
    'name' => 'start_new_loan_first_name',
    'value' => '',
    'placeholder' => 'First Name',
    'label' => false,
    'required' => true
]);

$out .= $formFieldsManager->renderField('text', [
    'name' => 'start_new_loan_last_name',
    'value' => '',
    'placeholder' => 'Last Name',
    'label' => false,
    'required' => true
]);

$out .= $formFieldsManager->renderField('select', [
    'name' => 'start_new_loan_suffix',
    'label' => false,
    'required' => true,
    'placeholder' => 'Suffix',
    'options' => [
        'mr' => 'Mr',
        'mrs' => 'Mrs',
        'dr' => 'Dr',
        'jr' => 'Jr',
        '1' => 'I',
        '2' => 'II',
        '3' => 'III'
    ],
    'button_style' => 'outline',
    'color' => '#FF5722'
]);

//$out .= '</div>';// end class="wpp-sub-sub-group" data-show="search_all_profiles_none";

$out .= '</div>';// end  class="wpp-sub-group"


$out .= '</div>'; //end  class="wpp-step-form-1"


echo $out;