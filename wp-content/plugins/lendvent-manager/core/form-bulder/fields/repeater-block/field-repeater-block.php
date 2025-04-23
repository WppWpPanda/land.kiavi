<?php
class Field_Repeater_Block {
    private $args;
    private $formFieldsManager;

    public function __construct(array $args = [], FormFieldsManager $formFieldsManager = null) {
        $defaults = [
            'name' => 'repeater_block',
            'label' => 'Повторяющиеся блоки',
            'add_button_text' => 'Добавить блок',
            'remove_button_text' => 'Удалить',
            'min_blocks' => 0,
            'max_blocks' => 0,
            'fields' => [],
            'values' => []
        ];

        $this->args = wp_parse_args($args, $defaults);
        $this->formFieldsManager = $formFieldsManager ?? FormFieldsManager::getInstance();
    }

    public function render(): string {
        $html = '<div class="repeater-block-field" 
                data-name="'.esc_attr($this->args['name']).'" 
                data-min="'.absint($this->args['min_blocks']).'" 
                data-max="'.absint($this->args['max_blocks']).'">';

        $html .= '<label class="repeater-block-label">'.esc_html($this->args['label']).'</label>';
        $html .= '<div class="repeater-block-rows">';

        // Рендер существующих блоков
        foreach ($this->args['values'] as $index => $values) {
            $html .= $this->render_block($index, $values);
        }

        $html .= '</div>';

        // Кнопка добавления
        $html .= '<button type="button" class="repeater-block-add">'.esc_html($this->args['add_button_text']).'</button>';

        // Шаблон нового блока
        $html .= '<script type="text/template" class="repeater-block-template">';
        $html .= $this->render_block('{{index}}');
        $html .= '</script>';

        $html .= '</div>';

        return $html;
    }

    private function render_block($index, $values = []) {
        $html = '<div class="repeater-block-row">';
        $html .= '<div class="repeater-block-fields">';

        foreach ($this->args['fields'] as $field) {
            $field['args']['name'] = $this->args['name'].'['.$index.']['.$field['name'].']';

            if (isset($values[$field['name']])) {
                $field['args']['value'] = $values[$field['name']];
            }

            $html .= $this->formFieldsManager->renderField($field['type'], $field['args']);
        }

        $html .= '</div>';
        $html .= '<button type="button" class="repeater-block-remove">'.esc_html($this->args['remove_button_text']).'</button>';
        $html .= '</div>';

        return $html;
    }
}