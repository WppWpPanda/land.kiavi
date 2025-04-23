<?php
class Field_Repeater {
    private $args;
    private $fields;
    private $values;

    public function __construct(array $args = []) {
        $this->args = wp_parse_args($args, [
            'name' => 'repeater_field',
            'label' => 'Повторитель',
            'add_button_text' => 'Добавить',
            'remove_button_text' => 'Удалить',
            'min_rows' => 0,
            'max_rows' => 0,
            'template' => [],
            'value' => []
        ]);

        $this->fields = $this->args['template'];
        $this->values = $this->args['value'];
    }

    public function render(): string {
        $name = esc_attr($this->args['name']);
        $html = '<div class="repeater-field" data-min-rows="'.absint($this->args['min_rows']).'" data-max-rows="'.absint($this->args['max_rows']).'">';
        $html .= '<label>'.esc_html($this->args['label']).'</label>';
        $html .= '<div class="repeater-rows">';

        // Вывод существующих строк
        foreach ($this->values as $i => $row) {
            $html .= $this->render_row($i, $row);
        }

        $html .= '</div>';

        // Кнопка добавления
        $html .= '<button type="button" class="repeater-add-button">'.esc_html($this->args['add_button_text']).'</button>';

        // Шаблон для новых строк (скрыт)
        $html .= '<script type="text/template" class="repeater-template">';
        $html .= $this->render_row('{{row_index}}');
        $html .= '</script>';

        $html .= '</div>';

        return $html;
    }

    private function render_row($index, $values = []) {
        $html = '<div class="repeater-row">';
        $html .= '<div class="repeater-fields">';

        foreach ($this->fields as $field) {
            $field_name = $this->args['name'].'['.$index.']['.esc_attr($field['name']).']';
            $field_value = $values[$field['name']] ?? '';

            $html .= '<div class="repeater-field-item">';
            $html .= '<label>'.esc_html($field['label']).'</label>';

            switch ($field['type']) {
                case 'text':
                    $html .= '<input type="text" name="'.$field_name.'" value="'.esc_attr($field_value).'">';
                    break;
                case 'textarea':
                    $html .= '<textarea name="'.$field_name.'">'.esc_textarea($field_value).'</textarea>';
                    break;
                // Добавьте другие типы полей по необходимости
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '<button type="button" class="repeater-remove-button">'.esc_html($this->args['remove_button_text']).'</button>';
        $html .= '</div>';

        return $html;
    }
}