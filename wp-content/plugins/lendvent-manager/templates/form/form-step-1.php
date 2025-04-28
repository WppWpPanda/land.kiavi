<?php
defined('ABSPATH') || exit;

$notes_content = '<p><b>A New Construction project at Landvent could be:</b></p><ul><li>A vacant lot w/ ground-up development</li><li>An addition to existing improvements with an increase of gross living area greater than 100% of the existing square footage</li><li>Structural replacement of the roof and more than one exterior wall while retaining existing foundation</li><li>Structural replacement of the roof and all interior framing</li></ul>';

$formFieldsManager = FormFieldsManager::getInstance();
$formFieldsManager->init();


$out = '<div class="wpp-step-form-1">';

$out .= $formFieldsManager->renderField('heading', [
    'type' => 'h2',
    'text' => 'What kind of real estate investment are you considering?',
    'wrapper' => false
]);

$out .= '<div class="wpp-line-buttons">';

$out .= $formFieldsManager->renderField('radio-buttons', [
    'name' => 'program',
    'label' => '',
    'options' => [
        'hard_money' => 'Bridge / Fix and Flip / Fix to Rent',
        'hard_money_infill' => 'New Construction *',
        'not_sure' => 'I\'m not sure yet'
    ],
    'button_style' => 'outline',
    'color' => '#FF5722'
]);

$out .= '</div>'; // end class="wpp-line-buttons"

$out .= $formFieldsManager->renderField('accordion', [
    'icon' => false,
    'icon_open' => false,
    'title' => '* What is a New Construction deal at Landvent?',
    'content' => $notes_content,
    'wrapper_class'=>'wpp-transparent',
    'is_open' => false
]);

$out .= '</div>'; //end  class="wpp-step-form-1"


echo $out;